<?php

require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/rand.php");

function encrypt($data) {
   static $type = false;
   static $pass = false;
   if ($type === false) {
      $config = getZombieConfig();
      $type = $config['crypt']['type'];
      $pass = $config['crypt']['pass'];
   }
   $iv = strongRand(12);
   $encrypted = openssl_encrypt($data, $type, $pass, false, $iv);
   return $iv . $encrypted;
}

function decrypt($data) {
   static $type = false;
   static $pass = false;
   if ($type === false) {
      $config = getZombieConfig();
      $type = $config['crypt']['type'];
      $pass = $config['crypt']['pass'];
   }
   $iv = substr($data, 0, 16);
   $data = substr($data, 16);
   $decrypted = openssl_decrypt($data, $type, $pass, false, $iv);
   return $decrypted;
}

?>
