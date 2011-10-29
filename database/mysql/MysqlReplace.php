<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage mysql
 */

class MysqlReplace extends MysqlInsert {

   /**
    * @ignore
    */
   protected $replace_into = '';

   /**
    * @ignore
    */
   protected $values = '';

   /**
    * Create a replace query;
    * @param string $replace_into the table to replace into.
    * @param string $connector the connector to use from the config file
    */
   public function __construct($replace_into = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->replaceInto($replace_into);
      return $this;
   }

   /**
    * Return the query string.
    * @ignore
    */
   public function getQueryString() {
      $this->insert_into = $this->replace_into;
      return MysqlInsert::getQueryString();
   }

   /**
    * Replace into clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function replaceInto($sql, $params = array()) {
      return $this->syntax("replace_into", $sql, $params);
   }

   /**
    * Insert into clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function insertInto() {
   }

   /**
    * On duplicate key update clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function onDuplicateKeyUpdate() {
   }

}

?>
