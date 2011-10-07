<?php

function strongRand($num_bytes, $raw = false) {
   $rand = fread(fopen('/dev/urandom', 'r'), $num_bytes);
   if (!$raw) {
      $rand = base64_encode($rand);
   }
   return $rand;
}

?>
