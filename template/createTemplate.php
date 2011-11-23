<?php

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../util/dir.php');
require_once('TemplatePHP.php');
require_once('TemplateJS.php');

function compileTemplates() {
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $apps = getDirContents($root . "/apps/", array("dir"));
   foreach ($apps as $app) {
      $view_dir = "$root/apps/$app/views/";
      $views = getDirContents($view_dir, array("file"));
      foreach ($views as $view) {
         $file_in = $view_dir . $view;
         $parts = pathinfo($file_in);
         $view_name = $parts['filename'];
         $ext = $parts['extension'];
         $compile_dir = $view_dir . ".compiled/";
         $file_out_php = $compile_dir . $view_name . ".php";
         $file_out_js = $compile_dir . $view_name . ".js";
         if (!file_exists($compile_dir)) {
            mkdir($compile_dir);
         }
         if ($ext == 'html') {
            $php_code = getPHPTemplate($app, $view_name);
            $js_code = getJSTemplate($app, $view_name);
            echo "writing $file_out_php\n\n";
            file_put_contents($file_out_php, $php_code);
            echo "writing $file_out_js\n\n";
            file_put_contents($file_out_js, $js_code);
         } else if ($ext == 'js') {
            echo "copying $file_in\nto $file_out_js\n\n";
            copy($file_in, $file_out_js);
         } else if ($ext == 'php') {
            echo "copying $file_in\nto $file_out_php\n\n";
            copy($file_in, $file_out_php);
         }
      }
   }
}

function getPHPTemplate($app, $view, $compress = false) {
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $input_file = "$root/apps/$app/views/$view.html";
   $php_file = "$root/apps/$app/views/$view.php";
   if (!file_exists($input_file) && file_exists($php_file)) {
      return file_get_contents($php_file);
   }
   $php_code = getTemplateOutput($input_file, 'PHP', $compress);
   return $php_code;
}

function getJSTemplate($app, $view, $compress = true) {
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   $input_file = "$root/apps/$app/views/$view.html";
   $js_file = "$root/apps/$app/views/$view.js";
   if (file_exists($input_file)) {
      $function_declare = "zs.template.templates['$app/$view'] = function";
      $js_code = getTemplateOutput($input_file, 'JS', $compress, $function_declare);
      return $js_code;
   } else if (file_exists($js_file)) {
      $js_code = file_get_contents($js_file);
      $js_code = "zs.template.templates['$app/$view'] = function() { " .
                 "return (" . trim($js_code) . ")();}\n";
      return $js_code;
   }
}

function getTemplateOutput($file_in, $lang, $compress = false,
                           $js_function = 'template') {
   $lang = strtoupper($lang);
   $class = 'Template' . $lang;
   $t = new $class($file_in, true, $compress);
   $template = $t->render($js_function);
   return $template;
}

?>
