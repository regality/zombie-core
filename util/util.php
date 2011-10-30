<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Util
 */

/**
 * Convert camel case to underscore separated.
 * @param string $class
 * @return string
 */
function classToUnderscore($class) {
   // AppName => app_name
   $underscore = preg_replace('/([A-Z])/', '_$1', $class);
   $underscore = strtolower($underscore);
   $underscore = trim($underscore, '_');
   return $underscore;
}

/**
 * Takes underscore separated words and convert
 * them to camel case with uppercase first letter.
 * @param string $underscore
 * @return string
 */
function underscoreToClass($underscore) {
   // app_name => AppName
   $class = str_replace('_', ' ', $underscore);
   $class = str_replace('/', '/ ', $class);
   $class = ucwords($class);
   $class = str_replace(' ', '', $class);
   return $class;
}

/**
 * Takes underscore separated words and convert
 * them to camel case with lowercase first letter.
 * @param string $underscore
 * @return string
 */
function underscoreToMethod($underscore) {
   // app_name => AppName
   $method = str_replace('_', ' ', $underscore);
   $method = str_replace('/', '/ ', $method);
   $method = ucwords($method);
   $method = str_replace(' ', '', $method);
   $method[0] = strtolower($method[0]);
   return $method;
}

?>
