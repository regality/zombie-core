<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

class PsqlException extends Exception { }

class PsqlQuery extends SqlQuery {
   private $query;
   private $params = array();
   private $param_count = 0;
   private $magic_quotes_on = false;
   private static $db = null;

   public function __construct($query, $params = array(), $connector = 'mysql') {
      if (PsqlQuery::$db == null) {
         $config = get_zombie_config();
         $connect_str = "host="      . $config['psql']['host'] .
                        " dbname="   . $config['psql']['database'] .
                        " user="     . $config['psql']['user'] .
                        " password=" . $config['psql']['pass'];
         PsqlQuery::$db = pg_pconnect($connect_str);
      }
      if (get_magic_quotes_gpc()) {
         $this->magic_quotes_on = true;
      }
      $this->query = $query;
      $this->addParams($params);
   }

   public function clear() {
      $this->clearParams();
      $this->clearQuery();
   }

   public function clearParams() {
      $this->param_count = 0;
      $this->params = array();
   }

   public function clearQuery() {
      $this->query = null;
   }

   private function getPsqlResult($query, $params, $debug = false) {
      if ($debug) {
         trigger_error("Query debug:" . $query, E_USER_NOTICE);
      }
      $result = pg_query_params(PsqlQuery::$db, $query, $params);
      $error = pg_last_error();
      if (strlen($error) > 0) {
          throw new PsqlException("Psql Error: " . $error);
      }
      return $result;
   }

   public function exec($debug = false) {
      $this->getPsqlResult($this->query, $this->params, $debug);
      return pg_affected_rows();
   }

   public function query($debug = false) {
      $result = $this->getPsqlResult($this->query, $this->params, $debug);
      return new PsqlResult($result);
   }

   public function addParams($params) {
      foreach ($params as $param) {
         $this->addParam($param);
      }
   }

   public function addParam($value, $type = null) {
      $this->params[$this->param_count] = $this->sanitize($value);
      $this->param_count += 1;
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
            $value = Model::purify_html($value);
         } else if ($type != "raw") {
            $value = htmlentities($value);
         }
         $value = "'" . pg_escape_string($value) . "'";
      } else if (is_numeric($value)) {
         $value = (string)$value;
      } else if (is_bool($value)) {
         $value = (string)(int)$value;
      } else if (is_null($value)) {
         $value = "NULL";
      } else /* array, object, or unknown */ {
         $value = "'" . pg_escape_string(serialize($value)) . "'";
      }
      return $value;
   }

   public function begin() {
      $this->getPsqlResult("BEGIN", array(), false);
   }

   public function rollback() {
      $this->getPsqlResult("ROLLBACK", array(), false);
   }

   public function commit() {
      $this->getPsqlResult("COMMIT", array(), false);
   }

   public function lastInsertId($table = null) {
      $query = "SELECT CURVAL('" . $table . "_seq) id";
      $result = $this->GetPsqlResult($query);
      $result = new PsqlResult($result);
      return $result->fetchItem("id");
   }

   public function describe($table) {
     $query = "SELECT column_name
               FROM INFORMATION_SCHEMA.COLUMNS
               WHERE table_name = $1";
     $result = $this->getPsqlResult($query, array($table), false);
     return new PsqlResult($result);
   }

}

class PsqlResult extends SqlResult {
   private $result;
   private $row;
   private $position;
   private $col_types = array();
   private $callbacks = array();

   public function __construct($result) {
      $this->result = $result;
      $this->row = null;
      $this->position = 0;
   }

   public function numRows() {
      return pg_num_rows($this->result);
   }

   public function fetchOne() {
      $this->rewind();
      return $this->row;
   }

   public function fetchItem($item_name) {
      $this->rewind();
      return $this->row[$item_name];
   }

   public function setColType($col, $type, $extra = null) {
      $valid_types = array('array', 'secure', 'callback');
      if (!in_array($type, $valid_types)) {
         trigger_error('Unkown datatype: ' . $type, E_USER_ERROR);
      } else {
         $this->col_types[$col] = $type;
         if ($type == 'callback') {
            $this->callbacks[$col] = $extra;
         }
      }
   }

   private function fixTypes() {
      foreach ($this->col_types as $col => $type) {
         if (isset($this->row[$col])) {
            if ($type == 'array') {
               $this->row[$col] = unserialize($this->row_data[$col]);
            } else if ($type == 'secure') {
               $this->row[$col] = decrypt($this->row_data[$col]);
            } else if ($type == 'callback') {
               $this->row[$col] = $this->callbacks[$col]($this->row[$col]);
            }
         }
      }
   }

   /******************************
    * Iterator functions
    ******************************/

   public function current() {
      return $this->row;
   }

   public function key() {
      return $this->position;
   }

   public function next() {
      $this->row = pg_fetch_assoc($this->result);
      $this->fixTypes();
      $this->position++;
   }

   public function rewind() {
      $this->position = 0;
      if ($this->num_rows() > 0) {
         pg_result_seek($this->result, 0);
      }
      $this->row = pg_fetch_assoc($this->result); 
      $this->fixTypes();
   }

   public function valid() {
      return (boolean) $this->row;
   }
}

?>
