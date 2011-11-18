<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Controllers
 */

require_once(__DIR__ . "/../util/error.php");
require_once(__DIR__ . "/../util/util.php");
require_once(__DIR__ . "/../util/mobile.php");
require_once(__DIR__ . "/../util/helper.php");
require_once(__DIR__ . "/../util/rand.php");
require_once(__DIR__ . "/../../config/config.php");

/**
 * This is the base class for application controllers.
 */
abstract class Controller {
   /**
    * Stores the session.
    */
   public $session;

   /**
    * This will be set to true if the
    * request came from a mobile browser.
    * @var boolean
    */
   public $is_mobile;

   /**
    * Status of save method.
    * @var string
    */
   public $save_status;

   /**
    * If the format allows template rendering,
    * such as html, all data for the template
    * should be stored in this variable.
    * @var array
    */
   public $data;

   /**
    * This is used to make sure we only check
    * for mobile browsers once per request.
    * @var boolean
    * @ignore
    */
   static public $mobile_set = false;

   /**
    * Will be set by the bouncer.
    * Determines if the save function should be executed.
    * @var boolean
    * @ignore
    */
   public $do_save = false;

   /**
    * Will be set by the bouncer.
    * Determines if the run function should be executed.
    * @var boolean
    * @ignore
    */
   public $do_run = false;

   public function __construct() {
      $this->config = getZombieConfig();
      $this->json = array();
      $this->view_base = classToUnderscore(get_class($this));
      $sess_class = underscoreToClass($this->config['session']['type'] . '_' . 'session');
      $this->session = $sess_class::getSession();
      $this->mobileInit();
      $this->bouncer = "Basic";
      $this->renderer = "Html";
   }

   /**
    * Check if we are coming from a mobile browser and set
    * the $mobile_set variable for use by controllers.
    * @ignore
    */
   function mobileInit() {
      if (!Controller::$mobile_set && isset($_GET['mobile'])) {
         $this->is_mobile = (boolean)$_GET['mobile'];
         $mobile_device = isMobile($_SERVER['HTTP_USER_AGENT']);
         if (!$this->is_mobile && $mobile_device) {
            $cookie = 'o';
         } else if ($this->is_mobile) {
            $cookie = 'y';
         } else {
            $cookie = 'n';
         }
         setcookie('m', $cookie, time() + 86400);
         $_COOKIE['m'] = $cookie;
         Controller::$mobile_set = true;
      } else if (!isset($_COOKIE['m'])) {
         $this->is_mobile = isMobile($_SERVER['HTTP_USER_AGENT']);
         $cookie = ($this->is_mobile ? 'y' : 'n');
         setcookie('m', $cookie, time() + 31536000);
         $_COOKIE['m'] = $cookie;
      } else {
         $this->is_mobile = ($_COOKIE['m'] == 'y' ? true : false);
      }
   }

   /**
    * Prepare some basic variables for the request.
    * @ignore
    */
   function prepare($action, $request) {
      if (is_null($action) && !empty($_REQUEST['action'])) {
         $this->action = $_REQUEST['action'];
      } else if (is_null($action)) {
         $this->action = 'index';
      } else {
         $this->action = $action;
      }
      $this->view = $this->action;
      $this->request = is_null($request) ? $_REQUEST : $request;
      $this->format = isset($this->request['format']) ? $this->request['format'] : 'html';
      if ($this->format == 'json') {
         $this->renderer = 'Json';
      }
      if ($this->format == 'xml') {
         $this->renderer = 'Xml';
      }
      $this->data = array();
   }

   /**
    * This function will be called prior to executing
    * the run method for a request. It can be overridden
    * by application controllers.
    */
   public function init() {
   }

   public function run($action = null, $request = null) {
      $this->prepare($action, $request);
      $this->init();

      if (!is_object($this->bouncer)) {
         $bouncer_class = $this->bouncer . "Bouncer";
         require_once(__DIR__ . "/bouncers/$bouncer_class.php");
         $this->bouncer = new $bouncer_class();
      }
      if (!is_object($this->renderer)) {
         $renderer_class = $this->renderer . "Renderer";
         require_once(__DIR__ . "/renderers/$renderer_class.php");
         $this->renderer = new $renderer_class();
      }

      $this->bouncer->bounce($this);
      try {
         $this->execute();
      } catch (Exception $e) {
         $this->handleException($e);
      }
      $this->renderer->render($this);
   }

   /**
    * Find the appropriate method, execute it,
    * and render the corresponding template.
    * @ignore
    */
   function execute() {
      if ($this->do_save) {
         $save_func = underscoreToMethod($this->action) . "Save";
         if (method_exists($this, $save_func)) {
            $this->$save_func($this->request);
         }
      }
      if ($this->do_run) {
         $run_func = underscoreToMethod($this->action) . "Run";
         if (method_exists($this, $run_func)) {
            $this->$run_func($this->request);
         } else if (method_exists($this, 'defaultRun')) {
            $this->view = 'default';
            $this->defaultRun($this->request);
         } else {
            $this->renderer = "FourOhFour";
         }
      }
   }

   /**
    * Rather than let an exception eat the entire stack,
    * catch it and report it in a pretty format.
    * @ignore
    */
   function handleException($e) {
      if ($this->config['env'] == 'dev') {
         $this->error((string)$e);
      } else {
         $this->error($e->getMessage());
      }
   }


   /**
    * Returns the current csrf token or
    * generates a new one if none exists.
    */
   function getCsrfToken() {
      $token = $this->session->get('csrf_token');
      if (!$token) {
         $token = strongRand(32);
         $token = preg_replace("/[+=\/]/", "", $token);
         $this->session->set('csrf_token', $token);
      }
      return $token;
   }

   /**
    * Use this function to send an error message to the frontend.
    * It will arrive if the format is json or html.
    * @param string $message the error message
    */
   public function error($message) {
      if (empty($this->errors)) {
         $this->errors = array();
      }
      array_push($this->errors, $message);
   }

   /**
    * Use this function to send a warning to the frontend.
    * It will arrive if the format is json or html.
    * @param string $message the error message
    */
   public function warn($message) {
      if (empty($this->warnings)) {
         $this->warnings = array();
      }
      array_push($this->warnings, $message);
   }

   /**
    * Use this function to send a message to the frontend.
    * It will arrive if the format is json or html.
    * @param string $message the error message
    */
   public function message($message) {
      if (empty($this->messages)) {
         $this->messages = array();
      }
      array_push($this->messages, $message);
   }

}

?>
