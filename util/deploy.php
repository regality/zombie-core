<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.
/**
 * @package Util
 * @ignore
 */

function deploy() {
   $config = getZombieConfig();
   $zombie_root = $config['zombie_root'];
   $deploy_root = $config['deploy_root'];
   if (!isset($config['deploy_root'])) {
      die("deploy_root must be set in config before deploying.\n");
   }
   if (!file_exists($zombie_root . "/config/version.php")) {
      die("It appears you haven't compiled your javascript.\n" .
          "Don't forget to do this before deploying.\n");
   }
   if (!file_exists($deploy_root . "/config/config.php")) {
      die("Deploy directory has no config/config.php\n" .
          "Create it before deploying.\n");
   }
   if (file_get_contents($zombie_root . "/config/version.php") ==
       file_get_contents($deploy_root . "/config/version.php"))
   {
      echo "NOTICE: You have not compiled since last deploy.\n";
   }
   echo "Deleting old.\n";
   exec("rm -rf $deploy_root/web");
   exec("rm -rf $deploy_root/apps");
   exec("rm -rf $deploy_root/model");
   exec("rm -rf $deploy_root/zombie-core");
   exec("rm -rf $deploy_root/zombie.php");

   echo "Copying config files.\n";
   exec("cp $zombie_root/config/version.php $deploy_root/config/version.php");
   exec("cp $zombie_root/config/javascript.xml $deploy_root/config/javascript.xml");
   exec("cp $zombie_root/config/images.xml $deploy_root/config/images.xml");
   exec("cp $zombie_root/config/stylesheets.xml $deploy_root/config/stylesheets.xml");
   echo "Copying apps.\n";
   exec("cp -r $zombie_root/apps $deploy_root/apps");
   echo "Copying model.\n";
   exec("cp -r $zombie_root/model $deploy_root/model");
   echo "Copying web.\n";
   exec("cp -r $zombie_root/web $deploy_root/web");
   echo "Copying core.\n";
   exec("cp -r $zombie_root/zombie-core $deploy_root/zombie-core");
   symlink($deploy_root . "/zombie-core/zombie.php", $deploy_root . "/zombie.php");

   echo "Migrating database.\n";
   echo passthru("php $deploy_root/zombie.php migrate action=run");

   $other_config = file_get_contents("$deploy_root/config/config.php");
   $other_config = str_replace("getZombieConfig", "getDeployConfig", $other_config);
   eval("?>" . $other_config);
   $other_config = getDeployConfig();
   if ($config['web_root'] != $other_config['web_root']) {
      echo "Compiling.\n";
      echo passthru("php $deploy_root/zombie.php compile");
   }

   echo "\nDone.\n";
}

?>
