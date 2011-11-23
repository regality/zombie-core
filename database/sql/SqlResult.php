<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage sql
 */

/**
 * Base class for sql results.
 */
abstract class SqlResult implements Iterator {
   /**
    * column types
    * @ignore
    */
   protected $col_types = array();

   /**
    * callbacks
    * @ignore
    */
   protected $callbacks = array();

   /**
    * callbacks
    * @ignore
    */
   protected $filter_callbacks = array();

   /**
    * Get the number of rows returned.
    * @return int
    */
   abstract public function numRows();

   /**
    * Fetch the first row from the result.
    * @return array
    */
   abstract public function fetchOne();

   /**
    * Fetch the column named $item_name from the first row.
    * @param string $item_name
    * @return mixed
    */
   abstract public function fetchItem($item_name);

   /**
    * Set the type of column for a result.
    * Options are 'array', 'secure', and 'callback'.
    * If the callback type if used, pass the function
    * into $extra.
    *
    * @param string $col the name of the column
    * @param string $type the type of the column
    * @param mixed $extra extra parameter, such as callback.
    */
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

   /**
    * Fixes column types.
    * @ignore
    */
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

   public function addFilter($filter_callback) {
      array_push($this->filter_callbacks, $filter_callback);
   }

   public function removeLastFilter() {
      array_pop($this->filter_callbacks);
   }

   protected function applyFilters(&$row) {
      foreach ($this->filter_callbacks as $filter) {
         $row = $filter($row, $this->key());
      }
   }

}

?>
