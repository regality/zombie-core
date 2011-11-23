<?php

require_once('Template.php');

class TemplatePHP extends Template {
   public $lang = 'PHP';
}

class FunctionTagPHP {
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
      $function = "<?= " . $this->tag . "(";
      if ($this->contents) {
         if ($p = $this->jsonParamsToPhp($this->contents)) {
            $function .= $p;
         } else {
            $function .= $this->contents;
         }
      }
      $function .= ") ?>";
      return $function;
   }
}

class ImgTagPHP extends FunctionTagPHP {
   function __toString() {
      $img_php = $this->jsonParamsToPhp($this->contents);
      if (strpos($img_php, "array") === false) {
         $img_php .= ",array()";
      }
      $img_php .= ",true";
      $img_php = "\$img_tag = img($img_php);";
      eval($img_php);
      return $img_tag;
   }
}

class EchoTagPHP extends FunctionTagPHP {
   function __toString() {
      return "<?= " . $this->contents . " ?>";
   }
}

class CechoTagPHP extends FunctionTagPHP {
   function __toString() {
      return "<?= (isset(" . $this->contents . ") ? " . $this->contents . " : '') ?>";
   }
}

class IncludeTagPHP extends FunctionTagPHP {
   function __construct($tag, $contents, $compress) {
      parent::__construct($tag, $contents);
      $this->compress = $compress;
   }

   function __toString() {
      $file = trim($this->contents, "\"'");
      $include = new TemplatePHP($file, true, $this->compress);
      return $include->render();
   }
}

class IssetTagPHP extends FunctionTagPHP {
   function __toString() {
      return "<?php if(isset(" . $this->contents . ")): ?>";
   }
}

class EndStructureTagPHP extends FunctionTagPHP {
   function __toString() {
      return "<?php " . $this->tag . " ?>";
   }
}

class StructureTagPHP extends FunctionTagPHP {
   function __toString() {
      return "<?php " . $this->tag . " (" . $this->contents . "): ?>";
   }
}

class ForTagPHP     extends StructureTagPHP { }
class WhileTagPHP   extends StructureTagPHP { }
class IfTagPHP      extends StructureTagPHP { }
class ElseifTagPHP  extends StructureTagPHP { }
class ForeachTagPHP extends StructureTagPHP {
   function __toString() {
      $clause = str_replace(":", "=>", $this->contents);
      return "<?php foreach (" . $clause . "): ?>";
   }
}

class EndforeachTagPHP extends EndStructureTagPHP { }
class EndforTagPHP     extends EndStructureTagPHP { }
class EndwhileTagPHP   extends EndStructureTagPHP { }
class EndifTagPHP      extends EndStructureTagPHP { }
class ElseTagPHP       extends EndStructureTagPHP {
   function __toString() {
      return "<?php " . $this->tag . ": ?>";
   }
}

class TemplateStringPHP {
   function __construct($str, $compress = false) {
      $this->str = $str;
      $this->compress = $compress;
   }

   function __toString() {
      $str = $this->str;
      $str = trim($str);
      if ($this->compress) {
         $str = preg_replace("/>\s+</", "><", $str);
      }
      return $str;
   }
}

?>
