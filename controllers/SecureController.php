<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Controllers
 */

/**
 * This controller checks if the user is logged in
 * and in the groups specified by the controller
 * prior to executing or rendering.
 */
abstract class SecureController extends Controller {
   public function __construct() {
      parent::__construct();
      $this->bouncer = "Secure";
   }
}

?>
