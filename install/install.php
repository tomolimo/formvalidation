<?php

function formvalidation_install() {
   global $DB;

   $DB->runFile(GLPI_ROOT . "/plugins/formvalidation/install/mysql/formvalidation-empty.sql");

   $query = "INSERT INTO glpi_plugin_formvalidation_configs(`id`)
             VALUE (1)";
   $DB->query($query) or die("Error inserting default item config into glpi_plugin_formvalidation_configs " . $DB->error());

   // init data itemtypes
   $query = "INSERT INTO `glpi_plugin_formvalidation_itemtypes` (`id`, `name`, `itemtype`, `URL_path_part`)
               VALUES (1, 'Computer', 'Computer', '/front/computer.form.php'),
                      (2, 'Monitor', 'Monitor', '/front/monitor.form.php'),
                      (3, 'Software', 'Software', '/front/software.form.php'),
                      (4, 'NetworkEquipment', 'NetworkEquipment', '/front/networkequipment.form.php'),
                      (5, 'Peripheral', 'Peripheral', '/front/peripheral.form.php'),
                      (6, 'Printer', 'Printer', '/front/printer.form.php'),
                      (7, 'CartridgeItem', 'CartridgeItem', '/front/cartridgeitem.form.php'),
                      (8, 'ConsumableItem', 'ConsumableItem', '/front/consumableitem.form.php'),
                      (9, 'Phone', 'Phone', '/front/phone.form.php'),
                      (10, 'Ticket', 'Ticket', '/front/ticket.form.php,'),
                      (11, 'Problem', 'Problem', '/front/problem.form.php'),
                      (12, 'TicketRecurrent', 'TicketRecurrent', '/front/ticketrecurrent.form.php'),
                      (13, 'Budget', 'Budget', '/front/budget.form.php'),
                      (14, 'Supplier', 'Supplier', '/front/supplier.form.php'),
                      (15, 'Contact', 'Contact', '/front/contact.form.php'),
                      (16, 'Contract', 'Contract', '/front/contract.form.php'),
                      (17, 'Document', 'Document', '/front/document.form.php'),
                      (18, 'Notes', 'Notes', '/front/notes.form.php'),
                      (19, 'RSSFeed', 'RSSFeed', '/front/rssfeed.form.php'),
                      (20, 'User', 'User', '/front/user.form.php'),
                      (21, 'Group', 'Group', '/front/group.form.php'),
                      (22, 'Entity', 'Entity', '/front/entity.form.php'),
                      (23, 'Profile', 'Profile', '/front/profile.form.php'),
                      (24, 'PluginFormcreatorForm', 'PluginFormcreatorForm', '/plugins/formcreator/formdisplay.php'),
                      (25, 'PluginFormvalidationPage', 'PluginFormvalidationPage', '/plugins/formvalidation/front/page.form.php'),
                      (26, 'PluginFormvalidationForm', 'PluginFormvalidationForm', '/plugins/formvalidation/front/form.form.php'),
                      (27, 'PluginFormvalidationField', 'PluginFormvalidationField', '/plugins/formvalidation/front/field.form.php'),
                      (28, 'PluginRayusermanagementticketRayusermanagementticket', 'PluginRayusermanagementticketRayusermanagementticket', '/plugins/rayusermanagementticket/front/rayusermanagementticket.helpdesk.public.php'),
                      (29, 'PluginFormvalidationItemtype','pluginFormvalidationItemtype', '/plugins/formvalidation/front/itemtype.form.php') ;";
   $DB->query($query) or die("Error inserting default item types into glpi_plugin_formvalidation_itemtypes " . $DB->error());

   // init data page
   $query = "INSERT INTO `glpi_plugin_formvalidation_pages` (`id`, `name`, `entities_id`, `itemtypes_id`, `is_recursive`, `is_active`, `comment`, `date_mod`)
               VALUES (1, 'Form Validation Page', 0, 25, 1, 1, NULL, NULL),
                      (2, 'Form Validation Form', 0, 26, 1, 1, NULL, NULL),
                      (3, 'Form Validation Field', 0, 27, 1, 1, NULL, NULL),
                      (4, 'Ticket Validations', 0, 10, 1, 1, NULL, NULL),
                      (5, 'Form Validation Itemtype',0, 29, 1, 1, NULL, NULL);";
   $DB->query($query) or die("Error inserting default pages into glpi_plugin_formvalidation_pages " . $DB->error());

   // init data forms
   $query = "INSERT INTO `glpi_plugin_formvalidation_forms` (`id`, `name`, `pages_id`, `css_selector`, `is_createitem`, `is_active`, `use_for_massiveaction`, `formula`, `comment`, `date_mod`)
               VALUES (1, 'form(/plugins/formvalidation/front/page.form.php)', 1, 'form[name=\\\"form\\\"][action=\\\"/plugins/formvalidation/front/page.form.php\\\"]', 0, 1, 0, NULL, NULL, NULL),
                      (2, 'form(/plugins/formvalidation/front/form.form.php)', 2, 'form[name=\\\"form\\\"][action=\\\"/plugins/formvalidation/front/form.form.php\\\"]', 0, 1, 0, NULL, NULL, NULL),
                      (3, 'form(/plugins/formvalidation/front/field.form.php)', 3, 'form[name=\\\"form\\\"][action=\\\"/plugins/formvalidation/front/field.form.php\\\"]', 0, 1, 0, NULL, NULL, NULL),
                      (4, 'Simplified interface Creation', 4, 'form[name=\\\"helpdeskform\\\"][action=\\\"/front/tracking.injector.php\\\"]', 1, 1, 0, NULL, NULL, NULL),
                      (5, 'Followup Validations', 4, 'form[name=\\\"form\\\"][action=\\\"/front/ticketfollowup.form.php\\\"]', 0, 1, 0, NULL, NULL, NULL),
                      (6, 'Central Interface Edit', 4, 'form[name=\\\"form_ticket\\\"][action=\\\"/front/ticket.form.php\\\"]', 0, 1, 1, NULL, NULL,NULL),
                      (7, 'Central Interface Creation', 4, 'form[name=\\\"form_ticket\\\"][action=\\\"/front/ticket.form.php\\\"]', 1, 1, 0, NULL, NULL, NULL),
                      (8, 'form(/plugins/formvalidation/front/page.form.php)', 1, 'form[name=\\\"form\\\"][action=\\\"/plugins/formvalidation/front/page.form.php\\\"]', 1, 1, 0, NULL, NULL, NULL),
                      (9, 'form(/plugins/formvalidation/front/itemtype.form.php)',5, 'form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 1, 1, 0, NULL, NULL, NULL),
                      (10, 'form(/plugins/formvalidation/front/itemtype.form.php)',5, 'form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 0, 1, 0, NULL, NULL, NULL);";
   $DB->query($query) or die("Error inserting default data into glpi_plugin_formvalidation_forms " . $DB->error());

   // init data fields
   $query = "INSERT INTO `glpi_plugin_formvalidation_fields` (`id`, `name`, `forms_id`, `css_selector_value`, `css_selector_altvalue`, `css_selector_errorsign`, `css_selector_mandatorysign`, `is_active`, `show_mandatory`, `show_mandatory_if`, `formula`, `comment`, `date_mod`)
                VALUES (1,  'Name', 1, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (2,  'Name', 2, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (3,  'CSS Selector', 2, 'div>table>tbody>tr:eq(4)>td:eq(1) input[name=\\\"css_selector\\\"]', NULL, 'div>table>tbody>tr:eq(4)>td:eq(1)', 'div>table>tbody>tr:eq(4)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (4,  'Name', 3, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (5,  'Value CSS selector', 3, 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\"css_selector_value\\\"]', NULL, 'div>table>tbody>tr:eq(3)>td:eq(1)', 'div>table>tbody>tr:eq(3)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (6,  'Force mandatory sign', 3, 'div>table>tbody>tr:eq(6)>td:eq(1)>span input[name=\\\"show_mandatory\\\"]', NULL, 'div>table>tbody>tr:eq(6)>td:eq(1)', 'div>table>tbody>tr:eq(6)>td:eq(0)', 1, 0, NULL, NULL, NULL, NULL),
                       (7,  'Mandatory sign formula', 3, 'div>table>tbody>tr:eq(7)>td:eq(1) input[name=\\\"show_mandatory_if\\\"]', NULL, 'div>table>tbody>tr:eq(7)>td:eq(1)', 'div>table>tbody>tr:eq(7)>td:eq(0)', 0, 0, NULL, NULL, NULL, NULL),
                       (8,  'Mandatory sign CSS selector', 3, 'div>table>tbody>tr:eq(8)>td:eq(1) input[name=\\\"css_selector_mandatorysign\\\"]', NULL, 'div>table>tbody>tr:eq(8)>td:eq(1)', 'div>table>tbody>tr:eq(8)>td:eq(0)', 1, 0, '#6 || #7!=\'\'', NULL, NULL, NULL),
                       (9,  'Title', 4, 'div>table>tbody>tr:eq(8)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(8)>td:eq(1)', 'div>table>tbody>tr:eq(8)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (10, 'Description', 4, 'div>table>tbody>tr:eq(9)>td:eq(1)>div textarea[name=\\\"content\\\"]', NULL, 'div>table>tbody>tr:eq(9)>td:eq(1)', 'div>table>tbody>tr:eq(9)>td:eq(0)', 1, 0, '#11 == 2','(#11 == 1) || (#11 == 2 && #.length > 10 && countWords(#) > 5)', NULL, NULL),
                       (11, 'Type', 4, 'div>table>tbody>tr:eq(1)>td:eq(1) select[name=\\\"type\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 0, NULL, NULL, NULL, NULL),
                       (12, 'Description', 5, 'div>table>tbody>tr:eq(1)>td:eq(1) textarea[name=\\\"content\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, '#.length > 10 && countWords(#) > 3', NULL, NULL),
                       (13, 'Title', 6, 'div>table:eq(2)>tbody>tr:eq(0)>td input[name=\\\"name\\\"]', NULL, 'div>table:eq(2)>tbody>tr:eq(0)>td', 'div>table:eq(2)>tbody>tr:eq(0)>th', 1, 1, NULL, NULL, NULL, NULL),
                       (14, 'Description', 6, 'div>table:eq(2)>tbody>tr:eq(1)>td>div textarea[name=\\\"content\\\"]', NULL, 'div>table:eq(2)>tbody>tr:eq(1)>td', 'div>table:eq(2)>tbody>tr:eq(1)>th', 1, 1, NULL, NULL, NULL, NULL),
                       (15, 'Type', 6, 'div>table:eq(1)>tbody>tr:eq(0)>td:eq(0) select[name=\\\"type\\\"]', NULL, 'div>table:eq(1)>tbody>tr:eq(0)>td:eq(0)', 'div>table:eq(1)>tbody>tr:eq(0)>th:eq(0)', 1, 0, NULL, NULL, NULL, NULL),
                       (16, 'Title', 7, 'div>table:eq(2)>tbody>tr:eq(0)>td input[name=\\\"name\\\"]', NULL, 'div>table:eq(2)>tbody>tr:eq(0)>td', 'div>table:eq(2)>tbody>tr:eq(0)>th', 1, 1, NULL, NULL, NULL, NULL),
                       (17, 'Description', 7, 'div>table:eq(2)>tbody>tr:eq(1)>td>div textarea[name=\\\"content\\\"]', NULL, 'div>table:eq(2)>tbody>tr:eq(1)>td', 'div>table:eq(2)>tbody>tr:eq(1)>th', 1, 1, NULL, NULL, NULL, NULL),
                       (18, 'Name', 8, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (19, 'Name', 9, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (20, 'Item type (example: Project)', 9, 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\"itemtype\\\"]', NULL, 'div>table>tbody>tr:eq(2)>td:eq(1)','div>table>tbody>tr:eq(2)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (21, 'URL path part (example: /front/project.form.php)', 9, 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\"URL_path_part\\\"]', NULL, 'div>table>tbody>tr:eq(3)>td:eq(1)', 'div>table>tbody>tr:eq(3)>td:eq(0)', 1, 1, NULL, '(#.length > 0) && (function(){var res;$.ajax({async: false, url: \"/plugins/formvalidation/ajax/existingFile.php\",type: \"GET\",data: { filename: # },success: function (response) {if(response==1){res = 1;}else{res=0;}}});return res;})();', NULL, NULL),
                       (22, 'Name', 10, 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (23, 'Item type (example: Project)', 10, 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\"itemtype\\\"]', NULL, 'div>table>tbody>tr:eq(2)>td:eq(1)','div>table>tbody>tr:eq(2)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL),
                       (24, 'URL path part (example: /front/project.form.php)', 10, 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\"URL_path_part\\\"]', NULL, 'div>table>tbody>tr:eq(3)>td:eq(1)', 'div>table>tbody>tr:eq(3)>td:eq(0)', 1, 1, NULL, '(#.length > 0) && (function(){var res;$.ajax({async: false, url: \"/plugins/formvalidation/ajax/existingFile.php\",type: \"GET\",data: { filename: # },success: function (response) {if(response==1){res = 1;}else{res=0;}}});return res;})();', NULL, NULL); ";
   $DB->query($query) or die("Error inserting default data into glpi_plugin_formvalidation_fields " . $DB->error());

}