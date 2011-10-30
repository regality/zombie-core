<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Database
 * @subpackage mysqlexception
 */

class MysqlException extends Exception {
}

class MysqlParamCountException extends MysqlException {
}

class MysqlDuplicateEntryException extends MysqlException {
   public $errno = 1062;
}

class MysqlUnknownTableException extends MysqlException {
   public $errno = 1146;
}

class MysqlUnknownDatabaseException extends MysqlException {
   public $errno = 1049;
}

class MysqlNoDatabaseSelectedException extends MysqlException {
   public $errno = 1046;
}

class MysqlKeyNotFoundException extends MysqlException {
   public $errno = 1058;
}

class MysqlColumnCountException extends MysqlException {
   public $errno = 1058;
}

class MysqlParseException extends MysqlException {
   public $errno = 1064;
}

function getMysqlExceptionClassName($errno) {
   switch ($errno) {
      case 1062:
         return 'MysqlDuplicateEntryException';
         break;
      case 1051:
      case 1146:
         return 'MysqlUnknownTableException';
         break;
      case 1049:
         return 'MysqlUnknownDatabaseException';
         break;
      case 1046:
         return 'MysqlNoDatabaseSelectedException';
         break;
      case 1032:
         return 'MysqlKeyNotFoundException';
         break;
      case 1058:
         return 'MysqlColumnCountException';
         break;
      case 1064:
         return 'MysqlParseException';
         break;
      default:
         return 'MysqlException';
   }
}
