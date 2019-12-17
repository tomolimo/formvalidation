<?php
include ("../../../inc/includes.php");

header("Content-type: application/javascript");
Html::header_nocache();

$plug = new Plugin;
if (!$plug->isActivated( 'formvalidation' )) {
   return '';
}

$dateFormat = Toolbox::phpDateFormat();
$file = file_get_contents("./helpers_function.js.tpl");
$file = str_replace('$dateFormat', $dateFormat, $file);

echo $file;