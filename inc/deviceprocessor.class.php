<?php
/*
 * @version $Id: deviceprocessor.class.php 20130 2013-02-04 16:55:15Z moyo $
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

/// Class DeviceProcessor
class DeviceProcessor extends CommonDevice {

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['devices'][9];
      }
      return $LANG['devices'][4];
   }


   function getAdditionalFields() {
      global $LANG;

      return array_merge(parent::getAdditionalFields(),
                         array(array('name'  => 'specif_default',
                                     'label' => $LANG['device_ram'][1]." ".$LANG['devices'][24],
                                     'type'  => 'text',
                                     'unit'  => $LANG['setup'][35]),
                               array('name'  => 'frequence',
                                     'label' => $LANG['device_ram'][1],
                                     'type'  => 'text',
                                     'unit'  => $LANG['setup'][35])));
   }


   static function getSpecifityLabel() {
      global $LANG;

      return array('specificity' => $LANG['device_ram'][1]);
   }


   function getSearchOptions() {
      global $LANG;

      $tab = parent::getSearchOptions();

      $tab[11]['table']    = $this->getTable();
      $tab[11]['field']    = 'specif_default';
      $tab[11]['name']     = $LANG['device_ram'][1]." ".$LANG['devices'][24];
      $tab[11]['datatype'] = 'text';

      $tab[12]['table']    = $this->getTable();
      $tab[12]['field']    = 'frequence';
      $tab[12]['name']     = $LANG['device_ram'][1];
      $tab[12]['datatype'] = 'text';

      return $tab;
   }


   function prepareInputForAdd($input) {

      if (isset($input['frequence']) && !is_numeric($input['frequence'])) {
         $input['frequence'] = 0;
      }
      return $input;
   }


   /**
    * return the display data for a specific device
    *
    * @return array
   **/
   function getFormData() {
      global $LANG;

      $data['label'] = $data['value'] = array();

      // Specificity
      $data['label'][] = $LANG['device_ram'][1];
      $data['size']    = 10;

      return $data;
   }

}

?>