<?php

require_once('RenderInterface.php');

class BadFormatRenderer implements RenderInterface {
   public function render($controller) {
      $controller->error("Format not allowed: " . $controller->format);
      $renderer_class = $controller->format . "Renderer";
      require_once(__DIR__ . "/$renderer_class.php");
      $renderer = new $renderer_class();
      $renderer->renderMessages($controller);
   }

   public function renderMessages($controller) {
      $this->render($controller);
   }
}

?>
