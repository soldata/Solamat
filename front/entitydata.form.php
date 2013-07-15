<?php
/*
 * @version $Id: entitydata.form.php 20130 2013-02-04 16:55:15Z moyo $
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

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkCentralAccess();

$data = new EntityData();

if (isset($_POST["add"])) {
   $data->check(-1,'w',$_POST);

   if ($data->add($_POST)) {
      Event::log($_POST["entities_id"], "entity", 4, "setup", $_SESSION["glpiname"]." ".$LANG['log'][21]);
   }
   Html::back();

} else if (isset($_POST["update"])) {
   $data->check($_POST["entities_id"],'w');

   if ($data->update($_POST)) {
      Event::log($_POST["entities_id"], "entity", 4, "setup", $_SESSION["glpiname"]." ".$LANG['log'][21]);
   }
   Html::back();

}
Html::displayErrorAndDie("lost");
?>