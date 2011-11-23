<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . '/ssp.php');
require_once(__DIR__ . '/../dir.php');

function getCssFileLists() {
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = getDirContents($apps_dir, array('dir'));
   $files = array("main" => array(),
                  "mobile-main" => array());
   foreach ($apps as $app) {
      $main_file = $root . "/apps/" . $app . "/views/css/main.css";
      $mobile_file = $root . "/apps/" . $app . "/views/css/mobile-main.css";
      if (file_exists($main_file)) {
         array_push($files['main'], $main_file);
      }
      if (file_exists($mobile_file)) {
         array_push($files['mobile-main'], $mobile_file);
      } else if (file_exists($main_file)) {
         array_push($files['mobile-main'], $main_file);
      }
   }

   foreach ($apps as $app) {
      $config_file = "$root/apps/$app/config/compile.json";
      if (file_exists($config_file)) {
         $contents = file_get_contents($config_file);
         $config_arr = json_decode($contents, true);
         $stylesheets = $config_arr['stylesheets'];
         foreach ($stylesheets as $name => $file_list) {
            if (!isset($files[$name])) {
               $files[$name] = array();
            }
            foreach ($file_list as $file) {
               $file_name = "$root/apps/$app/views/css/$file.css";
               if (file_exists($file_name)) {
                  array_push($files[$name], $file_name);
               } else {
                  echo "error: file does not exist: $filename\n";
               }
            }
         }
      }
   }

   return $files;
}

function compileCssList($list, $minify = false, $version = false, $images_version = false) {
   $compiled_css = '';
   foreach ($list as $source) {
      $css = file_get_contents($source);
      $c = new CssFile($css, $version, $images_version);
      $compiled_css .= $c->render($minify);
   }
   return $compiled_css;
}

function compileCss($version, $old_version, $images_version) {
   echo "COMPILING CSS\n";
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   exec("rm -rf $root/web/build/css/" . $old_version);
   exec("mkdir -p $root/web/build/css/" . $version);
   $files = getCssFileLists();
   foreach ($files as $file => $list) {
      $css = compileCssList($list, true, $version, $images_version);
      $out_file = $root . "/web/build/css/$version/$file.css";
      echo "writing $out_file\n";
      file_put_contents($out_file, $css);
   }
   echo "\n";
}

?>
