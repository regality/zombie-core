<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Util
 */

require_once(__DIR__ . "/../../config/config.php");

/**
 * Turn an array into html tag attributes
 * @param array $attrs attribute key value pairs
 * @return string
 */
function attrsToString($attrs) {
   $html_attrs = '';
   foreach ($attrs as $attr => $value) {
      $html_attrs .= " " . $attr . "=\"" . htmlentities($value) . "\"";
   }
   return $html_attrs;
}

/**
 * Helper function for image uri.
 * @param string $uri image uri
 * @param string $mode prod or dev mode
 * @param string $version image build version
 * @return string
 */
function imgUri($uri, $mode, $version, $web_root) {
   $pat = "/^\/images\/([a-z0-9_]+\/[a-z_-]+\.[a-z]+)/i";
   if ($mode == 'prod' && preg_match($pat, $uri, $matches)) {
      $uri = $web_root . '/build/images/' . $version . '/' . $matches[1];
   }
   return $uri;
}

/**
 * Helper to create an img tag
 * @param string $uri image uri
 * @param array $attrs attribute key value pairs
 * @param boolean $return if set to true a string is returned
 * @return string
 */
function img($uri, $attrs = array(), $return = false) {
   static $mode = false;
   static $version = false;
   static $web_root = false;
   if ($mode === false) {
      $config = getZombieConfig();
      $mode = $config['env'];
      $web_root = $config['web_root'];
      if ($web_root == '/') {
         $web_root = '';
      }
      if ($mode == 'prod') {
         require_once($config['zombie_root'] . "/config/version.php");
         $version = version();
         $version = $version['images'];
      }
   }

   $html_attrs = attrsToString($attrs);

   $uri = imgUri($uri, $mode, $version, $web_root);
   $tag = "<img src=\"$uri\" $html_attrs />";
   if ($return) {
      return $tag;
   } else {
      echo $tag;
   }
}

/**
 * Helper for a youtube video
 * @param string $code the code for the youtube video
 * @param boolean $return if set to true a string is returned
 * @return string
 */
function youtubeVideo($code, $return = false) {
   $tag = "<iframe width=\"560\" height=\"315\" " .
          "src=\"http://www.youtube.com/embed/$code\" frameborder=\"0\" " .
          "allowfullscreen></iframe>";
   if ($return) {
      return $tag;
   } else {
      echo $tag;
   }
}

?>
