<?php
/*
 * @version $Id: ocsng.clean.php 20130 2013-02-04 16:55:15Z moyo $
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


Html::header($LANG['ocsng'][0],$_SERVER['PHP_SELF'], "utils", "ocsng", "clean");

if (!isset($_POST["clean_ok"])) {
   Session::checkRight("clean_ocsng", "r");

   if (!isset($_GET['check'])) {
      $_GET['check'] = 'all';
   }
   if (!isset($_GET['start'])) {
      $_GET['start'] = 0;
   }
   OcsServer::manageDeleted($_SESSION["ocsservers_id"]);
   OcsServer::showComputersToClean($_SESSION["ocsservers_id"], $_GET['check'], $_GET['start']);

} else {
   Session::checkRight("clean_ocsng", "w");
   if (count($_POST['toclean']) >0) {
      OcsServer::cleanLinksFromList($_SESSION["ocsservers_id"], $_POST['toclean']);
      echo "<div class='center b'>".$LANG['ocsng'][3]." - ".$LANG['log'][45]."<br>";
      Html::displayBackLink();
      echo "</div>";
   }
}

Html::footer();
?>