<?php

require("JsonRenderer.php");

class XmlRenderer extends JsonRenderer {
   public function render($controller) {
      $this->messagesToDataArray($controller);
      $this->traversableToArray($controller);
      echo $this->arrayToXml($controller->data);
   }

   public static function arrayToXml($data, $rootNodeName = 'data', $xml = null) {
      if (ini_get('zend.ze1_compatibility_mode') == 1) {
         ini_set ('zend.ze1_compatibility_mode', 0);
      }
      if (is_null($xml)) {
         $xml_str = "<?xml version='1.0' encoding='utf-8'?><$rootNodeName />";
         $xml = simplexml_load_string($xml_str);
      }
      foreach($data as $key => $value) {
         if (is_numeric($key)) {
            $key = "item_". (string) $key;
         }
         $key = preg_replace('/[^a-z]/i', '', $key);
         if (is_array($value)) {
            $node = $xml->addChild($key);
            XmlRenderer::arrayToXml($value, $rootNodeName, $node);
         } else {
            $value = htmlentities($value);
            $xml->addChild($key,$value);
         }
      }
      return $xml->asXML();
   }
}

?>
