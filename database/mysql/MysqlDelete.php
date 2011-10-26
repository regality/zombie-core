<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

class MysqlDelete extends MysqlQuery {
   protected $delete_from = '';
   protected $where = '';

   public function __construct($delete_from = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->deleteFrom($delete_from);
      return $this;
   }

   public function getQueryString() {
      return $this->delete_from . PHP_EOL .
             $this->where;
   }

   public function deleteFrom($sql, $params = array()) {
      return $this->syntax("delete_from", $sql, $params);
   }

   public function where($sql, $params = array()) {
      return $this->syntax("where", $sql, $params, ' ');
   }

}

?>
