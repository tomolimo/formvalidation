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

use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * hook short summary.
 *
 * hook description.
 *
 * @version 1.0
 * @author MoronO
 */
class PluginFormvalidationHook extends CommonDBTM {

   /**
    * Summary of plugin_post_item_form_formvalidation
    * @param mixed $parm mixed
    * @return
    */
   static public function plugin_post_item_form_formvalidation($parm) {
      if ($parm['item']->getType() == 'TicketSatisfaction') {
         echo "<script type='text/javascript'>
                     $('#stars').on('rated', function() { $('#satisfaction_data').change(); });
                </script>";
         echo "</td></tr>";
      }
   }



   /**
    * Summary of plugin_pre_item_update_formvalidation
    * @param mixed $parm the object that is going to be updated
    * @return
    */
   static public function plugin_pre_item_update_formvalidation($parm) {
      global $DB;

      $config = PluginFormvalidationConfig::getInstance();
      $path = $config->fields["js_path"];
      // to be executed only for massive actions
      if (strstr($_SERVER['PHP_SELF'], "/front/massiveaction.php")) {
         $ret=[];

         //clean input values
         $input = $parm->input;
         unset($input['id']);
         if (isset($input['_no_history'])) {
            unset($input['_no_history']);
         }

         $itemvalues = array_merge( $parm->fields, $input );
         $formulas = [];
         $fieldnames = [];
         $fieldtitles = [];

         $query2 =    [
                        'SELECT'    => [
                           'glpi_plugin_formvalidation_forms.id',
                           'glpi_plugin_formvalidation_forms.name',
                           'glpi_plugin_formvalidation_forms.plugin_formvalidation_pages_id',
                           'glpi_plugin_formvalidation_forms.css_selector',
                           'glpi_plugin_formvalidation_forms.is_createitem',
                           'glpi_plugin_formvalidation_forms.is_active',
                           'glpi_plugin_formvalidation_forms.use_for_massiveaction',
                           'glpi_plugin_formvalidation_forms.formula',
                           'glpi_plugin_formvalidation_forms.comment',
                           'glpi_plugin_formvalidation_forms.date_mod',
                           'glpi_plugin_formvalidation_forms.guid'
                           ],
                        'DISTINCT'  => true,
                        'FROM'      => 'glpi_plugin_formvalidation_forms',
                        'LEFT JOIN' => [
                           'glpi_plugin_formvalidation_fields' => [
                              'FKEY' => [
                                 'glpi_plugin_formvalidation_forms' => 'id',
                                 'glpi_plugin_formvalidation_fields' => 'plugin_formvalidation_forms_id'
                              ]
                           ],
                           'glpi_plugin_formvalidation_pages' => [
                              'FKEY' => [
                                 'glpi_plugin_formvalidation_pages' => 'id',
                                 'glpi_plugin_formvalidation_forms' => 'plugin_formvalidation_pages_id'
                              ]
                           ],
                           'glpi_plugin_formvalidation_itemtypes' => [
                              'FKEY' => [
                                 'glpi_plugin_formvalidation_itemtypes' => 'id',
                                 'glpi_plugin_formvalidation_pages' => 'plugin_formvalidation_itemtypes_id'
                              ]
                           ]
                        ],
                        'WHERE' => [
                           'AND' => [
                              'glpi_plugin_formvalidation_itemtypes.name' => $parm->getType(),
                              'glpi_plugin_formvalidation_forms.use_for_massiveaction' => 1
                           ]
                        ]
                  ];

         // if $input is empty, then do not continue the validation
         if (empty($input)) {
            return;
         }

         $key = array_keys($input);
         $query2['WHERE']['AND']["glpi_plugin_formvalidation_fields.css_selector_value"] = ['LIKE', '%'.$key[0].'%'];

         foreach ($DB->request( $query2) as $form) {
            foreach ($DB->request('glpi_plugin_formvalidation_fields', ['AND' => ['plugin_formvalidation_forms_id' => $form['id'], 'is_active' => 1]])  as $field) {
               $matches = [];
               if (preg_match('/\[(name|id\^)=\\\\{0,1}"(?<name>[a-z_\-0-9]+)\\\\{0,1}"\]/i', $field['css_selector_value'], $matches)) {
                  $fieldnames[$field['id']] = trim($matches['name'], "_");
                  $formulas[$field['id']] = ($field['formula'] ? $field['formula'] : '#>0 || #!=""');
                  $fieldtitles[$field['id']] = $field['name'];
               }
            }

            $values=[];
            foreach ($formulas as $fieldnum => $formula) {
               $values[$fieldnum] = $DB->escape($itemvalues[$fieldnames[$fieldnum]] ? $itemvalues[$fieldnames[$fieldnum]] : "" );
            }
            $formulaJS=[];
            foreach ($formulas as $fieldnum => $formula) {
               $formulaJS[$fieldnum] = $formula;
               foreach ($values as $valnum => $val) {
                  if ($fieldnum == $valnum) {
                     $regex = '/#\B/i';
                  } else {
                     $regex = '/#'.$valnum.'\b/i';
                  }
                  $formulaJS[$fieldnum] = preg_replace( $regex, '"'.$values[$valnum].'"', $formulaJS[$fieldnum] );
               }
            }

            $ret=[];
            $helpers = file_get_contents(__DIR__ . "/../js/helpers_function.js");
            foreach ($formulaJS as $index => $formula) {
               try {
                  if (extension_loaded('v8js')) {
                     $v8 = new V8Js();
                     if (!$v8->executeString($helpers."\n
                        exec = $formula;" )
                        ) {
                        Session::addMessageAfterRedirect(sprintf( __('Mandatory fields or wrong value: \'%s\'', 'formvalidation'), __($fieldtitles[$index])), true, ERROR );
                        $ret[] = $fieldnames[$index];
                     }
                  } else {
                     if (file_exists($path)) {
                        $tmpfile = tempnam(GLPI_ROOT.'/files/_tmp', 'tmp');
                        $handle = fopen($tmpfile, "w");
                        fwrite($handle, $helpers . "\nif(" . $formula . '){console.log(1);}else{console.log(0);}');
                        fclose($handle);
                        $valid = exec("\"$path\" \"$tmpfile\"");
                        if ($valid == 0 || is_null($valid)) {
                           Session::addMessageAfterRedirect(sprintf( __('Mandatory fields or wrong value: \'%s\'', 'formvalidation'), __($fieldtitles[$index])), true, ERROR );
                           $ret[] = $fieldnames[$index];
                        }
                        unlink($tmpfile);
                     } else {
                        Session::addMessageAfterRedirect( __('The field was not updated because node.js or v8js are not installed/enabled. Contact your system administrator', 'formvalidation'), true, ERROR );
                        $ret[] = $fieldnames[$index];
                     }
                  }
               } catch (Exception $ex) {
                  Session::addMessageAfterRedirect( __('Error: ').__($ex->message), false, ERROR );
                  $ret[]=$fieldnames[$index];
               }
            }
         }
         if (count($ret) > 0) {
            $parm->input = []; //to prevent update of unconsistant data
         }

      }
   }

   static public function plugin_pre_show_tab_formvalidation($parm) {
      echo '';
   }

}
