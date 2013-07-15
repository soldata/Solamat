<?php
/*
 * @version $Id: softwareversion.form.php 20130 2013-02-04 16:55:15Z moyo $
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

Session::checkRight("software", "r");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["softwares_id"])) {
   $_GET["softwares_id"] = "";
}

$version = new SoftwareVersion();

if (isset($_POST["add"])) {
    $version->check(-1,'w',$_POST);

   if ($newID = $version->add($_POST)) {
      Event::log($_POST['softwares_id'], "software", 4, "inventory",
                 $_SESSION["glpiname"]." ".$LANG['log'][82]." $newID.");
      Html::redirect($CFG_GLPI["root_doc"]."/front/software.form.php?id=".
                  $version->fields['softwares_id']);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $version->check($_POST['id'],'w');

   $version->delete($_POST);
   Event::log($version->fields['softwares_id'], "software", 4, "inventory",
              $_SESSION["glpiname"]." ".$LANG['log'][84]." ".$_POST["id"]);
   $version->redirectToList();

} else if (isset($_POST["update"])) {
   $version->check($_POST['id'],'w');

   $version->update($_POST);
   Event::log($version->fields['softwares_id'], "software", 4, "inventory",
              $_SESSION["glpiname"]." ".$LANG['log'][83]." ".$_POST["id"]);
   Html::back();

} else {
   Html::header($LANG['Menu'][4],$_SERVER['PHP_SELF'],"inventory","software");
   $version->showForm($_GET["id"], array('softwares_id' => $_GET["softwares_id"]));
   Html::footer();
}
?>