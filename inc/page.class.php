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

use Glpi\Application\View\TemplateRenderer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * process short summary.
 *
 * process description.
 *
 * @version 1.0
 * @author MoronO
 */
class PluginFormvalidationPage extends CommonDBTM {

   static $rightname = 'config';

   static function canPurge() {
      return Config::canUpdate();
   }

   static function canCreate() {
      return Config::canUpdate();
   }

   /**
    * Summary of rawSearchOptions
    * @return mixed
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
            'id'                 => 'common',
            'name'               => __('Page', 'formvalidation')
         ];
      $tab[] = [
        'id'                 => '1',
        'table'              => $this->getTable(),
        'field'              => 'name',
        'name'               => __('Name'),
        'datatype'           => 'itemlink',
        'searchtype'         => 'contains',
        'massiveaction'      => false,
        'itemlink_type'      => 'PluginFormvalidationPage'
      ];

      $tab[] = [
         'id'                 => '803',
         'table'              => 'glpi_plugin_formvalidation_itemtypes',
         'field'              => 'name',
         'linkfield'          => 'plugin_formvalidation_itemtypes_id',
         'name'               => __('Associated item type'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'is_active',
         'name'               => __('Active'),
         'massiveaction'      => true,
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'massiveaction'      => true,
         'datatype'           => 'text'
      ];

      $tab[] = [
         'id'                 => '19',
         'table'              => $this->getTable(),
         'field'              => 'date_mod',
         'name'               => __('Last update'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '80',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '86',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Child entities'),
         'datatype'           => 'bool'
      ];

      return $tab;
   }


   static function getTypeName($nb = 0) {

      if ($nb>1) {
         return __('Pages', 'formvalidation');
      }
      return __('Page', 'formvalidation');
   }

   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);

      $this->addStandardTab('PluginFormvalidationForm', $ong, $options);

      return $ong;
   }


   /**
    * Summary of showForm
    * @param  $ID
    * @param  $options
    */
   function showForm ($ID, $options = ['candel'=>false]) {

      if ($ID > 0) {
         $this->check($ID, READ);
      }

      $canedit = $this->can($ID, UPDATE);
      $options['canedit'] = $canedit;

      $this->initForm($ID, $options);

      $this->showFormHeader($options);

      $html = TemplateRenderer::getInstance()->render('@formvalidation/page_form_validation.html.twig', [
        'data' => $this->fields,
        'ID' => $ID
     ]);

      echo $html;

      $this->showFormButtons($options );
   }


   /**
    * Actions done after the PURGE of the item in the database
    *
    * @return
    **/
   function post_purgeItem() {
      global $DB;
      // as it is purged, then need to purge the associated forms
      // get list of form to purge them
      $frm = new PluginFormvalidationForm;
      $res = $DB->request(
                     $frm->getTable(),
                     ['pages_id' => $this->getID()]
            );
      foreach ($res as $row) {
         $frm->delete($row, true);
      }

   }


   /**
    * Summary of showMassiveActionsSubForm
    * @param MassiveAction $ma
    * @return bool
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'exportPage':
            echo Html::submit(__('Export'), ['name' => 'massiveaction'])."</span>";

            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }


   /**
    * Summary of getForms
    * @return array
    */
   function getForms() {
      $forms = new PluginFormvalidationForm();
      return $forms->find(['pages_id' => $this->fields['id']]);
   }


   /**
    * Summary of getItemtypes
    * @return array
    */
   function getItemtypes() {
      $itemType = new PluginFormvalidationItemtype();
      $itemType_id = $this->fields['itemtypes_id'];
      $datas = $itemType->find(['id' => $this->fields['itemtypes_id']]);
      $guid = $datas[$itemType_id]['guid'];
      $datas[$guid] = $datas[$itemType_id];
      unset($datas[$itemType_id]);
      return $datas;
   }


   /**
    * Summary of processMassiveActionsForOneItemtype
    * @param MassiveAction $ma
    * @param CommonDBTM $item
    * @param array $ids
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      global $CFG_GLPI;

      $json = [];
      $page = new PluginFormvalidationPage();
      $name = '';
      switch ($ma->getAction()) {
         case 'exportPage':
            foreach ($ids as $id) {
               $page->getFromDB($id);
               $datas = $page->fields;
               $datas["form"] = $page->getForms();
               $datas["itemtype"] = $page->getItemtypes();
               $name .= "-";
               $name .= $id;

               foreach ($datas["form"] as $key => $form) {
                  $datas["form"][$form['guid']] = $form;
                  $fields = new PluginFormvalidationField();
                  $form_id = $form["id"];
                  $f = $fields->find(['forms_id' => $form_id]);
                  $datas["form"][$form['guid']]["fields"] = $f;
                  unset($datas["form"][$key]);
               }
               array_push($json, $datas);
               $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
            }
            $json = json_encode($json);
            $filename = 'export_page'.$name.'.json';
            $export = GLPI_TMP_DIR."/$filename";
            $fichier = fopen($export, 'w+');
            fwrite($fichier, $json);
            fclose($fichier);
      }
      $ma->setRedirect($CFG_GLPI['root_doc']."/plugins/formvalidation/front/formvalidation.backup.php?action=download&filename=$filename&itemtype=".$item->getType());
   }


   
   function prepareInputForAdd($input){
      global $CFG_GLPI;
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/page/".time()."/".rand()."/";
      $input['guid'] = md5($guid);

      return $input;
   }


   /**
    * Summary of titleBackup
    * Display import button on page.php
    */
   static function titleBackup() {
      global $CFG_GLPI;
      $val =  _x('button', 'Import');

      echo "<div class='center'><table class='tab_glpi'><tr>";
      echo "<td><i class='fa fa-save fa-3x'></i></td>";
      echo "<td><a class='vsubmit' href='".$CFG_GLPI["root_doc"]."/plugins/formvalidation/front/formvalidation.backup.php?action=import'>".$val."</a></td>";
      echo "</tr></table></div>";
   }


   /**
    * Summary of displayImportFormvalidationForm
    */
   static function displayImportFormvalidationForm() {

      echo "<form name='form' method='post' action='formvalidation.backup.php' ".
      "enctype='multipart/form-data' >";
      echo "<div class='center'>";

      echo "<h2>".__("Import pages from a JSON file")."</h2>";
      echo "<input type='file' name='json_file'>&nbsp;";
      echo "<input type='hidden' name='action' value='process_import'>";
      echo "<input type='submit' name='import' value=\""._sx('button', 'Import').
      "\" class='submit'>";

      // Close for Form
      echo "</div>";
      Html::closeForm();
   }


   /**
    * Summary of processImportPage
    * @throws Exception
    * @return bool
    */
   static function processImportPage() {
      global $DB;
      $nbPagesUpdate = 0;
      $oldNew = [];//array();
      $cptWarning = 0;
      if (!isset($_FILES["json_file"]) || ($_FILES["json_file"]["size"] == 0)) {
         return false;
      }

      $file = file_get_contents($_FILES["json_file"]["tmp_name"]);
      if (!$array = json_decode($file)) {
         Session::addMessageAfterRedirect(
               sprintf(__('The file is badly formatted')),
               true,
               ERROR
            );
         return false;
      }
      try {
         $DB->beginTransaction();
         foreach ($array as $p) { // For each page in JSON File
            foreach ($p->{'itemtype'} as $it) { // For itemtype in current page
               $guid_it = $it->{'guid'};
               $query = $DB->request('glpi_plugin_formvalidation_itemtypes', ['OR' => ['guid'=>$guid_it, 'name'=> $it->{'name'}]]);
               if (count($query)) {
                  //manage case guid already exists
                  //get ID itemtypes
                  if ($row = $query->next()) {
                     $lastIdItemTypes = $row['id'];
                  }
                  if (!$DB->update(
                     'glpi_plugin_formvalidation_itemtypes',
                     [
                        'name'         => $DB->escape($it->{'name'}),
                        'URL_path_part'=> $it->{'URL_path_part'},
                        'guid'         => $it->{'guid'}
                     ],
                     [
                        'id'           => $lastIdItemTypes//$it->{'id'}
                     ]
                  )) {
                     throw new Exception('Error updating itemtypes field into glpi_plugin_formvalidation_itemtypes ' . $DB->error());
                  }
               } else {
                  if (!$DB->insert(
                     'glpi_plugin_formvalidation_itemtypes',
                     [
                        'name'         => $DB->escape($it->{'name'}),
                        'URL_path_part'=> $it->{'URL_path_part'},
                        'guid'         => $it->{'guid'}
                     ]
                  )) {
                     throw new Exception('Error inserting itemtypes field into glpi_plugin_formvalidation_itemtypes ' . $DB->error());
                  } else {
                     $lastIdItemTypes = $DB->insertId();
                  }
               }
            } // End itemtypes

            $guid_page = $p->{'guid'};
            $query = $DB->request('glpi_plugin_formvalidation_pages', ['guid' => $guid_page]);
            if (count($query)) {
               //manage case guid already exists
               // if update ok => get ID of the page on the new environment
               if ($row = $query->next()) {
                  $lastIdPages = $row['id'];
               }
               if (!$DB->update(
                  'glpi_plugin_formvalidation_pages',
                  [
                     'name'         => $DB->escape($p->{'name'}),
                     'entities_id'  => $p->{'entities_id'},
                     'itemtypes_id' => $lastIdItemTypes,
                     'is_recursive' => $p->{'is_recursive'},
                     'is_active'    => $p->{'is_active'},
                     'comment'      => $DB->escape($p->{'comment'}),
                     'date_mod'     => $p->{'date_mod'},
                     'guid'         => $p->{'guid'}
                  ],
                  [
                     'id'           => $lastIdPages //$p->{'id'}
                  ]
               )) {
                  throw new Exception('Error updating pages field into glpi_plugin_formvalidation_pages ' . $DB->error());
               }
               //disable all existing form for this page
               $DB->updateOrDie(
                  'glpi_plugin_formvalidation_forms',
                  [
                     'is_active' => 0,
                  ],
                  [
                     'pages_id'  => $lastIdPages //$p->{'id'}
                  ],
                  'Error updating pages field into glpi_plugin_formvalidation_pages ' . $DB->error()
               );
            } else {
               $query = $DB->request('glpi_plugin_formvalidation_pages', ['name' => $p->{'name'}]);
               if (count($query)) {
                  $cptWarning++;
                  $name = $p->{'name'}.date('Ymd H:i:s');
               } else {
                  $name = $p->{'name'};
               }
               if (!$DB->insert(
                  'glpi_plugin_formvalidation_pages',
                  [
                     'name'         => $DB->escape($name),
                     'entities_id'  => $p->{'entities_id'},
                     'itemtypes_id' => $lastIdItemTypes,
                     'is_recursive' => $p->{'is_recursive'},
                     'is_active'    => $p->{'is_active'},
                     'comment'      => $DB->escape($p->{'comment'}),
                     'date_mod'     => $p->{'date_mod'},
                     'guid'         => $p->{'guid'}
                  ],
                  'Error inserting pages field into glpi_plugin_formvalidation_pages ' . $DB->error()
               )) {
                  throw new Exception('Error inserting pages field into glpi_plugin_formvalidation_pages ' . $DB->error());
               } else {
                  $lastIdPages = $DB->insertId();
               }
            }
            foreach ($p->{'form'} as $fo) {
               $query = $DB->request('glpi_plugin_formvalidation_forms', ['guid' => $fo->{'guid'}]);
               if (count($query)) {
                  //manage case guid already exists
                  //get ID forms
                  if ($row = $query->next()) {
                     $lastIdForms = $row['id'];
                  }
                  if (!$DB->update(
                     'glpi_plugin_formvalidation_forms',
                     [
                        'name'         => $DB->escape($fo->{'name'}),
                        'pages_id'     => $lastIdPages,//$fo->{'pages_id'},
                        'css_selector' => $fo->{'css_selector'},
                        'is_createItem'=> $fo->{'is_createitem'},
                        'is_active'    => $fo->{'is_active'},
                        'use_for_massiveaction' =>  $fo->{'use_for_massiveaction'},
                        'formula'      => $DB->escape($fo->{'formula'}),
                        'comment'      => $DB->escape($fo->{'comment'}),
                        'date_mod'     => $fo->{'date_mod'},
                        'guid'         => $fo->{'guid'}
                     ],
                     [
                        'id'           => $lastIdForms //$fo->{'id'}
                     ]
                  )) {
                     throw new Exception('Error updating forms fields into glpi_plugin_formvalidation_forms ' . $DB->error());
                  }

                  //disable all existing fields in the forms
                  $DB->updateOrDie(
                     'glpi_plugin_formvalidation_fields',
                     [
                        'is_active' => 0
                     ],
                     [
                        'forms_id' => $lastIdForms 
                     ],
                     'Error inserting pages field into glpi_plugin_formvalidation_pages ' . $DB->error()
                  );
               } else {
                  if (!$DB->insert(
                     'glpi_plugin_formvalidation_forms',
                     [
                        'name'         => $DB->escape($fo->{'name'}),
                        'pages_id'     => $lastIdPages,
                        'css_selector' => $fo->{'css_selector'},
                        'is_createItem'=> $fo->{'is_createitem'},
                        'is_active'    => $fo->{'is_active'},
                        'use_for_massiveaction' =>  $fo->{'use_for_massiveaction'},
                        'formula'      => $DB->escape($fo->{'formula'}),
                        'comment'      => $DB->escape($fo->{'comment'}),
                        'date_mod'     => $fo->{'date_mod'},
                        'guid'         => $fo->{'guid'}
                     ]
                  )) {
                     throw new Exception('Error inserting forms fields into glpi_plugin_formvalidation_forms ' . $DB->error());
                  } else {
                     $lastIdForms = $DB->insertId();
                  }
               }
               //insert fields
               $fieldstoUpdate = [];//array();
               foreach ($fo->{'fields'} as $f) {
                  $query = $DB->request('glpi_plugin_formvalidation_fields', ['guid' => $f->{'guid'}]);
                  if (count($query)) {
                     if ($row = $query->next()) {
                        $currentIdFields = $row['id'];
                     }
                     if ($DB->update(
                        'glpi_plugin_formvalidation_fields',
                        [
                           'name'                        => $DB->escape($f->{'name'}),
                           'forms_id'                    => $lastIdForms,//$f->{'forms_id'},
                           'css_selector_value'          => $f->{'css_selector_value'},
                           'css_selector_altvalue'       => $f->{'css_selector_altvalue'},
                           'css_selector_errorsign'      => $f->{'css_selector_errorsign'},
                           'css_selector_mandatorysign'  => $f->{'css_selector_mandatorysign'},
                           'is_active'                   => $f->{'is_active'},
                           'show_mandatory'              => $f->{'show_mandatory'},
                           'show_mandatory_if'           => $DB->escape($f->{'show_mandatory_if'}),
                           'formula'                     => $DB->escape($f->{'formula'}),
                           'comment'                     => $DB->escape($f->{'comment'}),
                           'date_mod'                    => $f->{'date_mod'},
                           'guid'                        => $f->{'guid'}
                        ],
                        [
                           'id'                          => $currentIdFields
                        ]
                     )) {
                        $oldNew[$f->{'id'}] =  $currentIdFields;
                     } else {
                        throw new Exception('Error updating fields fields into glpi_plugin_formvalidation_fields ' . $DB->error());
                     }
                  } else {
                     if ($DB->insert(
                        'glpi_plugin_formvalidation_fields',
                        [
                           'name'                        => $DB->escape($f->{'name'}),
                           'forms_id'                    => $lastIdForms,
                           'css_selector_value'          => $f->{'css_selector_value'},
                           'css_selector_altvalue'       => $f->{'css_selector_altvalue'},
                           'css_selector_errorsign'      => $f->{'css_selector_errorsign'},
                           'css_selector_mandatorysign'  => $f->{'css_selector_mandatorysign'},
                           'is_active'                   => $f->{'is_active'},
                           'show_mandatory'              => $f->{'show_mandatory'},
                           'show_mandatory_if'           => $DB->escape($f->{'show_mandatory_if'}),
                           'formula'                     => $DB->escape($f->{'formula'}),
                           'comment'                     => $DB->escape($f->{'comment'}),
                           'date_mod'                    => $f->{'date_mod'},
                           'guid'                        => $f->{'guid'}
                        ]
                     )) {
                        $lastIdFields = $DB->insertId();
                        $oldNew[$f->{'id'}] =  $lastIdFields;
                     } else {
                        throw new Exception('Error updating fields fields into glpi_plugin_formvalidation_fields ' . $DB->error());
                     }
                  }

                  if (!empty($f->{'formula'}) || !empty($f->{'show_mandatory_if'})) {
                     $fieldstoUpdate[] = $f;
                  }
               }
               foreach ($fieldstoUpdate as $ftu) {
                  if (!empty($ftu->{'formula'})) {
                     if (preg_match_all('/#[0-9]+\b/i', $ftu->{'formula'}, $match)) {
                        $newFormula = $ftu->{'formula'};
                        foreach ($match[0] as $m) {
                           $index = str_replace("#", "", $m);
                           $newFormula = preg_replace( "/#$index+\b/i", "#".$oldNew[$index], $newFormula );
                           $DB->updateOrDie(
                           'glpi_plugin_formvalidation_fields',
                           [
                              'formula' => $DB->escape($newFormula)
                           ],
                           [
                              'guid'     => $ftu->{'guid'}
                           ],
                           "Error"
                           );
                        }
                     }
                  }
                  if (!empty($ftu->{'show_mandatory_if'})) {
                     if (preg_match_all('/#[0-9]+\b/i', $ftu->{'show_mandatory_if'}, $match)) {
                        $newShowMandatory = $ftu->{'show_mandatory_if'};
                        foreach ($match[0] as $m) {
                           $index = str_replace("#", "", $m);
                           $newShowMandatory = preg_replace( "/#$index+\b/i", "#".$oldNew[$index], $newShowMandatory );
                           $DB->updateOrDie(
                              'glpi_plugin_formvalidation_fields',
                              [
                                 'show_mandatory_if' => $DB->escape($newShowMandatory)
                              ],
                              [
                                 'guid'     => $ftu->{'guid'}
                              ],
                              "Error"
                           );
                        }
                     }
                  }
               }
            }
            
            $nbPagesUpdate++;
         }
         $DB->commit();
         return true;
      } catch (Exception $e) {
         $DB->rollback();
         $error = true;
      } finally {
         if ($error) {
            Session::addMessageAfterRedirect(
               sprintf(__('Error: No updates done ! See your log files')),
               true,
               ERROR
            );
         } else {
            if ($cptWarning > 0) {
               Session::addMessageAfterRedirect(
                  sprintf(__('You have created %d page(s) with an existing name but with a different GUID'), $cptWarning),
                  true,
                  WARNING
               );
            } else {
               Session::addMessageAfterRedirect(
                     sprintf(__('%d pages updated !'), $nbPagesUpdate),
                     true,
                     INFO
                  );
            }
         }
      }
   }

}

