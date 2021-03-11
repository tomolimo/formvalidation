<?php

function formvalidation_install() {
   global $DB, $CFG_GLPI;

   $DB->runFile(GLPI_ROOT . "/plugins/formvalidation/install/mysql/formvalidation-empty.sql");

   $DB->insertOrDie(
         'glpi_plugin_formvalidation_configs', [
            'id'            => '1',
            'css_mandatory' => '{\"background-color\":\"lightgrey\", \"font-weight\":\"bold\"}',
            'css_error'     => '{\"background-color\": \"red\"}'
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
   $query = "INSERT INTO `glpi_plugin_formvalidation_pages` (`id`, `name`, `entities_id`, `itemtypes_id`, `is_recursive`, `is_active`, `comment`, `date_mod`, `guid`)
               VALUES (1, 'Form Validation Page', 0, 25, 1, 1, NULL, NULL,'".md5($guid."1")."'),
                      (2, 'Form Validation Form', 0, 26, 1, 1, NULL, NULL,'".md5($guid."2")."'),
                      (3, 'Form Validation Field', 0, 27, 1, 1, NULL, NULL,'".md5($guid."3")."'),
                      (4, 'Ticket Validations', 0, 10, 1, 1, NULL, NULL,'".md5($guid."4")."'),
                      (5, 'Form Validation Itemtype',0, 29, 1, 1, NULL, NULL,'".md5($guid."5")."');";
   $DB->query($query) or die("Error inserting default pages into glpi_plugin_formvalidation_pages " . $DB->error());

   // init data forms
   $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[2]."/".time()."/".rand()."/";
   $query = "INSERT INTO `glpi_plugin_formvalidation_forms` (`id`, `name`, `pages_id`, `css_selector`, `is_createitem`, `is_active`, `use_for_massiveaction`, `formula`, `comment`, `date_mod`,`guid`)
               VALUES (1, 'form(/plugins/formvalidation/front/page.form.php)', 1, 'form[name=\\\"form\\\"][action=\\\"/plugins/formvalidation/front/page.form.php\\\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."1")."'),
                      (2, 'form(/plugins/formvalidation/front/form.form.php)', 2, 'form[name=\\\"form\\\"][action=\\\"/plugins/formvalidation/front/form.form.php\\\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."2")."'),
                      (3, 'form(/plugins/formvalidation/front/field.form.php)', 3, 'form[name=\\\"form\\\"][action=\\\"/plugins/formvalidation/front/field.form.php\\\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."3")."'),
                      (4, 'Simplified interface Creation', 4, 'form[name=\\\"helpdeskform\\\"][action=\\\"/front/tracking.injector.php\\\"]', 1, 1, 0, NULL, NULL, NULL,'".md5($guid."4")."'),
                      (5, 'Followup Validations', 4, 'form[name=\\\"form\\\"][action=\\\"/front/ticketfollowup.form.php\\\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."5")."'),
                      (6, 'Central Interface Edit', 4, 'form[name=\\\"form_ticket\\\"][action=\\\"/front/ticket.form.php\\\"]', 0, 1, 1, NULL, NULL,NULL,'".md5($guid."6")."'),
                      (7, 'Central Interface Creation', 4, 'form[name=\\\"form_ticket\\\"][action=\\\"/front/ticket.form.php\\\"]', 1, 1, 0, NULL, NULL, NULL,'".md5($guid."7")."'),
                      (8, 'form(/plugins/formvalidation/front/page.form.php)', 1, 'form[name=\\\"form\\\"][action=\\\"/plugins/formvalidation/front/page.form.php\\\"]', 1, 1, 0, NULL, NULL, NULL,'".md5($guid."8")."'),
                      (9, 'form(/plugins/formvalidation/front/itemtype.form.php)',5, 'form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 1, 1, 0, NULL, NULL, NULL,'".md5($guid."9")."'),
                      (10, 'form(/plugins/formvalidation/front/itemtype.form.php)',5, 'form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 0, 1, 0, NULL, NULL, NULL,'".md5($guid."10")."');";
   $DB->query($query) or die("Error inserting default data into glpi_plugin_formvalidation_forms " . $DB->error());

   // init data fields
   $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[3]."/".time()."/".rand()."/";
   $query = "INSERT INTO `glpi_plugin_formvalidation_fields` (`id`, `name`, `forms_id`, `css_selector_value`, `css_selector_altvalue`, `css_selector_errorsign`, `css_selector_mandatorysign`, `is_active`, `show_mandatory`, `show_mandatory_if`, `formula`, `comment`, `date_mod`,`guid`)
                VALUES (1,  'Name', 1, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."1")."'),
                       (2,  'Name', 2, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."2")."'),
                       (3,  'CSS Selector', 2, 'div>table>tbody>tr:eq(4)>td:eq(1) input[name=\\\"css_selector\\\"]', NULL, 'div>table>tbody>tr:eq(4)>td:eq(1)', 'div>table>tbody>tr:eq(4)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."3")."'),
                       (4,  'Name', 3, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."4")."'),
                       (5,  'Value CSS selector', 3, 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\"css_selector_value\\\"]', NULL, 'div>table>tbody>tr:eq(3)>td:eq(1)', 'div>table>tbody>tr:eq(3)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."5")."'),
                       (6,  'Force mandatory sign', 3, 'div>table>tbody>tr:eq(6)>td:eq(1)>span input[name=\\\"show_mandatory\\\"]', NULL, 'div>table>tbody>tr:eq(6)>td:eq(1)', 'div>table>tbody>tr:eq(6)>td:eq(0)', 1, 0, NULL, NULL, NULL, NULL,'".md5($guid."6")."'),
                       (7,  'Mandatory sign formula', 3, 'div>table>tbody>tr:eq(7)>td:eq(1) input[name=\\\"show_mandatory_if\\\"]', NULL, 'div>table>tbody>tr:eq(7)>td:eq(1)', 'div>table>tbody>tr:eq(7)>td:eq(0)', 0, 0, NULL, NULL, NULL, NULL,'".md5($guid."7")."'),
                       (8,  'Mandatory sign CSS selector', 3, 'div>table>tbody>tr:eq(8)>td:eq(1) input[name=\\\"css_selector_mandatorysign\\\"]', NULL, 'div>table>tbody>tr:eq(8)>td:eq(1)', 'div>table>tbody>tr:eq(8)>td:eq(0)', 1, 0, '#6 || #7!=\'\'', NULL, NULL, NULL,'".md5($guid."8")."'),
                       (9,  'Title', 4, 'div>table>tbody>tr:eq(8)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(8)>td:eq(1)', 'div>table>tbody>tr:eq(8)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."9")."'),
                       (10, 'Description', 4, 'div>table>tbody>tr:eq(9)>td:eq(1)>div textarea[name=\\\"content\\\"]', NULL, 'div>table>tbody>tr:eq(9)>td:eq(1)', 'div>table>tbody>tr:eq(9)>td:eq(0)', 1, 0, '#11 == 2','(#11 == 1) || (#11 == 2 && #.length > 10 && countWords(#) > 5)', NULL, NULL,'".md5($guid."10")."'),
                       (11, 'Type', 4, 'div>table>tbody>tr:eq(1)>td:eq(1) select[name=\\\"type\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 0, NULL, NULL, NULL, NULL,'".md5($guid."11")."'),
                       (12, 'Description', 5, 'div>table>tbody>tr:eq(1)>td:eq(1) textarea[name=\\\"content\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, '#.length > 10 && countWords(#) > 3', NULL, NULL,'".md5($guid."12")."'),
                       (13, 'Title', 6, 'div>table:eq(2)>tbody>tr:eq(0)>td input[name=\\\"name\\\"]', NULL, 'div>table:eq(2)>tbody>tr:eq(0)>td', 'div>table:eq(2)>tbody>tr:eq(0)>th', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."13")."'),
                       (14, 'Description', 6, 'div>table:eq(2)>tbody>tr:eq(1)>td>div textarea[name=\\\"content\\\"]', NULL, 'div>table:eq(2)>tbody>tr:eq(1)>td', 'div>table:eq(2)>tbody>tr:eq(1)>th', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."14")."'),
                       (15, 'Type', 6, 'div>table:eq(1)>tbody>tr:eq(0)>td:eq(0) select[name=\\\"type\\\"]', NULL, 'div>table:eq(1)>tbody>tr:eq(0)>td:eq(0)', 'div>table:eq(1)>tbody>tr:eq(0)>th:eq(0)', 1, 0, NULL, NULL, NULL, NULL,'".md5($guid."15")."'),
                       (16, 'Title', 7, 'div>table:eq(2)>tbody>tr:eq(0)>td input[name=\\\"name\\\"]', NULL, 'div>table:eq(2)>tbody>tr:eq(0)>td', 'div>table:eq(2)>tbody>tr:eq(0)>th', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."16")."'),
                       (17, 'Description', 7, 'div>table:eq(2)>tbody>tr:eq(1)>td>div textarea[name=\\\"content\\\"]', NULL, 'div>table:eq(2)>tbody>tr:eq(1)>td', 'div>table:eq(2)>tbody>tr:eq(1)>th', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."17")."'),
                       (18, 'Name', 8, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."18")."'),
                       (19, 'Name', 9, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."19")."'),
                       (20, 'Item type (example: Project)', 9, 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\"itemtype\\\"]', NULL, 'div>table>tbody>tr:eq(2)>td:eq(1)','div>table>tbody>tr:eq(2)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."20")."'),
                       (21, 'URL path part (example: /front/project.form.php)', 9, 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\"URL_path_part\\\"]', NULL, 'div>table>tbody>tr:eq(3)>td:eq(1)', 'div>table>tbody>tr:eq(3)>td:eq(0)', 1, 1, NULL, '(#.length > 0) && (function(){var res;$.ajax({async: false, url: \"/plugins/formvalidation/ajax/existingFile.php\",type: \"GET\",data: { filename: # },success: function (response) {if(response==1){res = 1;}else{res=0;}}});return res;})();', NULL, NULL,'".md5($guid."21")."'),
                       (22, 'Name', 10, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."22")."'),
                       (23, 'Item type (example: Project)', 10, 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\"itemtype\\\"]', NULL, 'div>table>tbody>tr:eq(2)>td:eq(1)','div>table>tbody>tr:eq(2)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL,'".md5($guid."23")."'),
                       (24, 'URL path part (example: /front/project.form.php)', 10, 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\"URL_path_part\\\"]', NULL, 'div>table>tbody>tr:eq(3)>td:eq(1)', 'div>table>tbody>tr:eq(3)>td:eq(0)', 1, 1, NULL, '(#.length > 0) && (function(){var res;$.ajax({async: false, url: \"/plugins/formvalidation/ajax/existingFile.php\",type: \"GET\",data: { filename: # },success: function (response) {if(response==1){res = 1;}else{res=0;}}});return res;})();', NULL, NULL,'".md5($guid."24")."'); ";
   $DB->query($query) or die("Error inserting default data into glpi_plugin_formvalidation_fields " . $DB->error());

}