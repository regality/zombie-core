<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . '/ssp.php');
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

function compileJs($version, $css_version) {
   $xml_config = simplexml_load_file(__DIR__ . "/../../../config/javascript.xml");
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = getDirContents($apps_dir, array('dir'));
   $base_dir = realpath(__DIR__ . "/../../../web/build/js/" . $version);

   $compile = array("main" => array(),
                    "modules" => array(),
                    "nocompile" => array(),
                    "standalone" => array());

   foreach ($apps as $app) {
      $js_dir = __DIR__ . "/../../../apps/" . $app . "/views/scripts/";
      $js_files = getDirContents($js_dir, array("file"));
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
         mkdir(__DIR__ . "/../../../web/build/js/$version/$app");
      }
   }

   $main_files = array();
   $loaded_js = "var zs = zs || {};\n" .
                "zs.settings = zs.settings || {};\n" .
                "zs.settings.mode = \"prod\";\n" .
                "zs.settings.version = \"$version\";\n" .
                "zs.settings.cssVersion = \"$css_version\";\n" .
                "zs.util = zs.util || {};\n" .
                "zs.util.scripts = zs.util.scripts || {};\n";
   foreach ($compile['main'] as $main) {
      $dir = realpath(__DIR__ . "/../../../apps/" . $main[0] . "/views/scripts");
      $file = $dir . '/' . $main[1];
      array_push($main_files, $file);
      $loaded_js .= "zs.util.scripts[\"/build/js/{$version}/{$main[0]}/{$main[1]}\"] = \"loaded\";\n";
   }
   echo "\nCOMPILING MAIN JS:\n   ";
   echo implode("\n   ", $main_files) . "\n";
   $tmp_file = __DIR__ . "/tmp/tmp.js";
   file_put_contents($tmp_file, $loaded_js);
   array_unshift($main_files, $tmp_file);
   $main_js = getCompiledJs($main_files);
   $write_file = $base_dir . "/main.js";
   echo "WRITING MAIN JS: $write_file\n";
   file_put_contents($write_file, $main_js);

   echo "\nCOMPILING STANDALONE JS\n\n";
   foreach ($compile['standalone'] as $standalone) {
      $dir = realpath(__DIR__ . "/../../../apps/" . $standalone[0] . "/views/scripts");
      $file = $dir . '/' . $standalone[1];
      echo "compiling $file\n";
      $standalone_compiled = getCompiledJs(array($file));
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
}

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

   $xml_config_file = "$root/config/stylesheets.xml";
   if (file_exists($xml_config_file)) {
      $xml = simplexml_load_file($xml_config_file);
      foreach ($xml->file as $file) {
         $name = (string)$file['name'];
         $files[$name] = array();
         foreach ($file->source as $source) {
            $tmp = explode("/", $source['name']);
            $filename = $root . "/apps/" . $tmp[0] . "/views/css/" . $tmp[1] . ".css";
            if (file_exists($filename)) {
               array_push($files[$name], $filename);
            } else {
               echo "error: file does not exist: $filename\n";
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

function compileCss($version, $images_version) {
   echo "COMPILING CSS\n";
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $files = getCssFileLists();
   foreach ($files as $file => $list) {
      $css = compileCssList($list, true, $version, $images_version);
      $out_file = $root . "/web/build/css/$version/$file.css";
      echo "writing $out_file\n";
      file_put_contents($out_file, $css);
   }
}

function copyImages($version) {
   echo "COPYING IMAGES\n\n";
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = getDirContents($apps_dir, array('dir'));

   $xml_file = __DIR__ . "/../../../config/images.xml";
   $xml = simplexml_load_file($xml_file);

   $resize = array();
   foreach ($xml->app as $app) {
      $app_name = (string)$app['name'];
      foreach ($app->file as $file) {
         $name = (string)$file['name'];
         $size = (string)$file['resize'];
         array_push($resize, array('app' => $app_name,
                                   'name' => $name,
                                   'size' => $size));
      }
   }


   foreach ($apps as $app) {
      $images_src = realpath($apps_dir . $app . "/views/images");
      if (!$images_src) {
         continue;
      }
      $images = getDirContents($images_src . "/", array("file"));
      if (count($images) > 0) {
         $image_dest = realpath(__DIR__ . "/../../../web/build/images/" . $version) . "/" . $app;
         echo "creating dir " . $image_dest . "\n";
         mkdir($image_dest);
      }
      foreach ($images as $image) {
         $path_parts = pathinfo($images_src . "/" . $image);
         $ext = $path_parts['extension'];
         $img_source = $images_src . "/" . $image;
         $img_dest = $image_dest . "/" . $image;
         if (`which convert`) {
            foreach ($resize as $resize_info) {
               if ($app == $resize_info['app'] &&
                   $resize_info['name'] == $image)
               {
                  $size = $resize_info['size'];
                  echo "resizing $img_source\n";
                  exec("convert -resize $size $img_source $img_dest");
                  $img_source = $img_dest;
               }
            }
         }
         if ($ext == 'png' && `which pngcrush`) {
            echo "optimizing png\n";
            exec("pngcrush -rem alla -rem gAMA -rem cHRM -rem iCCP -rem sRGB -brute -reduce $img_source $img_dest");
         } else if ($ext == 'jpg' || $ext == 'jpeg') {
            echo "optimizing jpeg\n";
            exec("jpegtran -copy none -optimize -outfile $img_dest $img_source");
         } else {
            copy($img_source, $img_dest);
         }
         echo "copying $images_src/$image\n" .
              "to $image_dest/$image\n\n";

      }
   }
}

function writeVersion($css, $js, $images) {
   $php_str = "<?php /* auto-generated do not touch */\n" .
              "function version() {\n" .
              "   return array('css' => '$css', 'js' => '$js', 'images' => '$images');\n" .
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
   $compileCss = false;
   $compileJs = false;
   $compile_images = false;
   if (isset($options['css'])) {
      $compileCss = true;
   }
   if (isset($options['js'])) {
      $compileJs = true;
      $compileCss = true;
   }
   if (isset($options['images'])) {
      $compile_images = true;
      $compileCss = true;
   }
   if (isset($options['all']) ||
      (!$compileCss && !$compileJs && !$compile_images))
   {
      $compileCss = true;
      $compileJs = true;
      $compile_images = true;
   }
   $css_version = ($compileCss ? $version : $old_version['css']);
   $js_version = ($compileJs ? $version : $old_version['js']);
   $images_version = ($compile_images ? $version : $old_version['images']);
   writeVersion($css_version, $js_version, $images_version);
   if ($compileCss) {
      exec("rm -rf $root/web/build/css/" . $old_version['css']);
      exec("mkdir -p $root/web/build/css/" . $css_version);
      compileCss($css_version, $images_version);
      if (!$compileJs) {
         $main_js_file = $root . "/web/build/js/" . $js_version . "/main.js";
         $main_js = file_get_contents($main_js_file);
         $main_js = preg_replace("/zs\.settings\.cssVersion\s*=\s*\"[0-9a-f]+\"/",
                                 "zs.settings.cssVersion=\"$css_version\"",
                                 $main_js);
         file_put_contents($main_js_file, $main_js);
      }
   }
   if ($compileJs) {
      exec("rm -rf $root/web/build/js/" . $old_version['js']);
      exec("mkdir -p $root/web/build/js/" . $js_version);
      if (!file_exists(__DIR__ . "/tmp")) {
         mkdir(__DIR__ . "/tmp");
      }
      compileJs($js_version, $css_version);
      exec("rm -rf " . __DIR__ . "/tmp");
   }
   if ($compile_images) {
      exec("rm -rf $root/web/build/images/" . $old_version['images']);
      exec("mkdir -p $root/web/build/images/" . $images_version);
      copyImages($images_version);
   }
}

?>
