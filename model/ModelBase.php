<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Model
 */

require_once(__DIR__ . "/../util/util.php");
require_once(__DIR__ . "/../../config/config.php");

abstract class ModelBase {
   public function __construct() {
      $this->config = getZombieConfig();
   }
}

?>
