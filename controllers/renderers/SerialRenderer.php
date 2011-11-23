<?php

class SerialRenderer extends DataRenderer {
   public function __construct() {
      $this->render_function = "serialize";
   }
}

?>
