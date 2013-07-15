<?php
/*
 * @version $Id: dropdownValuesBlacklist.php 20130 2013-02-04 16:55:15Z moyo $
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

if (strpos($_SERVER['PHP_SELF'], "dropdownValuesBlacklist.php")) {
   define('GLPI_ROOT','..');
   include (GLPI_ROOT."/inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkRight("config", "w");

if (isset($_POST['itemtype']) && isset($_POST['id_field'])) {
   $blacklist = new Fieldblacklist();
   if (isset($_POST['id']) && $_POST['id'] > 0) {
      $blacklist->getFromDB($_POST['id']);
   } else {
      $blacklist->getEmpty();
      $blacklist->fields['field']    = $_POST['id_field'];
      $blacklist->fields['itemtype'] = $_POST['itemtype'];
   }
   $blacklist->selectValues();
}
?>