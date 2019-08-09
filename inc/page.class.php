<?php

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
      // global $LANG;

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
         'linkfield'          => 'itemtypes_id',
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
      global $LANG;

      if ($nb>1) {
         return __('Pages', 'formvalidation');
      }
      return __('Page', 'formvalidation');
   }

   function defineTabs($options = []) {

      //        $ong = array('empty' => $this->getTypeName(1));
      $ong = [];
      $this->addDefaultFormTab($ong);
      //$this->addStandardTab(__CLASS__, $ong, $options);

      $this->addStandardTab('PluginFormvalidationForm', $ong, $options);
      //$this->addStandardTab('PluginProcessmakerProcess_Profile', $ong, $options);

      return $ong;
   }

   function showForm ($ID, $options = ['candel'=>false]) {
      //global $DB;

      if ($ID > 0) {
         $this->check($ID, READ);
      }

      $canedit = $this->can($ID, UPDATE);
      $options['canedit'] = $canedit;

      $this->initForm($ID, $options);

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Name")."&nbsp;:</td><td>";
      //Html::autocompletionTextField($this, "name");
      echo "<input type='text' size='50' maxlength=250 name='name' ".
      " value=\"".htmlentities($this->fields["name"], ENT_QUOTES)."\">";
      echo "</td>";
      echo "<td rowspan='3' class='middle right'>".__("Comments")."&nbsp;:</td>";
      echo "<td class='center middle' rowspan='3'><textarea cols='45' rows='5' name='comment' >".
      htmlentities($this->fields["comment"], ENT_QUOTES)."</textarea></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("Active")."&nbsp;:</td><td>";
      Html::showCheckbox(['name'           => 'is_active',
                                  'checked'        => $this->fields["is_active"]
                                  ]);
      echo "</td></tr>";

      if (version_compare(GLPI_VERSION, '9.1', 'lt')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td >".__("Child entities")."&nbsp;:</td><td>";
         Html::showCheckbox(['name'           => 'is_recursive',
                                      'checked'        => $this->fields["is_recursive"]
                                      ]);
         echo "</td></tr>";
      }

      echo "<tr>";
      echo "<td>".__("Associated item type")." : </td>";
      echo "<td>";
      if ($ID > 0) {
         echo Dropdown::getDropdownName('glpi_plugin_formvalidation_itemtypes', $this->fields["itemtypes_id"]);
      } else {
         Dropdown::show('PluginFormvalidationItemtype', [ 'name' => 'itemtypes_id' ]); //, array( 'name' => 'name')
      }
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options );
      //$this->addDivForTabs();

   }

   /**
    * Actions done after the PURGE of the item in the database
    *
    * @return nothing
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
      //$query = "SELECT * FROM ".$frm->getTable()." WHERE pages_id=".$this->getID();
      //foreach ($DB->request($query) as $frmkey => $row) {
      foreach ($res as $frmkey => $row) {
         $frm->delete($row, 1);
      }

   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'exportPage':
            echo Html::submit(__('Export'), ['name' => 'massiveaction'])."</span>";

            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   function getForms() {
      $forms = new PluginFormvalidationForm();
      return $forms->find("pages_id='".$this->fields['id']."'");
   }

   function getItemtypes() {
      $itemType = new PluginFormvalidationItemtype();
      $itemType_id = $this->fields['itemtypes_id'];
      $datas = $itemType->find("id = '".$this->fields['itemtypes_id']."'");
      $guid = $datas[$itemType_id]['guid'];
      $datas[$guid] = $datas[$itemType_id];
      unset($datas[$itemType_id]);
      return $datas;
      //return $itemType->find("id = '".$this->fields['itemtypes_id']."'");
   }

   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
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
               $name .= "_".$datas['name'];
               foreach ($datas["form"] as $key => $form) {
                  $datas["form"][$form['guid']] = $form;
                  $fields = new PluginFormvalidationField();
                  $form_id = $form["id"];
                  $f = $fields->find("forms_id=$form_id");
                  $datas["form"][$form['guid']]["fields"] = $f;
                  unset($datas["form"][$key]);
               }
               array_push($json, $datas);
            }
            $json = json_encode($json);
            $filename = 'export_page'.$name.'.json';
            $export = '../files/_tmp/'.$filename;
            $fichier = fopen($export, 'w+');
            fwrite($fichier, $json);
            fclose($fichier);
      }
      $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_OK);
      $ma->setRedirect($CFG_GLPI['root_doc']."/plugins/formvalidation/front/formvalidation.backup.php?action=download&filename=$filename&itemtype=".$item->getType());
   }
   function post_addItem() {
      global $DB, $CFG_GLPI;
      $id = $this->fields['id'];
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/page/".time()."/".rand()."/".$id;
      $DB->updateOrDie(
         'glpi_plugin_formvalidation_pages',
         [
            'guid' => md5($guid)
         ],
         [
            'id'  => $id
         ]
      );
   }

   //Display import button on page.php
   static function titleBackup() {
      global $CFG_GLPI;
      $buttons = [];
      $title   = "";

      //$buttons["{$CFG_GLPI["root_doc"]}/plugins/formvalidation/front/formvalidation.backup.php?action=import"] = _x('button', 'Import');
      //$buttons["{$CFG_GLPI["root_doc"]}/front/formvalidation.backup.php?action=export"] = _x('button', 'Export');
      $val =  _x('button', 'Import');

      echo "<div class='center'><table class='tab_glpi'><tr>";
      echo "<td><i class='fa fa-save fa-3x'></i></td>";
      //foreach ($buttons as $key => $val) {
      //   echo "<td><a class='vsubmit' href='".$key."'>".$val."</a></td>";
      //}
      echo "<td><a class='vsubmit' href='".$CFG_GLPI["root_doc"]."/plugins/formvalidation/front/formvalidation.backup.php?action=import'>".$val."</a></td>";
      echo "</tr></table></div>";
   }

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

   static function processImportPage() {
      global $DB;
      $nbPagesUpdate = 0;
      $oldNew = [];//array();
      if (!isset($_FILES["json_file"]) || ($_FILES["json_file"]["size"] == 0)) {
         return false;
      }//else{
      $fileInfo = new SplFileInfo($_FILES["json_file"]['name']);
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
                     $lastIdItemTypes = $DB->insert_id();
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
                     'id'           => $lastIdPages//$p->{'id'}
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
                     'pages_id'  => $lastIdPages//$p->{'id'}
                  ],
                  'Error updating pages field into glpi_plugin_formvalidation_pages ' . $DB->error()
               );
            } else {
               if (!$DB->insert(
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
                  'Error inserting pages field into glpi_plugin_formvalidation_pages ' . $DB->error()
               )) {
                  throw new Exception('Error inserting pages field into glpi_plugin_formvalidation_pages ' . $DB->error());
               } else {
                  $lastIdPages = $DB->insert_id();
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
                        'formula'      => $fo->{'formula'},
                        'comment'      => $DB->escape($fo->{'comment'}),
                        'date_mod'     => $fo->{'date_mod'},
                        'guid'         => $fo->{'guid'}
                     ],
                     [
                        'id'           => $lastIdForms//$fo->{'id'}
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
                        'forms_id' => $lastIdForms //$fo->{'id'}
                     ],
                     'Error inserting pages field into glpi_plugin_formvalidation_pages ' . $DB->error()
                  );
                  //$lastIdForms = $fo->{'id'};
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
                        'formula'      => $fo->{'formula'},
                        'comment'      => $DB->escape($fo->{'comment'}),
                        'date_mod'     => $fo->{'date_mod'},
                        'guid'         => $fo->{'guid'}
                     ]
                  )) {
                     throw new Exception('Error inserting forms fields into glpi_plugin_formvalidation_forms ' . $DB->error());
                  } else {
                     $lastIdForms = $DB->insert_id();
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
                           'show_mandatory_if'           => $f->{'show_mandatory_if'},
                           'formula'                     => $f->{'formula'},
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
                     //$oldNew[$f->{'id'}] =  $f_id;
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
                           'show_mandatory_if'           => $f->{'show_mandatory_if'},
                           'formula'                     => $f->{'formula'},
                           'comment'                     => $DB->escape($f->{'comment'}),
                           'date_mod'                    => $f->{'date_mod'},
                           'guid'                        => $f->{'guid'}
                        ]
                     )) {
                        $lastIdFields = $DB->insert_id();
                        //$oldNew[$f->{'id'}] = array('old'=>$f->{'id'}, 'new' => $lastIdFields);
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
                              'formula' => $newFormula
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
                           //$newShowMandatory = $ftu->{'show_mandatory_if'};
                           $newShowMandatory = preg_replace( "/#$index+\b/i", "#".$oldNew[$index], $newShowMandatory );
                           $DB->updateOrDie(
                              'glpi_plugin_formvalidation_fields',
                              [
                                 'show_mandatory_if' => $newShowMandatory
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
            //}
            //}
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
            Session::addMessageAfterRedirect(
                  sprintf(__('%d pages updated !'), $nbPagesUpdate),
                  true,
                  INFO
               );
         }
      }
   }

}

