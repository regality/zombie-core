<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . '/../../../config/config.php');
require_once(__DIR__ . '/../dir.php');

function getCompiledJs($files, $level = 'SIMPLE_OPTIMIZATIONS') {
   $cmd = "java -jar " . __DIR__ . "/closure-compiler/compiler.jar " .
          "--compilation_level=$level " .
          "--warning_level=QUIET ";
   foreach ($files as $file) {
      $cmd .= "--js $file ";
   }
   $compiled_js = shell_exec($cmd);
   return $compiled_js;
}

function updateJsCssVersion($js_version, $css_version) {
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $main_js_file = $root . "/web/build/js/" . $js_version . "/main.js";
   $main_js = file_get_contents($main_js_file);
   $main_js = preg_replace("/zs\.settings\.cssVersion\s*=\s*\"[0-9a-f]+\"/",
                           "zs.settings.cssVersion=\"$css_version\"",
                           $main_js);
   file_put_contents($main_js_file, $main_js);
}

function getJsCompileConfig() {
   $config = getZombieConfig();
   $root = $config['zombie_root'];

   $js_config = array("main" => array(),
                      "module" => array(),
                      "standalone" => array(),
                      "nocompile" => array());

   $apps_list = getDirContents($root . "/apps/");
   foreach ($apps_list as $app) {
      $js_config = mergeJsConfig($js_config, getAppJsConfig($app));
   }
   return $js_config;
}

function mergeJsConfig($config_a, $config_b) {
   $js_config = array("main" => array(),
                      "module" => array(),
                      "standalone" => array(),
                      "nocompile" => array());
   foreach ($js_config as $type => $list) {
      $js_config[$type] = array_merge($config_a[$type], $config_b[$type]);
   }
   return $js_config;
}

function getAppJsConfig($app) {
   static $finished_apps = array();
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $js_config = array("main" => array(),
                      "module" => array(),
                      "standalone" => array(),
                      "ignore" => array(),
                      "nocompile" => array());
   if (in_array($app, $finished_apps)) {
      return $js_config;
   }
   $js_config["module"][$app] = array();
   array_push($finished_apps, $app);

   $config_file = "$root/apps/$app/config/compile.json";
   if (file_exists($config_file)) {
      $contents = file_get_contents($config_file);
      $config_arr = json_decode($contents, true);
      if (!$config_arr) {
         echo "ERROR: Possible problem with compile config for `$app`";
      }
      $config_arr = $config_arr['javascript'];
   }
   if (isset($config_arr) && is_array($config_arr)) {
      foreach ($config_arr as $config_type => $files) {
         if ($config_type == "dependencies") {
            foreach ($files as $dep_app) {
               $dep_config = getAppJsConfig($dep_app);
               $js_config = mergeJsConfig($dep_config, $js_config);
            }
         } else {
            foreach ($files as $file) {
               if ($config_type == "module") {
                  array_push($js_config[$config_type][$app], $file . ".js");
               } else {
                  $js_file = array("app" => $app, "file" => $file . ".js");
                  array_push($js_config[$config_type], $js_file);
               }
            }
         }
      }
   } else {
      $module_js_dir = "$root/apps/$app/views/scripts/";
      $module_js = getDirContents($module_js_dir, array("file"));
      foreach ($module_js as $js_file) {
         array_push($js_config["module"][$app], $js_file);
      }
   }
   if (empty($js_config["module"][$app])) {
      unset($js_config["module"][$app]);
   }
   return $js_config;
}

function compileJs($version, $old_version, $css_version, $images_version) {
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $js_config = getJsCompileConfig();
   $base_dir = "$root/web/build/js/$version";
   exec("rm -rf $root/web/build/js/" . $old_version);
   exec("mkdir -p $base_dir");
   if (!file_exists(__DIR__ . "/tmp")) {
      mkdir(__DIR__ . "/tmp");
   }
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = getDirContents($apps_dir, array('dir'));

   foreach ($apps as $app) {
      $create_dir = false;
      foreach ($js_config['standalone'] as $arr) {
         if ($arr["app"] == $app) {
            $create_dir = true;
         }
      }
      foreach ($js_config['nocompile'] as $arr) {
         if ($arr["app"] == $app) {
            $create_dir = true;
         }
      }
      $templates = getDirContents($apps_dir . $app . "/views/.compiled/", array("file"));
      foreach ($templates as $template) {
         if (substr($template, -3) == '.js') {
            $create_dir = true;
            if (!isset($js_config['module'][$app])) {
               $js_config['module'][$app] = array();
            }
            break;
         }
      }
      if ($create_dir || !empty($js_config['module'][$app])) {
         echo "creating dir $base_dir/$app\n";
         mkdir("$base_dir/$app");
      }
   }

   $main_files = array();
   $loaded_js = "var zs = zs || {};\n" .
                "zs.settings = zs.settings || {};\n" .
                "zs.settings.mode = \"prod\";\n" .
                "zs.settings.version = \"$version\";\n" .
                "zs.settings.cssVersion = \"$css_version\";\n" .
                "zs.settings.imagesVersion = \"$images_version\";\n" .
                "zs.util = zs.util || {};\n" .
                "zs.util.scripts = zs.util.scripts || {};\n";
   foreach ($js_config['main'] as $main) {
      $dir = realpath(__DIR__ . "/../../../apps/" .
                      $main["app"] . "/views/scripts");
      $file = $dir . '/' . $main["file"];
      array_push($main_files, $file);
      $loaded_js .= "zs.util.scripts[\"/build/js/{$version}/{$main["app"]}/{$main["file"]}\"] = \"loaded\";\n";
   }
   echo "\nCOMPILING MAIN JS:\n   ";
   echo implode("\n  ", $main_files) . "\n";
   $tmp_file = __DIR__ . "/tmp/tmp.js";
   file_put_contents($tmp_file, $loaded_js);
   array_unshift($main_files, $tmp_file);
   $main_js = getCompiledJs($main_files);
   $write_file = $base_dir . "/main.js";
   echo "WRITING MAIN JS: $write_file\n";
   file_put_contents($write_file, $main_js);

   echo "\nCOMPILING STANDALONE JS\n\n";
   foreach ($js_config['standalone'] as $standalone) {
      $dir = realpath(__DIR__ . "/../../../apps/" . $standalone["app"] . "/views/scripts");
      $file = $dir . '/' . $standalone["file"];
      echo "compiling $file\n";
      $standalone_compiled = getCompiledJs(array($file));
      $write_file = $base_dir . "/" . $standalone["app"] . "/" . $standalone["file"];
      echo "writing $write_file\n\n";
      file_put_contents($write_file, $standalone_compiled);
   }

   echo "\nCOPYING NOCOMPILE JS\n\n";
   foreach ($js_config['nocompile'] as $nocompile) {
      $dir = realpath(__DIR__ . "/../../../apps/" . $nocompile["app"] . "/views/scripts");
      $file = $dir . '/' . $nocompile["file"];
      echo "copying $file\n";
      $write_file = $base_dir . "/" . $nocompile["app"] . "/" . $nocompile["file"];
      echo "to $write_file\n\n";
      file_put_contents($write_file, file_get_contents($file));
   }

   echo "\nCOMPILING MODULE JS\n\n";
   foreach ($js_config['module'] as $app => $js_files) {
      $templates = getDirContents($apps_dir . $app . "/views/.compiled/", array("file"));
      foreach ($templates as $key => $template) {
         if (substr($template, -3) !== '.js') {
            unset($templates[$key]);
         }
      }
      if (count($js_files) > 0 || count($templates) > 0) {
         echo "compiling module $app\n   ";
         $dir = realpath(__DIR__ . "/../../../apps/" . $app . "/views/scripts");
         $tdir = realpath(__DIR__ . "/../../../apps/" . $app . "/views/.compiled");
         $read_files = array();
         $loaded_js = '';
         foreach ($templates as $template) {
            array_push($read_files, $tdir . '/' . $template);
            $loaded_js .= "zs.util.scripts[\"/build/js/{$version}/{$app}/template/{$template}\"] = \"loaded\";\n";
         }
         foreach ($js_files as $js_file) {
            array_push($read_files, $dir . '/' . $js_file);
            $loaded_js .= "zs.util.scripts[\"/build/js/{$version}/{$app}/{$js_file}\"] = \"loaded\";\n";
         }
         echo implode("\n   ", $read_files);
         echo "\n";
         $compiled = $loaded_js . getCompiledJs($read_files);
         $write_file = $base_dir . "/" . $app . "/main.js";
         echo "writing $write_file\n\n";
         file_put_contents($write_file, $compiled);
      } else {
         echo "skipping module $app (no javascript)\n\n";
      }
   }
   exec("rm -rf " . __DIR__ . "/tmp");
}

?>
