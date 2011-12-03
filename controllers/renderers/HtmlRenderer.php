<?php

require_once("RenderInterface.php");

class HtmlRenderer implements RenderInterface {
   public function render($controller) {
      $root = $controller->config['zombie_root'];
      if ($controller->do_run) {
         foreach ($controller->data as $var => $val) {
            $$var = $val;
         }
         if ($controller->config['env'] == 'dev') {
            require_once("$root/zombie-core/template/createTemplate.php");
            eval("?>" . getPHPTemplate($controller->view_base, $controller->view));
         } else {
            $file = $root . "/apps/" . $controller->view_base . 
                    "/views/.compiled/" . $controller->view . ".php";
            @include($file);
         }
      }
      $this->renderMessages($controller);
   }

   function renderMessages($controller) {
      renderErrorsJs();
      $this->renderJsMesg($controller);
   }

   /**
    * Turn the errors, warnings, and messages into
    * pretty javascript messages to be handled by
    * the frontend.
    * @access private
    */
   function renderJsMesg($controller) {
      if (!empty($controller->errors) ||
          !empty($controller->warnings) ||
          !empty($controller->messages)) {
         echo '<script type="text/javascript">';
         if (!empty($controller->errors)) {
            foreach ($controller->errors as $error) {
               echo "zs.ui.error(\"" . 
                    htmlentities($error, ENT_QUOTES) . "\");\n";
            }
         }
         if (!empty($controller->warnings)) {
            foreach ($controller->warnings as $warning) {
               echo "zs.ui.warn(\"" . 
                    htmlentities($warning, ENT_QUOTES) . "\");\n";
            }
         }
         if (!empty($controller->messages)) {
            foreach ($controller->messages as $message) {
               echo "zs.ui.message(\"" . 
                    htmlentities($message, ENT_QUOTES) . "\");\n";
            }
         }
         echo '</script>';
      }
   }
}

?>
