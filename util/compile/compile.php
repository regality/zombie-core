<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . '/ssp.php');
require_once(__DIR__ . '/../dir.php');

function get_compiled_js($files, $level = 'SIMPLE_OPTIMIZATIONS') {
   $cmd = "java -jar " . __DIR__ . "/closure-compiler/compiler.jar " .
          "--compilation_level=$level " .
          "--warning_level=QUIET ";
   foreach ($files as $file) {
      $cmd .= "--js $file ";
   }
   $compiled_js = shell_exec($cmd);
   return $compiled_js;
}

function compile_js($version) {
   $xml_config = simplexml_load_file(__DIR__ . "/../../../config/javascript.xml");
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = get_dir_contents($apps_dir, array('dir'));
   $base_dir = realpath(__DIR__ . "/../../../web/build/" . $version . "/js");

   $compile = array("main" => array(),
                    "modules" => array(),
                    "nocompile" => array(),
                    "standalone" => array());

   foreach ($apps as $app) {
      $js_dir = __DIR__ . "/../../../apps/" . $app . "/views/scripts/";
      $js_files = get_dir_contents($js_dir, array("file"));
      $module_files = array();
      if (!$js_files) {
         $js_files = array();
      }
      foreach ($js_files as $js_file) {
         if (substr_compare($js_file, ".js", -3) === 0) {
            array_push($module_files, $js_file);
         }
      }
      $compile['modules'][$app] = $module_files;
   }

   foreach ($xml_config->app as $app_cfg) {
      $app_name = (string)$app_cfg['name'];
      if (isset($app_cfg->main)) {
         foreach ($app_cfg->main->script as $script) {
            foreach ($compile['modules'][$app_name] as $key => $file) {
               if ($file == $script['name'] . '.js') {
                  unset($compile['modules'][$app_name][$key]);
               }
            }
            array_push($compile['main'], array($app_name, $script['name'] . '.js'));
         }
      }

      if (isset($app_cfg->standalone)) {
         foreach ($app_cfg->standalone->script as $script) {
            foreach ($compile['modules'][$app_name] as $key => $file) {
               if ($file == $script['name'] . '.js') {
                  unset($compile['modules'][$app_name][$key]);
               }
            }
            array_push($compile['standalone'], array($app_name, $script['name'] . '.js'));
         }
      }

      if (isset($app_cfg->nocompile)) {
         foreach ($app_cfg->nocompile->script as $script) {
            foreach ($compile['modules'][$app_name] as $key => $file) {
               if ($file == $script['name'] . '.js') {
                  unset($compile['modules'][$app_name][$key]);
               }
            }
            array_push($compile['nocompile'], array($app_name, $script['name'] . '.js'));
         }
      }

      if (isset($app_cfg->ignore)) {
         foreach ($app_cfg->ignore->script as $script) {
            foreach ($compile['modules'][$app_name] as $key => $file) {
               if ($file == $script['name'] . '.js') {
                  unset($compile['modules'][$app_name][$key]);
               }
            }
         }
      }

      if (isset($app_cfg->module)) {
         $compile['modules'][$app_name] = array();
         $module_files = array();
         foreach ($app_cfg->module->script as $script) {
            array_push($module_files, $script['name'] . '.js');
         }
         $compile['modules'][$app_name] = $module_files;
      }
   }

   foreach ($apps as $app) {
      $create_dir = false;
      foreach ($compile['standalone'] as $arr) {
         if ($arr[0] == $app) {
            $create_dir = true;
         }
      }
      foreach ($compile['nocompile'] as $arr) {
         if ($arr[0] == $app) {
            $create_dir = true;
         }
      }
      if ($create_dir || count($compile['modules'][$app]) > 0) {
         mkdir(__DIR__ . "/../../../web/build/" . $version . "/js/" . $app);
      }
   }

   $main_files = array();
   $loaded_js = "var zs = zs || {};\n" .
                "zs.settings = zs.settings || {};\n" .
                "zs.settings.mode = \"prod\";\n" .
                "zs.settings.version = \"$version\";\n" .
                "zs.util = zs.util || {};\n" .
                "zs.util.scripts = zs.util.scripts || {};\n";
   foreach ($compile['main'] as $main) {
      $dir = realpath(__DIR__ . "/../../../apps/" . $main[0] . "/views/scripts");
      $file = $dir . '/' . $main[1];
      array_push($main_files, $file);
      $loaded_js .= "zs.util.scripts[\"/build/{$version}/js/{$main[0]}/{$main[1]}\"] = \"loaded\";\n";
   }
   echo "\nCOMPILING MAIN JS:\n   ";
   echo implode("\n   ", $main_files) . "\n";
   $tmp_file = __DIR__ . "/tmp/tmp.js";
   file_put_contents($tmp_file, $loaded_js);
   array_unshift($main_files, $tmp_file);
   $main_js = get_compiled_js($main_files);
   $write_file = $base_dir . "/main.js";
   echo "WRITING MAIN JS: $write_file\n";
   file_put_contents($write_file, $main_js);

   echo "\nCOMPILING STANDALONE JS\n\n";
   foreach ($compile['standalone'] as $standalone) {
      $dir = realpath(__DIR__ . "/../../../apps/" . $standalone[0] . "/views/scripts");
      $file = $dir . '/' . $standalone[1];
      echo "compiling $file\n";
      $standalone_compiled = get_compiled_js(array($file));
      $write_file = $base_dir . "/" . $standalone[0] . "/" . $standalone[1];
      echo "writing $write_file\n\n";
      file_put_contents($write_file, $standalone_compiled);
   }

   echo "\nCOPYING NOCOMPILE JS\n\n";
   foreach ($compile['nocompile'] as $nocompile) {
      $dir = realpath(__DIR__ . "/../../../apps/" . $nocompile[0] . "/views/scripts");
      $file = $dir . '/' . $nocompile[1];
      echo "copying $file\n";
      $write_file = $base_dir . "/" . $nocompile[0] . "/" . $nocompile[1];
      echo "to $write_file\n\n";
      file_put_contents($write_file, file_get_contents($file));
   }

   echo "\nCOMPILING MODULE JS\n\n";
   foreach ($compile['modules'] as $app => $js_files) {
      if (count($js_files) > 0) {
         echo "compiling module $app\n   ";
         $dir = realpath(__DIR__ . "/../../../apps/" . $app . "/views/scripts");
         $read_files = array();
         $loaded_js = '';
         foreach ($js_files as $js_file) {
            array_push($read_files, $dir . '/' . $js_file);
            $loaded_js .= "zs.util.scripts[\"/build/{$version}/js/{$app}/{$js_file}\"] = \"loaded\";\n";
         }
         echo implode("\n   ", $read_files);
         echo "\n";
         $compiled = $loaded_js . get_compiled_js($read_files);
         $write_file = $base_dir . "/" . $app . "/main.js";
         echo "writing $write_file\n\n";
         file_put_contents($write_file, $compiled);
      } else {
         echo "skipping module $app (no javascript)\n\n";
      }
   }
}

function get_css_file_lists() {
<<<<<<< HEAD
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = get_dir_contents($apps_dir, array('dir'));

   $files = array("main" => array(),
                  "mobile-main" => array());
   $main = array();
   $mobile_main = array();
   foreach ($apps as $app) {
      $css_file = __DIR__ . "/../../../apps/" . $app . "/views/css/main.css";
      $mobile_css_file = __DIR__ . "/../../../apps/" . $app . "/views/css/mobile-main.css";
      if (file_exists($css_file)) {
         array_push($files['main'], $css_file);
      }
      if (file_exists($mobile_css_file)) {
         array_push($files['mobile-main'], $mobile_css_file);
      } else if (file_exists($css_file)) {
         array_push($files['mobile-main'], $css_file);
      }
   }

   $xml_config_file = __DIR__ . "/../../../config/stylesheets.xml";
   if (file_exists($xml_config_file)) {
      $xml_config = simplexml_load_file(__DIR__ . "/../../../config/stylesheets.xml");
      foreach ($xml_config->file as $file) {
         $name = (string)$file['name'];
         $files[$name] = array();
         foreach ($file->source as $source) {
            $tmp = explode("/", $source['name']);
            $filename = __DIR__ . "/../../../apps/" . $tmp[0] .
                                  "/views/css/" . $tmp[1] . ".css";
            if (file_exists($filename)) {
               array_push($files[$name], $filename);
            } else {
               echo "error: file does not exist: $filename\n";
            }
         }
=======
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = get_dir_contents($apps_dir, array('dir'));
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

   $xml = simplexml_load_file($root . "/config/stylesheets.xml");
   foreach ($xml->file as $file) {
      $name = (string)$file['name'];
      $files[$name] = array();
      foreach ($file->source as $source) {
         $tmp = explode("/", $source['name']);
         $filename = $root . "/apps/" . $tmp[0] . "/views/css/" . $tmp[1] . ".css";
         array_push($files[$name], $filename);
>>>>>>> new_branch_name
      }
   }
   return $files;
}

<<<<<<< HEAD
function compile_css_list($list, $minify = false, $version = false) {
   $css_output = '';
   foreach ($list as $source) {
      $contents = file_get_contents($source);
      $c = new CssFile($contents, $version);
      $css_output .= $c->render($minify);
   }
   return $css_output;
}

function compile_css($version) {
   echo "COMPILING CSS\n";
   $files = get_css_file_lists();
   foreach ($files as $file => $sources) {
      $css_output = compile_css_list($sources, $version);
      $write_file = realpath(__DIR__ . "/../../../web/build/" .
                             $version . "/css") . "/$file.css";
      echo "writing $write_file\n";
      file_put_contents($write_file, $css_output);
=======
   return $files;
}

function compile_css_list($list, $minify = false, $version = false) {
   $compiled_css = '';
   foreach ($list as $source) {
      $css = file_get_contents($source);
      $c = new CssFile($css, $version);
      $compiled_css .= $c->render($minify);
   }
   return $compiled_css;
}

function compile_css($version) {
   echo "COMPILING CSS\n";
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $files = get_css_file_lists();
   foreach ($files as $file => $list) {
      $css = compile_css_list($list, true, $version);
      $out_file = $root . "/web/build/" . $version .  "/css/" . $file . ".css";
      echo "writing $out_file\n";
      file_put_contents($out_file, $css);
>>>>>>> new_branch_name
   }
}

function copy_images($version) {
   echo "COPYING IMAGES\n\n";
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = get_dir_contents($apps_dir, array('dir'));
   foreach ($apps as $app) {
      $images_src = realpath($apps_dir . $app . "/views/images");
      if (!$images_src) {
         continue;
      }
      $images = get_dir_contents($images_src . "/", array("file"));
      if (count($images) > 0) {
         $image_dest = realpath(__DIR__ . "/../../../web/build/" . $version . "/images") . "/" . $app;
         echo "creating dir " . $image_dest . "\n";
         mkdir($image_dest);
      }
      foreach ($images as $image) {
         echo "copying $images_src/$image\n" .
              "to $image_dest/$image\n\n";
         copy($images_src . '/' . $image, $image_dest . '/' . $image);
      }
   }
}

function write_version($version) {
   $php_str = "<?php /* auto-generated */ function version() { return '{$version}'; } ?" . ">\n";
   file_put_contents(__DIR__ . "/../../../config/version.php", $php_str);
}

function compile($options) {
   if (include(__DIR__ . "/../../../config/version.php")) {
      $old_version = version();
      if (!isset($options['keep_old'])) {
         exec("rm -rf " . __DIR__ . "/../../../web/build/" . $old_version);
      }
   }
   $version = substr(md5(microtime()), 0, 10);
   write_version($version);
   mkdir(__DIR__ . "/../../../web/build/" . $version);
   mkdir(__DIR__ . "/../../../web/build/" . $version . "/css");
   mkdir(__DIR__ . "/../../../web/build/" . $version . "/js");
   mkdir(__DIR__ . "/../../../web/build/" . $version . "/images");
   if (!file_exists(__DIR__ . "/tmp")) {
      mkdir(__DIR__ . "/tmp");
   }
   copy_images($version);
   compile_css($version);
   compile_js($version);
   exec("rm -rf " . __DIR__ . "/tmp");
}

?>
