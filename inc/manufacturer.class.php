<?php
/*
 * @version $Id: manufacturer.class.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Class Manufacturer
class Manufacturer extends CommonDropdown {

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['dropdown'][2];
      }
      return $LANG['common'][5];
   }

   static function processName($old_name) {

      if ($old_name == null) {
         return $old_name;
      }

      $rulecollection = new RuleDictionnaryManufacturerCollection();
      $output=array();
      $output = $rulecollection->processAllRules(array("name"=>addslashes($old_name)),$output,array());
      if (isset($output["name"])) {
         return $output["name"];
      }
      return $old_name;
   }


   function cleanDBonPurge() {

      // Rules use manufacturer intread of manufacturers_id
      Rule::cleanForItemAction($this, 'manufacturer');
   }
}

?>
