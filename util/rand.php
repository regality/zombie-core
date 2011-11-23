<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Util
 */

/**
 * Get a cryptographically strong random string.
 * @param int $num_bytes the number of bytes needed
 * @param boolean $raw if true the data will not be encoded
 * @return string
 */
function strongRand($num_bytes, $raw = false) {
   $rand = openssl_random_pseudo_bytes($num_bytes);
   if (!$raw) {
      $rand = base64_encode($rand);
   }
   return $rand;
}

?>
