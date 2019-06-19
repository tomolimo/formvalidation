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
$LANG['plugin_formvalidation']['alert']="Form Validation";
$LANG['plugin_formvalidation']['mandatorytitle']="Mandatory fields or wrong values:";
$LANG['plugin_formvalidation']['formulaerror']="Contact your admin: an evaluation error occured in formula: ";

// Form Validation Messages
// Here you may add localized form validation messages
// with the following syntax:
// $LANG['plugin_formvalidation']['forms'][page_id][form_id][field_id]=your_text;
// where page_id is the id of the page
// where form_id is the id of the form
// where field_id is the id of the field
// where your_text is the validation error message you want to show when validation formula is false for the field with field_id


$LANG['plugin_formvalidation']['forms'][4][4][10]="Description must be at least 10 chars and 5 words.";
$LANG['plugin_formvalidation']['forms'][4][5][12]="Description must be at least 10 chars and 3 words.";
$LANG['plugin_formvalidation']['forms'][4][6][14]="Description must be at least 10 chars and 5 words.";

$LANG['plugin_formvalidation']['forms'][10][25][126]="File does not exist !";


