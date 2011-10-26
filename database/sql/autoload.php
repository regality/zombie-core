<?php

function autoloadSqlClass($class) {
   $classes = array('SqlQuery', 'SqlResult');
   if (in_array($class, $classes)) {
      include(__DIR__ . '/' . $class . ".php");
   }
}

spl_autoload_register('autoloadSqlClass');

?>
