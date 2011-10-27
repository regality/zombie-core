<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . "/MysqlException.php");

class MysqlQuery extends SqlQuery {
   protected $query;
   protected $params = array();
   protected $param_count = 0;
   protected $magic_quotes_on = false;
   protected static $db = null;

   public function __construct($query = '', $connector = 'mysql') {
      if (MysqlQuery::$db == null) {
         $config = getZombieConfig();
         MysqlQuery::$db = mysql_connect($config[$connector]['host'],
                                         $config[$connector]['user'],
                                         $config[$connector]['pass']);
         mysql_select_db($config[$connector]['database'], MysqlQuery::$db);
      }
      if (get_magic_quotes_gpc()) {
         $this->magic_quotes_on = true;
      }
      $this->query = $query;
   }

   public function __toString() {
      return $this->getQueryString();
   }

   public function getMysqlResult($query, $debug = false) {
      if ($debug) {
         trigger_error("Query debug:" . $query, E_USER_NOTICE);
      }
      $result = mysql_query($query, MysqlQuery::$db);
      $errno = mysql_errno();
      if ($errno != 0) {
         $error = "Mysql Error: " . mysql_error();
         $exception_class = getMysqlExceptionClassName($errno);
         throw new $exception_class($error);
      }
      return $result;
   }

   public function exec($debug = false) {
      $bound_query = $this->getBoundQuery();
      $result = $this->getMysqlResult($bound_query, $debug);
      return mysql_affected_rows(MysqlQuery::$db);
   }

   public function query($debug = false) {
      $bound_query = $this->getBoundQuery();
      $result = $this->getMysqlResult($bound_query, $debug);
      return new MysqlResult($result);
   }

   public function getQueryString() {
      return $this->query;
   }

   protected function getBoundQuery() {
      $query = $this->getQueryString();
      $qlen = strlen($query);
      $bound_query = ''; 
      for ($i = 0; $i < $qlen; ++$i) {
         $char = $query[$i];
         if ($char == '$') {
            $key = ''; 
            while ($i < ($qlen - 1) && is_numeric($query[$i + 1])) {
               ++$i;
               $key .= $query[$i];
            }   
            $key = intval($key) - 1;
            if (isset($this->params[$key])) {
               $bound_query .= $this->params[$key];
            } else {
               $error = "Wrong number of params in query: " .  $query;
               throw new MysqlParamCountException($error);
            }
         } else {
            $bound_query .= $query[$i];
         }   
      }   
      return $bound_query;
   }   

   public function addParams($params) {
      foreach ($params as $param) {
         $this->addParam($param);
      }
      return $this;
   }

   public function addParam($value, $type = null) {
      $this->params[$this->param_count] = $this->sanitize($value, $type);
      $this->param_count += 1;
      return $this;
   }

   public function sanitize($value, $type = null) {
      if (is_null($type)) {
         $type = '';
      }
      if (is_string($value)) {
         if ($this->magic_quotes_on) {
            $value = stripslashes($value);
         }
         if ($type == "html") {
            $value = purifyHtml($value);
         } else if ($type == "secure") {
            $value = encrypt($value);
         } else if ($type != "raw") {
            $value = htmlentities($value);
         }
         $value = "'" . mysql_real_escape_string($value) . "'";
      } else if (is_numeric($value)) {
         $value = (string)$value;
      } else if (is_bool($value)) {
         $value = (string)(int)$value;
      } else if (is_null($value)) {
         $value = "NULL";
      } else /* array, object, or unknown */ {
         $value = "'" . mysql_real_escape_string(serialize($value)) . "'";
      }
      return $value;
   }

   public function begin() {
      $this->getMysqlResult("SET autocommit = 0");
      $this->getMysqlResult("START TRANSACTION");
   }

   public function rollback() {
      $this->getMysqlResult("ROLLBACK");
   }

   public function commit() {
      $this->getMysqlResult("COMMIT");
   }

   public function lastInsertId() {
      return mysql_insert_id();
   }

   public function describe($table) {
     $query = "DESCRIBE $table";
     $result = $this->getMysqlResult($query);
     return new MysqlResult($result);
   }

}

?>
