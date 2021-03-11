<?php


include ("../../../inc/includes.php");

Session::checkCentralAccess();
if (isset($_GET['action'])) {
   $action = $_GET['action'];
} else if (isset($_POST['action'])) {
   $action = $_POST['action'];
} else {
   $action = "import";
}

if ($action != "export") {
   Html::header("Import", $_SERVER['PHP_SELF'], "admin", "page", -1);
}

switch ($action) {
   case 'export':
      if (isset($_GET['filename'])) {
         $file = GLPI_TMP_DIR."/".$_GET['filename'];
         if (file_exists($file)) {
            header('Content-type: application/json');
            header('Content-Disposition: attachment; filename="'.$_GET['filename'].'"');
            readfile($file);
         }
      }
      break;
   case "download":
      echo "<div class='center'>";
      $itemtype = $_REQUEST['itemtype'];
      echo "<a href='".$itemtype::getSearchURL()."'>".__('Back')."</a>";
      echo "</div>";
      Html::redirect("formvalidation.backup.php?action=export&filename=".$_GET['filename']);
      break;
   case "import":
      PluginFormvalidationPage::displayImportFormvalidationForm();
      break;
   case "process_import":
      PluginFormvalidationPage::processImportPage();
      Html::back();
      break;
}

if ($action != "export") {
   Html::footer();
}
