<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Immobilizationsheets plugin for GLPI
 Copyright (C) 2003-2011 by the Immobilizationsheets Development Team.

 https://forge.indepnet.net/projects/immobilizationsheets
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Immobilizationsheets.

 Immobilizationsheets is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Immobilizationsheets is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Immobilizationsheets. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginImmobilizationsheetsItem extends CommonDBTM {
   
   static $types = array('Computer','Monitor','NetworkEquipment','Peripheral',
         'Phone','Printer');
         
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_immobilizationsheets']['title'][1];
   }
   
   function canView() {
      return plugin_immobilizationsheets_haveRight("immobilizationsheets","r");
   }
   
   function canCreate() {
      return plugin_immobilizationsheets_haveRight("immobilizationsheets","r");
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         if (in_array($item->getType(), self::getTypes(true))
                    && plugin_immobilizationsheets_haveRight("immobilizationsheets","r")) {

            return self::getTypeName();
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
   
      $self = new self();
      
      if (in_array($item->getType(), self::getTypes(true))) {
      
         $self->showForm(get_class($item),$item->getField('id'));

      }
      return true;
   }
   
   function showForm($itemtype,$ID) {
      global $LANG,$CFG_GLPI;

      echo "<form action='".$CFG_GLPI["root_doc"]."/plugins/immobilizationsheets/front/export.php?id=".$ID."&itemtype=".$itemtype."' method=\"post\">";
      echo "<div align=\"center\"><table cellspacing=\"2\" cellpadding=\"2\">";

      echo "<tr>";
      echo "<td class='center'>";

      $config= new PluginImmobilizationsheetsConfig();

      if ($config->getFromDB(1))
         if ($config->fields["use_backup"]==1) {
            echo "<input type='checkbox' name='saveas' value='1'>";
            echo "&nbsp;".$LANG['plugin_immobilizationsheets']['setup'][12];
         } else {
            echo "<input type='hidden' name='saveas' value='0'>";
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td class='center'>";
      echo "<input type='submit' value=\"".$LANG['plugin_immobilizationsheets']['title'][2]."\" class='submit' >";
      echo "</div></td></tr>";
      echo "</td>";
      echo "</tr>";

      echo "</table></div>";
      Html::closeForm();
   }

   function mainPdf($itemtype,$tab_id,$save=0,$saveas=0) {
      global $PDF,$LANG,$DB;

      $config= new PluginImmobilizationsheetsConfig();
      
      $nb_id = count($tab_id);

      foreach($tab_id as $key => $ID) {
         $itemtable=getTableForItemType($itemtype);
         $PDF =new Cezpdf();
         $PDF->Cezpdf('a4', 'portrait');
         $PDF->selectFont(GLPI_ROOT.'/lib/ezpdf/fonts/Helvetica.afm');
         $PDF->ezStartPageNumbers(550,10,10,'left',"GLPI PDF export - ".date("Y-m-d H:i:s")." - ".
                                 Toolbox::decodeFromUtf8($LANG['pager'][5])."- {PAGENUM}/{TOTALPAGENUM}");

         //items
         $query = "SELECT *
             FROM `".$itemtable."`
             WHERE `id` = '$ID'";
         $result = $DB->query($query);
         $number = $DB->numrows($result);

         if ($number!=0) {
            while ($data=$DB->fetch_array($result)) {
               $this->generatePdf($itemtype,$data,$saveas);
            }
         }
       
         if ($config->getFromDB(1)) {
            if ($config->fields["use_backup"]!=1) {
               if($nb_id!=$key+1) {
                  $PDF->ezNewPage();
               }
            }
         }
      }

      if ($save == 0)
         $PDF->ezstream();

   }
   
   static function getDeviceTypes() {
      return array (2=>'DeviceProcessor',3=>'DeviceMemory',
                          4=>'DeviceHardDrive',6=>'DeviceDrive');
   }
   
   function generatePdf($itemtype,$data,$saveas) {
      global $LANG,$CFG_GLPI, $PDF,$DB;
         
      $ID=$data["id"];
      //name
      if (!empty($data["name"]))
         $name_item = Toolbox::decodeFromUtf8($data["name"]);
      else
         $name_item="";
      //user
      if (!empty($data["users_id"]))
      $user_item = Toolbox::decodeFromUtf8(Html::clean(getUserName($data["users_id"])));
      else if (!empty($data["groups_id"]))
      $user_item = Toolbox::decodeFromUtf8(Dropdown::getDropdownName("glpi_groups",$data["groups_id"]));
      else
         $user_item="";
      //fabricant
      if (!empty($data["manufacturers_id"]))
         $fabricant_item = Toolbox::decodeFromUtf8(Dropdown::getDropdownName("glpi_manufacturers",$data["manufacturers_id"]));
      else
         $fabricant_item="";
      //serial
      if (!empty($data["serial"]))
         $serial_item = Toolbox::decodeFromUtf8($data["serial"]);
      else
         $serial_item="";

      $class = $itemtype."Type";
      $item = new $class();
      $typefield = getForeignKeyFieldForTable(getTableForItemType($itemtype."Type"));
      $item->getFromDB($data[$typefield]);
      
      if (!empty($typefield))
         $type_item = Toolbox::decodeFromUtf8($item->fields["name"]);
      else
         $type_item="";

      //infocoms
      $ic = new Infocom;
      if($ic->getfromDBforDevice($itemtype,$ID)) {

         //immobilizationsheets_item
         if (!empty($ic->fields["immo_number"]))
            $immobilizationsheets_item = Toolbox::decodeFromUtf8($ic->fields["immo_number"]);
         else
            $immobilizationsheets_item="";
         //buy_date
         if (!empty($ic->fields["buy_date"]))
            $buy_date_item = Toolbox::decodeFromUtf8(Html::convdate($ic->fields["buy_date"]));
         else
            $buy_date_item="";
         //use_date
         if (!empty($ic->fields["use_date"]))
            $use_date_item = Toolbox::decodeFromUtf8(Html::convdate($ic->fields["use_date"]));
         else
            $use_date_item="";
         //order_number
         if (!empty($ic->fields["order_number"]))
            $order_number_item = Toolbox::decodeFromUtf8($ic->fields["order_number"]);
         else
            $order_number_item="";
         //value_item
         if (!empty($ic->fields["value"]))
            $value_item = Toolbox::decodeFromUtf8(Html::clean(Html::formatNumber($ic->fields["value"])));
         else
            $value_item="";
         //sink_time
         if (!empty($ic->fields["sink_time"]))
            $sink_time_item = Toolbox::decodeFromUtf8($ic->fields["sink_time"]." ".$LANG['financial'][9]);
         else
            $sink_time_item="";
         //sink_type
         if (!empty($ic->fields["sink_type"]))
            $sink_type_item = Toolbox::decodeFromUtf8(Infocom::getAmortTypeName($ic->fields["sink_type"]));
         else
            $sink_type_item="";

      } else {
         $immobilizationsheets_item="";
         $buy_date_item="";
         $use_date_item="";
         $order_number_item="";
         $value_item="";
         $sink_time_item="";
         $sink_type_item="";
      }

      //composants
      $devtypes=self::getDeviceTypes();
      
      if ($itemtype == 'Computer') {
         
         $device2 = new $devtypes[2];
         $query2 = "SELECT `deviceprocessors_id`
                  FROM `".getTableForItemType($itemtype.'_'.$devtypes[2])."`
                  WHERE `computers_id` = '$ID'";
         $result2 = $DB->query($query2);
         $number2 = $DB->numrows($result2);
         
         $device3 = new $devtypes[3];
         $query3 = "SELECT SUM(`specificity`) AS total
                  FROM `".getTableForItemType($itemtype.'_'.$devtypes[3])."`
                  WHERE `computers_id` = '$ID'";
         $result3 = $DB->query($query3);
         $number3 = $DB->numrows($result3);

         $query3b = "SELECT `devicememories_id`
                  FROM `".getTableForItemType($itemtype.'_'.$devtypes[3])."`
                  WHERE `computers_id` = '$ID'";
         $result3b = $DB->query($query3b);
         $number3b = $DB->numrows($result3b);
         
         $device4 = new $devtypes[4];
         $query4 = "SELECT `deviceharddrives_id`,`specificity`
                  FROM `".getTableForItemType($itemtype.'_'.$devtypes[4])."`
                  WHERE `computers_id` = '$ID'";
         $result4 = $DB->query($query4);
         $number4 = $DB->numrows($result4);
         
         $device5 = new $devtypes[6];
         $query5 = "SELECT `devicedrives_id`
                  FROM `".getTableForItemType($itemtype.'_'.$devtypes[6])."`
                  WHERE `computers_id` = '$ID'";
         $result5 = $DB->query($query5);
         $number5 = $DB->numrows($result5);

         if ($number2!=0) {
            while ($data2=$DB->fetch_array($result2)) {
               //proc_item
               if (!empty($data2["deviceprocessors_id"])) {
                  $query_proc = "SELECT `designation`
                              FROM `glpi_deviceprocessors`
                              WHERE `id` = '".$data2["deviceprocessors_id"]."'";
                  $result_proc = $DB->query($query_proc);
                  $number_proc = $DB->numrows($result_proc);
                  if ($number_proc!=0) {
                     while ($data_proc=$DB->fetch_array($result_proc)) {
                        $proc_item = Toolbox::decodeFromUtf8($data_proc["designation"]);
                     }
                  }
               }
            }
         } else
            $proc_item="";

         if ($number3!=0) {
            while ($data3=$DB->fetch_array($result3)) {
               //ram_item
               $ram_item= $data3["total"];
            }
         } else
            $ram_item="";

         $ram_type_item="";
         if ($number3b!=0) {
            while ($data3b=$DB->fetch_array($result3b)) {
               //ram_type_item

               if (!empty($data3b["devicememories_id"])) {
                  $query_ram = "SELECT `glpi_devicememorytypes`.`name`
                              FROM `glpi_devicememories`,`glpi_devicememorytypes`
                              WHERE `glpi_devicememories`.`id` = '".$data3b["devicememories_id"]."'
                              AND `glpi_devicememorytypes`.`id` = `glpi_devicememories`.`devicememorytypes_id` ";
                  $result_ram = $DB->query($query_ram);
                  $number_ram = $DB->numrows($result_ram);
                  if ($number_ram!=0) {
                     while ($data_ram=$DB->fetch_array($result_ram)) {
                        $ram_type_item=Toolbox::decodeFromUtf8($data_ram["name"]);
                     }
                  } else
                     $ram_type_item="";
               }
            }
         } else
            $ram_type_item="";

         $hdd_item="";
         $hdd_designation_item="";
         $hdd_interface_item="";
         if ($number4!=0) {
            while ($data4=$DB->fetch_array($result4)) {
               //hdd_item
               $hdd_specificity_item=Toolbox::decodeFromUtf8($data4["specificity"]);
               if (!empty($data4["deviceharddrives_id"])) {

                  $query_hdd = "SELECT `designation`
                              FROM `glpi_deviceharddrives`
                              WHERE `id` = '".$data4["deviceharddrives_id"]."'";
                  $result_hdd = $DB->query($query_hdd);
                  $number_hdd = $DB->numrows($result_hdd);
                  if ($number_hdd != 0) {
                     while ($data_hdd=$DB->fetch_array($result_hdd)) {
                        $hdd_designation_item=Toolbox::decodeFromUtf8($data_hdd["designation"]);
                     }
                  } else
                     $hdd_designation_item="";

                  $query_hdd1 = "SELECT `glpi_interfacetypes`.`name`
                              FROM `glpi_deviceharddrives`,`glpi_interfacetypes`
                              WHERE `glpi_deviceharddrives`.`id` = '".$data4["deviceharddrives_id"]."'
                              AND `glpi_interfacetypes`.`id` = `glpi_deviceharddrives`.`interfacetypes_id` ";
                              //replace interface by FK_interface 0.72.1
                  $result_hdd1 = $DB->query($query_hdd1);
                  $number_hdd1 = $DB->numrows($result_hdd1);
                  if ($number_hdd1 != 0) {
                     while ($data_hdd1=$DB->fetch_array($result_hdd1)) {
                        $hdd_interface_item=Toolbox::decodeFromUtf8($data_hdd1["name"]);
                     }
                  }
               } else
               $hdd_interface_item="";

               $hdd_item.=$hdd_designation_item." ".$hdd_interface_item." (".$hdd_specificity_item." Mo)";

               if ($number4 > 1)
                  $hdd_item.=" - ";
            }
         } else
            $hdd_item="";

         $lecteur_item="";
         if ($number5!=0) {
            while ($data5=$DB->fetch_array($result5)) {
               //lecteur_item
               if (!empty($data5["items_id"])) {
                  $query_lecteur = "SELECT `designation`
                                 FROM `glpi_devicedrives`
                                 WHERE `id` = '".$data5["interfacetypes_id"]."'";
                  $result_lecteur = $DB->query($query_lecteur);
                  $number_lecteur = $DB->numrows($result_lecteur);
                  if ($number_lecteur!=0) {
                     while ($data_lecteur=$DB->fetch_array($result_lecteur)) {
                        $lecteur_item .= Toolbox::decodeFromUtf8($data_lecteur["designation"]);
                        if ($number5 > 1)
                        $lecteur_item.=" - ";
                     }
                  }
               }
            }
         } else
            $lecteur_item="";

         //softwares

         $query6 = "SELECT `glpi_softwares`.`name`,`glpi_softwarelicenses`.`softwareversions_id_buy`,`glpi_softwarelicenses`.`serial`
                  FROM `glpi_softwarelicenses`
                  INNER JOIN `glpi_computers_softwarelicenses` ON (`glpi_softwarelicenses`.`id` = `glpi_computers_softwarelicenses`.`softwarelicenses_id` AND `glpi_computers_softwarelicenses`.`computers_id` = '$ID') 
                  INNER JOIN `glpi_softwares` ON (`glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`) ";
         $result6 = $DB->query($query6);
         $number6 = $DB->numrows($result6);

      }
      //Affichage

      //$PDF->addJpegFromFile('../pics/immobilizationsheets.jpg',285,785,32,32);
      //title
      $PDF->ezSetDy(-20);
      $title=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports_title'][1])));
      $cols=array('' => array('width'=>530, 'justification' => 'center'));

      $PDF->ezTable($title,'',''
      ,array(
      'cols' => $cols
       ,'showHeadings'=>0
      ,'fontSize' => 12
      ,'showLines' => 0
      ,'shaded'=>2
      ,'shadeCol' => array(0.8,0.8,0.8)
      ,'shadeCol2' => array(0.8,0.8,0.8)
       ));
      $PDF->ezSetDy(-10);

      //partie 1
      $PDF->ezSetDy(-20);
      $title1=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports_title'][2])));
      $cols=array('' => array('width'=>530, 'justification' => 'left'));

      $PDF->ezTable($title1,'',''
      ,array(
      'cols' => $cols
       ,'showHeadings'=>0
      ,'fontSize' => 10
      ,'showLines' => 0
      ,'shaded'=>2
      ,'shadeCol' => array(0.8,0.8,0.8)
      ,'shadeCol2' => array(0.8,0.8,0.8)
       ));
       $title2=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports_title'][3])));
       $cols=array('' => array('width'=>530, 'justification' => 'left'));

      $PDF->ezTable($title2,'',''
      ,array(
      'cols' => $cols
       ,'showHeadings'=>0
      ,'fontSize' => 8
      ,'showLines' => 0
      ,'shaded'=>2
      ,'shadeCol' => array(0.8,0.8,0.8)
      ,'shadeCol2' => array(0.8,0.8,0.8)
       ));
      $PDF->ezSetDy(-10);
      //Date mise en service

      $PDF->ezText("<u>".Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][0])."</u> : ".$buy_date_item,8,array("justification"=>"left"));
      $PDF->ezSetDy(-10);

      //ligne1 (entreprise / fournisseur)
         $data1 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][3]),
         '1'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][8]))
         );

      $cols1=array('0' => array('width'=>265, 'justification' => 'center'),'1' => array('width'=>265, 'justification' => 'center'));

      $PDF->ezTable($data1,'',$LANG['plugin_immobilizationsheets']['reports'][1]
      ,array('showHeadings'=>0
      ,'cols' => $cols1
       ));
      //ligne2 (4 / 9)
         $data2 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][4]),
         '1'=>'',
         '2'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][9]),
         '3'=>$fabricant_item)
         );

      $cols2=array('0' => array('width'=>132.5,'fontSize' => 8, 'justification' => 'left'),'1' => array('width'=>132.5, 'justification' => 'left'),'2' => array('width'=>132.5, 'justification' => 'left'),'3' => array('width'=>132.5, 'justification' => 'left'));

      $PDF->ezTable($data2,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols2
      ,'fontSize' => 8
       ));
      //ligne3 (5 / 10)
         $data3 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][5]),
         '1'=>$order_number_item,
         '2'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][10]),
         '3'=>$type_item)
         );

      $cols3=array('0' => array('width'=>132.5,'fontSize' => 8, 'justification' => 'left'),'1' => array('width'=>132.5, 'justification' => 'left'),'2' => array('width'=>132.5, 'justification' => 'left'),'3' => array('width'=>132.5, 'justification' => 'left'));

      $PDF->ezTable($data3,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols3
      ,'fontSize' => 8
       ));
      //ligne4 (6 / 11)
         $data4 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][6]),
         '1'=>$value_item,
         '2'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][11]),
         '3'=>$serial_item)
         );

      $cols4=array('0' => array('width'=>132.5,'fontSize' => 8, 'justification' => 'left'),'1' => array('width'=>132.5, 'justification' => 'left'),'2' => array('width'=>132.5, 'justification' => 'left'),'3' => array('width'=>132.5, 'justification' => 'left'));

      $PDF->ezTable($data4,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols4
      ,'fontSize' => 8
       ));
      //ligne5 (7 / 12)
         $data5 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][7]),
         '1'=>'',
         '2'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][12]),
         '3'=>$immobilizationsheets_item)
         );

      $cols5=array('0' => array('width'=>132.5,'fontSize' => 8, 'justification' => 'left'),'1' => array('width'=>132.5, 'justification' => 'left'),'2' => array('width'=>132.5, 'justification' => 'left'),'3' => array('width'=>132.5, 'justification' => 'left'));

      $PDF->ezTable($data5,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols5
      ,'fontSize' => 8
       ));

      $PDF->ezSetDy(-10);
      //trigramme
         $data6 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][13]),
         '1'=>'')
         );

      $cols6=array('0' => array('width'=>430,'fontSize' => 8, 'justification' => 'right'),'1' => array('width'=>100, 'justification' => 'left'));

      $PDF->ezTable($data6,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols6
      ,'xPos' => 'center'
      ,'xOreintation' => 'right'
      ,'fontSize' => 8
       ));

      //partie 2
      $PDF->ezSetDy(-20);
      $title1=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports_title'][4])));
      $cols=array('' => array('width'=>530, 'justification' => 'left'));

      $PDF->ezTable($title1,'',''
      ,array(
      'cols' => $cols
       ,'showHeadings'=>0
      ,'fontSize' => 10
      ,'showLines' => 0
      ,'shaded'=>2
      ,'shadeCol' => array(0.8,0.8,0.8)
      ,'shadeCol2' => array(0.8,0.8,0.8)
       ));
       $title2=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports_title'][5])));
       $cols=array('' => array('width'=>530, 'justification' => 'left'));

      $PDF->ezTable($title2,'',''
      ,array(
      'cols' => $cols
       ,'showHeadings'=>0
      ,'fontSize' => 8
      ,'showLines' => 0
      ,'shaded'=>2
      ,'shadeCol' => array(0.8,0.8,0.8)
      ,'shadeCol2' => array(0.8,0.8,0.8)
       ));
      $PDF->ezSetDy(-10);
      //ligne1 (name  / user)
         $data1 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][15]),
         '1'=>$user_item,
         '2'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][16]),
         '3'=>$name_item)
         );

      $cols1=array('0'=> array('width'=>132.5, 'justification' => 'center'),'1' => array('width'=>132.5, 'justification' => 'center'),'2' => array('width'=>132.5, 'justification' => 'center'),'3' => array('width'=>132.5, 'justification' => 'center'));

      $PDF->ezTable($data1,'',$LANG['plugin_immobilizationsheets']['reports'][14]
      ,array(
      'fontSize' => 8
      ,'showHeadings'=>0
      ,'cols' => $cols1
       ));
      $PDF->ezSetDy(-10);

      if ($itemtype == 'Computer') {
         //title config mat?ielle
         $title=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][17])));
         $cols=array('' => array('width'=>530, 'justification' => 'center'));

         $PDF->ezTable($title,'',''
         ,array(
         'cols' => $cols
         ,'showHeadings'=>0
         ,'fontSize' => 12
         ,'showLines' => 1
          ));
         $PDF->ezSetDy(-10);

         //ligne1 (processeur  / ram quantit?/ ram type)
         $data1 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][18]),
         '1'=>$proc_item,
         '2'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][19]),
         '3'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][20]),
         '4'=>$ram_item,
         '5'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][21]),
         '6'=>$ram_type_item)
         );

         $cols1=array('0'=> array('width'=>55, 'justification' => 'center'),'1'=> array('width'=>200, 'justification' => 'left'),'2'=> array('width'=>55, 'justification' => 'center'),'3'=> array('width'=>55, 'justification' => 'center'),'4'=> array('width'=>55, 'justification' => 'center'),'5'=> array('width'=>55, 'justification' => 'center'),'6'=> array('width'=>55, 'justification' => 'center'));

         $PDF->ezTable($data1,'',''
         ,array(
         'fontSize' => 8
         ,'showHeadings'=>0
         ,'cols' => $cols1
          ));


         //ligne2 (Disque dur / Lecteur)
         $data1 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][22]),
         '1'=>$hdd_item,
         '2'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][23]),
         '3'=>$lecteur_item)
         );

         $cols1=array('0'=> array('width'=>55, 'justification' => 'center'),'1'=> array('width'=>200, 'justification' => 'left'),'2'=> array('width'=>55, 'justification' => 'center'),'3'=> array('width'=>220, 'justification' => 'center'));

         $PDF->ezTable($data1,'',''
         ,array(
         'fontSize' => 8
         ,'showHeadings'=>0
         ,'cols' => $cols1
          ));
         $PDF->ezSetDy(-10);
         //titre suppl?ent

         $PDF->ezText("<u>".Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][24])."</u> : ",8,array("justification"=>"left"));
         $PDF->ezSetDy(-10);

       if ($number6>0) {
             //title config logicielle
            $title=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][25])));
            $cols=array('' => array('width'=>530, 'justification' => 'center'));

            $PDF->ezTable($title,'',''
            ,array(
            'cols' => $cols
            ,'showHeadings'=>0
            ,'fontSize' => 12
            ,'showLines' => 1
             ));

            $PDF->ezSetDy(-10);

            //titre logiciels

            $PDF->ezText("<u>".Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][26])."</u> : ",8,array("justification"=>"left"));
            $PDF->ezSetDy(-10);

            //ligne1 (name  / version / licence)
            $data0 = array(array(
               '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][27]),
               '1'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][28]),
               '2'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][29]))
               );


            $cols1=array('0'=> array('width'=>240, 'justification' => 'left'),'1' => array('width'=>50, 'justification' => 'center'),'2' => array('width'=>240, 'justification' => 'center'));

            $PDF->ezTable($data0,'',''
               ,array(
               'fontSize' => 8
               ,'showHeadings'=>0
               ,'cols' => $cols1
                ));

            while ($data6=$DB->fetch_array($result6)) {

               $soft_name_item=$data6["name"];
               $soft_version_item=Dropdown::getDropdownName("glpi_softwareversions",$data6["softwareversions_id_buy"]);
               $soft_license_item=$data6["serial"];

               $data1 = array(array(
               '0'=>$soft_name_item,
               '1'=>$soft_version_item,
               '2'=>$soft_license_item)
               );

               $cols1=array('0'=> array('width'=>240, 'justification' => 'left'),'1' => array('width'=>50, 'justification' => 'center'),'2' => array('width'=>240, 'justification' => 'center'));


               $PDF->ezTable($data1,'',''
               ,array(
               'fontSize' => 8
               ,'showHeadings'=>0
               ,'cols' => $cols1
                ));
            }
            $PDF->ezSetDy(-10);
         }
      }
       //trigramme
      $data6 = array(array(
      '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][13]),
      '1'=>'')
      );

      $cols6=array('0' => array('width'=>430,'fontSize' => 8, 'justification' => 'right'),'1' => array('width'=>100, 'justification' => 'left'));

      $PDF->ezTable($data6,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols6
      ,'xPos' => 'center'
      ,'xOreintation' => 'right'
      ,'fontSize' => 8
       ));

      //partie 3
       $PDF->ezSetDy(-20);
       $title1=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports_title'][6])));
       $cols=array('' => array('width'=>530, 'justification' => 'left'));

      $PDF->ezTable($title1,'',''
      ,array(
      'cols' => $cols
       ,'showHeadings'=>0
      ,'fontSize' => 10
      ,'showLines' => 0
      ,'shaded'=>2
      ,'shadeCol' => array(0.8,0.8,0.8)
      ,'shadeCol2' => array(0.8,0.8,0.8)
       ));
       $title2=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports_title'][7])));
       $cols=array('' => array('width'=>530, 'justification' => 'left'));

      $PDF->ezTable($title2,'',''
      ,array(
      'cols' => $cols
       ,'showHeadings'=>0
      ,'fontSize' => 8
      ,'showLines' => 0
      ,'shaded'=>2
      ,'shadeCol' => array(0.8,0.8,0.8)
      ,'shadeCol2' => array(0.8,0.8,0.8)
       ));
       $PDF->ezSetDy(-20);
       //title
       $title=array(array(''=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][30])));
       $cols=array('' => array('width'=>530, 'justification' => 'center'));

      $PDF->ezTable($title,'',''
      ,array(
      'cols' => $cols
       ,'showHeadings'=>0
      ,'fontSize' => 12
      ,'showLines' => 1
       ));
      $PDF->ezSetDy(-10);

      //Base d'amortissement
      $amort0 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][31].":"),
         '1'=>$sink_type_item,
         ));

      $cols1=array('0' => array('width'=>130,'fontSize' => 8, 'justification' => 'left'),'1' => array('width'=>400, 'justification' => 'left'));

      $PDF->ezTable($amort0,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols1
      ,'fontSize' => 8
      ,'showLines' => 0
       ));

      $PDF->ezSetDy(-10);

      //Duree d'amortissement
      $amort1 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][32].":"),
         '1'=>$sink_time_item,
         ));

      $cols1=array('0' => array('width'=>130,'fontSize' => 8, 'justification' => 'left'),'1' => array('width'=>400, 'justification' => 'left'));

      $PDF->ezTable($amort1,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols1
      ,'fontSize' => 8
      ,'showLines' => 0
       ));

      $PDF->ezSetDy(-10);
      //Date de debut d'amortissement

      $amort2 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][33].":"),
         '1'=>$use_date_item,
         ));

      $cols1=array('0' => array('width'=>130,'fontSize' => 8, 'justification' => 'left'),'1' => array('width'=>400, 'justification' => 'left'));

      $PDF->ezTable($amort2,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols1
      ,'fontSize' => 8
      ,'showLines' => 0
       ));

      $PDF->ezSetDy(-10);
       //visa
         $amort1 = array(array(
         '0'=>Toolbox::decodeFromUtf8($LANG['plugin_immobilizationsheets']['reports'][34]),
         '1'=>"")
         );

      $cols6=array('0' => array('width'=>430,'fontSize' => 8, 'justification' => 'right'),'1' => array('width'=>100, 'justification' => 'left'));

      $PDF->ezTable($data6,'',''
      ,array('showHeadings'=>0
      ,'cols' => $cols6
      ,'xPos' => 'center'
      ,'xOreintation' => 'right'
      ,'fontSize' => 8
       ));
       //end

     $PDF->addInfo('Author', Toolbox::decodeFromUtf8(Html::clean(getUserName(Session::getLoginUserID()))));

      $config= new PluginImmobilizationsheetsConfig();
      if ($config->getFromDB(1)) {
         if ($saveas==1) {
            if ($config->fields["use_backup"]==1) {

               $path = GLPI_DOC_DIR."/_uploads/";
               $time_file=date("Y-m-d-H-i-s");
               $filename="immo_".$time_file."_".$data["name"].".pdf";
               $filepath=$path.$filename;
               $fp=fopen($filepath,'wb');
               fwrite($fp,$PDF->output());
               fclose($fp);
               
               $doc = new document();
               
               $input=array();
               $input["entities_id"]=$data["entities_id"];
               $input["name"]=addslashes($LANG['plugin_immobilizationsheets']['title'][3]." ".$data["name"]." ".$time_file);
               $input["upload_file"]=$filename;
               $input["documentcategories_id"]=$config->fields["documentcategories_id"];
               $input["type"]="application/pdf";
               $input["date_mod"]=date("Y-m-d H:i:s");
               $input["users_id"]=Session::getLoginUserID();
               
               $newdoc=$doc->add($input);
               $docitem=new Document_Item();
               $docitem->add(array('documents_id' => $newdoc,
                  'itemtype' => $itemtype,
                  'items_id' => $ID));
            }
         }
      }
   }
   
   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
}

?>