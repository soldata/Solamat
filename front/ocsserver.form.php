<?php

/*
 * @version $Id: ocsserver.form.php 20130 2013-02-04 16:55:15Z moyo $
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
$ocs = new OcsServer();

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

Html::header($LANG['ocsng'][0], $_SERVER['PHP_SELF'], "config","ocsng");

//Delete template or server
if (isset($_POST["delete"])) {
   $ocs->delete($_POST);
   $ocs->redirectToList();

//Update server
} else if (isset($_POST["update"])) {
   $ocs->update($_POST);
   Html::back();

//Update server
} else if (isset($_POST["update_server"])) {
   $ocs->update($_POST);
   Html::back();

//Add new server
} else if (isset($_POST["add"])) {
   $newid = $ocs->add($_POST);
   Html::back();

//Other
} else {
   $ocs->showForm($_GET["id"]);
}

Html::footer();
?>