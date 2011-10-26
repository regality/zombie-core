<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once('util.php');
require_once(__DIR__ . '/../../config/config.php');
$config = getZombieConfig();
$GLOBALS['zombie_root'] =  $config['zombie_root'];

function autoloadApp($class) {
   $slug = classToUnderscore($class);
   @include($GLOBALS['zombie_root'] . '/apps/' . $slug . '/' . $slug . '.php');
}

function autoloadModel($class) {
   if (substr($class, -5) == 'Model') {
      $slug = classToUnderscore(substr($class, 0, strlen($class) - 5)); 
      include($GLOBALS['zombie_root'] . '/model/' . $slug . '.php');
   }
}

function autoloadSession($class) {
   if (substr($class, -7) == 'Session') {
      include($GLOBALS['zombie_root'] . '/zombie-core/session/' . $class . '.php');
   }
}

function autoloadModelBase($class) {
   if (substr($class, -9) == 'ModelBase') {
      include($GLOBALS['zombie_root'] . '/zombie-core/model/' . $class . '.php');
   }
}

function autoloadController($class) {
   if (substr($class, -10) == 'Controller') {
      include($GLOBALS['zombie_root'] . '/zombie-core/controllers/' . $class . '.php');
   }
}

include(__DIR__ . "/../database/sql/autoload.php");
include(__DIR__ . "/../database/mysql/autoload.php");
spl_autoload_register('autoloadModel');
spl_autoload_register('autoloadSession');
spl_autoload_register('autoloadModelBase');
spl_autoload_register('autoloadController');
spl_autoload_register('autoloadApp');

?>
