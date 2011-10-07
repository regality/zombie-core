<?php
# Copyright (c) 2011, Regaltic LLC.  This file is
# licensed under the General Public License version 3.
# See the LICENSE file.

global $_ERRORS;
$_ERRORS = array();

function errorStore($errno, $errstr, $errfile, $errline) {
   if (!(error_reporting() & $errno)) {
      return;
   }
   $e = array("errno" => $errno,
              "errstr" => $errstr,
              "errfile" => $errfile,
              "errline" => $errline);
   array_push($GLOBALS['_ERRORS'], $e);
}

function getErrorArray() {
   return $GLOBALS['_ERRORS'];
}

function clearErrors() {
   $GLOBALS['_ERRORS'] = array();
}

function renderErrorsJs() {
   $errors = $GLOBALS['_ERRORS'];
   if (count($errors) > 0) {
      ?>
      <script type="text/javascript">
      $(document).ready(function() {
      var mesg;
      <?php foreach ($errors as $error): ?>
      mesg = <?= json_encode($error['errstr'] .
                             " in " . $error['errfile'] .
                             " on line " . $error['errline'] . ".") ?>;
      zs.ui.error(mesg, <?= $error['errno'] ?>);
      <?php endforeach ?>
      });
      </script>
      <?php
   }
   $GLOBALS['_ERRORS'] = array();
}

set_error_handler("errorStore");

?>
