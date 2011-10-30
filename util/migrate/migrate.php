<?php

require_once(__DIR__ . "/../../../config/config.php");
require_once(__DIR__ . "/../dir.php");
require_once(__DIR__ . "/../autoload.php");

function runMigrations() {
   $config = getZombieConfig();
   $migrate_config_file = $config['zombie_root'] . "/config/migrate.xml";
   if (!file_exists($migrate_config_file)) {
      $migrate_xml = '<?xml version="1.0" standalone="yes"?>' . PHP_EOL .
                     '<migrate>' . PHP_EOL .
                     '   <!-- Auto generated. Do not touch. -->' . PHP_EOL .
                     '   <completed>' . PHP_EOL .
                     '   </completed>' . PHP_EOL .
                     '</migrate>' . PHP_EOL;
      file_put_contents($migrate_config_file, $migrate_xml);
   }
   $dom = new DOMDocument("1.0");
   $dom->formatOutput = true;
   $dom->preserveWhiteSpace = true;
   $dom->load($migrate_config_file);
   $completed_node = $dom->getElementsByTagName("completed")->item(0);
   $completed = array();
   $files = $completed_node->childNodes;
   foreach ($files as $file) {
      if (is_a($file, "DOMElement")) {
         $completed[$file->getAttribute("name")] = true;
      }
   }
   $applied_dir = $config['zombie_root'] . "/model/migrate/applied/";
   $migrations = getDirContents($applied_dir, array("file"));
   foreach ($migrations as $migration) {
      if (!isset($completed[$migration])) {
         echo "Applying migration $migration\n";
         include($applied_dir . "/" . $migration);
         $file = $dom->createElement("file");
         $file->setAttribute("name", $migration);
         $completed_node->appendChild($file);
      }
   }
   file_put_contents($migrate_config_file, $dom->saveXML());
}

function applyMigration($name) {
   $config = getZombieConfig();
   $timestamp = time();
   $migrate_file = $config['zombie_root'] . "/model/migrate/" . $name . ".php";
   $applied_dir = $config['zombie_root'] . "/model/migrate/applied";
   $applied_file = $applied_dir . "/" . $timestamp . "-" . $name . ".php";
   rename($migrate_file, $applied_file);
}

function newMigration($name) {
   $config = getZombieConfig();
   $migrate_file = $config['zombie_root'] . "/model/migrate/" . $name . ".php";
   if (file_exists($migrate_file)) {
      die("Migration '" . $name . "' already exists.\n");
   }
   $php = "<?php\n\n";
   file_put_contents($migrate_file, $php);
}

function addTable($name, $table) {
   $php = <<<PHP
\$query = new MysqlQuery('
   CREATE TABLE $table ( 
      id INT NOT NULL AUTO_INCREMENT PRIMARY KEY
   )
');
\$query->exec();


PHP;
   addToMigration($name, $php);
}

function addColumn($name, $table, $column, $type, $extra) {
   $type = addslashes($type);
   $extra = addslashes($extra);
   $php = <<<PHP
\$query = new MysqlQuery('
   ALTER TABLE $table ADD COLUMN $column $type $extra
');
\$query->exec();


PHP;
   addToMigration($name, $php);
}

function addToMigration($name, $php) {
   $config = getZombieConfig();
   $migrate_file = $config['zombie_root'] . "/model/migrate/" . $name . ".php";
   if (!file_exists($migrate_file)) {
      die("Migration '" . $name . "' does not exist.\n");
   }
   $contents = file_get_contents($migrate_file) . $php;
   file_put_contents($migrate_file, $contents);
}

function migrate($options) {
   if (!isset($options['action'])) {
      die("Usage: php zombie.php migrate action=<action>\n" . 
          "Actions:\n" .
          "\tadd-column\n" .
          "\tadd-table\n" .
          "\tapply\n" .
          "\tnew\n" .
          "\trun\n");
   }
   if ($options['action'] == "apply") {
      applyMigration($options['name']);
   } else if ($options['action'] == "run") {
      runMigrations();
   } else if ($options['action'] == "new") {
      if (!isset($options['name'])) {
         die("Usage: php zombie.php migrate action=new name=<name>\n");
      }
      newMigration($options['name']);
   } else if ($options['action'] == "add-table") {
      if (!isset($options['name']) ||
          !isset($options['table']))
      {
         die("Usage: php zombie.php migrate action=add-table name=<name> table=<table>\n");
      }
      addTable($options['name'],
               $options['table']);
   } else if ($options['action'] == "add-column") {
      if (!isset($options['name']) ||
          !isset($options['table']) ||
          !isset($options['column']) ||
          !isset($options['type']))
      {
         die("Usage: php zombie.php migrate action=add-column name=<name> " .
             "table=<table> column=<column> type=<type> [extra=<extra>]\n");
      }
      if (!isset($options['extra'])) {
         $options['extra'] = '';
      }
      addColumn($options['name'],
                $options['table'],
                $options['column'],
                $options['type'],
                $options['extra']);
   } else {
      die("Error: Unkown action '" . $options['action'] . "'.\n");
   }
}

?>
