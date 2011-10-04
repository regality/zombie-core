<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . "/../../../config/config.php");

class CssSelector {
   function __construct($raw_css) {
      preg_match("/([^\{]*)\s*\{([^\}]*)\}/", $raw_css, $matches);
      $selector = $matches[1];
      $extends = explode("extends", $selector, 2);
      $this->selector = trim($extends[0]);
      $this->selector = str_replace("\n", "", $this->selector);
      $this->selector = str_replace(", ", ",", $this->selector);
      if (count($extends) > 1) {
         $this->super = trim($extends[1]);
      } else {
         $this->super = false;
      }

      $this->attrs = array();
      $attrs = explode(";", $matches[2]);
      foreach ($attrs as $attr) {
         $tmp = explode(":", $attr, 2);
         if (count($tmp) == 2) {
            $this->attrs[trim($tmp[0])] = trim($tmp[1]);
         }
      }
      $this->add_hacks();
   }

   function add_hacks() {
      $new_attrs = array();
      foreach ($this->attrs as $attr => $value) {
         switch ($attr) {
            case 'min-height':
               $new_attrs['height'] = "auto !important; height: $value";
               break;
            case 'display':
               if ($value == 'inline-block') {
                  $new_attrs['display'] = "-moz-inline-stack; display: inline-block";
                  $new_attrs['zoom'] = "1";
                  $new_attrs['*display'] = "inline";
               }
               break;
            case 'opacity':
               $new_attrs['-moz-opacity'] = $value;
               $new_attrs['-khtml-opacity'] = $value;
               $new_attrs['-ms-filter'] = '"progid:DXImageTransform.Microsoft.Alpha(opacity=' . intval($value * 100) . ')"';
               $new_attrs['filter'] = 'alpha(opacity=' . intval($value * 100) . ')';
               break;
            case 'text-shadow':
               $parts = preg_match("/(\d+)px (\d+)px (\d+)px #(.+)/", $value, $matches);
               $x = $matches[1];
               $y = $matches[2];
               $strength = $matches[3];
               $color = $matches[4];
               $angle = intval(atan($x/$y)*360/2/3.14159265358979323 + 90);
               // currently this kills opacity, better fix it
               $new_attrs['filter'] = "\"progid:DXImageTransform.Microsoft.Shadow(direction=$angle,strength=$strength,color=$color)\"";
               break;
            case 'box-shadow':
               $new_attrs['-webkit-box-shadow'] = $value;
               $new_attrs['-moz-box-shadow'] = $value;
               break;
            case 'border-radius':
               $new_attrs['-webkit-border-radius'] = $value;
               $new_attrs['-moz-border-radius'] = $value;
               break;
            case 'border-top-left-radius':
               $new_attrs['-moz-border-radius-topleft'] = $value;
               $new_attrs['-webkit-border-top-left-radius'] = $value;
               break;
            case 'border-top-right-radius':
               $new_attrs['-moz-border-radius-topright'] = $value;
               $new_attrs['-webkit-border-top-right-radius'] = $value;
               break;
            case 'border-bottom-left-radius':
               $new_attrs['-moz-border-radius-bottomleft'] = $value;
               $new_attrs['-webkit-border-bottom-left-radius'] = $value;
               break;
            case 'border-bottom-right-radius':
               $new_attrs['-moz-border-radius-bottomright'] = $value;
               $new_attrs['-webkit-border-bottom-right-radius'] = $value;
               break;
         }
      }
      $this->attrs = array_merge($this->attrs, $new_attrs);
   }

   function has_super() {
      return (boolean)$this->super;
   }

   function inherit($super) {
      foreach ($super->attrs as $attr => $value) {
         if (!isset($this->attrs[$attr])) {
            $this->attrs[$attr] = $value;
         }
      }
   }

   function render($minify = false) {
      if ($minify) {
         $css = $this->selector . '{';
         foreach ($this->attrs as $attr => $value) {
            $css .= $attr . ":" . $value . ";";
         }
         $css .= "}";
      } else {
         $css = $this->selector . " {\n";
         foreach ($this->attrs as $attr => $value) {
            $css .= "  " . $attr . ": " . $value . ";\n";
         }
         $css .= "}\n\n";
      }
      return $css;
   }
}

class CssFile {
   function __construct($css, $version = false) {
      $this->css = $css;
      $this->version = $version;
      $this->parse_css();
   }

   function render($minify = false) {
      $css = '';
      foreach ($this->selectors as $selector) {
         $css .= $selector->render($minify);
      }
      return $css;
   }

   function parse_css() {
      $this->css = $this->strip_comments($this->css);
      $this->substitute_includes();
      $this->substitute_vars();
      $this->build_urls();
      $matches = array();
      preg_match_all('/([^{]*)\s*{([^}]+)(?:\s+)?}(?:\s+)?/', $this->css, $matches);
      $this->selectors = array();
      for ($i = 0; $i < count($matches[0]); ++$i) {
         $selector = new CssSelector($matches[0][$i]);
         if (isset($this->selectors[$selector->selector])) {
            $selector->inherit($this->selectors[$selector->selector]);
         }
         $this->selectors[$selector->selector] = $selector;
      }

      foreach ($this->selectors as $selector) {
         if ($selector->has_super()) {
            $super = $this->selectors[$selector->super];
            $selector->inherit($super);
         }
      }
   }

   function strip_comments($css) {
      // block comments
      $css = preg_replace('/\/\*(.|\n)*?\*\//', '', $css);
      // single line comments
      $css = preg_replace('/\/\/.*?\n/', '', $css);
      return $css;
   }

   function substitute_includes() {
      $matches = array();
      while (preg_match_all('/@include ([a-z0-9_\-\/]+).css;(\s+)?/', $this->css, $matches) > 0) {
         for ($i = 0; $i < count($matches[0]); ++$i) {
            $sp = explode('/', $matches[1][$i], 2);
            $include_file_name = __DIR__ . "/../../../apps/" . $sp[0] . "/views/css/" . $sp[1] . ".css";
            $include_file = file_get_contents($include_file_name);
            $include_file = $this->strip_comments($include_file);
            $this->css = str_replace($matches[0][$i], $include_file, $this->css);
         }
      }
   }

   function substitute_vars() {
      $matches = array();
      preg_match_all('/@variables {([^}]+)(?:\s+)?}(?:\s+)?/', $this->css, $matches);
      $vars = array();
      for ($i = 0; $i < count($matches[0]); ++$i) {
         $var_list = explode(';', $matches[1][$i]);
         foreach ($var_list as $var) {
            $kv = explode(':', $var, 2);
            if (strlen(trim($kv[0])) && strlen(trim($kv[1]))) {
               $vars[trim($kv[0])]= trim($kv[1]);
            }
         }
         $this->css = str_replace($matches[0][$i], '', $this->css);
      }
      foreach ($vars as $key => $value) {
         $this->css = str_replace('var(' . $key . ')', $value, $this->css);
      }
   }

   function build_urls() {
      if ($this->version !== false) {
         $build = "/build/" . $this->version;
         preg_match_all("/url\(['\"]?(\/images\/[a-z0-9_]+\/[a-z0-9_\-]+\.[a-z]+)['\"]?\)/i", $this->css, $matches);
         for ($i = 0; $i < count($matches[0]); ++$i) {
            $new_url = "url('" . $build . $matches[1][$i] . "')";
            $this->css = str_replace($matches[0][$i], $new_url, $this->css);
         }
      }
   }

}


?>
