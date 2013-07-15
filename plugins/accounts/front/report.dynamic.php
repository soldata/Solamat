<?php
/*
 * @version $Id: report.dynamic.php 17152 2012-01-24 11:22:16Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkCentralAccess();

if (isset($_POST["display_type"])) {
   
   $post = $_POST;
   
   if ($post["display_type"] == PDF_OUTPUT_LANDSCAPE
       || $post["display_type"] == PDF_OUTPUT_PORTRAIT) {

      include (GLPI_ROOT . "/lib/ezpdf/class.ezpdf.php");
   }
   $parm["display_type"]   = $post["display_type"];
   $parm["id"]             = $post["hash_id"];
   $parm["aeskey"]         = $post["aeskey"];
   $parm["item_type"]      = $post["item_type"];
   $parm["export_x"]       = $post["export_x"];
   $parm["export_y"]       = $post["export_y"];
   
   $accounts = array();
   foreach ($post["id"] as $k => $v) {
      $accounts[$k]["id"] = $v;
   }
   foreach ($post["name"] as $k => $v) {
      $accounts[$k]["name"] = $v;
   }
   foreach ($post["entities_id"] as $k => $v) {
      $accounts[$k]["entities_id"] = $v;
   }
   foreach ($post["type"] as $k => $v) {
      $accounts[$k]["type"] = $v;
   }
   foreach ($post["login"] as $k => $v) {
      $accounts[$k]["login"] = $v;
   }
   foreach ($post["password"] as $k => $v) {
      $accounts[$k]["password"] = $v;
   }
   
   PluginAccountsReport::showAccountsList($parm, $accounts);

}

?>