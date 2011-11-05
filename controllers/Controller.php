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
   protected $session;

   /**
    * This will be set to true if the
    * request came from a mobile browser.
    * @var boolean
    */
   protected $is_mobile;

   /**
    * Status of save method.
    * @var string
    */
   protected $save_status;

   /**
    * If the format is set to json,
    * all json data should be stored
    * in this variable.
    * @var array
    */
   protected $json;

   /**
    * If the format allows template rendering,
    * such as html, all data for the template
    * should be stored in this variable.
    * @var array
    */
   protected $data;

   /**
    * This is used to make sure we only check
    * for mobile browsers once per request.
    * @var boolean
    * @ignore
    */
   static protected $mobile_set = false;

   public function __construct() {
      $this->config = getZombieConfig();
      $this->json = array();
      $this->is_page = false;
      $this->is_secure = false;
      $this->view_base = classToUnderscore(get_class($this));
      $sess_class = underscoreToClass($this->config['session']['type'] . '_' . 'session');
      $this->session = $sess_class::getSession();
      $this->mobileInit();
   }

   /**
    * Check if we are coming from a mobile browser and set
    * the $mobile_set variable for use by controllers.
    * @access private
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
    * This function will be called prior to executing
    * the run method for a request. It can be overridden
    * by application controllers.
    */
   public function init() {
   }

   /**
    * The run method should be used to execute
    * a controller and have it render.
    * @param string $action action to be run
    * @param array $request request variables
    */
   public function run($action = null, $request = null) {
      if ($this->is_secure) {
         $this->runSecure($action, $request);
      } else {
         $this->prepare($action, $request);
         $this->init();
         $this->execute();
      }
   }

   /**
    * If the constructor is marked as secure,
    * the run method will defer to runSecure.
    * This method will only execute and render
    * if the user is properly authenticated.
    * @access private
    */
   function runSecure($action = null, $request = null) {
      $this->prepare($action, $request);
      if (!isset($this->secure_methods) || in_array($this->action, $this->secure_methods)) {
         $access = $this->hasAccess();
      } else {
         $access = true;
      }
      if ($access === true) {
         $this->execute();
      } else {
         if ($this->format == 'json') {
            $this->json['status'] = $access;
            $this->renderJson();
         } else {
            echo $access;
         }
      }
   }

   /**
    * Check if a user is authenticated and
    * belongs to a group if necessary.
    * @access private
    */
   function hasAccess() {
      if (!$this->session->exists('username')) {
         return "logged out";
      }
      if (isset($this->groups)) {
         $user_groups = $this->session->get('groups');
         if (!is_array($user_groups)) {
            return "access denied";
         }
         foreach ($this->groups as $group) {
            foreach ($user_groups as $user_group) {
               if ($user_group == $group) {
                  return true;
               }
            }
         }
         return 'access denied';
      } else {
         return true;
      }
   }

   /**
    * Prepare some basic variables for the request.
    * @access private
    */
   function prepare($action, $request) {
      $this->data = array();
      if (is_null($action) && !empty($_REQUEST['action'])) {
         $this->action = $_REQUEST['action'];
      } else if (is_null($action)) {
         $this->action = 'index';
      } else {
         $this->action = $action;
      }
      $this->view = $this->action;

      if (is_null($request)) {
         $this->request = $_REQUEST;
      } else {
         $this->request = $request;
      }
      $this->format = (isset($this->request['format']) ? $this->request['format'] : 'html');
   }

   /**
    * Find the appropriate method, execute it,
    * and render the corresponding template.
    * @access private
    */
   function execute() {
      try {
         $run_func = underscoreToMethod($this->action) . "Run";
         $this->saveSafe($this->action, $this->request);
         if (method_exists($this, $run_func)) {
            $this->$run_func($this->request);
         } else if (method_exists($this, 'defaultRun')) {
            $this->view = 'default';
            $this->defaultRun($this->request);
         } else {
            include($this->config['zombie_root'] .
                    '/apps/home/views/404.php');
            return;
         }
      } catch (Exception $e) {
         $this->handleException($e);
      }
      $this->render();
   }

   /**
    * Renders a template to the browser.
    * If the format is set to json it will
    * json encode the $json variable instead.
    * @access private
    */
   function render() {
      if ($this->format == 'json') {
         $errors = getErrorArray();
         if (!empty($errors)) {
            $this->json['php_errors'] = $errors;
         }
         if (!empty($this->errors)) {
            $this->json['errors'] = $this->errors;
         }
         if (!empty($this->warnings)) {
            $this->json['warnings'] = $this->warnings;
         }
         if (!empty($this->messages)) {
            $this->json['messages'] = $this->messages;
         }
         $this->renderJson();
      } else {
         $file = $this->config['zombie_root'] . "/apps/" . $this->view_base . 
                 "/views/" . $this->view . ".php";
         if (file_exists($file)) {
            foreach ($this->data as $var => $val) {
               $$var = $val;
            }
            if ($this->is_page) {
               if (!method_exists($menu, 'run')) {
                  require_once($this->config['zombie_root'] . '/apps/menu/menu.php');
                  $menu = new Menu(); 
               }
               if (isset($token)) {
                  $token= $this->getCsrfToken();
               }
               include($this->config['zombie_root'] . "/apps/home/views/open.php");
               include($file);
               include($this->config['zombie_root'] . "/apps/home/views/close.php");
            } else {
               include($file);
            }
         }
         renderErrorsJs();
         $this->renderJsMesg();
      }
   }

   /**
    * Rather than let an exception eat the entire stack,
    * catch it and report it in a pretty format.
    * @access private
    */
   function handleException($e) {
      if ($this->config['env'] == 'dev') {
         $this->error((string)$e);
      } else {
         $this->error($e->getMessage());
      }
   }

   /**
    * Turn the errors, warnings, and messages into
    * pretty javascript messages to be handled by
    * the frontend.
    * @access private
    */
   function renderJsMesg() {
      if (!empty($this->errors) ||
          !empty($this->warnings) ||
          !empty($this->messages)) {
         echo '<script type="text/javascript">';
         if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
               echo "zs.ui.error(\"" . 
                    htmlentities($error, ENT_QUOTES) . "\");\n";
            }
         }
         if (!empty($this->warnings)) {
            foreach ($this->warnings as $warning) {
               echo "zs.ui.warn(\"" . 
                    htmlentities($warning, ENT_QUOTES) . "\");\n";
            }
         }
         if (!empty($this->messages)) {
            foreach ($this->messages as $message) {
               echo "zs.ui.message(\"" . 
                    htmlentities($message, ENT_QUOTES) . "\");\n";
            }
         }
         echo '</script>';
      }
   }

   /**
    * json encode the $json variable and
    * send it to the browser.
    * @access private
    */
   function renderJson() {
      echo json_encode($this->json);
   }

   /**
    * Check if the current user belongs to a group.
    * @param string $group_name the name of the group
    * @access private
    */
   function inGroup($group_name) {
      $groups = $this->session->get('groups');
      if (is_array($groups) && in_array($group_name, $groups)) {
         return true;
      } else {
         return false;
      }
   }

   /**
    * Check for signs of a CSRF attack and only
    * run the save function if the coast is clear.
    * @param string $action save method to be run
    * @param array $request request variables
    * @access private
    */
   function saveSafe($action, $request) {
      $save_func = underscoreToMethod($action) . "Save";
      if (!method_exists($this, $save_func)) {
         return;
      }
      if (isset($_REQUEST['csrf'])) {
         $token = $this->session->get('csrf_token');
         $req_token = $_REQUEST['csrf'];
         $match = ($req_token == $token ? true : false);
         if (!$match) {
            $this->save_status = "bad csrf";
            $this->error("bad csrf");
            return;
         } else {
            if (isset($_SERVER['referer'])) {
               $domain = parse_url($_SERVER['referer']);
               $domain = $domain['host'];
               if ($domain != $this->config['domain']) {
                  $this->save_status = "bad referer";
                  return;
               }
            }
         }
      } else {
         $this->save_status = "no csrf";
         $this->error("no csrf");
         return;
      }
      $this->save_status = "success";
      $this->$save_func($request);
   }

   /**
    * Returns the current csrf token or
    * generates a new one if none exists.
    * @access private
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
