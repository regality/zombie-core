<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

class MysqlSelect extends MysqlQuery {
   protected $select = '';
   protected $from = '';
   protected $all_joins = '';
   protected $join = '';
   protected $left_join = '';
   protected $right_join = '';
   protected $union = '';
   protected $where = '';
   protected $group_by = '';
   protected $having = '';
   protected $order_by = '';
   protected $limit = '';
   protected $procedure = '';

   public function __construct($select = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->select($select);
      return $this;
   }

   public function getQueryString() {
      return $this->select . PHP_EOL .
             $this->from . (!empty($this->from) ? PHP_EOL : '') .
             $this->all_joins .
             $this->union . (!empty($this->union) ? PHP_EOL : '') .
             $this->where . (!empty($this->where) ? PHP_EOL : '') .
             $this->group_by . (!empty($this->group_by) ? PHP_EOL : '') .
             $this->having . (!empty($this->having) ? PHP_EOL : '') .
             $this->order_by . (!empty($this->order_by) ? PHP_EOL : '') .
             $this->limit . (!empty($this->limit) ? PHP_EOL : '') .
             $this->procedure;
   }

   public function select($sql, $params = array()) {
      return $this->syntax("select", $sql, $params);
   }

   public function from($sql, $params = array()) {
      return $this->syntax("from", $sql, $params);
   }

   public function join($sql, $params = array()) {
      $r = $this->syntax("join", $sql, $params);
      $this->all_joins .= $this->join . PHP_EOL;
      $this->join = '';
      return $r;
   }

   public function leftJoin($sql, $params = array()) {
      $r = $this->syntax("left_join", $sql, $params);
      $this->all_joins .= $this->left_join . PHP_EOL;
      $this->left_join = '';
      return $r;
   }

   public function rightJoin($sql, $params = array()) {
      $r = $this->syntax("right_join", $sql, $params);
      $this->all_joins .= $this->right_join . PHP_EOL;
      $this->right_join = '';
      return $r;
   }

   public function union($sql, $params = array()) {
      return $this->syntax("union", (string)$sql, $params, 'UNION ');
   }

   public function where($sql, $params = array()) {
      return $this->syntax("where", $sql, $params, ' ');
   }

   public function groupBy($sql, $params = array()) {
      return $this->syntax("group_by", $sql, $params);
   }

   public function having($sql, $params = array()) {
      return $this->syntax("having", $sql, $params, ' ');
   }

   public function orderBy($sql, $params = array()) {
      return $this->syntax("order_by", $sql, $params);
   }

   public function limit($sql, $params = array()) {
      return $this->syntax("limit", $sql, $params);
   }

   public function procedure($sql, $params = array()) {
      return $this->syntax("procedure", $sql, $params);
   }

}

?>
