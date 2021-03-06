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

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$pos=new PluginPositionsPosition();

if (isset($_POST["add"])) {
   $test= explode(";", $_POST['items_id']);
   if (isset($test[0]) && isset($test[1])
    && !empty($test[1])) {
      $_POST['items_id']= $test[1];
      $_POST['itemtype']= $test[0];
      $pos->check(-1,'w',$_POST);
      $pos->add($_POST);
   } else {
      $pos->check(-1,'w',$_POST);
      $pos->add($_POST);
   }

} else if (isset($_POST["additem"])) {

	$pos->check(-1,'w',$_POST);
   $pos->add($_POST);

	Html::back();

} else if (isset($_POST["update"])) {

   if (isset($_POST["multi"])) {
     
      $data = explode(",", $_POST["multi"]);
      for ($i=0; $i<count($data); $i=$i+3) {
         if (isset($data[$i+1]) && isset($data[$i+2])) {
            $input= array( 'id' => $data[$i],
                           'x_coordinates'=>$data[$i+1],
                           'y_coordinates'=>$data[$i+2],
                           'locations_id'=> $_POST["locations_id"]);
            $pos->check($input['id'],'w');
            $pos->update($input);
         }
      }
      
      if (isset($_POST["referrer"]) && $_POST["referrer"] > 0) {
         Html::back();
      } else {
         Html::redirect($CFG_GLPI["root_doc"].
         "/plugins/positions/front/map.php?locations_id=".$_POST["locations_id"]);
      } 
         
   } else {
	
      $pos->check($_POST['id'],'w');
      $pos->update($_POST);
      if (isset($_POST["referrer"]) && $_POST["referrer"] > 0) {
         Html::back();
      } else {
         Html::redirect($CFG_GLPI["root_doc"].
         "/plugins/positions/front/position.form.php?id=".$_POST['id']);
      }
	}
	
} else if (isset($_POST["delete"])) {

	$pos->check($_POST['id'],'w');
   $pos->delete($_POST);
	$pos->redirectToList();

} else if (isset($_POST["restore"])) {

	$pos->check($_POST['id'],'w');
   $pos->restore($_POST);
	$pos->redirectToList();
	
} else if (isset($_POST["purge"])) {

	$pos->check($_POST['id'],'w');
   $pos->delete($_POST,1);
	$pos->redirectToList();

//from items
}  else if (isset($_POST["delete_item"])) {

	$pos->check($_POST['id'],'w');
   $pos->delete($_POST,1);
   Html::back();

//from coordinates ou map	
} else if (isset($_POST["deletepos"])) {

	$pos->check($_POST['id'],'w');
   $pos->delete($_POST,1);
   Html::redirect($CFG_GLPI["root_doc"].
   "/plugins/positions/front/map.php?locations_id=".$_POST["locations_id"]);
	
} else if (isset($_POST["addLocation"])) {

   $pos->checkGlobal("r");

   Html::header($LANG['plugin_positions']['title'][1],'',"plugins","positions");
   $map = PluginPositionsPosition::getDocument($_POST["locations_id"]);
   $options = array("document_id"  => $map,
                     "locations_id" => $_POST["locations_id"]);
   PluginPositionsPosition::showMapCreateLocation($options);
   Html::footer();

}
else {

	$pos->checkGlobal("r");
	
	Html::header($LANG['plugin_positions']['title'][1],'',"plugins","positions");

	$pos->showForm($_GET["id"]);

	Html::footer();
}

?>