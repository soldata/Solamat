<?php
/*
 * @version $Id: checklist.form.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if(!isset($_GET["id"])) $_GET["id"] = "";
if(!isset($_GET["plugin_resources_contracttypes_id"])) $_GET["plugin_resources_contracttypes_id"] = 0;
if(!isset($_GET["checklist_type"])) $_GET["checklist_type"] = 0;
if(!isset($_GET["plugin_resources_resources_id"])) $_GET["plugin_resources_resources_id"] = -1;

$checklist=new PluginResourcesChecklist();

//from central
//update checklist
if (isset($_POST["add"])) {

	$checklist->add($_POST);
	Html::back();

}else if (isset($_POST["update"])) {
	
	if($checklist->canCreate()) {
		$checklist->update($_POST);
	}
	Html::back();
	
} else {

	$checklist->checkGlobal("r");
	
	Html::header($LANG['plugin_resources']['title'][1],'',"plugins","resources");
   
   $options = array('checklist_type' => $_GET["checklist_type"],
                     'plugin_resources_contracttypes_id' => $_GET["plugin_resources_contracttypes_id"],
                     'plugin_resources_resources_id' => $_GET["plugin_resources_resources_id"]);
	$checklist->showForm($_GET["id"], $options);
	
	Html::footer();
}

?>