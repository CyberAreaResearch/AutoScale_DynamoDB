#!/usr/bin/php
<?php
/**
 * AutoScale Amazon DynamoDB
 * Amazon DynamoDBのスループットを自動的にスケーリングするためのPHPスクリプト
 *
 * @copyright   2014 Cyber Area Research,Inc.
 * @author      Ken Nakanishi <ken@arearesearch.co.jp>
 * @license     MIT License
 * @version     1.0
 * @last update 2014-10-24
*/

include("ParseInit2Array.class.php");
include("DynamoDB_Monitor.class.php");

$ini = parse_ini_file("autoscale.ini",true);
$ini = ParseInit2Array::parse( $ini );
define('AWS_ACCESS_KEY_ID', $ini['global']['AWS_ACCESS_KEY_ID']);
define('AWS_SECRET_ACCESS_KEY', $ini['global']['AWS_SECRET_ACCESS_KEY']);
define('AWS_REGION_NAME', $ini['global']['AWS_REGION_NAME']);
$dynamo = new DynamoDB_Monitor();
$v = $ini['tables'];

foreach( array_keys($v) as $tableName ) {
    $v[$tableName]['Read']['counterLowThreshold'] = 0;
	$v[$tableName]['Write']['counterLowThreshold'] = 0;
}

while( 1==1 ) {
	foreach( array_keys($v) as $tableName ) {
		$v[$tableName]['Read']['Current'] = $dynamo->getreadCapacityUnits( $tableName );
		$v[$tableName]['Read']['Consumed'] = $dynamo->getConsumedReadCapacityUnits( $tableName );
		$v[$tableName]['Write']['Current'] = $dynamo->getwriteCapacityUnits( $tableName );
		$v[$tableName]['Write']['Consumed'] = $dynamo->getConsumedWriteCapacityUnits( $tableName );

		foreach( array("Read", "Write") as $rw ) {
			$v[$tableName][$rw]['Target'] = $v[$tableName][$rw]['Current'];

			# check CapacityUnits
			if($v[$tableName][$rw]['Consumed'] / $v[$tableName][$rw]['Current'] >= 0.8) {
				$v[$tableName][$rw]['Target'] =
				( (int)($v[$tableName][$rw]['Current'] * 1.5) > $v[$tableName][$rw]['Max'] )
				  ? (int)($v[$tableName][$rw]['Max'])
				  : (int)($v[$tableName][$rw]['Current'] * 1.5);
			}

			if($v[$tableName][$rw]['Consumed'] / $v[$tableName][$rw]['Current'] <= 0.25) {
				$v[$tableName][$rw]['counterLowThreshold']++;
				if($v[$tableName][$rw]['counterLowThreshold'] == 24) {
					$v[$tableName][$rw]['Target'] =
						( (int)($v[$tableName][$rw]['Consumed'] * 3) <= $v[$tableName][$rw]['Min'] )
						  ? (int)($v[$tableName][$rw]['Min'])
						  : (int)($v[$tableName][$rw]['Consumed'] * 3);  // target =
					$v[$tableName][$rw]['counterLowThreshold'] = 0;
				}
			} else {
				// reset counter
				$v[$tableName][$rw]['counterLowThreshold'] = 0;
			}
		} // loop read/write

		if( ( $v[$tableName]['Read']['Target'] <> $v[$tableName]['Read']['Current'] ) or
			( $v[$tableName]['Write']['Target'] <> $v[$tableName]['Write']['Current'] ) ) {
			$dynamo->setCapacityUnits($tableName, $v[$tableName]['Read']['Target'], $v[$tableName]['Write']['Target']);
		}
	} // loop for each tables
	print_r( $v );
	sleep( 300 );
}
