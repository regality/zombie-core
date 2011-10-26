<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

define("SQL_DEBUG", true);

require_once(__DIR__ . "/../../../config/config.php");
require_once(__DIR__ . "/../../util/util.php");
require_once(__DIR__ . "/../../util/cipher.php");
require_once(__DIR__ . "/../../util/purify.php");

abstract class SqlQuery {
   abstract public function exec($debug = false);
   abstract public function query($debug = false);

   protected function beginsWith($str, $sub) {
      $strsub = substr(trim($str), 0, strlen($sub));
      return strcasecmp($strsub, $sub) == 0;
   }

   protected function syntax($clause, $sql, $params, $alt = ', ') {
      $str = &$this->$clause;
      $clause = strtoupper(str_replace('_', ' ', $clause));
      $this->addSqlClause($str, $sql, $clause, $alt);
      $this->addParams($params);
      return $this;
   }

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
