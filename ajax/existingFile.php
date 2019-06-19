<?php
include ("../../../inc/includes.php");
$b = "0";
if (isset($_GET['filename']) && file_exists(GLPI_ROOT.$_GET['filename'])) {
      $b = "1";
}
echo $b;


