<?php
/**
 * AutoScale Amazon DynamoDB
 *
 * 設定ファイルをパースして2次元配列に格納する
 * @link        http://d.hatena.ne.jp/kanehama/20091110/1257863009
 *
 * @copyright   2014 Cyber Area Research,Inc.
 * @version     1.0
 * @last update 2014-10-24
 */

class parseInit2Array {
   /**
    * parse init file into array
    *
    * @var       array $ini    parse_ini_file後のarray
    * @return    array         設定を解釈した多次元配列　
    *
    * @access    public
    */
    public function parse( $ini ) {
        $setting = array();
        foreach ($ini as $section => $properties) {
            foreach ($properties as $key => $value) {
                $keys = explode('.', $section . '.' . $key);
                $setting = self::add_init_param($keys, $value, $setting);
            }
        }
        return $setting;
    }

   /**
    * add an init key&value into array
    *
    * @var       string $keys     設定キー
    * @var       string $value    設定値  
    * @var       array  $retval   多次元配列
    * @return    array            設定を解釈した多次元配列　
    *
    * @access    private
    */
    private function add_init_param($keys, $value, $retval) {
        $key = array_shift($keys);
        if (!isset($retval[$key])) {
            $retval[$key] = array();
        }
        if (count($keys) > 0) {
            $retval[$key] = self::add_init_param($keys, $value, $retval[$key]);
        } else {
            $retval[$key] = $value;
        }
        return $retval;
    }
}
?>
