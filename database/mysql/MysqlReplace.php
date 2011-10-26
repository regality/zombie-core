<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

class MysqlReplace extends MysqlInsert {
   protected $replace_into = '';
   protected $values = '';

   public function __construct($replace_into = '', $connector = 'mysql') {
      parent::__construct('', $connector);
      $this->replaceInto($replace_into);
      return $this;
   }

   public function getQueryString() {
      $this->insert_into = $this->replace_into;
      return MysqlInsert::getQueryString();
   }

   public function replaceInto($sql, $params = array()) {
      return $this->syntax("replace_into", $sql, $params);
   }

   public function insertInto() {
   }

   public function onDuplicateKeyUpdate() {
   }

}

?>
