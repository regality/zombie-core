<?php

function purifyHtml($html) {
   static $purifier = false;
   if ($purifier === false) {
      require_once(__DIR__ . '/htmlpurifier-standalone/HTMLPurifier.standalone.php');
      $config = HTMLPurifier_Config::createDefault();
      $config->set('Cache.DefinitionImpl', null);
      $purifier = new HTMLPurifier($config);
   }
   $clean_html = $purifier->purify($html);
   return $clean_html;
}

?>
