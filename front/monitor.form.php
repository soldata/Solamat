<?php
/*
 * @version $Id: monitor.form.php 20130 2013-02-04 16:55:15Z moyo $
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

Session::checkRight("monitor", "r");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["sort"])) {
   $_GET["sort"] = "";
}
if (!isset($_GET["order"])) {
   $_GET["order"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$monitor = new Monitor();

if (isset($_POST["add"])) {
   $monitor->check(-1,'w',$_POST);

   $newID = $monitor->add($_POST);
   Event::log($newID, "monitors", 4, "inventory",
              $_SESSION["glpiname"]." ".$LANG['log'][20]." ".$_POST["name"].".");
   Html::back();

} else if (isset($_POST["delete"])) {
   $monitor->check($_POST["id"],'d');
   $monitor->delete($_POST);

   Event::log($_POST["id"], "monitors", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][22]);
   $monitor->redirectToList();

} else if (isset($_POST["restore"])) {
   $monitor->check($_POST["id"],'d');

   $monitor->restore($_POST);
   Event::log($_POST["id"], "monitors", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][23]);
   $monitor->redirectToList();

} else if (isset($_REQUEST["purge"])) {
   $monitor->check($_REQUEST["id"],'d');

   $monitor->delete($_REQUEST,1);
   Event::log($_REQUEST["id"], "monitors", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][24]);
   $monitor->redirectToList();

} else if (isset($_POST["update"])) {
   $monitor->check($_POST["id"],'w');

   $monitor->update($_POST);
   Event::log($_POST["id"], "monitors", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][21]);
   Html::back();

} else if (isset($_GET["unglobalize"])) {
   $monitor->check($_GET["id"],'w');

   Computer_Item::unglobalizeItem($monitor);
   Event::log($_GET["id"], "monitors", 4, "inventory", $_SESSION["glpiname"]." ".$LANG['log'][60]);
   Html::redirect($CFG_GLPI["root_doc"]."/front/monitor.form.php?id=".$_GET["id"]);

} else {
   Html::header($LANG['Menu'][3],$_SERVER['PHP_SELF'],"inventory","monitor");

   $monitor->showForm($_GET["id"], array('withtemplate' => $_GET["withtemplate"]));

   Html::footer();
}
?>