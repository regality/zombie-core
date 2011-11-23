<?php

class FourOhFourRenderer extends HtmlRenderer {
   public function render($controller) {
      $root = $controller->config['zombie_root'];
      if ($controller->config['env'] == 'dev') {
         require_once("$root/zombie-core/template/createTemplate.php");
         eval("?>" . getPHPTemplate("home", "404"));
      } else {
         $file = $root . "/apps/home/views/.compiled/404.php";
         @include($file);
      }
      $this->renderMessages($controller);
   }
}

?>
