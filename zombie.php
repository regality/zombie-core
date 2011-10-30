<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

require_once(__DIR__ . "/util/util.php");
require_once(__DIR__ . "/util/autoload.php");
require_once(__DIR__ . "/../config/config.php");

function cliMain($argv) {
   $argc = count($argv);
   if ($argc < 2) {
      die("Usage: zombie.php <action> <option=value> ...\n" .
           "Availabe actions:\n" .
           "\tcompile\n" .
           "\tcreate-app\n");
   }

   $action = $argv[1];

   $options = array();
   for ($i = 2; $i < $argc; ++$i) {
      $opt = explode("=", $argv[$i], 2);
      if (count($opt) == 2) {
         $options[$opt[0]] = $opt[1];
      } else {
         $options[$opt[0]] = true;
      }
   }

   if ($action == "create-app") {
      if (!isset($options['app'])) {
         die ("Usage: zombie.php create-app app=<app name> [template=<template_name>] [option=<value>] ...\n");
      }

      $template = (isset($options['template']) ? $options['template'] : 'basic');

      $base_dir = "/config/template";
      $template_file = realpath(__DIR__ . "/../config/template/" . $template) . "/template.php";
      if (!file_exists($template_file)) {
         $base_dir = "/zombie-core/template/";
         $template_file = __DIR__ . "/template/" . $template . "/template.php";
         if (!file_exists($template_file)) {
            die("unknown template: " . $template . "\n");
         }
      }

      $app = $options['app'];

      require_once(__DIR__ . "/template/ZombieTemplate.php");

      require($template_file);
      $template_class = underscoreToClass($template . "_template");
      $template = new $template_class($template, $app, $base_dir, $options);
      $template->run();
   } else if ($action == "compile") {
      require(__DIR__ . "/util/compile/compile.php");
      compile($options);
   } else if ($action == "migrate") {
      require(__DIR__ . "/util/migrate/migrate.php");
      migrate($options);
   } else if ($action == "deploy") {
      require(__DIR__ . "/util/deploy.php");
      deploy();
   } else if ($action == "kachow") {
      echo "kachow!\n";
   } else {
      echo "Error: unknown action '" . $action . "'.\n";
   }
}

cliMain($argv);

?>
