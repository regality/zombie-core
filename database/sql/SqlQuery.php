<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage sql
 */

/**
 * Pass to exec or query to debug the query.
 */
define("SQL_DEBUG", true);

require_once(__DIR__ . "/../../../config/config.php");
require_once(__DIR__ . "/../../util/util.php");
require_once(__DIR__ . "/../../util/cipher.php");
require_once(__DIR__ . "/../../util/purify.php");

/**
 * Base class for sql query classes.
 * Provides functionality for adding syntax rules.
 */
abstract class SqlQuery {
   /**
    * Execute a query and return the number of rows effected.
    * @param boolean $debug prints the executed query if true
    * @return integer|boolean
    */
   abstract public function exec($debug = false);

   /**
    * Execute a query and return the results.
    * @param boolean $debug prints the executed query if true
    * @return SqlResult|boolean
    */
   abstract public function query($debug = false);

   /**
    * Checks if a string begins with another string.
    * @ignore
    */
   protected function beginsWith($str, $sub) {
      $strsub = substr(trim($str), 0, strlen($sub));
      return strcasecmp($strsub, $sub) == 0;
   }

   /**
    * Adds syntax.
    * @ignore
    */
   protected function syntax($clause, $sql, $params, $alt = ', ') {
      $str = &$this->$clause;
      $clause = strtoupper(str_replace('_', ' ', $clause));
      $this->addSqlClause($str, $sql, $clause, $alt);
      $this->addParams($params);
      return $this;
   }

   /**
    * Add to an sql clause.
    * @ignore
    */
   protected function addSqlClause(&$add_to, $str, $begin, $alt = ", ") {
      if (!empty($str)) {
         if (empty($add_to) && !$this->beginsWith($str, $begin)) {
            $add_to = $begin . ' ';
         } else if (!empty($add_to)) {
            $add_to .= $alt;
         }
         $add_to .= $str;
      }
   }

}

?>
