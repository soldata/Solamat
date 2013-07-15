<?php

/*
 * @version $Id: computer.class.php 322 2012-04-06 17:03:33Z remi $
 -------------------------------------------------------------------------
 pdf - Export to PDF plugin for GLPI
 Copyright (C) 2003-2011 by the pdf Development Team.

 https://forge.indepnet.net/projects/pdf
 -------------------------------------------------------------------------

 LICENSE

 This file is part of pdf.

 pdf is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 pdf is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with pdf. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: Remi Collet
// ----------------------------------------------------------------------

class PluginPdfComputer extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {

      $this->obj = ($obj ? $obj : new Computer());
   }


   function defineAllTabs($options=array()) {

      $onglets = parent::defineAllTabs($options);
      unset($onglets['OcsLink$1']); // TODO add method to print OCS
      unset($onglets['Item_Problem$1']); // TODO add method to print linked Problems
      return $onglets;
   }


   static function pdfMain(PluginPdfSimplePDF $pdf, Computer $computer){
      global $DB, $LANG;

      $ID = $computer->getField('id');

      $pdf->setColumnsSize(50,50);
      $col1 = '<b>'.$LANG['common'][2].' '.$computer->fields['id'].'</b>';
      $col2 = $LANG['common'][26].' : '.Html::convDateTime($computer->fields['date_mod']);
      if(!empty($computer->fields['template_name'])) {
         $col2 .= ' ('.$LANG['common'][13].' : '.$computer->fields['template_name'].')';
      } else if($computer->fields['is_ocs_import']) {
         $col2 .= ' ('.$LANG['ocsng'][7].')';
      }
      $pdf->displayTitle($col1, $col2);

      $pdf->displayLine(
         '<b><i>'.$LANG['common'][16].' :</i></b> '.$computer->fields['name'],
         '<b><i>'.$LANG['state'][0].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_states',$computer->fields['states_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG['common'][15].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_locations', $computer->fields['locations_id'])),
         '<b><i>'.$LANG['common'][17].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_computertypes',
                                                 $computer->fields['computertypes_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG['common'][10].' :</i></b> '.getUserName($computer->fields['users_id_tech']),
         '<b><i>'.$LANG['common'][5].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_manufacturers',
                                                 $computer->fields['manufacturers_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG['common'][109].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_groups',$computer->fields['groups_id_tech'])),
         '<b><i>'.$LANG['common'][22].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_computermodels',
                                                 $computer->fields['computermodels_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG['common'][21].' :</i></b> '.$computer->fields['contact_num'],
                        '<b><i>'.$LANG['common'][19].' :</i></b> '.$computer->fields['serial']);

      $pdf->displayLine('<b><i>'.$LANG['common'][18].' :</i></b> '.$computer->fields['contact'],
         '<b><i>'.$LANG['common'][20].' :</i></b> '.$computer->fields['otherserial']);

      $pdf->displayLine(
         '<b><i>'.$LANG['common'][34].' :</i></b> '.getUserName($computer->fields['users_id']),
         '<b><i>'.$LANG['setup'][88].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_networks', $computer->fields['networks_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG['common'][35].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_groups',$computer->fields['groups_id'])),
         '<b><i>'.$LANG['computers'][53].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_operatingsystemservicepacks',
                                                 $computer->fields['operatingsystemservicepacks_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG['setup'][89].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_domains', $computer->fields['domains_id'])),
         '<b><i>'.$LANG['computers'][52].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_operatingsystemversions',
                                                 $computer->fields['operatingsystemversions_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG['computers'][9].' :</i></b> '.
            Html::clean(Dropdown::getDropdownName('glpi_operatingsystems',
                                                 $computer->fields['operatingsystems_id'])),
         '<b><i>'.$LANG['computers'][10].' :</i></b> '.$computer->fields['os_license_number']);


      $pdf->displayLine(
         '<b><i>'.$LANG['computers'][11].' :</i></b> '.$computer->fields['os_licenseid'],
                        '<b><i>'.$LANG['computers'][51].' :</i></b> '.
         Html::clean(Dropdown::getDropdownName('glpi_autoupdatesystems',
                                              $computer->fields['autoupdatesystems_id'])));

      $pdf->setColumnsSize(100);
      if ($computer->fields['is_ocs_import'] && Session::haveRight("view_ocsng","r")) {
         $tmp = '';
         $query = "SELECT *
                   FROM `glpi_ocslinks`
                   WHERE `computers_id` = '$ID'";

         $result = $DB->query($query);
         if ($DB->numrows($result)==1) {
            $dataocs = $DB->fetch_array($result);
            $tmp .= '<b>'.$LANG['ocsng'][14].'</b> : '.Html::convDateTime($dataocs["last_ocs_update"]).', ';
            $tmp .= '<b>'.$LANG['ocsng'][13].'</b> : '.Html::convDateTime($dataocs["last_update"]).', ';
         }

         $tmp .= '<b>'.$LANG['ocsng'][6].'</b> : '.
                 ($computer->getField('use_auto_update') ? $LANG['choice'][1] : $LANG['choice'][0]);

         $pdf->displayText('<b>'.$LANG['ocsng'][0].'</b> : ', $tmp);
      }


      $pdf->displayLine(
         '<b><i>'.$LANG['computers'][58].' :</i></b> '.$computer->fields['uuid']);


      $pdf->displayText('<b><i>'.$LANG['common'][25].' :</i></b>', $computer->fields['comment']);

      $pdf->displaySpace();
   }


   static function pdfDevice(PluginPdfSimplePDF $pdf, Computer $computer) {
      global $DB, $LANG;

      $devtypes = Computer_Device::getDeviceTypes();

      $ID = $computer->getField('id');
      if (!$computer->can($ID, 'r')) {
         return false;
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.Toolbox::ucfirst($LANG['log'][18]).'</b>');

      $pdf->setColumnsSize(3,14,42,41);

      foreach ($devtypes as $itemtype) {
         $device = new $itemtype;

         $specificities = $device->getSpecifityLabel();
         $specif_fields = array_keys($specificities);
         $specif_text = implode(',',$specif_fields);
         if (!empty($specif_text)) {
            $specif_text=" ,".$specif_text." ";
         }

         $linktable = getTableForItemType('Computer_'.$itemtype);
         $fk = getForeignKeyFieldForTable(getTableForItemType($itemtype));

         $query = "SELECT count(*) AS NB, `id`, `$fk` $specif_text
                  FROM `$linktable`
                  WHERE `computers_id` = '$ID'
                  GROUP BY `$fk` $specif_text";

         foreach($DB->request($query) as $data) {

            if ($device->getFromDB($data[$fk])) {

               $spec = $device->getFormData();
               $col4 = '';
               if (isset($spec['label']) && count($spec['label'])) {
                  $colspan = (60/count($spec['label']));
                  foreach ($spec['label'] as $i => $label) {
                     if (isset($spec['value'][$i])) {
                        $col4 .= '<b><i>'.$spec['label'][$i].' :</i></b> '.$spec['value'][$i]." ";
                     } else {
                        $col4 .= '<b><i>'.$spec['label'][$i].' :</i></b> '.$data['specificity']." ";
                     }
                  }
               }
               $pdf->displayLine($data['NB'], $device->getTypeName(), $device->getName(), $col4);
            }
         }
      }

      $pdf->displaySpace();
   }


   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      switch ($tab) {
         case '_main_' :
            self::pdfMain($pdf, $item);
            break;

         case 'DeviceProcessor$1' :
            self::pdfDevice($pdf, $item);
            break;

         case 'ComputerDisk$1' :
            PluginPdfComputerDisk::pdfForComputer($pdf, $item);
            break;

         case 'Computer_SoftwareVersion$1' :
            PluginPdfComputer_SoftwareVersion::pdfForComputer($pdf, $item);
            break;

         case 'Computer_Item$1' :
            PluginPdfComputer_Item::pdfForComputer($pdf, $item);
            break;

         case 'Document$1' :
            PluginPdfDocument::pdfForItem($pdf, $item);
            break;

         case 'ComputerVirtualMachine$1' :
            PluginPdfComputerVirtualMachine::pdfForComputer($pdf, $item);
            break;

         case 'RegistryKey$1' :
            PluginPdfRegistryKey::pdfForComputer($pdf, $item);
            break;

         default :
            return false;
      }
      return true;
   }
}