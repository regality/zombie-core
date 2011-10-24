<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . "/../../config/config.php");

function attrs_to_string($attrs) {
   $html_attrs = '';
   foreach ($attrs as $attr => $value) {
      $html_attrs .= " " . $attr . "=\"" . htmlentities($value) . "\"";
   }
   return $html_attrs;
}

function img_uri($uri, $mode, $version) {
   $pat = "/^\/images\/([a-z0-9_]+\/[a-z_-]+\.[a-z]+)/i";
   if ($mode == 'prod' && preg_match($pat, $uri, $matches)) {
      $uri = '/build/images/' . $version . '/' . $matches[1];
   }
   return $uri;
}

function img($uri, $attrs = array(), $return = false) {
   static $mode = false;
   static $version = false;
   if ($mode === false) {
      $config = getZombieConfig();
      $mode = $config['env'];
      if ($mode == 'prod') {
         require_once($config['zombie_root'] . "/config/version.php");
         $version = version();
         $version = $version['images'];
      }
   }

   $html_attrs = attrs_to_string($attrs);

   $uri = img_uri($uri, $mode, $version);
   $tag = "<img src=\"$uri\" $html_attrs />";
   if ($return) {
      return $tag;
   } else {
      echo $tag;
   }
}

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
