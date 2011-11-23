<?php
/**
 * @package Database
 * @subpackage couch
 * @ignore
 */

function autoloadCouch($class) {
   $classes = array('CouchDB', 'CouchResult');
   if (in_array($class, $classes)) {
      include(__DIR__ . '/' . $class . ".php");
   }
}

spl_autoload_register('autoloadCouch');

?>
