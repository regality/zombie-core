<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

abstract class SqlResult implements Iterator {
   protected $col_types = array();
   protected $callbacks = array();

   abstract public function numRows();
   abstract public function fetchOne();
   abstract public function fetchItem($item_name);

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

   protected function fixTypes(&$row) {
      foreach ($this->col_types as $col => $type) {
         if (isset($row[$col])) {
            if ($type == 'array') {
               $row[$col] = unserialize($row[$col]);
            } else if ($type == 'secure') {
               $row[$col] = decrypt($row[$col]);
            } else if ($type == 'callback') {
               $this->row[$col] = $this->callbacks[$col]($row[$col]);
            }
         }
      }
   }

}

?>
