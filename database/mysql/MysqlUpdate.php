<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage mysql
 */

class MysqlUpdate extends MysqlQuery {

   /**
    * @ignore
    */
   protected $update = '';

   /**
    * @ignore
    */
   protected $set = '';

   /**
    * @ignore
    */
   protected $where = '';

   /**
    * @ignore
    */
   protected $order_by = '';

   /**
    * @ignore
    */
   protected $limit = '';

   /**
    * Create an update query;
    * @param string $update the table to update.
    * @param string $connector the connector to use from the config file
    */
   public function __construct($update = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->update($update);
      return $this;
   }

   /**
    * Return the query string.
    * @ignore
    */
   public function getQueryString() {
      return $this->update . PHP_EOL .
             $this->set . PHP_EOL .
             $this->where . PHP_EOL .
             $this->order_by . PHP_EOL .
             $this->limit;
   }

   /**
    * Update clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function update($sql, $params = array()) {
      return $this->syntax("update", $sql, $params);
   }

   /**
    * Set clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function set($sql, $params = array()) {
      return $this->syntax("set", $sql, $params);
   }

   /**
    * Where clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function where($sql, $params = array()) {
      return $this->syntax("where", $sql, $params, ' ');
   }

   /**
    * Order by clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function orderBy($sql, $params = array()) {
      return $this->syntax("order_by", $sql, $params);
   }

   /**
    * Limit clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function limit($sql, $params = array()) {
      return $this->syntax("limit", $sql, $params);
   }

}

?>
