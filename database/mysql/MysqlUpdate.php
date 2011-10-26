<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

class MysqlUpdate extends MysqlQuery {
   protected $update = '';
   protected $set = '';
   protected $where = '';
   protected $order_by = '';
   protected $limit = '';

   public function __construct($update = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->update($update);
      return $this;
   }

   public function getQueryString() {
      return $this->update . PHP_EOL .
             $this->set . PHP_EOL .
             $this->where . PHP_EOL .
             $this->order_by . PHP_EOL .
             $this->limit;
   }

   public function update($sql, $params = array()) {
      return $this->syntax("update", $sql, $params);
   }

   public function set($sql, $params = array()) {
      return $this->syntax("set", $sql, $params);
   }

   public function where($sql, $params = array()) {
      return $this->syntax("where", $sql, $params, ' ');
   }

   public function orderBy($sql, $params = array()) {
      return $this->syntax("order_by", $sql, $params);
   }

   public function limit($sql, $params = array()) {
      return $this->syntax("limit", $sql, $params);
   }

}

?>
