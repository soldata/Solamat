<?php
/*
 * @version $Id: devicecontrol.class.php 20130 2013-02-04 16:55:15Z moyo $
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

/// Class DeviceControl
class DeviceControl extends CommonDevice {

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['devices'][20];
      }
      return $LANG['devices'][17];
   }


   function getAdditionalFields() {
      global $LANG;

      return array_merge(parent::getAdditionalFields(),
                         array(array('name'  => 'is_raid',
                                     'label' => $LANG['device_control'][0],
                                     'type'  => 'bool'),
                               array('name'  => 'interfacetypes_id',
                                     'label' => $LANG['common'][65],
                                     'type'  => 'dropdownValue')));
   }


   function getSearchOptions() {
      global $LANG;

      $tab = parent::getSearchOptions();

      $tab[12]['table']    = $this->getTable();
      $tab[12]['field']    = 'is_raid';
      $tab[12]['name']     = $LANG['device_control'][0];
      $tab[12]['datatype'] = 'bool';

      $tab[14]['table']    = 'glpi_interfacetypes';
      $tab[14]['field']    = 'name';
      $tab[14]['name']     = $LANG['common'][65];

      return $tab;
   }


   /**
    * return the display data for a specific device
    *
    * @return array
   **/
   function getFormData() {
      global $LANG;

      $data['label'] = $data['value'] = array();

      if ($this->fields["is_raid"]) {
         $data['label'][] = $LANG['device_control'][0];
         $data['value'][] = Dropdown::getYesNo($this->fields["is_raid"]);
      }
      if ($this->fields["interfacetypes_id"]) {
         $data['label'][] = $LANG['common'][65];
         $data['value'][] = Dropdown::getDropdownName("glpi_interfacetypes",
                                                      $this->fields["interfacetypes_id"]);
      }

      return $data;
   }

}

?>