<?php

class JsonRenderer {
   public function render($controller) {
      $this->messagesToDataArray($controller);
      $this->traversableToArray($controller);
      echo json_encode($controller->data);
   }

   public function messagesToDataArray($controller) {
      $errors = getErrorArray();
      if (!empty($errors)) {
         $controller->data['php_errors'] = $errors;
      }
      if (!empty($controller->errors)) {
         $controller->data['errors'] = $controller->errors;
      }
      if (!empty($controller->warnings)) {
         $controller->data['warnings'] = $controller->warnings;
      }
      if (!empty($controller->messages)) {
         $controller->data['messages'] = $controller->messages;
      }
   }

   public function traversableToArray($controller) {
      foreach ($controller->data as $name => &$object) {
         if (!is_array($object) && $object instanceof Traversable) {
            $tmp = array();
            foreach ($object as $key => $value) {
               $tmp[$key] = $value;
            }
            $object = $tmp;
         }
      }
   }
}

?>
