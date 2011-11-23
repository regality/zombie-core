<?php

require_once('HtmlRenderer.php');

class PageRenderer extends HtmlRenderer {
   public function render($controller) {
      $root = $controller->config['zombie_root'];
      if ($controller->do_run) {
         foreach ($controller->data as $var => $val) {
            $$var = $val;
         }
         if ($controller->config['env'] == 'dev') {
            require_once("$root/zombie-core/template/createTemplate.php");
            eval("?>" . getPHPTemplate("home", "open"));
            eval("?>" . getPHPTemplate($controller->view_base, $controller->view));
            eval("?>" . getPHPTemplate("home", "close"));
         } else {
            $file = $root . "/apps/" . $controller->view_base . 
                    "/views/.compiled/" . $controller->view . ".php";
            $home_view_dir = $root . "/apps/home/views/.compiled/";
            $open_view = $home_view_dir . "open.php";
            $close_view = $home_view_dir . "close.php";
            @include($open_view);
            if (file_exists($file)) {
               @include($file);
            } else {
               $this->warn("Template does not exist: " .
                           $controller->view_base . "/" . $controller->view);
            }
            @include($close_view);
         }
      }
      renderErrorsJs();
      $this->renderJsMesg($controller);
   }
}

?>
