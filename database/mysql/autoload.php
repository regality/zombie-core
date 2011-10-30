<?php
/**
 * @package Database
 * @subpackage mysql
 * @ignore
 */

function autoloadMysqlClass($class) {
   $classes = array('MysqlDelete', 'MysqlException', 'MysqlInsert',
                    'MysqlQuery', 'MysqlReplace', 'MysqlResult',
                    'MysqlSelect', 'MysqlUpdate');
   if (in_array($class, $classes)) {
      include(__DIR__ . '/' . $class . ".php");
   }
}

spl_autoload_register('autoloadMysqlClass');

?>
