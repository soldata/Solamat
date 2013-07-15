<?php
/*
 * @version $Id: dropdownMassiveActionPorts.php 20130 2013-02-04 16:55:15Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
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

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT','..');
include (GLPI_ROOT."/inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkRight("networking", "w");

if (isset($_POST["action"])) {
   echo "<input type='hidden' name='action' value='".$_POST["action"]."'>";

   switch($_POST["action"]) {
      case "delete" :
         echo "&nbsp;<input type='submit' name='delete_several' class='submit' value='".
               $LANG['buttons'][2]."'>";
         break;

      case "assign_vlan" :
         Dropdown::show('Vlan');
         echo "&nbsp;<input type='submit' name='assign_vlan_several' class='submit' value='".
               $LANG['buttons'][2]."'>";
         break;

      case "unassign_vlan" :
         Dropdown::show('Vlan');
         echo "&nbsp;<input type='submit' name='unassign_vlan_several' class='submit' value='".
               $LANG['buttons'][2]."'>";
         break;

      case "move" :
         Dropdown::show('NetworkEquipment', array('name' => 'items_id'));
         echo "&nbsp;<input type='submit' name='move' class='submit' value=\"".
                      $LANG['buttons'][2]."\">";
         break;
   }
}

?>
