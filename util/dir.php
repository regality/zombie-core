<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Util
 */

/**
 * get the contents of a directory
 * @param string $dir directory to search
 * @param array $types the types of contents to return
 */
function getDirContents($dir, $types = array("dir", "file")) {
   $files = array();
   if (file_exists($dir) && $dh = opendir($dir)) {
      while (($file = readdir($dh)) !== false) {
         if (in_array(filetype($dir . $file), $types) &&
             $file != "." && $file != "..")
         {
            array_push($files, $file);
         }
      }
      closedir($dh);
   }
   return $files;
}

?>
