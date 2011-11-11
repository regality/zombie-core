<?php

require_once(__DIR__ . "/../util/helper.php");

abstract class Template {
   public $parts = array();
   public $lang;

   function __construct($file, $force_file = false, $compress = false) {
      $this->compress = $compress;
      if (file_exists($file)) {
         $this->template = file_get_contents($file);
      } else if ($force_file) {
         trigger_error("File does not exist: $file\n", E_USER_ERROR);
      } else {
         $this->template = $file;
      }
      $this->template .= ' ';
      $this->parseTemplate();
   }

   function parseTemplate() {
      $mode = 'string';
      $tag = '';
      $current_str = '';
      $template = $this->template;
      for ($i = 0, $c = strlen($this->template) - 1; $i < $c; ++$i) {
         $char = $this->template[$i];
         $next_char = $this->template[$i + 1];
         if ($mode == 'string') {
            if ($char == '<' && $next_char == ':') {
               $mode = 'tag';
               $i += 1;
               $this->addString($current_str);
               $current_str = '';
               continue;
            } else {
               $current_str .= $char;
            }
         }
         if ($mode == 'tag') {
            if ($tag == '') {
               $params = '';
               if ($char == '*') {
                  $continue = true;
                  while ($continue) {
                     if ($template[$i    ] == '*' &&
                         $template[$i + 1] == ':' &&
                         $template[$i + 2] == '>')
                     {
                        if ($template[$i + 3] == "\n") {
                           ++$i;
                        }
                        $continue = false;
                        $i += 2;
                     } else {
                        $i++;
                     }
                  }
                  $mode = 'string';
                  continue;
               } else if ($char == '=') {
                  $tag = 'echo';
                  continue;
               } else if ($char == '?' && $next_char == '=') {
                  $tag = 'cecho';
                  ++$i;
                  continue;
               } else {
                  $tag = '';
                  while (preg_match('/\s/', $template[$i])) {
                     ++$i;
                  }
                  while (preg_match('/[a-zA-Z0-9_]/', $template[$i])) {
                     $tag .= $template[$i];
                     $i++;
                  }
                  $i--;
               }
            } else if ($char == ':' && $next_char == '>') {
               ++$i;
               $this->addTag($tag, $current_str);
               $current_str = '';
               $tag = '';
               $mode = 'string';
            } else {
               $current_str .= $char;
            }
         } // endif

      } //endfor
      if ($current_str) {
         $this->addString($current_str);
      }

   } //endfunction


   function addTag($tag, $contents) {
      $class = ucwords($tag) . "Tag" . strtoupper($this->lang);
      if (!class_exists($class)) {
         $class = "FunctionTag" . $this->lang;
      }
      $tag = new $class($tag, $contents, $this->compress);
      array_push($this->parts, $tag);
   }

   function addString($str) {
      $class = "TemplateString" . $this->lang;
      $str = new $class($str, $this->compress);
      array_push($this->parts, $str);
   }

   function render() {
      $str = '';
      foreach ($this->parts as $part) {
         $str .= $part;
      }
      return $str;
   }
}

class PhpToJs {
   public static $stack = array();
   static function pushVar($var, $replace) {
      array_push(PhPToJs::$stack, array($var, $replace));
   }

   static function popVar() {
      array_pop(PhPToJs::$stack);
   }

   static function replace($var) {
      foreach (PhPToJs::$stack as $replace) {
         $cvar = $replace[0];
         $nvar = $replace[1];
         if (strpos($var, $cvar) === 0) {
            return str_replace($cvar, $nvar, $var);
         }
      }
      return $var;
   }

   static function varToJsAll($code) {
      $var_pat = "/\\$([a-zA-z_].[a-zA-Z0-9_'\"\[\]]+)/";
      $callback = function($matches) {
         return PhpToJs::varToJs($matches[1]);
      };
      return preg_replace_callback($var_pat, $callback, $code);
   }

   static function varToJs($var) {
      $var = trim($var, '$');
      $var = str_replace("']['", ".", $var);
      $var = str_replace("\"][\"", ".", $var);
      $var = str_replace("['", ".", $var);
      $var = str_replace("[\"", ".", $var);
      $var = str_replace("']", "", $var);
      $var = str_replace("\"]", "", $var);
      $var = "data." . $var;
      $var = PhpToJs::replace($var);
      return $var;
   }
}

?>
