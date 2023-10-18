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

/** @file
* @brief
*/

include ('../../../inc/includes.php');

if (empty($_GET["id"])) {
   $_GET["id"] = '';
}

Session::checkLoginUser();

$page = new PluginFormvalidationPage();
if (isset($_POST["add"])) {
   $page->check(-1, CREATE, $_POST);

   $newID = $page->add($_POST);
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($page->getFormURL()."?id=".$newID);
   } else {
      Html::back();
   }

} else if (isset($_POST["purge"])) {
   $page->check($_POST["id"], PURGE);
   $page->delete($_POST, true);

   $page->redirectToList();

} else if (isset($_POST["update"])) {
   $page->check($_POST["id"], UPDATE);

   $page->update($_POST);

   Html::back();

} else {
   //   Html::header(Change::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "helpdesk", "change");
   Html::header(__('Form Validation - Page', 'formvalidation'), $_SERVER['PHP_SELF'], "config", "PluginFormvalidationMenu", "formvalidationpage");
   $page->display($_GET);
   Html::footer();
}
