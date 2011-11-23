<?php

require_once(__DIR__ . "/../../config/config.php");

function installPackage($package_name) {
   $to_install = array($package_name => true);
   getDeps($package_name, $to_install);
   foreach ($to_install as $package => $x) {
      doInstall($package);
   }
}

function getDeps($package_name, &$to_install) {
   $config = getZombieConfig();
   $zroot = $config['zombie_root'];
   $package_config_file = __DIR__ . "/$package_name/package.json";
   $deps = array();
   if (file_exists($package_config_file)) {
      $json = file_get_contents($package_config_file);
      $config = json_decode($json, true);
      if (isset($config['dependencies'])) {
         $deps = $config['dependencies'];
         foreach ($deps as $dep) {
            if (!isset($to_install[$dep])) {
               $to_install[$dep] = true;
               getDeps($dep, $to_install);
            }
         }
      }
   }
}

function doInstall($package_name) {
   echo "\nINSTALLING PACKAGE: $package_name\n";
   $config = getZombieConfig();
   $zroot = $config['zombie_root'];
   $files_to_copy = array();
   $package_dir = __DIR__ . "/" . $package_name;
   if (!file_exists($package_dir)) {
      die("Package '$package_name' does not exist.\n");
   }
   $find_cmd = "find " . $package_dir;
   $list = shell_exec($find_cmd);
   $files_to_copy = explode("\n", trim($list));
   $pdir_len = strlen($package_dir) + 1;
   foreach ($files_to_copy as $key => $file) {
      $files_to_copy[$key] = substr($file, $pdir_len);
   }
   foreach ($files_to_copy as $file) {
      $src_file = $package_dir . "/" . $file;
      $dest_file = $zroot . "/" . $file;
      if ($file == "package.json" || $file == "install.php") {
         continue;
      }
      if (file_exists($dest_file)) {
         if (!is_dir($dest_file)) {
            echo "skipping $dest_file\n";
         }
         continue;
      }
      if (is_dir($src_file)) {
         echo "mkdir $dest_file\n";
         mkdir($dest_file);
      } else {
         echo "writing $dest_file\n";
         copy($src_file, $dest_file);
      }
   }
   $install_file = $package_dir . "/install.php";
   if (file_exists($install_file)) {
      echo shell_exec("php $install_file");
   }
}

?>
