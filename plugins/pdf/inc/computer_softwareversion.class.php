<?php

/*
 * @version $Id: computer_softwareversion.class.php 311 2011-12-05 13:58:19Z remi $
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

class PluginPdfComputer_SoftwareVersion extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {

      $this->obj = ($obj ? $obj : new Computer_SoftwareVersion());
   }


   static function pdfForItem(PluginPdfSimplePDF $pdf, CommonDBTM $item){
      global $DB,$LANG;

      $ID = $item->getField('id');
      $type = $item->getType();
      $crit = ($type=='Software' ? 'softwares_id' : 'id');

      if ($type=='Software') {
         $crit = 'softwares_id';
         // Software ID
         $query_number = "SELECT COUNT(*) AS cpt
                          FROM `glpi_computers_softwareversions`
                          INNER JOIN `glpi_softwareversions`
                              ON (`glpi_computers_softwareversions`.`softwareversions_id`
                                    = `glpi_softwareversions`.`id`)
                          INNER JOIN `glpi_computers`
                              ON (`glpi_computers_softwareversions`.`computers_id`
                                    = `glpi_computers`.`id`)
                          WHERE `glpi_softwareversions`.`softwares_id` = '$ID'" .
                                getEntitiesRestrictRequest(' AND', 'glpi_computers') ."
                                AND `glpi_computers`.`is_deleted` = '0'
                                AND `glpi_computers`.`is_template` = '0'";

      } else {
         $crit = 'id';
         //SoftwareVersion ID
         $query_number = "SELECT COUNT(*) AS cpt
                          FROM `glpi_computers_softwareversions`
                          INNER JOIN `glpi_computers`
                              ON (`glpi_computers_softwareversions`.`computers_id`
                                    = `glpi_computers`.`id`)
                          WHERE `glpi_computers_softwareversions`.`softwareversions_id` = '$ID'" .
                                getEntitiesRestrictRequest(' AND', 'glpi_computers') ."
                                AND `glpi_computers`.`is_deleted` = '0'
                                AND `glpi_computers`.`is_template` = '0'";
      }
      $total = 0;
      if ($result =$DB->query($query_number)) {
         $total  = $DB->result($result,0,0);
      }
         $query = "SELECT DISTINCT `glpi_computers_softwareversions`.*,
                          `glpi_computers`.`name` AS compname,
                          `glpi_computers`.`id` AS cID,
                          `glpi_computers`.`serial`,
                          `glpi_computers`.`otherserial`,
                          `glpi_users`.`name` AS username,
                          `glpi_users`.`id` AS userid,
                          `glpi_users`.`realname` AS userrealname,
                          `glpi_users`.`firstname` AS userfirstname,
                          `glpi_softwareversions`.`name` AS version,
                          `glpi_softwareversions`.`id` AS vID,
                          `glpi_softwareversions`.`softwares_id` AS sID,
                          `glpi_softwareversions`.`name` AS vername,
                          `glpi_entities`.`completename` AS entity,
                          `glpi_locations`.`completename` AS location,
                          `glpi_states`.`name` AS state,
                          `glpi_groups`.`name` AS groupe
                   FROM `glpi_computers_softwareversions`
                   INNER JOIN `glpi_softwareversions`
                        ON (`glpi_computers_softwareversions`.`softwareversions_id`
                              = `glpi_softwareversions`.`id`)
                   INNER JOIN `glpi_computers`
                        ON (`glpi_computers_softwareversions`.`computers_id` = `glpi_computers`.`id`)
                   LEFT JOIN `glpi_entities` ON (`glpi_computers`.`entities_id` = `glpi_entities`.`id`)
                   LEFT JOIN `glpi_locations`
                        ON (`glpi_computers`.`locations_id` = `glpi_locations`.`id`)
                   LEFT JOIN `glpi_states` ON (`glpi_computers`.`states_id` = `glpi_states`.`id`)
                   LEFT JOIN `glpi_groups` ON (`glpi_computers`.`groups_id` = `glpi_groups`.`id`)
                   LEFT JOIN `glpi_users` ON (`glpi_computers`.`users_id` = `glpi_users`.`id`)
                   WHERE (`glpi_softwareversions`.`$crit` = '$ID') " .
                          getEntitiesRestrictRequest(' AND', 'glpi_computers') ."
                          AND `glpi_computers`.`is_deleted` = '0'
                          AND `glpi_computers`.`is_template` = '0'
                   ORDER BY version, compname
                   LIMIT 0," . intval($_SESSION['glpilist_limit']);

      $pdf->setColumnsSize(100);

      if (($result=$DB->query($query)) && ($number=$DB->numrows($result))>0) {
         if ($number==$total) {
            $pdf->displayTitle('<b>'.$LANG['software'][19]." : $number</b>");
         } else {
            $pdf->displayTitle('<b>'.$LANG['software'][19]." : $number / $total</b>");
         }
         $pdf->setColumnsSize(12,16,15,15,22,20);
         $pdf->displayTitle('<b><i>'.$LANG['software'][5],  // vername
                                     $LANG['common'][16],   // compname
                                     $LANG['common'][19],   // serial
                                     $LANG['common'][20],   // asset
                                     $LANG['common'][15],   // location
                                     $LANG['software'][11].'</i></b>'); // licname

         while ($data = $DB->fetch_assoc($result)) {
            $compname = $data['compname'];
            if (empty($compname) || $_SESSION['glpiis_ids_visible']) {
               $compname .= " (".$data['cID'].")";
            }
            $lics = Computer_SoftwareLicense::GetLicenseForInstallation($data['cID'], $data['vID']);

            $tmp = array();
            if (count($lics)) {
               foreach ($lics as $lic) {
                  $licname = $lic['name'];
                  if (!empty($lic['type'])) {
                     $licname .= " (".$lic['type'].")";
                  }
                  $tmp[] = $licname;
               }
            }
            $pdf->displayLine($data['version'], $compname,$data['serial'], $data['otherserial'],
                              $data['location'], implode(', ', $tmp));
         }
      } else {
         $pdf->displayTitle('<b>'.$LANG['software'][19].'</b>');
         $pdf->displayLine($LANG['search'][15]."!");
      }
      $pdf->displaySpace();
   }


   static function pdfForVersionByEntity(PluginPdfSimplePDF $pdf, SoftwareVersion $version) {
      global $DB, $CFG_GLPI, $LANG;

      $softwareversions_id = $version->getField('id');

      $pdf->setColumnsSize(75,25);
      $pdf->setColumnsAlign('left', 'right');

      $pdf->displayTitle('<b>'.$LANG['entity'][0], $LANG['software'][19].'</b>');

      $lig = $tot = 0;
      if (in_array(0, $_SESSION["glpiactiveentities"])) {
         $nb = Computer_SoftwareVersion::countForVersion($softwareversions_id,0);
         if ($nb>0) {
            $pdf->displayLine($LANG['entity'][2], $nb);
            $tot += $nb;
            $lig++;
         }
      }
      $sql = "SELECT `id`, `completename`
              FROM `glpi_entities` " .
              getEntitiesRestrictRequest('WHERE', 'glpi_entities') ."
              ORDER BY `completename`";

      foreach ($DB->request($sql) as $ID => $data) {
         $nb = Computer_SoftwareVersion::countForVersion($softwareversions_id,$ID);
         if ($nb>0) {
            $pdf->displayLine($data["completename"], $nb);
            $tot += $nb;
            $lig++;
         }
      }

      if ($tot>0) {
         if ($lig>1) {
            $pdf->displayLine($LANG['common'][33], $tot);
         }
      } else {
         $pdf->setColumnsSize(100);
         $pdf->displayLine($LANG['search'][15]);
      }
      $pdf->displaySpace();
   }


   static function pdfForComputer(PluginPdfSimplePDF $pdf, Computer $comp){
      global $DB,$LANG;

      $ID = $comp->getField('id');

      // From Computer_SoftwareVersion::showForComputer();
      $query = "SELECT `glpi_softwares`.`softwarecategories_id`,
                       `glpi_softwares`.`name` AS softname,
                       `glpi_computers_softwareversions`.`id`,
                       `glpi_states`.`name` AS state,
                       `glpi_softwareversions`.`id` AS verid,
                       `glpi_softwareversions`.`softwares_id`,
                       `glpi_softwareversions`.`name` AS version
                FROM `glpi_computers_softwareversions`
                LEFT JOIN `glpi_softwareversions`
                     ON (`glpi_computers_softwareversions`.`softwareversions_id`
                           = `glpi_softwareversions`.`id`)
                LEFT JOIN `glpi_states`
                     ON (`glpi_states`.`id` = `glpi_softwareversions`.`states_id`)
                LEFT JOIN `glpi_softwares`
                     ON (`glpi_softwareversions`.`softwares_id` = `glpi_softwares`.`id`)
                WHERE `glpi_computers_softwareversions`.`computers_id` = '$ID'
                ORDER BY `softwarecategories_id`, `softname`, `version`";

      $output = array();

      $software_category      = new SoftwareCategory();
      $software_version       = new SoftwareVersion();

      foreach ($DB->request($query) as $softwareversion) {
         $output[] = $softwareversion;
      }

      $installed = array();
      if (count($output)) {
         $pdf->setColumnsSize(100);
         $pdf->displayTitle('<b>'.$LANG["software"][17].'</b>');

         $cat = -1;
         foreach ($output as $soft) {
            if ($soft["softwarecategories_id"] != $cat) {
               $cat = $soft["softwarecategories_id"];
               if ($cat && $software_category->getFromDB($cat)) {
                  $catname = $software_category->getName();
               } else {
                  $catname = $LANG["softwarecategories"][2];
               }

               $pdf->setColumnsSize(100);
               $pdf->displayTitle('<b>'.$catname.'</b>');

               $pdf->setColumnsSize(50,13,13,24);
               $pdf->displayTitle('<b>'.$LANG['common'][16].'</b>',
                                  '<b>'.$LANG['state'][0].'</b>',
                                  '<b>'.$LANG['rulesengine'][78].'</b>',
                                  '<b>'.$LANG['install'][92].'</b>');
            }

            // From Computer_SoftwareVersion::displaySoftsByCategory()
            $verid = $soft['verid'];
            $query = "SELECT `glpi_softwarelicenses`.*,
                             `glpi_softwarelicensetypes`.`name` AS type
                      FROM `glpi_computers_softwarelicenses`
                      INNER JOIN `glpi_softwarelicenses`
                           ON (`glpi_computers_softwarelicenses`.`softwarelicenses_id`
                                    = `glpi_softwarelicenses`.`id`)
                      LEFT JOIN `glpi_softwarelicensetypes`
                           ON (`glpi_softwarelicenses`.`softwarelicensetypes_id`
                                    =`glpi_softwarelicensetypes`.`id`)
                      WHERE `glpi_computers_softwarelicenses`.`computers_id` = '$ID'
                            AND (`glpi_softwarelicenses`.`softwareversions_id_use` = '$verid'
                                 OR (`glpi_softwarelicenses`.`softwareversions_id_use` = '0'
                                     AND `glpi_softwarelicenses`.`softwareversions_id_buy` = '$verid'))";

            $lic = '';
            foreach ($DB->request($query) as $licdata) {
               $installed[] = $licdata['id'];
               $lic .= (empty($lic)?'':', ').'<b>'.$licdata['name'].'</b> '.$licdata['serial'];
               if (!empty($licdata['type'])) {
                  $lic .= ' ('.$licdata['type'].')';
               }
            }

            $pdf->displayLine($soft['softname'], $soft['state'], $soft['version'], $lic);
         } // Each version

      } else {
         $pdf->displayTitle('<b>'.$LANG['plugin_pdf']['software'][1].'</b>');
      }

      // Affected licenses NOT installed
      $query = "SELECT `glpi_softwarelicenses`.*,
                       `glpi_softwares`.`name` AS softname,
                       `glpi_softwareversions`.`name` AS version,
                       `glpi_states`.`name` AS state
                FROM `glpi_softwarelicenses`
                LEFT JOIN `glpi_computers_softwarelicenses`
                      ON (`glpi_computers_softwarelicenses`.softwarelicenses_id
                              = `glpi_softwarelicenses`.`id`)
                INNER JOIN `glpi_softwares`
                      ON (`glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`)
                LEFT JOIN `glpi_softwareversions`
                      ON (`glpi_softwarelicenses`.`softwareversions_id_use`
                              = `glpi_softwareversions`.`id`
                           OR (`glpi_softwarelicenses`.`softwareversions_id_use` = '0'
                               AND `glpi_softwarelicenses`.`softwareversions_id_buy`
                                       = `glpi_softwareversions`.`id`))
                LEFT JOIN `glpi_states`
                     ON (`glpi_states`.`id` = `glpi_softwareversions`.`states_id`)
                WHERE `glpi_computers_softwarelicenses`.`computers_id` = '$ID' ";

      if (count($installed)) {
         $query .= " AND `glpi_softwarelicenses`.`id` NOT IN (".implode(',',$installed).")";
      }

      $req = $DB->request($query);
      if ($req->numrows()) {
         $pdf->setColumnsSize(100);
         $pdf->displayTitle('<b>'.$LANG['software'][3].'</b>');

         $pdf->setColumnsSize(50,13,13,24);
         $pdf->displayTitle('<b>'.$LANG['common'][16].'</b>',
                            '<b>'.$LANG['state'][0].'</b>',
                            '<b>'.$LANG['rulesengine'][78].'</b>',
                            '<b>'.$LANG['install'][92].'</b>');

         foreach ($req as $data) {
            $lic .= '<b>'.$data['name'].'</b> '.$data['serial'];
            if (!empty($data['softwarelicensetypes_id'])) {
               $lic .= ' ('.html_clean(Dropdown::getDropdownName('glpi_softwarelicensetypes',
                                                                 $data['softwarelicensetypes_id'])).')';
            }
            $pdf->displayLine($data['softname'], $data['state'], $data['version'], $lic);
         }
      }

      $pdf->displaySpace();
   }
}