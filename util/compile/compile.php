<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . '/compileJs.php');
require_once(__DIR__ . '/compileCss.php');
require_once(__DIR__ . '/compileImages.php');

function writeVersion($css, $js, $images) {
   $php_str = "<?php /* Auto-generated. Do not touch. */\n" .
              "function version() { " .
              "return array('css' => '$css', 'js' => '$js', 'images' => '$images'); " .
              "}\n?" . ">\n";
   file_put_contents(__DIR__ . "/../../../config/version.php", $php_str);
}

function compile($options) {
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   if (@include(__DIR__ . "/../../../config/version.php")) {
      $old_version = version();
   } else {
      $old_version = array('css' => 'css',
                           'js' => 'js',
                           'images' => 'images');
   }
   $version = uniqid();
   $compile_css = false;
   $compile_js = false;
   $compile_images = false;
   if (isset($options['css'])) {
      $compile_css = true;
      $compile_js = true;
   }
   if (isset($options['js'])) {
      $compile_js = true;
      $compile_css = true;
   }
   if (isset($options['images'])) {
      $compile_images = true;
      $compile_css = true;
   }
   if (isset($options['all']) ||
      (!$compile_css && !$compile_js && !$compile_images))
   {
      $compile_css = true;
      $compile_js = true;
      $compile_images = true;
   }
   $css_version = ($compile_css ? $version : $old_version['css']);
   $js_version = ($compile_js ? $version : $old_version['js']);
   $images_version = ($compile_images ? $version : $old_version['images']);
   writeVersion($css_version, $js_version, $images_version);
   if ($compile_css) {
      compileCss($css_version, $old_version['css'], $images_version);
   }
   if ($compile_js) {
      compileJs($js_version, $old_version['js'], $css_version, $images_version);
   }
   if ($compile_images) {
      copyImages($images_version, $old_version['images']);
   }
}

?>
