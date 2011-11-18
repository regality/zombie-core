<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage couch
 */

class CouchResult implements Iterator {
   public function __construct($data) {
      $this->data = $data;
      $this->rows = $data['rows'];
      $this->count = count($this->rows);
      $this->position = 0;
   }

   public function numRows() {
      return $this->count;
   }

   /******************************
    * Iterator functions
    ******************************/

   public function current() {
      return $this->rows[$this->position]['value'];
   }

   public function key() {
      return $this->rows[$this->position]['key'];
   }

   public function next() {
      $this->position++;
   }

   public function rewind() {
      $this->position = 0;
   }

   public function valid() {
      return ($this->position < $this->count);
   }
}

?>
