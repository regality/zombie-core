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
      $this->addHacks();
   }

   function addHacks() {
      $num_pat = "\s*\d*\.?\d*\s*";
      $color_pat = "(rgba?\($num_pat,$num_pat,$num_pat,?(?:$num_pat)?\)" .
                   "|#[a-f0-9]+)?";
      $rgb_pat = "/^rgba?\(($num_pat),($num_pat),($num_pat),?($num_pat)?\)/";
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
               $pat = "/$color_pat?\s*(\d+)px\s+(\d+)px\s+(\d+)px?\s*$color_pat/";
               preg_match($pat, $value, $matches);
               $color = $matches[1];
               $x = $matches[2];
               $y = $matches[3];
               $strength = $matches[4];
               if (!$color) {
                  $color = $matches[5];
               }
               if (preg_match($rgb_pat, $color, $matches) > 0) {
                  $red = dechex($matches[1]);
                  $red = (strlen($red) == 1 ? '0'.$red : $red);
                  $green = dechex($matches[2]);
                  $green = (strlen($green) == 1 ? '0'.$green : $green);
                  $blue = dechex($matches[3]);
                  $blue = (strlen($blue) == 1 ? '0'.$blue : $blue);
                  $alpha = $matches[4];
                  $color = "#" . $red . $green . $blue;
               }
               $angle = intval(atan($x/$y)*360/2/3.14159265358979323 + 90);
               // currently this kills opacity, better fix it
               $new_attrs['filter'] = "\"progid:DXImageTransform.Microsoft.Shadow(direction=$angle,strength=$strength,color=$color)\"";
               break;
            case 'background':
               $color_percent_pat = "$color_pat\s+\d{1,3}%";
               $words_pat = "[a-z0-9\-]+\s*,?\s*";
               $gradient_pat = "/$color_pat?\s+(linear|radial)-gradient\s*" .
                               "\(($words_pat)\s+(?:$color_percent_pat\s*,?\s*)+\)/";
               if (preg_match($gradient_pat, $value, $matches) > 0) {
                  $color = $matches[1];
                  $type = $matches[2];
                  $words = $matches[3];
                  $colors = array();
                  preg_match_all("/$color_percent_pat/", $value, $matches) . "\n";
                  foreach ($matches[0] as $match) {
                     array_push($colors, $match);
                  }
                  $colors = implode(",", $colors);
                  $moz = "$color -moz-$type-gradient($words $colors)";
                  $wkit = "$color -webkit-$type-gradient($words $colors)";
                  $opera = "$color -o-$type-gradient($words $colors)";
                  $ie10 = "$color -ms-$type-gradient($words $colors)";
                  $w3c = "$color $type-gradient($words $colors)";
                  $bg_list = array($color, $moz, $wkit, $opera, $ie10, $w3c);
                  $new_attrs['background'] = implode(";\n  background: ", $bg_list);
               }
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

   function hasSuper() {
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
   function __construct($css, $version = false, $images_version = false) {
      $this->css = $css;
      $this->version = $version;
      $this->images_version = $images_version;
      $this->parseCss();
   }

   function render($minify = false) {
      $css = '';
      foreach ($this->selectors as $selector) {
         $css .= $selector->render($minify);
      }
      return $css;
   }

   function parseCss() {
      $this->css = $this->stripComments($this->css);
      $this->substituteIncludes();
      $this->substituteVars();
      $this->buildUrls();
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
         if ($selector->hasSuper()) {
            $super = $this->selectors[$selector->super];
            $selector->inherit($super);
         }
      }
   }

   function stripComments($css) {
      // block comments
      $css = preg_replace('/\/\*(.|\n)*?\*\//', '', $css);
      // single line comments
      $css = preg_replace('/\/\/.*?\n/', '', $css);
      return $css;
   }

   function substituteIncludes() {
      $matches = array();
      while (preg_match_all('/@include ([a-z0-9_\-\/\.]+).css;(\s+)?/', $this->css, $matches) > 0) {
         for ($i = 0; $i < count($matches[0]); ++$i) {
            $sp = explode('/', $matches[1][$i], 2);
            $include_file_name = __DIR__ . "/../../../apps/" . $sp[0] . "/views/css/" . $sp[1] . ".css";
            $include_file = file_get_contents($include_file_name);
            $include_file = $this->stripComments($include_file);
            $this->css = str_replace($matches[0][$i], $include_file, $this->css);
         }
      }
   }

   function substituteVars() {
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

   function buildUrls() {
      if ($this->images_version !== false) {
         $build = "/build/images/" . $this->images_version;
         preg_match_all("/url\(['\"]?\/images(\/[a-z0-9_]+\/[a-z0-9_\-]+\.[a-z]+)['\"]?\)/i", $this->css, $matches);
         for ($i = 0; $i < count($matches[0]); ++$i) {
            $new_url = "url('" . $build . $matches[1][$i] . "')";
            $this->css = str_replace($matches[0][$i], $new_url, $this->css);
         }
      }
      $config = getZombieConfig();
      $web_root = $config['web_root'];
      if ($web_root != '/' && !empty($web_root)) {
         preg_match_all("/url\(['\"]?(\/.*?)['\"]?\)/i", $this->css, $matches);
         for ($i = 0; $i < count($matches[0]); ++$i) {
            $new_url = "url('" . $web_root . $matches[1][$i] . "')";
            $this->css = str_replace($matches[0][$i], $new_url, $this->css);
         }
      }
   }

}


?>
