<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage mysql
 */

require_once(__DIR__ . "/MysqlException.php");

/**
 * A generic mysql query.
 */
class MysqlQuery extends SqlQuery {
   /**
    * The query to be executed.
    * @ignore
    */
   protected $query;

   /**
    * The parameters for the query.
    * @ignore
    */
   protected $params = array();

   /**
    * Number of parameters.
    * @ignore
    */
   protected $param_count = 0;

   /**
    * Check for magic quotes.
    * @ignore
    */
   protected $magic_quotes_on = false;

   /**
    * Database connection
    * @ignore
    */
   protected static $db = null;

   /**
    * Construct a new query.
    *
    * @param string $query the query to be executed.
    * @param string $connector the connector from the config file.
    */
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

   /**
    * Convert the query to a string.
    */
   public function __toString() {
      return $this->getQueryString();
   }

   /**
    * Retrieve the result from mysql.
    *
    * @param string $query the sql query
    * @param boolean $debug true will print debug info
    * @return MysqlResult
    */
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

   /**
    * Execute a query and return the number of rows effected.
    * @param boolean $debug prints the executed query if true
    * @return integer|boolean
    */
   public function exec($debug = false) {
      $bound_query = $this->getBoundQuery();
      $result = $this->getMysqlResult($bound_query, $debug);
      return mysql_affected_rows(MysqlQuery::$db);
   }

   /**
    * Execute a query and return the results.
    * @param boolean $debug prints the executed query if true
    * @return SqlResult|boolean
    */
   public function query($debug = false) {
      $bound_query = $this->getBoundQuery();
      $result = $this->getMysqlResult($bound_query, $debug);
      return new MysqlResult($result);
   }

   /**
    * Return the query string.
    * @return string
    * @ignore
    */
   public function getQueryString() {
      return $this->query;
   }

   /**
    * Bind the parameters into the query and return it.
    * @return string
    * @ignore
    */
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

   /**
    * Add multiple parameters.
    * @param array $params
    */
   public function addParams($params) {
      foreach ($params as $param) {
         $this->addParam($param);
      }
      return $this;
   }

   /**
    * Add a parameter. If the type parameter is used
    * valid options are 'html', 'secure', and 'raw'.
    * @param mixed $value the value of the parameter
    * @param string $type
    */
   public function addParam($value, $type = null) {
      $this->params[$this->param_count] = $this->sanitize($value, $type);
      $this->param_count += 1;
      return $this;
   }

   /**
    * Sanitze incoming data.
    * @ignore
    */
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

   /**
    * Begin a transaction.
    */
   public function begin() {
      $this->getMysqlResult("SET autocommit = 0");
      $this->getMysqlResult("START TRANSACTION");
   }

   /**
    * Begin a transaction.
    */
   public function rollback() {
      $this->getMysqlResult("ROLLBACK");
   }

   /**
    * Commit a transaction.
    */
   public function commit() {
      $this->getMysqlResult("COMMIT");
   }

   /**
    * Get the last insert id.
    * @return int
    */
   public function lastInsertId() {
      return mysql_insert_id();
   }

   /**
    * Describe a table.
    * @param string $table the name of the table
    */
   public function describe($table) {
     $query = "DESCRIBE $table";
     $result = $this->getMysqlResult($query);
     return new MysqlResult($result);
   }

}

?>
