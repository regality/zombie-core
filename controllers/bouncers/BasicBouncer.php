<?php

class BasicBouncer {

   public function bounce($controller) {
      $controller->do_run = $this->checkAccess($controller);
      $controller->do_save = $this->csrfProtect($controller);
   }

   public function checkAccess($controller) {
      return true;
   }

   /**
    * Check for signs of a CSRF attack and only
    * run the save function if the coast is clear.
    * @param Controller $controller
    */
   function csrfProtect($controller) {
      $request = $controller->request;
      $save_method = underscoreToMethod($controller->action . "Save");
      if (!method_exists($controller, $save_method)) {
         return false;
      }
      if (isset($request['csrf'])) {
         $sess_token = $controller->session->get('csrf_token');
         $req_token = $request['csrf'];
         $match = ($req_token == $sess_token);
         if (!$match) {
            $controller->save_status = "bad csrf";
            $controller->error("bad csrf");
            return false;
         } else {
            if (isset($_SERVER['referer'])) {
               $domain = parse_url($_SERVER['referer']);
               $domain = $domain['host'];
               if ($domain != $controller->config['domain']) {
                  $controller->save_status = "bad referer";
                  $controller->error("bad referer");
                  return false;
               }
            }
         }
      } else {
         $controller->save_status = "no csrf";
         $controller->error("no csrf");
         return false;
      }
      $controller->save_status = "success";
      return true;
   }

}

?>
