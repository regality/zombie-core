<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Session
 */

require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../util/rand.php");

/**
 * Base class for sessions.
 */
abstract class Session {
   /**
    * The session array
    * @ignore
    */
   protected $session;

   /**
    * Session id
    * @ignore
    */
   protected $session_id;

   /**
    * Zombie config
    * @ignore
    */
   protected $config;

   /**
    * Singleton class instance
    * @ignore
    */
   protected static $instance;

   /**
    * @ignore
    */
   protected function __construct() {
      $this->config = getZombieConfig();
   }

   /**
    * Retrieve the session
    */
   public static function getSession() {
      $class = get_called_class();
      if (!isset($class::$instance)) {
         $class::$instance = new $class();
      }
      return $class::$instance;
   }

   /**
    * Prevent session hijacking
    * @ignore
    */
   public function preventHijack() {
      if (!$this->exists('REMOTE_ADDR')) {
         $this->set('REMOTE_ADDR', $_SERVER['REMOTE_ADDR']);
         $this->set('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
      } else if (($this->config['session']['ip_sticky'] &&
                  $_SESSION['REMOTE_ADDR'] != $_SERVER['REMOTE_ADDR']) ||
                  $_SESSION['HTTP_USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']) {
         $this->destroy();
      }
   }

   /**
    * Generate a new session id
    */
   public function generateId() {
      return strongRand(30);
   }

   /**
    * Generate a new session id
    */
   abstract public function regenerateId();

   /**
    * Save the session
    */
   abstract public function save();

   /**
    * Get the session array
    */
   abstract public function getArray();

   /**
    * Create a new session
    */
   abstract public function create();

   /**
    * Check if a session variable is set
    */
   abstract public function exists($a);

   /**
    * Set a session variable
    */
   abstract public function set($a, $b = null);

   /**
    * Get a session variable
    */
   abstract public function get($key);

   /**
    * Destroy a session
    */
   abstract public function destroy();
}

?>
