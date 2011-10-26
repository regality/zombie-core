<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

class MysqlResult extends SqlResult {
   protected $result;
   protected $row_data;
   protected $position;

   public function __construct($pResult) {
      $this->result = $pResult;
      $this->row_data = null;
      $this->position = 0;
   }

   public function numRows() {
      return mysql_num_rows($this->result);
   }

   public function fetchOne() {
      $this->rewind();
      return $this->row_data;
   }

   public function fetchItem($itemName) {
      $this->rewind();
      return $this->row_data[$itemName];
   }

   /******************************
    * Iterator functions
    ******************************/

   public function current() {
      return $this->row_data;
   }

   public function key() {
      return $this->position;
   }

   public function next() {
      $this->row_data = mysql_fetch_assoc($this->result);
      $this->fixTypes($this->row_data);
      $this->position++;
   }

   public function rewind() {
      $this->position = 0;
      if ($this->numRows() > 0) {
         mysql_data_seek($this->result, 0);
      }
      $this->row_data = mysql_fetch_assoc($this->result); 
      $this->fixTypes($this->row_data);
   }

   public function valid() {
      return (boolean) $this->row_data;
   }
}

?>
