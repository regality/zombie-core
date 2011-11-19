<?php

class <MODEL_CLASS_NAME> extends ModelBase {
   public function getSelect() {
      $select = new MysqlSelect();
      $select->select('<SQL_FIELDS_COMMA_SEP>')
             ->from('<TABLE_NAME>')<MYSQL_JOINS>;
      return $select;
   }

   public function getAll() {
      $select = $this->getSelect();
      $select->orderBy('<TABLE_NAME>.id');
      return $select->query();
   }

   public function getOne($id) {
      $select = $this->getSelect();
      $select->where('<TABLE_NAME>.id = $1')
             ->addParam($id);
      return $select->query()->fetchOne();
   }

   public function delete($id) {
      $delete = new MysqlDelete();
      $delete->deleteFrom('<TABLE_NAME>')
             ->where('id = $1');
      return $delete->exec();
   }

   public function insert(<INSERT_FUNC_PARAMS_MODEL>) {
      $insert = new MysqlInsert();
      $insert->insertInto('<TABLE_NAME>')
             ->columns('<INSERT_FIELDS_COMMA_SEP>')
             ->values('DEFAULT, <INSERT_DOLLAR_PARAMS>')<MYSQL_ADD_PARAMS>;
      return $insert->exec();
   }

   public function update($id,
                          <INSERT_FUNC_PARAMS_MODEL>) {
      $update = new MysqlUpdate();
      $update->update('<TABLE_NAME>')
             ->set('<SET_FIELDS_COMMA_SEP>')
             ->where('id = $1')
             ->addParam($id)<MYSQL_ADD_PARAMS>;
      return $update->exec();
   }

}

?>
