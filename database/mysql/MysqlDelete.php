<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage mysql
 */

/**
 * Used to create a query to delete from a table.
 */
class MysqlDelete extends MysqlQuery {
   /**
    * @ignore
    */
   protected $delete_from = '';

   /**
    * @ignore
    */
   protected $where = '';

   /**
    * Create a delete query;
    * @param string $delete_from the table to delete from.
    * @param string $connector the connector to use from the config file
    */
   public function __construct($delete_from = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->deleteFrom($delete_from);
      return $this;
   }

   /**
    * Return the query string.
    * @ignore
    */
   public function getQueryString() {
      return $this->delete_from . PHP_EOL .
             $this->where;
   }

   /**
    * Delete from clause.
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function deleteFrom($sql, $params = array()) {
      return $this->syntax("delete_from", $sql, $params);
   }

   /**
    * Where clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function where($sql, $params = array()) {
      return $this->syntax("where", $sql, $params, ' ');
   }

}

?>
