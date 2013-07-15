<?php
/*
 * @version $Id: checklistactions.php 480 2012-11-09 tsmr $
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
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!defined('GLPI_ROOT')){
   die("Can not acces directly to this file");
}

if (isset($_POST["action"])) {
	switch ($_POST["action"]) {
		case "update_checklist":
			echo "&nbsp;<input type='submit' name='update_checklist' class='submit' value='".$LANG['buttons'][2]."'></td>";
         break;
      case "delete_checklist":
			echo "&nbsp;<input type='submit' name='delete_checklist' class='submit' value='".$LANG['buttons'][2]."'></td>";
         break;
      case "add_task":
			echo "&nbsp;".$LANG['job'][5]."&nbsp;";
			User::dropdown(array('name' => "users_id",'right' => 'interface'));
			echo "&nbsp;<input type='submit' name='add_task' class='submit' value='".$LANG['buttons'][2]."'></td>";
         break;
      case "add_ticket":
			echo "&nbsp;<input type='submit' name='add_ticket' class='submit' value='".$LANG['buttons'][2]."'></td>";
         break;
	}
}

?>