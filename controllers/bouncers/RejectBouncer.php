<?php

class RejectBouncer {
   public function bounce($controller) {
      $controller->do_run = false;
      $controller->do_save = false;
   }
}

?>
