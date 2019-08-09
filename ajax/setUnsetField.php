<?php
/*
 * -------------------------------------------------------------------------
Form Validation plugin
Copyright (C) 2016 by Raynet SAS a company of A.Raymond Network.

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

$AJAX_INCLUDE = 1;
include ('../../../inc/includes.php');

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$ret = false;
if (isset($_REQUEST['action'])) {

   $fields = new PluginFormvalidationField();
   switch ($_REQUEST['action']) {
      case 'set' :
         if ($_REQUEST['fieldindex'] > 0) {
            if ($fields->update([
               'id'                          => $_REQUEST['fieldindex'],
               'is_active'                   => 1,
               'show_mandatory'              => 1,
               'css_selector_errorsign'      => $DB->escape(html_entity_decode( $_REQUEST['css_selector_errorsign'])),
               'css_selector_mandatorysign'  => $DB->escape(html_entity_decode( $_REQUEST['css_selector_mandatorysign']))
               ])) {
               $ret = true;
            }
         } else {
            // we may need to add a form
            if ($_REQUEST['formindex'] == 0) {
               // add form
               $form = new PluginFormvalidationForm();
               // extract default form name from form_css_selector
               $name = '';
               $action = '';
               $matches = [];
               $regex = "/form(\\[name=\\\"(?'name'\\w*)\\\"])?\\[action=\\\"(?'action'[\\w\\/\\.]*)\\\"]/";
               if (preg_match( $regex, str_replace("\\", "", html_entity_decode($_REQUEST['form_css_selector'])), $matches )) {
                  if (isset($matches['name'] )) {
                     $name = $matches['name'];
                  }
                  $action =  $matches['action'];
               }
               if ($form->add(
                  [
                     'name'            => $DB->escape("$name($action)"),
                     'pages_id'        => $_REQUEST['pages_id'],
                     'css_selector'    => $DB->escape(html_entity_decode($_REQUEST['form_css_selector'])),
                     'is_active'       => 1,
                     'is_createitem'   => $_REQUEST['is_createitem']
                  ]
                  )) {
                  $_REQUEST['formindex'] = $form->fields['id'];
               } else {
                  $ret = false;
               }
            }
            if ($fields->add(
               [
                  'name'                        => $_REQUEST['name'],
                  'forms_id'                    => $_REQUEST['formindex'],
                  'css_selector_value'          => $DB->escape(html_entity_decode( $_REQUEST['css_selector_value'])),
                  'css_selector_errorsign'      => $DB->escape(html_entity_decode( $_REQUEST['css_selector_errorsign'])),
                  'css_selector_mandatorysign'  => $DB->escape(html_entity_decode( $_REQUEST['css_selector_mandatorysign'])),
                  'is_active'                   => 1
               ]
            )) {
               $_REQUEST['fieldindex'] = $fields->fields['id'];
               $ret = [ 'forms' => [ ] ]; // by default
               foreach ($DB->request('glpi_plugin_formvalidation_forms', ['id' => $_REQUEST['formindex']]) as $form) {
                  $ret['forms_id'] = $form['id'];
                  $ret['forms'][$form['id']]=Toolbox::stripslashes_deep( $form );
                  $ret['forms'][$form['id']]['fields'] = []; // needed in case this form has no fields
                  foreach ($DB->request('glpi_plugin_formvalidation_fields', ['id' => $_REQUEST['fieldindex']]) as $field) {
                     $ret['fields_id']=$field['id'];
                     $ret['forms'][$form['id']]['fields'][$field['id']] = Toolbox::stripslashes_deep( $field );
                  }
               }
            }
         }
         break;
      case 'unset' :
         if ($fields->update(['id'=>$_REQUEST['fieldindex'], 'is_active' => 0])) {
            $ret = true;
         }
         break;
      case 'hidemandatorysign':
         if ($fields->update(['id'=>$_REQUEST['fieldindex'], 'show_mandatory' => 0])) {
            $ret = true;
         }
         break;
   }
}

echo json_encode( $ret );

