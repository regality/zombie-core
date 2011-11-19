<?php

require_once("Template.php");

define('OUTPUT_VAR', 'o');

class TemplateJS extends Template {
   public $lang = 'JS';

   function render($function_declare = null) {
      if (is_null($function_declare)) {
         $funcion_dec = "function template";
      }
      $str = '';
      foreach ($this->parts as $part) {
         $part = trim($part);
         if (strlen($part)) {
            $str .= OUTPUT_VAR . " += $part;\n";
         }
      }
      $str = "(function(data) { var " . OUTPUT_VAR . " = '';\n"
                . $str .
             "\nreturn " . OUTPUT_VAR . ";\n})(data);\n";
      $str = str_replace(OUTPUT_VAR . " += '';", "", $str);
      $str = str_replace(OUTPUT_VAR . " += \"\";", "", $str);
      $str = str_replace("{;", "{", $str);
      $str = preg_replace("/\n+/", "\n", $str);
      $str = "$function_declare(data) {\n" .
             "var output = " . $str .
             "return output;\n" .
             "};\n";
      return $str;
   }
}

class FunctionTagJS {
   public $tag;
   public $contents;

   function __construct($tag, $contents) {
      $this->tag = trim($tag);
      $this->contents = trim($contents);
   }

   function jsonParamsToPhp($json_params) {
      $fparams = array();
      $params = json_decode("[" . $json_params . "]");
      if ($params) {
         foreach ($params as $param) {
            if (is_object($param) && is_a($param, 'stdClass')) {
               $param = get_object_vars($param);
               $param = var_export($param, true);
               $param = str_replace("\n", "", $param);
            } else {
               $param = var_export($param, true);
            }
            array_push($fparams, $param);
         }
         return implode(", ", $fparams);
      } else {
         return false;
      }
   }

   function __toString() {
      $function = " " . $this->tag . "(";
      if ($this->contents) {
         if ($p = $this->jsonParamsToPhp($this->contents)) {
            $function .= $p;
         } else {
            $function .= PhpToJs::varToJsAll($this->contents);
         }
      }
      $function .= ")";
      return $function;
   }
}

class ImgTagJS extends FunctionTagJS {
   function __toString() {
      $img_php = $this->jsonParamsToPhp($this->contents);
      if (strpos($img_php, "array") === false) {
         $img_php .= ",array()";
      }
      $img_php .= ",true";
      $img_php = "\$img_tag = img($img_php);";
      eval($img_php);
      return '"' . addslashes($img_tag) . '"';
   }
}

class EchoTagJS extends FunctionTagJS {
   function __toString() {
      $var = PhpToJs::varToJs($this->contents);
      return " " . $var . " ";
   }
}

class CechoTagJS extends FunctionTagJS {
   function issetCondition($var) {
      $levels = explode(".", $var);
      $cond = array();
      $sofar = '';
      foreach ($levels as $level) {
         if ($sofar) {
            $hop = "$sofar.hasOwnProperty(\"$level\")";
            array_push($cond, $hop);
            $sofar .= ".";
         } else {
            $hop = false;
         }
         $sofar .= $level;
         array_push($cond, $sofar);
      }
      $condition = implode(" && ", $cond);
      return $condition;
   }

   function __toString() {
      $var = PhpToJs::varToJs($this->contents);
      $condition = $this->issetCondition($var);
      return "(function(data) { var " . OUTPUT_VAR . " = '';\n" .
             "if ($condition) { " .
             "o += $var;\n" .
             "}})(data) ";
   }
}

class IncludeTagJS extends FunctionTagJS {
   function __construct($tag, $contents, $compress) {
      parent::__construct($tag, $contents);
      $this->compress = $compress;
   }

   function __toString() {
      $file = trim($this->contents, "\"'");
      $include = new TemplateJS($file, true, $this->compress);
      return $include->render();
   }
}

class IssetTagJS extends CechoTagJS {
   function __toString() {
      $var = PhpToJs::varToJs($this->contents);
      $condition = $this->issetCondition($var);
      return "(function(data) { var " . OUTPUT_VAR . " = '';\n" .
             "if ($condition) { ";
   }
}

class StructureTagJS extends FunctionTagJS {
   function __toString() {
      return "(function(data) { var " . OUTPUT_VAR . " = '';\n" . $this->tag . " (" . PhpToJs::varToJsAll($this->contents) . ") { ";
   }
}

class EndStructureTagJS extends FunctionTagJS {
   function __toString() {
      return " '';}\nreturn " . OUTPUT_VAR . ";\n})(data) ";
   }
}

class IfTagJS      extends StructureTagJS { }
class EndifTagJS   extends EndStructureTagJS { }
class ElseifTagJS  extends StructureTagJS {
   function __toString() {
      return "\"\";\n} else if " . $this->contents . " { ";
   }
}
class ElseTagJS       extends EndStructureTagJS {
   function __toString() {
      return "\"\";\n} else { ";
   }
}

class ForeachTagJS extends StructureTagJS {
   function __toString() {
      $var_pat = "\\$[a-zA-z_].[a-zA-Z0-9_'\"\[\]]+";
      $fe_pat = "/\s*($var_pat)\s+as(:?\s+($var_pat)\s+(:?=>|:))?\s+($var_pat)/";
      preg_match($fe_pat, $this->contents, $matches);
      $iterable = PhpToJs::varToJs($matches[1]);
      $key = ($matches[3] ? substr($matches[3], 1) : 'key');
      $iterator = PhpToJs::varToJs($matches[5]);
      $str = "(function(data) { var " . OUTPUT_VAR . " = '';\n";
      $str .= "for (var $key in $iterable) { if ($iterable.hasOwnProperty($key)) {";
      PhpToJs::pushVar($iterator, $iterable . '[' . $key . ']');
      PhpToJs::pushVar("data." . $key, $key);
      return $str;
   }
}

class EndforeachTagJS extends EndStructureTagJS {
   function __toString() {
      PhpToJs::popVar();
      PhpToJs::popVar();
      return " '';}}\nreturn " . OUTPUT_VAR . ";\n})(data) ";
   }
}

class ForTagJS     extends StructureTagJS { }
class WhileTagJS   extends StructureTagJS { }

class EndforTagJS     extends EndStructureTagJS { }
class EndwhileTagJS   extends EndStructureTagJS { }


class TemplateStringJS {
   function __construct($str, $compress = false) {
      $this->str = $str;
      $this->compress = $compress;
   }

   function __toString() {
      $str = trim($this->str);
      if ($this->compress) {
         $str = preg_replace("/>\s+</m", "><", $str);
      }
      if (strlen($str)) {
         return json_encode($str);
      } else {
         return '';
      }
   }
}

?>
