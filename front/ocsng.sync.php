<?php
/*
 * @version $Id: ocsng.sync.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkRight("ocsng", "w");

Html::header($LANG['ocsng'][0], $_SERVER['PHP_SELF'], "utils", "ocsng", "sync");

$display_list = true;

if (isset($_SESSION["ocs_update"]['computers'])) {
   if ($count = count($_SESSION["ocs_update"]['computers'])) {
      $percent = min(100,
                     round(100*($_SESSION["ocs_update_count"]-$count)/$_SESSION["ocs_update_count"],
                           0));


      $key    = array_pop($_SESSION["ocs_update"]['computers']);
      $action = OcsServer::updateComputer($key, $_SESSION["ocsservers_id"], 2);
      OcsServer::manageImportStatistics($_SESSION["ocs_update"]['statistics'], $action['status']);
      OcsServer::showStatistics($_SESSION["ocs_update"]['statistics']);
      Html::displayProgressBar(400, $percent);

      Html::redirect($_SERVER['PHP_SELF']);

   } else {
      OcsServer::showStatistics($_SESSION["ocs_update"]['statistics'], true);
      unset($_SESSION["ocs_update"]);
      $display_list = false;
      echo "<div class='center b'><br>";
      echo "<a href='".$_SERVER['PHP_SELF']."'>".$LANG['buttons'][13]."</a></div>";
   }
}

if (!isset($_POST["update_ok"])) {
   if (!isset($_GET['check'])) {
      $_GET['check'] = 'all';
   }
   if (!isset($_GET['start'])) {
      $_GET['start'] = 0;
   }
   OcsServer::manageDeleted($_SESSION["ocsservers_id"]);
   if ($display_list) {
      OcsServer::showComputersToUpdate($_SESSION["ocsservers_id"], $_GET['check'], $_GET['start']);
   }

} else {
   if (count($_POST['toupdate']) >0) {
      $_SESSION["ocs_update_count"] = 0;

      foreach ($_POST['toupdate'] as $key => $val) {
         if ($val == "on") {
            $_SESSION["ocs_update"]['computers'][] = $key;
            $_SESSION["ocs_update_count"]++;
         }
      }
   }
   Html::redirect($_SERVER['PHP_SELF']);
}

Html::footer();
?>