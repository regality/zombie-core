<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

function get_dir_contents($dir, $types = array("dir", "file")) {
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
