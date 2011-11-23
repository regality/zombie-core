<?php

require_once("BasicBouncer.php");

class SecureBouncer extends BasicBouncer {
   public $unsecure_methods = array();

   public $groups_allowed = array();

   public function unsecureMethods($methods) {
      if (is_array($methods)) {
         array_merge($this->unsecure_methods, $methods);
      } else {
         array_push($this->unsecure_methods, $methods);
      }
   }

   public function groupsAllowed($groups) {
      if (is_array($groups)) {
         array_merge($this->groups_allowed, $groups);
      } else {
         array_push($this->groups_allowed, $groups);
      }
   }


   public function checkAccess($controller) {
      if (!empty($this->unsecure_methods) &&
          in_array($controller->action, $this->unsecure_methods))
      {
         return true;
      } else {
         return $this->hasAccess($controller);
      }
   }

   /**
    * Check if a user is authenticated and
    * belongs to a group if necessary.
    */
   function hasAccess($controller) {
      $logged_in = true;
      $in_group = true;
      if (!$controller->session->exists('username')) {
         $controller->error("You must be logged in.");
         $logged_in = false;
      } else if (!empty($this->groups_allowed)) {
         $user_groups = $controller->session->get('groups');
         if (!is_array($user_groups)) {
            $in_group = false;
            $controller->error("You do not have access.");
         } else {
            $group_found = false;
            foreach ($this->groups_allowed as $group) {
               foreach ($user_groups as $user_group) {
                  if ($user_group == $group) {
                     $group_found = true;
                     break;
                  }
               }
               if ($group_found) {
                  break;
               }
            }
            $in_group = $group_found;
         }
      }
      return $logged_in && $in_group;
   }

}

?>
