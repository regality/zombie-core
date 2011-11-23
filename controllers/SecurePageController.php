<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Controllers
 */

/**
 * This is a combination of the SecureController
 * and PageController classes.
 */
abstract class SecurePageController extends SecureController {
   public function __construct() {
      parent::__construct();
      $this->bouncer = "Secure";
      $this->renderer = "Page";
   }
}

?>
