<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Util
 */

/**
 * Purify an html string to protect
 * against XSS attacks.
 * @param string $html the html string
 * @return string
 */
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
