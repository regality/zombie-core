<?php

class JsonRenderer extends DataRenderer {
   public function __construct() {
      $this->render_function = "json_encode";
   }
}

?>
