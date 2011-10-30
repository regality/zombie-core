<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Session
 */

class PhpSession extends Session {

   /**
    * @ignore
    */
   protected function __construct() {
      parent::__construct();
      session_set_cookie_params($this->config['session']['timeout'],
                                '/' . $this->config['web_root'],
                                $this->config['domain'],
                                $this->config['session']['secure'],
                                $this->config['session']['http_only']);
      $this->create();
      $this->preventHijack();
   }

   /**
    * Get the session array
    */
   public function getArray() {
      return $_SESSION;
   }

   /**
    * Save the session
    */
   public function save() {
   }

   /**
    * Create a new session
    */
   public function create() {
      session_start();
   }

   /**
    * Generate a new session id
    */
   public function regenerateId() {
      session_regenerate_id();
   }

   /**
    * Check if a session variable is set
    */
   public function exists($key) {
      return isset($_SESSION[$key]);
   }

   /**
    * Get a session variable
    */
   public function get($key) {
      if (isset($_SESSION[$key])) {
         return $_SESSION[$key];
      } else {
         return false;
      }
   }

   /**
    * Set a session variable
    */
   public function set($a, $b = null) {
      if (is_array($a)) {
         $_SESSION = array_merge($_SESSION, $a);
      } else {
         $_SESSION[$a] = $b;
      }
   }

   /**
    * Destroy a session
    */
   public function destroy() {
      setcookie(session_name(),'',time() - 1);
      session_destroy();
   }

}

?>
