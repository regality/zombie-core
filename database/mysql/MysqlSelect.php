<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage mysql
 */

class MysqlSelect extends MysqlQuery {

   /**
    * @ignore
    */
   protected $select = '';

   /**
    * @ignore
    */
   protected $from = '';

   /**
    * @ignore
    */
   protected $all_joins = '';

   /**
    * @ignore
    */
   protected $join = '';

   /**
    * @ignore
    */
   protected $left_join = '';

   /**
    * @ignore
    */
   protected $right_join = '';

   /**
    * @ignore
    */
   protected $union = '';

   /**
    * @ignore
    */
   protected $where = '';

   /**
    * @ignore
    */
   protected $group_by = '';

   /**
    * @ignore
    */
   protected $having = '';

   /**
    * @ignore
    */
   protected $order_by = '';

   /**
    * @ignore
    */
   protected $limit = '';

   /**
    * @ignore
    */
   protected $procedure = '';

   /**
    * Create a delete query;
    * @param string $select the columns to select.
    * @param string $connector the connector to use from the config file
    */
   public function __construct($select = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->select($select);
      return $this;
   }

   /**
    * Return the query string.
    * @ignore
    */
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

   /**
    * Select clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function select($sql, $params = array()) {
      return $this->syntax("select", $sql, $params);
   }

   /**
    * From clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function from($sql, $params = array()) {
      return $this->syntax("from", $sql, $params);
   }

   /**
    * Join clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function join($sql, $params = array()) {
      $r = $this->syntax("join", $sql, $params);
      $this->all_joins .= $this->join . PHP_EOL;
      $this->join = '';
      return $r;
   }

   /**
    * Left join clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function leftJoin($sql, $params = array()) {
      $r = $this->syntax("left_join", $sql, $params);
      $this->all_joins .= $this->left_join . PHP_EOL;
      $this->left_join = '';
      return $r;
   }

   /**
    * Right join clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function rightJoin($sql, $params = array()) {
      $r = $this->syntax("right_join", $sql, $params);
      $this->all_joins .= $this->right_join . PHP_EOL;
      $this->right_join = '';
      return $r;
   }

   /**
    * Union clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function union($sql, $params = array()) {
      return $this->syntax("union", (string)$sql, $params, 'UNION ');
   }

   /**
    * Where clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function where($sql, $params = array()) {
      return $this->syntax("where", $sql, $params, ' ');
   }

   /**
    * Group by clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function groupBy($sql, $params = array()) {
      return $this->syntax("group_by", $sql, $params);
   }

   /**
    * Having clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function having($sql, $params = array()) {
      return $this->syntax("having", $sql, $params, ' ');
   }

   /**
    * Order by clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function orderBy($sql, $params = array()) {
      return $this->syntax("order_by", $sql, $params);
   }

   /**
    * Limit clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function limit($sql, $params = array()) {
      return $this->syntax("limit", $sql, $params);
   }

   /**
    * Procedure clause
    * @param string $sql the sql
    * @param array $params the parameters to add
    */
   public function procedure($sql, $params = array()) {
      return $this->syntax("procedure", $sql, $params);
   }

}

?>
