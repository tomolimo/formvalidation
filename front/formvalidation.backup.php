<?php
/*
 * -------------------------------------------------------------------------
Form Validation plugin
Copyright (C) 2016-2023 by Raynet SAS a company of A.Raymond Network.

http://www.araymond.com
-------------------------------------------------------------------------

LICENSE

This file is part of Form Validation plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
*/

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
