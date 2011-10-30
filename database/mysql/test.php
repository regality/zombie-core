<?php
/**
 * @package Database
 * @subpackage mysql
 * @ignore
 */

include('autoload.php');
include('../sql/autoload.php');

testSelect();
testReplace();
testDelete();
testInsert();
testUpdate();

function testReplace() {
   $query = new MysqlReplace();
   $query->replaceInto('foo');
   $query->values('foo, bar, baz');
   echo $query;
   echo PHP_EOL;

   $query->columns('asdf,qwer,zxvc');
   echo $query;
   echo PHP_EOL;

   $query = new MysqlReplace();
   $query->replaceInto('foo');
   $query->set('x = y, z = 2');
   $query->set('foo = bar');
   echo $query;
   echo PHP_EOL;

   $select = new MysqlSelect();
   $select->select('*')->from('foo');
   $query = new MysqlReplace();
   $query->replaceInto('foo');
   $query->select($select);
   echo $query;
   echo PHP_EOL;

}

function testUpdate() {
   $query = new MysqlUpdate();
   $query->update('foo')
         ->set('bar = $1')
         ->set('baz = $1')
         ->where('x > $2')
         ->orderBy('bar')
         ->limit('2');
   echo $query;
   echo PHP_EOL;
}

function testInsert() {
   $query = new MysqlInsert();
   $query->insertInto('foo');
   $query->values('foo, bar, baz');
   echo $query;
   echo PHP_EOL;

   $query->columns('asdf,qwer,zxvc');
   echo $query;
   echo PHP_EOL;

   $query->onDuplicateKeyUpdate('asdf = 7');
   echo $query;
   echo PHP_EOL;
   echo PHP_EOL;

   $query = new MysqlInsert();
   $query->insertInto('foo');
   $query->set('x = y, z = 2');
   $query->set('foo = bar');
   echo $query;
   echo PHP_EOL;

   $select = new MysqlSelect();
   $select->select('*')->from('foo');
   $query = new MysqlInsert();
   $query->insertInto('foo');
   $query->select($select);
   echo $query;
   echo PHP_EOL;

}

function testDelete() {
   $query = new MysqlDelete();
   $query->deleteFrom('foo')
         ->where('x < 2')
         ->where('OR x > 1');
   echo $query;
   echo PHP_EOL;
}

function testSelect() {
   $query = new MysqlSelect('SELECT kachow');
   $query->select('foo, bar')
         ->select('baz')
         ->from('foo')
         ->join('bar on x = y')
         ->leftJoin('lefty on x = y')
         ->rightJoin('righty on x = y')
         ->leftJoin('lefter on x = y');
   $query->union($query);
   $query->where('x > 7')
         ->where('and y > 7')
         ->having('and y > 7')
         ->limit('7, 8')
         ->groupBy('foo');
   echo $query;
   return $query;
}
