<?php
/*
 * @version $Id: HEADER 15930 2013-02-07 09:47:55Z tsmr $
 -------------------------------------------------------------------------
 Positions plugin for GLPI
 Copyright (C) 2003-2011 by the Positions Development Team.

 https://forge.indepnet.net/projects/positions
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Positions.

 Positions is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Positions is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Positions. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

if (isset ($_POST['valid'])
      && (isset($_POST['locations_id']) || isset($_POST['locations_idParent']) )) {
         
   $x1            = $_POST['x1'];
   $y1            = $_POST['y1'];
   $x2            = $_POST['x2'];
   $y2            = $_POST['y2'];
   $width         = $_POST['w'];
   $height        = $_POST['h'];
   if ($_POST['test'] == "newLocation") {
      $name       = $_POST['name'];
   } else if ($_POST['test'] == "existLocation") {
      $loc = new Location();
      $loc->getFromDB($_POST['locations_id']);
      $name = $loc->fields['name'];
   }

   $document_id   = $_POST['document_id'];
   $extension     = $_POST['extension'];

   if (isset($_POST['locations_id']) ){
      $locations_id  = $_POST['locations_id'];
   }
   else {
      $locations_id  = $_POST['locations_idParent'];
   }
   
   $locations_idParent  = $_POST['locations_idParent'];
   $entities_id  = $_POST['entities_id'];
   $test  = $_POST['test'];

   $Doc = new Document();
   $Doc->getFromDB($document_id)  ;
   $path = GLPI_DOC_DIR."/".$Doc->fields["filepath"];

   if($extension=='PNG'){
      $srcImg  = imagecreatefrompng($path);
      $newImg  = imagecreatetruecolor($width, $height);

      imagecopyresampled($newImg, $srcImg, 0, 0, $x1, $y1, $width, $height, $width, $height);

      imagepng($newImg, GLPI_DOC_DIR."/_uploads/".$document_id.$name.'.png');
   }
   else if($extension=='JPG' || $extension=='JPEG'){
      $srcImg  = imagecreatefromjpeg($path);
      $newImg  = imagecreatetruecolor($width, $height);

      imagecopyresampled($newImg, $srcImg, 0, 0, $x1, $y1, $width, $height, $width, $height);

      imagejpeg($newImg, GLPI_DOC_DIR."/_uploads/".$document_id.$name.'.jpg');
   }

   else if($extension=='GIF'){
      $srcImg  = imagecreatefromgif($path);
      $newImg  = imagecreatetruecolor($width, $height);

      imagecopyresampled($newImg, $srcImg, 0, 0, $x1, $y1, $width, $height, $width, $height);

      imagegif($newImg, GLPI_DOC_DIR."/_uploads/".$document_id.$name.'.gif');
   }

   Html::header($LANG['plugin_positions']['title'][1],'',"plugins","positions");

   //on test si l'utilisateur à cocher la case pour continuer l'ajout de sous lieu
   //Si oui : on redirige vers la carte PNG
   if(!empty($_POST['continueAdd']))
   {
      $checked = $_POST['continueAdd'];
   }
   else{
      $checked = 'off';
   }

   //Si non : on envoie l'utilisateur vers la carte pour ajouter du matériel
   $options = array("document_id"        => $document_id,
                     "name"               => $name,
                     "locations_idParent" => $locations_idParent,
                     "locations_id"       => $locations_id,
                     "entities_id"        => $entities_id,
                     "test"               => $test,
                     "extension"          => strtolower($extension),
                     "checked"            => $checked);
   PluginPositionsPosition::showFormResCreateLocation($options);

   Html::footer();
}

?>