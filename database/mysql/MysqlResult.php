<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage mysql
 */

/**
 * Mysql result iterator.
 */
class MysqlResult extends SqlResult {
   /**
    * The result resource
    * @ignore
    */
   protected $result;

   /**
    * The row data
    * @ignore
    */
   protected $row_data;

   /**
    * The pointer position
    * @ignore
    */
   protected $position;

   /**
    * Create a result.
    * @param result resource
    */
   public function __construct($result) {
      $this->result = $result;
      $this->row_data = null;
      $this->position = 0;
   }

   /**
    * Returns the number of rows retrieved.
    * @return int
    */
   public function numRows() {
      return mysql_num_rows($this->result);
   }

   /**
    * Fetch the first row from the result.
    * @return array
    */
   public function fetchOne() {
      $this->rewind();
      return $this->row_data;
   }

   /**
    * Fetch the column named $item_name from the first row.
    * @param string $item_name
    * @return mixed
    */
   public function fetchItem($itemName) {
      $this->rewind();
      return $this->row_data[$itemName];
   }

   /******************************
    * Iterator functions
    ******************************/

   /**
    * @ignore
    */
   public function current() {
      return $this->row_data;
   }

   /**
    * @ignore
    */
   public function key() {
      return $this->position;
   }

   /**
    * @ignore
    */
   public function next() {
      $this->row_data = false;
      while (!$this->row_data) {
         $this->row_data = mysql_fetch_assoc($this->result);
         if ($this->row_data == false) {
            return false;
         }
         $this->position++;
         $this->fixTypes($this->row_data);
         $this->applyFilters($this->row_data);
      }
   }

   /**
    * @ignore
    */
   public function rewind() {
      $this->position = 0;
      if ($this->numRows() > 0) {
         mysql_data_seek($this->result, 0);
      }
      $this->next();
   }

   /**
    * @ignore
    */
   public function valid() {
      return (boolean) $this->row_data;
   }
}

?>
