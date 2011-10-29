<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Controllers
 */

/**
 * This controller should be used if the pages
 * rendered are intended to be standalone.
 */
abstract class PageController extends Controller {
   public function __construct() {
      parent::__construct();
      $this->is_page = true;
   }
}

?>
