<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Session
 */

require("session.php");

class MemcacheSession extends Session {
   /**
    * @ignore
    */
   public $session;

   /**
    * @ignore
    */
   public $session_id;

   /**
    * @ignore
    */
   public $mem;

   /**
    * @ignore
    */
   public function __construct() {
      parent::__construct();
      session_name($this->config['session']['name']);
      $this->session = false;
      $this->mem = memcache_connect('localhost', 11211);
      if (isset($_COOKIE['sessid'])) {
         $this->session_id = $_COOKIE['sessid'];
         $this->session = memcache_get($this->mem, 'sess_' . $this->session_id);
      }
      if ($this->session == false) {
         $this->create();
      } else {
         $this->session = unserialize($this->session);
         if (!is_array($this->session)) {
            $this->session = array();
         }
      }
   }

   /**
    * @ignore
    */
   public function __destruct() {
      if (!isset($this->deleted)) {
         $this->save();
      }
   }

   /**
    * Get the session array
    */
   public function getArray() {
      return $this->session;
   }

   /**
    * Save the session
    */
   public function save() {
      $session = serialize($this->session);
      //memcache_set($this->mem, 'foo', 'bar', 0, 1800);
      memcache_set($this->mem,
                   'sess_' . $this->session_id,
                   $session,
                   0,
                   1800);
   }

   /**
    * Create a new session
    */
   public function create() {
      $this->session_id = sha1(time() + rand(1,10000000));
      setcookie(session_name(),
                $this->session_id,
                time() + $this->config['session']['timeout'],
                '', // path
                '', // domain
                $this->config['session']['secure'],
                $this->config['session']['http_only']);
      $this->session = array();
   }

   /**
    * Check if a session variable is set
    */
   public function exists($key) {
      return isset($this->session[$key]);
   }

   /**
    * Get a session variable
    */
   public function get($key) {
      return $this->session[$key];
   }

   /**
    * Set a session variable
    */
   public function set($a, $b = null) {
      if (is_array($a)) {
         $this->session = array_merge($this->session, $a);
      } else {
         $this->session[$a] = $b;
      }
   }

   /**
    * Destroy a session
    */
   public function destroy() {
      memcache_delete($this->mem, 'sess_' . $this->session_id);
      $this->deleted = true;
      setcookie('sessid','',time() - 1);
   }

}

?>
