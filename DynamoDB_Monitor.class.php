<?php
/**
 * AutoScale Amazon DynamoDB
 *
 * Class for control Capacity Unit parameters in Amazon DymanoDB
 * CapacityUnitの上げ下げに関する機能を実装しています
 * データの送信受信に関しては別クラスで実装しています
 *
 * @copyright   2014 Cyber Area Research,Inc.
 * @version     1.0
 * @last update 2014-10-24
 */

require 'AWSSDKforPHP/aws.phar';
use Aws\Common\Aws;
use Aws\Common\Enum\Region;
use Aws\DynamoDb\Exception\DynamoDbException;

class DynamoDB_Monitor {

  /**
   * DynamoDB接続オブジェクト
   *
   * @var       object
   * @access    public
   */
  public $client;

  /**
   * CloudWatch接続オブジェクト
   *
   * @var       object
   * @access    public
   */
  public $client_cloudwatch;

  /**
   * コンストラクタ
   *
   * @return    object  DynamoDB
   * @access    public
   */
  public function __construct() {
    try {
      $aws = Aws::factory(array(
        'key'    => AWS_ACCESS_KEY_ID,
        'secret' => AWS_SECRET_ACCESS_KEY,
        'region' => AWS_REGION_NAME
      ));

      // Retrieve the DynamoDB client by its short name from the service builder
      $this->client = $aws->get('dynamodb');
      $this->client_cloudwatch = $aws->get('cloudwatch');
    } catch (DynamoDbException $e) {
      echo 'DynamoDB init failed.';
    }
  }

  /**
   * テーブルのReadCapacityUnit値取得
   *
   * @param	string	$_tableName	テーブル名
   * @return	integer	ReadCapacityUnit
   * @access	public
   */
  public function getReadCapacityUnits( $_tableName ) {
    try {
      $result = $this->client->describeTable( array('TableName' => $_tableName) );
      $readCU = $result['Table']['ProvisionedThroughput']['ReadCapacityUnits'];
    } catch (DynamoDbException $e) {
      $readCU = -1;
    }
    return $readCU;
  }

  /**
   * テーブルのWriteCapacityUnit値取得
   *
   * @param	string	$_tableName	テーブル名
   * @return	integer	WriteCapacityUnit
   * @access	public
   */
  public function getWriteCapacityUnits( $_tableName ) {
    try {
      $result = $this->client->describeTable( array('TableName' => $_tableName) );
      $writeCU = $result['Table']['ProvisionedThroughput']['WriteCapacityUnits'];
    } catch (DynamoDbException $e) {
      $writeCU = -1;
    }
    return $writeCU;
  }


  /**
   * テーブルのConsumedReadCapacityUnit値取得
   *
   * @param	string	$_tableName	テーブル名
   * @return	integer	ConsumedReadCapacityUnit
   * @access	public
   */
  public function getConsumedReadCapacityUnits( $_tableName ) {
    try {
      $result = $this->client_cloudwatch->getMetricStatistics( array(
        'Namespace' => 'AWS/DynamoDB',
        'MetricName' => 'ConsumedReadCapacityUnits',
        'Dimensions' => array( array( 'Name'=>'TableName', 'Value'=> $_tableName) ),
        'StartTime' => (string)time() - 750,
        'EndTime' => (string)time(),
        'Period' => '300',
        'Statistics' => array( 'Sum' )
      ));
      $readCU = 1;
      if(array_key_exists('0', $result['Datapoints'])) {
        $readCU = $result['Datapoints']['0']['Sum'] / 300;
      }
    } catch (Exception $e) {
      $readCU = -1;
    }
    return $readCU;
  }

  /**
   * テーブルのConsumedWriteCapacityUnit値取得
   *
   * @param	string	$_tableName	テーブル名
   * @return	integer	ConsumedWriteCapacityUnit
   * @access	public
   */
  public function getConsumedWriteCapacityUnits( $_tableName ) {
    try {
      $result = $this->client_cloudwatch->getMetricStatistics( array(
        'Namespace' => 'AWS/DynamoDB',
        'MetricName' => 'ConsumedWriteCapacityUnits',
        'Dimensions' => array( array( 'Name'=>'TableName', 'Value'=> $_tableName) ),
        'StartTime' => (string)time() - 750,
        'EndTime' => (string)time(),
        'Period' => '300',
        'Statistics' => array( 'Sum' )
      ));
      $writeCU = 1;
      if(array_key_exists('0', $result['Datapoints'])) {
        $writeCU = $result['Datapoints']['0']['Sum'] / 300;
      }
    } catch (Exception $e) {
      $writeCU = -1;
    }
    return $writeCU;
  }

  /**
   * テーブルのReadCapacityUnit,WriteCapacityUnit値を設定
   *
   * @param	string	$_tableName	テーブル名
   * @param	integer	$_readCU	新しいReadCU
   * @param	integer	$_writeCU	新しいWriteCU
   * @return	void
   * @access	public
   */
  public function setCapacityUnits( $_tableName, $_readCU, $_writeCU ) {
    try {
      $result = $this->client->UpdateTable(array(
          'ProvisionedThroughput' => array(
          'ReadCapacityUnits' => $_readCU + 0,
          'WriteCapacityUnits' => $_writeCU + 0
        ),
        'TableName' => "$_tableName"
      ));
      $this->client->waitUntilTableExists(array('TableName' => $_tableName));
    } catch (Exception $e) {
      //特に何もしない
      print "UpdateTable failed.\n";
    }
    return;
  }

# end  of class
}
