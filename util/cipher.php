<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Util
 */

require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/rand.php");

/**
 * encrypt data with default cipher and password
 * @param string $data plain text data
 * @param string $pass optional password
 * @param string $type optional algorithm
 * @return string
 */
function encrypt($data, $pass = null, $type = null) {
   if (is_null($type)) {
      $config = getZombieConfig();
      $type = $config['crypt']['type'];
      if (is_null($pass)) {
         $pass = $config['crypt']['pass'];
      }
   }
   $iv = strongRand(12);
   $encrypted = openssl_encrypt($data, $type, $pass, false, $iv);
   return $iv . $encrypted;
}

/**
 * decrypt data with default cipher and password
 * @param string $data encrypted data
 * @param string $pass optional password
 * @param string $type optional algorithm
 * @return string
 */
function decrypt($data, $pass = null, $type = null) {
   if (is_null($type)) {
      $config = getZombieConfig();
      $type = $config['crypt']['type'];
      if (is_null($pass)) {
         $pass = $config['crypt']['pass'];
      }
   }
   $iv = substr($data, 0, 16);
   $data = substr($data, 16);
   $decrypted = openssl_decrypt($data, $type, $pass, false, $iv);
   return $decrypted;
}

?>
