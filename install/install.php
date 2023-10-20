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

function formvalidation_install() {
   global $DB, $CFG_GLPI;

   $DB->runFile(GLPI_ROOT . "/plugins/formvalidation/install/mysql/formvalidation-empty.sql");

   $DB->insertOrDie(
         'glpi_plugin_formvalidation_configs', [
            'id'            => '1',
            'css_mandatory' => '{\"background-color\":\"lightgrey\", \"font-weight\":\"bold\"}',
            'css_error'     => '{\"outline\":\"solid 1mm red\"}'
         ],
         "Error inserting default config into glpi_plugin_formvalidation_configs "
   );
   $obj = ['itemtype','page','form','field'];
   $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[0]."/".time()."/".rand()."/";

   // init data itemtypes
   $query = "INSERT INTO `glpi_plugin_formvalidation_itemtypes` (`id`, `name`, `URL_path_part`, `guid`)
               VALUES (1, 'Computer','/front/computer.form.php','".md5($guid."1")."'),
                      (2, 'Monitor','/front/monitor.form.php','".md5($guid."2")."'),
                      (3, 'Software', '/front/software.form.php','".md5($guid."3")."'),
                      (4, 'NetworkEquipment', '/front/networkequipment.form.php','".md5($guid."4")."'),
                      (5, 'Peripheral', '/front/peripheral.form.php','".md5($guid."5")."'),
                      (6, 'Printer', '/front/printer.form.php','".md5($guid."6")."'),
                      (7, 'CartridgeItem', '/front/cartridgeitem.form.php','".md5($guid."7")."'),
                      (8, 'ConsumableItem', '/front/consumableitem.form.php','".md5($guid."8")."'),
                      (9, 'Phone', '/front/phone.form.php','".md5($guid."9")."'),
                      (10, 'Ticket','/front/ticket.form.php,','".md5($guid."10")."'),
                      (11, 'Problem', '/front/problem.form.php','".md5($guid."11")."'),
                      (12, 'TicketRecurrent', '/front/ticketrecurrent.form.php','".md5($guid."12")."'),
                      (13, 'Budget', '/front/budget.form.php','".md5($guid."13")."'),
                      (14, 'Supplier', '/front/supplier.form.php','".md5($guid."14")."'),
                      (15, 'Contact', '/front/contact.form.php','".md5($guid."15")."'),
                      (16, 'Contract', '/front/contract.form.php','".md5($guid."16")."'),
                      (17, 'Document', '/front/document.form.php','".md5($guid."17")."'),
                      (18, 'Notes', '/front/notes.form.php','".md5($guid."18")."'),
                      (19, 'RSSFeed', '/front/rssfeed.form.php','".md5($guid."19")."'),
                      (20, 'User', '/front/user.form.php','".md5($guid."20")."'),
                      (21, 'Group', '/front/group.form.php','".md5($guid."21")."'),
                      (22, 'Entity', '/front/entity.form.php','".md5($guid."22")."'),
                      (23, 'Profile', '/front/profile.form.php','".md5($guid."23")."'),
                      (24, 'PluginFormcreatorForm', '/plugins/formcreator/formdisplay.php','".md5($guid."24")."'),
                      (25, 'PluginFormvalidationPage', '/plugins/formvalidation/front/page.form.php','".md5($guid."25")."'),
                      (26, 'PluginFormvalidationForm', '/plugins/formvalidation/front/form.form.php','".md5($guid."26")."'),
                      (27, 'PluginFormvalidationField', '/plugins/formvalidation/front/field.form.php','".md5($guid."27")."'),
                      (28, 'PluginRayusermanagementticketRayusermanagementticket', '/plugins/rayusermanagementticket/front/rayusermanagementticket.helpdesk.public.php','".md5($guid."28")."'),
                      (29, 'PluginFormvalidationItemtype','/plugins/formvalidation/front/itemtype.form.php','".md5($guid."29")."') ;";
   $DB->query($query) or die("Error inserting default item types into glpi_plugin_formvalidation_itemtypes " . $DB->error());

   // init data page
   $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[1]."/".time()."/".rand()."/";
   $query = "INSERT INTO `glpi_plugin_formvalidation_pages` (`id`, `name`, `entities_id`, `plugin_formvalidation_itemtypes_id`, `is_recursive`, `is_active`, `comment`, `date_mod`, `guid`)
               VALUES (1, 'Form Validation Page', 0, 25, 1, 1, NULL, NULL,'".md5($guid."1")."'),
                      (2, 'Form Validation Form', 0, 26, 1, 1, NULL, NULL,'".md5($guid."2")."'),
                      (3, 'Form Validation Field', 0, 27, 1, 1, NULL, NULL,'".md5($guid."3")."'),
                      (4, 'Ticket Validations', 0, 10, 1, 1, NULL, NULL,'".md5($guid."4")."'),
                      (5, 'Form Validation Itemtype',0, 29, 1, 1, NULL, NULL,'".md5($guid."5")."');";
   $DB->query($query) or die("Error inserting default pages into glpi_plugin_formvalidation_pages " . $DB->error());

   // init data forms
   $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[2]."/".time()."/".rand()."/";
   $query = "INSERT INTO `glpi_plugin_formvalidation_forms` (`id`, `name`, `plugin_formvalidation_pages_id`, `css_selector`, `is_createitem`, `is_active`, `use_for_massiveaction`, `formula`, `comment`, `date_mod`,`guid`)
               VALUES (1, 'form(/plugins/formvalidation/front/page.form.php)', 1, 'form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/page.form.php\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."1")."'),
                      (2, 'form(/plugins/formvalidation/front/form.form.php)', 2, 'form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/form.form.php\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."2")."'),
                      (3, 'form(/plugins/formvalidation/front/field.form.php)', 3, 'form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/field.form.php\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."3")."'),
                      (4, 'Simplified interface Creation', 4, 'form[id=\"itil-form\"][action=\"/front/tracking.injector.php\"]', 1, 1, 0, NULL, NULL, NULL,'".md5($guid."4")."'),
                      (5, 'Followup Validations', 4, 'form[name=\"asset_form\"][action=\"/front/itilfollowup.form.php\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."5")."'),
                      (6, 'Central Interface Edit', 4, 'form[id=\"itil-form\"][action^=\"/front/ticket.form.php\"]', 0, 1, 1, NULL, NULL,NULL,'".md5($guid."6")."'),
                      (7, 'Central Interface Creation', 4, 'form[id=\"itil-form\"][action=\"/front/ticket.form.php\"]', 1, 1, 0, NULL, NULL, NULL,'".md5($guid."7")."'),
                      (8, 'form(/plugins/formvalidation/front/page.form.php)', 1, 'form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/page.form.php\"]', 1, 1, 0, NULL, NULL, NULL,'".md5($guid."8")."'),
                      (9, 'form(/plugins/formvalidation/front/itemtype.form.php)',5, 'form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 1, 1, 0, NULL, NULL, NULL,'".md5($guid."9")."'),
                      (10, 'form(/plugins/formvalidation/front/itemtype.form.php)',5, 'form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."10")."'),
                      (11, 'main_description(/front/ticket.form.php)', 4, 'form[name=\"main_description\"][action=\"/front/ticket.form.php\"]', 0, 1, 0, NULL, NULL, '2022-07-12 15:53:46', '".md5($guid."11")."');";

   $DB->query($query) or die("Error inserting default data into glpi_plugin_formvalidation_forms " . $DB->error());


   // init data fields
   $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[3]."/".time()."/".rand()."/";
   $query = "INSERT INTO `glpi_plugin_formvalidation_fields` (`id`, `name`, `plugin_formvalidation_forms_id`, `css_selector_value`, `css_selector_altvalue`, `css_selector_errorsign`, `css_selector_mandatorysign`, `is_active`, `show_mandatory`, `show_mandatory_if`, `formula`, `comment`, `date_mod`, `guid`)
                VALUES (1, 'Name', 1, 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(0)>div>input', 'div>table>tbody>tr>td>div>div:eq(0)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."1")."'),
                       (2, 'Name', 2, 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(0)>div>input', 'div>table>tbody>tr>td>div>div:eq(0)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."2")."'),
                       (3, 'CSS Selector', 2, 'div>table>tbody>tr>td>div>div:eq(4)>div input[name=\\\"css_selector\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(0)>div>input', 'div>table>tbody>tr>td>div>div:eq(0)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."3")."'),
                       (4, 'Name', 3, 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(0)>div>input', 'div>table>tbody>tr>td>div>div:eq(0)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."4")."'),
                       (5, 'Value CSS selector', 3, 'div>table>tbody>tr>td>div>div:eq(3)>div input[name=\\\"css_selector_value\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(3)>div>input', 'div>table>tbody>tr>td>div>div:eq(3)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."5")."'),
                       (6, 'Force mandatory sign', 3, 'div>table>tbody>tr>td>div>div:eq(6)>div input[name=\\\"show_mandatory\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(6)>div>input:eq(1)', 'div>table>tbody>tr>td>div>div:eq(6)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."6")."'),
                       (7, 'Mandatory sign formula', 3, 'div>table>tbody>tr>td>div>div:eq(7)>div input[name=\\\"show_mandatory_if\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(7)>div>input', 'div>table>tbody>tr>td>div>div:eq(7)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."7")."'),
                       (8, 'Mandatory sign CSS selector', 3, 'div>table>tbody>tr>td>div>div:eq(8)>div input[name=\\\"css_selector_mandatorysign\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(8)>div>input', 'div>table>tbody>tr>td>div>div:eq(8)>label', 1, 1, '#6 || #7!=\'\'', NULL, NULL, NULL, '".md5($guid."8")."'),
                       (9, 'Title', 4, 'div>div:eq(2)>div>div:eq(6)>div input[name=\"name\"]', NULL, 'div>div:eq(2)>div>div:eq(6)>div>input', 'div>div:eq(2)>div>div:eq(6)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."9")."'),
                       (10, 'Description', 4, 'div>div:eq(2)>div>div:eq(7)>div textarea[name=\"content\"]', NULL, 'div>div:eq(2)>div>div:eq(7)>div>div:eq(0)', 'div>div:eq(2)>div>div:eq(7)>label', 1, 1, '#11 == 2', '(#11 == 1) || (#11 == 2 && #.length > 10 && countWords(#) > 5)', NULL, NULL, '".md5($guid."10")."'),
                       (11, 'Type', 4, 'div>div:eq(2)>div>div:eq(0)>div select[name=\"type\"]', NULL, 'div>div:eq(2)>div>div:eq(0)>div>span', 'div>div:eq(2)>div>div:eq(0)>label', 1, 0, NULL, NULL, NULL, NULL, '".md5($guid."11")."'),
                       (12, 'Followup Description', 5, 'div:eq(0)>div:eq(0)>div textarea[name=\"content\"]', NULL, 'div:eq(0)>div:eq(0)>div>div:eq(0):not(\'.fileupload\')', 'div:eq(0)>div:eq(0)>div>div:eq(0):not(\'.fileupload\')>div:eq(0)>div:eq(0)', 1, 1, NULL, '#.length > 10 && countWords(#) > 3', NULL, NULL, '".md5($guid."12")."'),
                       (13, 'Title', 11, 'div:eq(0)>div:eq(1)>div input[name=\\\"name\\\"]', NULL, 'div:eq(0)>div:eq(1)>div>input', 'div:eq(0)>div:eq(1)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."13")."'),
                       (14, 'Description', 11, 'div:eq(0)>div:eq(2)>div:eq(0) textarea[name=\"content\"]', NULL, 'div:eq(0)>div:eq(2)>div:eq(0)>div', 'div:eq(0)>div:eq(2)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."14")."'),
                       (15, 'Type', 6, 'div:eq(0)>div>div>div>div:eq(2)>div select[name=\\\"type\\\"]', NULL, 'div:eq(0)>div>div>div>div:eq(2)>div>span', 'div:eq(0)>div>div>div>div:eq(2)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."15")."'),
                       (16, 'Title', 7, 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(0)>div input[name=\\\"name\\\"]', NULL, 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(0)>div>input', 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(0)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."16")."'),
                       (17, 'Description', 7, 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(1)>div:eq(0) textarea[name=\"content\"]', NULL, 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(1)>div:eq(0)>div', 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(1)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."17")."'),
                       (18, 'Name', 8, 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(0)>div>input', 'div>table>tbody>tr>td>div>div:eq(0)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."18")."'),
                       (19, 'Item type (example: Project)', 9, 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(0)>div>input', 'div>table>tbody>tr>td>div>div:eq(0)>label', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."19")."'),
                       (20, 'URL path part (example: /front/project.form.php)', 9, 'div>table>tbody>tr>td>div>div:eq(1)>div input[name=\\\"URL_path_part\\\"]', NULL, 'div>table>tbody>tr>td>div>div:eq(1)>div>input', 'div>table>tbody>tr>td>div>div:eq(1)>label', 1, 1, NULL, '(#.length > 0) && (function(){var res;$.ajax({async: false, url: \\\"/plugins/formvalidation/ajax/existingFile.php\\\",type: \\\"GET\\\",data: { filename: # },success: function (response) {if(response==1){res = 1;}else{res=0;}}});return res;})();', NULL, NULL, '".md5($guid."20")."'),
                       (21, 'Item type (example: Project)', 10, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL, '".md5($guid."21")."'),
                       (22, 'URL path part (example: /front/project.form.php)', 10, 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\"URL_path_part\\\"]', NULL, 'div>table>tbody>tr:eq(2)>td:eq(1)', 'div>table>tbody>tr:eq(2)>td:eq(0)', 1, 1, NULL, '(#.length > 0) && (function(){var res;$.ajax({async: false, url: \\\"/plugins/formvalidation/ajax/existingFile.php\\\",type: \\\"GET\\\",data: { filename: # },success: function (response) {if(response==1){res = 1;}else{res=0;}}});return res;})();', NULL, NULL, '".md5($guid."22")."')";
   $DB->query($query) or die("Error inserting default data into glpi_plugin_formvalidation_fields " . $DB->error());

}
