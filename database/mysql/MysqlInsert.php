<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage mysql
 */

class MysqlInsert extends MysqlQuery {

   /**
    * @ignore
    */
   protected $insert_into = '';

   /**
    * @ignore
    */
   protected $values = '';

   /**
    * @ignore
    */
   protected $columns = '';

   /**
    * @ignore
    */
   protected $set = '';

   /**
    * @ignore
    */
   protected $select = '';

   /**
    * @ignore
    */
   protected $on_duplicate_key_update = '';

   /**
    * Create an insert query;
    * @param string $insert_into the table to insert into.
    * @param string $connector the connector to use from the config file
    */
   public function __construct($insert_into = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->insertInto($insert_into);
      return $this;
   }

   /**
    * Return the query string.
    * @ignore
    */
   public function getQueryString() {
      $cparen = (empty($this->columns) ? '' : ')');
      $vparen = (empty($this->values) ? '' : ')');
      return $this->insert_into . PHP_EOL .
             $this->set . (empty($this->set) ? '' : PHP_EOL) .
             $this->columns . $cparen . (empty($this->columns) ? '' : PHP_EOL) .
             $this->values . $vparen . (empty($this->values) ? '' : PHP_EOL) .
             $this->select . (empty($this->select) ? '' : PHP_EOL) .
             $this->on_duplicate_key_update;
   }

   /**
    * Insert into clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function insertInto($sql, $params = array()) {
      return $this->syntax("insert_into", $sql, $params);
   }

   /**
    * Set clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function set($sql, $params = array()) {
      if (!empty($this->values)) {
         throw new MysqlException('Cannot use set and values in same query');
      }
      if (!empty($this->select)) {
         throw new MysqlException('Cannot use set and select in same query');
      }
      return $this->syntax("set", $sql, $params);
   }

   /**
    * Columns clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function columns($sql, $params = array()) {
      if (empty($this->columns)) {
         $this->columns = '(';
         $alt = '';
      } else {
         $alt = ', ';
      }
      return $this->syntax("columns", $sql, $params, $alt);
   }

   /**
    * Values clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function values($sql, $params = array()) {
      if (!empty($this->set)) {
         throw new MysqlException('Cannot use values and set in same query');
      }
      if (!empty($this->select)) {
         throw new MysqlException('Cannot use values and select in same query');
      }
      if (empty($this->values)) {
         $sql = '(' . $sql;
      }
      return $this->syntax("values", $sql, $params);
   }

   /**
    * Select clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function select($sql, $params = array()) {
      if (!empty($this->set)) {
         throw new MysqlException('Cannot use select and set in same query');
      }
      if (!empty($this->values)) {
         throw new MysqlException('Cannot use select and values in same query');
      }
      return $this->syntax("select", $sql, $params);
   }

   /**
    * On duplicate key update clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function onDuplicateKeyUpdate($sql, $params = array()) {
      return $this->syntax("on_duplicate_key_update", $sql, $params);
   }

}

?>
