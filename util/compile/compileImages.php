<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . '/../dir.php');

function copyImages($version, $old_version) {
   echo "COPYING IMAGES\n\n";
   $config = getZombieConfig();
   $root = $config['zombie_root'];
   exec("rm -rf $root/web/build/images/" . $old_version);
   exec("mkdir -p $root/web/build/images/" . $version);
   $apps_dir = __DIR__ . "/../../../apps/";
   $apps = getDirContents($apps_dir, array('dir'));

   $resize = array();
   foreach ($apps as $app) {
      $config_file = "$root/apps/$app/config/compile.json";
      if (file_exists($config_file)) {
         $contents = file_get_contents($config_file);
         $config_arr = json_decode($contents, true);
         if (!$config_arr) {
            echo "ERROR: Possible problem with compile config for `$app`\n";
         }
         if (isset($config_arr['images']) && is_array($config_arr['images'])) {
            $images = $config_arr['images'];
            foreach ($images as $image) {
               if (isset($image['resize'])) {
                  array_push($resize, array('app' => $app,
                                            'name' => $image['name'],
                                            'size' => $image['resize']));
               }
            }
         }
      }
   }

   foreach ($apps as $app) {
      $images_src = realpath($apps_dir . $app . "/views/images");
      if (!$images_src) {
         continue;
      }
      $images = getDirContents($images_src . "/", array("file"));
      if (count($images) > 0) {
         $image_dest = realpath("$root/web/build/images/" . $version) . "/" . $app;
         echo "creating dir " . $image_dest . "\n";
         mkdir($image_dest);
      }
      foreach ($images as $image) {
         $path_parts = pathinfo($images_src . "/" . $image);
         $ext = $path_parts['extension'];
         $img_source = $images_src . "/" . $image;
         $img_dest = $image_dest . "/" . $image;
         if (`which convert`) {
            foreach ($resize as $resize_info) {
               if ($app == $resize_info['app'] &&
                   $resize_info['name'] == $image)
               {
                  $size = $resize_info['size'];
                  echo "resizing $img_source\n";
                  exec("convert -resize $size $img_source $img_dest");
                  $img_source = $img_dest;
               }
            }
         } else if (count($resize) > 0) {
            echo "error: could not find convert command\n";
         }
         if ($ext == 'png' && `which pngcrush`) {
            echo "optimizing png\n";
            exec("pngcrush -rem alla -rem gAMA -rem cHRM -rem iCCP -rem sRGB -brute -reduce $img_source $img_dest");
         } else if ($ext == 'jpg' || $ext == 'jpeg') {
            echo "optimizing jpeg\n";
            exec("jpegtran -copy none -optimize -outfile $img_dest $img_source");
         } else {
            copy($img_source, $img_dest);
         }
         echo "copying $images_src/$image\n" .
              "to $image_dest/$image\n\n";

      }
   }
}

?>
