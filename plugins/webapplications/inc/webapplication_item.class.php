<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2003-2011 by the Webapplications Development Team.

 https://forge.indepnet.net/projects/webapplications
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginWebapplicationsWebapplication_Item extends CommonDBRelation {

   // From CommonDBRelation
   public $itemtype_1 = 'PluginWebapplicationsWebapplication';
   public $items_id_1 = 'plugin_webapplications_webapplications_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';


   function canCreate() {
      return plugin_webapplications_haveRight('webapplications', 'w');
   }


   function canView() {
      return plugin_webapplications_haveRight('webapplications', 'r');
   }


  /* static function getClasses($all=false) {

      static $types = array();

      $plugin = new Plugin();
      if ($plugin->isActivated("appliances")) {
         $types[] = 'PluginAppliancesAppliance';
      }

      if ($all) {
         return $types;
      }

      foreach ($types as $key=>$type) {
         if (!class_exists($type)) {
            continue;
         }
         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }*/


   function getFromDBbyWebApplicationsAndItem($plugin_webapplications_webapplications_id,
                                              $items_id,$itemtype) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `plugin_webapplications_webapplications_id`
                           = '" . $plugin_webapplications_webapplications_id . "'
                      AND `itemtype` = '" . $itemtype . "'
                      AND `items_id` = '" . $items_id . "'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }


	function addItem($plugin_webapplications_webapplications_id, $items_id,$itemtype) {

      $this->add(array('plugin_webapplications_webapplications_id'
                                    => $plugin_webapplications_webapplications_id,
                       'items_id'   => $items_id,
                       'itemtype'   => $itemtype));

   }


  function deleteItemByWebApplicationsAndItem($plugin_webapplications_webapplications_id,
                                              $items_id, $itemtype) {

      if ($this->getFromDBbyWebApplicationsAndItem($plugin_webapplications_webapplications_id,
                                                   $items_id,$itemtype)) {
         $this->delete(array('id' => $this->fields["id"]));
      }
   }


   //show form of linking webapplications to glpi items
   function showItemFromPlugin($ID, $search='') {
      global $DB,$CFG_GLPI,$LANG;

      if (!$this->canView()) {
        return false;
      }
      $rand = mt_rand();

      $PluginWebapplicationsWebapplication = new PluginWebapplicationsWebapplication();
      if ($PluginWebapplicationsWebapplication->getFromDB($ID)) {
         $canedit = $PluginWebapplicationsWebapplication->can($ID,'w');

         $query = "SELECT DISTINCT `itemtype`
                   FROM `".$this->getTable()."`
                   WHERE `plugin_webapplications_webapplications_id` = '$ID'
                   ORDER BY `itemtype`";
         $result = $DB->query($query);
         $number = $DB->numrows($result);

         $i = 0;

         if (Session::isMultiEntitiesMode()) {
            $colsup = 1;
         } else {
            $colsup = 0;
         }

         echo "<form method='post' name='webapplications_form$rand' id='webapplications_form$rand'
                action=\"".$CFG_GLPI["root_doc"]."/plugins/webapplications/front/webapplication.form.php\">";

         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".
                     $LANG['plugin_webapplications']['setup'][15]." : </th></tr><tr>";
         if ($canedit) {
            echo "<th>&nbsp;</th>";
         }
         echo "<th>".$LANG['common'][17]."</th>";
         echo "<th>".$LANG['common'][16]."</th>";
         if (Session::isMultiEntitiesMode()) {
            echo "<th>".$LANG['entity'][0]."</th>";
         }
         echo "<th>".$LANG['common'][19]."</th>";
         echo "<th>".$LANG['common'][20]."</th>";
         echo "</tr>";

         for ($i=0 ; $i < $number ; $i++) {
            $type = $DB->result($result, $i, "itemtype");
            if (!class_exists($type)) {
               continue;
            }
            $item = new $type();
            if ($item->canView()) {
               $column = "name";
               $table  = getTableForItemType($type);
               if ($type != 'Entity') {
                  $query = "SELECT `".$table."`.*,
                                   `".$this->getTable()."`.`id` AS table_items_id,
                                   `glpi_entities`.`id` AS entity
                            FROM `".$this->getTable()."`,
                                 `".$table."`
                            LEFT JOIN `glpi_entities`
                                 ON (`glpi_entities`.`id` = `".$table."`.`entities_id`)
                            WHERE `".$table."`.`id` = `".$this->getTable()."`.`items_id`
                                  AND `".$this->getTable()."`.`itemtype` = '$type'
                                  AND `".$this->getTable()."`.`plugin_webapplications_webapplications_id` = '$ID' "
                                . getEntitiesRestrictRequest(" AND ", $table, '', '', $item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query .= " AND ".$table.".is_template='0'";
                  }
                  $query .= " ORDER BY `glpi_entities`.`completename`,
                                       `".$table."`.`$column` ";
               } else {
                  $query = "SELECT `".$table."`.*,
                                   `".$this->getTable()."`.`id` AS table_items_id,
                                   `glpi_entities`.`id` AS entity
                            FROM `".$this->getTable()."`,
                                 `".$table."`
                            WHERE `".$table."`.`id` = `".$this->getTable()."`.`items_id`
                                  AND `".$this->getTable()."`.`itemtype` = '$type'
                                  AND `".$this->getTable()."`.`plugin_webapplications_webapplications_id` = '$ID' "
                                . getEntitiesRestrictRequest(" AND ", $table, '', '', $item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query .= " AND ".$table.".is_template='0'";
                  }
                  $query .= " ORDER BY `glpi_entities`.`completename`,
                                       `".$table."`.`$column` ";
               }

               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)) {
                     Session::initNavigateListItems($type,
                                           $LANG['plugin_webapplications'][4]." = ".
                                                $PluginWebapplicationsWebapplication->fields['name']);

                  while ($data=$DB->fetch_assoc($result_linked)) {
                     $item->getFromDB($data["id"]);
                     Session::addToNavigateListItems($type, $data["id"]);

                     echo "<tr class='tab_bg_1'>";

                     if ($canedit) {
                        echo "<td width='10'>";
                        $sel = "";
                        if (isset($_GET["select"]) && $_GET["select"]=="all") {
                            $sel = "checked";
                        }
                        echo "<input type='checkbox' name='item[".$data["table_items_id"]."]'
                               value='1' $sel>";
                        echo "</td>";
                     }
                     echo "<td class='center'>".$item->getTypeName()."</td>";

                     $ID = "";
                     if ($_SESSION["glpiis_ids_visible"]||empty($data["name"])) $ID= " (".$data["id"].")";
                     $link = Toolbox::getItemTypeFormURL($type);
                     $name = "<a href=\"".$link."?id=".$data["id"]."\">".$data["name"]."$ID</a>";
                     echo "<td class='center' ".
                            (isset($data['is_deleted'])&&$data['is_deleted']?"class='tab_bg_2_2'":"").
                           ">".$name."</td>";
                     if (Session::isMultiEntitiesMode()) {
                        echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                                             $data['entity']).
                             "</td>";
                     }
                     echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-").
                          "</td>";
                     echo "<td class='center'>".
                            (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";

                     echo "</tr>";
                  }
               }
            }
         }

         if ($canedit)	{
            echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
            echo "<input type='hidden' name='plugin_webapplications_webapplications_id' value='$ID'>";
            Dropdown::showAllItems("items_id", 0, 0,
                                   ($PluginWebapplicationsWebapplication->fields['is_recursive']
                                       ?-1:$PluginWebapplicationsWebapplication->fields['entities_id']),
                                    PluginWebapplicationsWebapplication::getTypes());
            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>" ;
            Html::openArrowMassives("webapplications_form$rand",true);
            Html::closeArrowMassives(array('deleteitem' => $LANG['buttons'][6]));

         } else {

            echo "</table></div>";
         }
         Html::closeForm();
      }
   }


   //show webapplications linking from glpi items
   static function showPluginFromItems($itemtype,$ID,$withtemplate='') {
      global $DB,$CFG_GLPI, $LANG;

      $PluginWebapplicationsWebapplication = new PluginWebapplicationsWebapplication();
      $item = new $itemtype();
      $canread  = $item->can($ID,'r');
      $canedit  = $item->can($ID,'w');
      $webitem = new self();

      $query = "SELECT `glpi_plugin_webapplications_webapplications_items`.`id` AS items_id,
                       `glpi_plugin_webapplications_webapplications`.*
                FROM `glpi_plugin_webapplications_webapplications_items`,
                     `glpi_plugin_webapplications_webapplications`
                LEFT JOIN `glpi_entities`
                  ON (`glpi_entities`.`id` = `glpi_plugin_webapplications_webapplications`.`entities_id`)
                WHERE `glpi_plugin_webapplications_webapplications_items`.`items_id` = '".$ID."'
                      AND `glpi_plugin_webapplications_webapplications_items`.`itemtype` = '".$itemtype."'
                      AND `glpi_plugin_webapplications_webapplications_items`.`plugin_webapplications_webapplications_id`
                              =`glpi_plugin_webapplications_webapplications`.`id` "
                    . getEntitiesRestrictRequest(" AND ", "glpi_plugin_webapplications_webapplications",
                                                 '', '',
                                                 $PluginWebapplicationsWebapplication->maybeRecursive())."
                ORDER BY `glpi_plugin_webapplications_webapplications`.`name` ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($withtemplate!=2) {
        echo "<form method='post' action=\"".
               $CFG_GLPI["root_doc"]."/plugins/webapplications/front/webapplication.form.php\">";
      }
      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='".(7+$colsup)."'>".$LANG['plugin_webapplications'][21].":</th></tr>";
      echo "<tr><th>".$LANG['plugin_webapplications'][0]."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['common'][109]."</th>";
      echo "<th>".$LANG['plugin_webapplications']['setup'][1]."</th>";
      echo "<th>".$LANG['plugin_webapplications'][1]."</th>";
      echo "<th>".$LANG['plugin_webapplications'][12]."</th>";
      echo "<th>".$LANG['plugin_webapplications']['setup'][14]."</th>";

      if ($webitem->canCreate())
         if ($withtemplate!=2) echo "<th>&nbsp;</th>";

      echo "</tr>";
      $used = array();
      while ($data=$DB->fetch_array($result)) {
         $webapplicationsID = $data["id"];
         $used[]            = $webapplicationsID;
         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

         if ($withtemplate!=3
             && $canread
             && (in_array($data['entities_id'], $_SESSION['glpiactiveentities'])
                 || $data["is_recursive"])) {
            echo "<td class='center'>".
                  "<a href='".$CFG_GLPI["root_doc"].
                    "/plugins/webapplications/front/webapplication.form.php?id=".$data["id"]."'>".
                  $data["name"];
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                echo " (".$data["id"].")";
            }
            echo "</a></td>";

         } else {
            echo "<td class='center'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])){
                 echo " (".$data["id"].")";
            }
            echo "</td>";
         }

         if (Session::isMultiEntitiesMode())
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id']).
                 "</td>";

         echo "<td class='center'>".Dropdown::getDropdownName("glpi_groups", $data["groups_id_tech"]).
              "</td>";

         echo "<td>".Dropdown::getDropdownName("glpi_plugin_webapplications_webapplicationtypes",
                                               $data["plugin_webapplications_webapplicationtypes_id"]).
              "</td>";

         $link = Toolbox::substr($data["address"],0,30)."...";
         echo "<td class='center'>".
               "<a href=\"".str_replace("&","&amp;",$data["address"])."\" target=\"_blank\" ><u>".
                  $link."</u></a></td>";

         echo "<td>".$data["version"]."</td>";
         echo "<td>";
         echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/enterprise.form.php?id=".$data["suppliers_id"]."\">";
         echo Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]);
         if ($_SESSION["glpiis_ids_visible"]) {
            echo " (".$data["suppliers_id"].")";
         }
         echo "</a></td>";

         $caneditwebapplications = $PluginWebapplicationsWebapplication->can($webapplicationsID,'w');

         if ($webitem->canCreate() && $caneditwebapplications) {
            echo "<td class='center tab_bg_2'>";
            Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/webapplications/front/webapplication.form.php',
                                    'deletewebapplications',
                                    $LANG['buttons'][6],
                                    array('id' => $data['items_id']));
            echo "</td>";
         }
         echo "</tr>";
      }

      if ($canedit) {

         $entities = "";
         if ($itemtype!='Entity') {
             if ($webitem->isRecursive()) {
               $entities = getSonsOf('glpi_entities',$item->getEntityID());
            } else {
               $entities = $item->getEntityID();
            }
         } else {
            $entities = $item->getEntityID();
         }
         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_webapplications_webapplications",
                                             '', $entities, true);

         $q  = "SELECT COUNT(*)
                FROM `glpi_plugin_webapplications_webapplications`
                WHERE `is_deleted` = '0' ";
         $q .= $limit;

         $result = $DB->query($q);
         $nb     = $DB->result($result,0,0);

         if ($withtemplate<2 && $nb>count($used)) {
            if ($webitem->canCreate()) {
               echo "<tr class='tab_bg_1'><td class='right' colspan='".(6+$colsup)."'>";
               echo "<input type='hidden' name='items_id' value='$ID'>".
                     "<input type='hidden' name='itemtype' value='$itemtype'>";
               $PluginWebapplicationsWebapplication->dropdownWebApplications("plugin_webapplications_webapplications_id",
                                                                              $entities, $used);
               echo "</td><td class='center'>";
               echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\"
                      class='submit'>";
               echo "</td>";
               echo "</tr>";
            }
         }
      }
      if ($canedit) {
         echo "<tr class='tab_bg_1'><td colspan='".(7+$colsup)."' class='right'>".
               "<a href='".$CFG_GLPI["root_doc"]."/plugins/webapplications/front/webapplication.form.php'>".
                  $LANG['plugin_webapplications'][6]."</a></td></tr>";
      }
      echo "</table></div>";
      Html::closeForm();
   }


   //show webapplications linking from glpi enterprises
   static function showPluginFromSupplier(Supplier $item, $withtemplate='') {
      global $DB,$CFG_GLPI, $LANG;

      $ID      = $item->getField('id');
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');
      $PluginWebapplicationsWebapplication = new PluginWebapplicationsWebapplication();

      $query = "SELECT `glpi_plugin_webapplications_webapplications`.*
                FROM `glpi_plugin_webapplications_webapplications`
                LEFT JOIN `glpi_entities`
                  ON (`glpi_entities`.`id` = `glpi_plugin_webapplications_webapplications`.`entities_id`)
                WHERE `suppliers_id` = '$ID' "
                    . getEntitiesRestrictRequest(" AND ",
                                                 "glpi_plugin_webapplications_webapplications",
                                                 '', '',
                                                 $PluginWebapplicationsWebapplication->maybeRecursive())."
                ORDER BY `glpi_plugin_webapplications_webapplications`.`name` ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($withtemplate!=2) {
        echo "<form method='post' action=\"".
               $CFG_GLPI["root_doc"]."/plugins/webapplications/front/webapplication.form.php\">";
      }
      if (Session::isMultiEntitiesMode()) {
         $colsup = 1;
      } else {
         $colsup = 0;
      }

      echo "<div class='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='".(12+$colsup)."'>".$LANG['plugin_webapplications'][21]." : </th></tr>";
      echo "<tr><th>".$LANG['plugin_webapplications'][0]."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['plugin_webapplications']['setup'][1]."</th>";
      echo "<th>".$LANG['plugin_webapplications'][1]."</th>";
      echo "<th>".$LANG['plugin_webapplications']['setup'][11]."</th>";
      echo "<th>".$LANG['plugin_webapplications']['setup'][12]."</th>";
      echo "<th>".$LANG['plugin_webapplications'][12]."</th>";
      echo "<th>".$LANG['plugin_webapplications'][2]."</th>";
      echo "</tr>";

      while ($data=$DB->fetch_array($result)) {

         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
         if ($withtemplate!=3
             && $canread
             && (in_array($data['entities_id'],$_SESSION['glpiactiveentities'])
                 || $data["is_recursive"])) {
            echo "<td class='center'>".
                  "<a href='".$CFG_GLPI["root_doc"]."/plugins/webapplications/front/webapplication.form.php?id=".
                  $data["id"]."'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                echo " (".$data["id"].")";
            }
            echo "</a></td>";
         } else {
            echo "<td class='center'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                echo " (".$data["id"].")";
            }
            echo "</td>";
         }
         echo "</a></td>";
         if (Session::isMultiEntitiesMode()) {
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id']).
                 "</td>";
         }
         echo "<td>".Dropdown::getDropdownName("glpi_plugin_webapplications_webapplicationtypes",
                                               $data["plugin_webapplications_webapplicationtypes_id"]).
              "</td>";

         $link = Toolbox::substr($data["address"],0,30)."...";
         echo "<td class='center'>".
               "<a href=\"".str_replace("&","&amp;",$data["address"])."\" target=\"_blank\">".
                  "<u>".$link."</u></a></td>";

         echo "<td>".Dropdown::getDropdownName("glpi_plugin_webapplications_webapplicationservertypes",
                                               $data["plugin_webapplications_webapplicationservertypes_id"]).
              "</td>";
         echo "<td>".Dropdown::getDropdownName("glpi_plugin_webapplications_webapplicationtechnics",
                                               $data["plugin_webapplications_webapplicationtechnics_id"]).
              "</td>";
         echo "<td>".$data["version"]."</td>";
         echo "<td>".$data["comment"]."</td></tr>";
      }
      echo "</table></div>";
      Html::closeForm();
   }


   static function ItemsPdf(PluginPdfSimplePDF $pdf, PluginWebapplicationsWebapplication $item) {
      global $DB,$CFG_GLPI,$LANG;

      $ID = $item->getField('id');
      
      if (!$item->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_webapplications_haveRight('webapplications', 'r')) {
         return false;
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.$LANG['plugin_webapplications']['setup'][15].'</b>');

      $query = "SELECT DISTINCT `itemtype`
                FROM `glpi_plugin_webapplications_webapplications_items`
                WHERE `plugin_webapplications_webapplications_id` = '$ID'
                ORDER BY `itemtype`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $pdf->setColumnsSize(12,27,25,18,18);
         $pdf->displayTitle( '<b><i>'.$LANG['common'][17],
                                      $LANG['common'][16],
                                      $LANG['entity'][0],
                                      $LANG['common'][19],
                                      $LANG['common'][20].'</i></b>');
      } else {
         $pdf->setColumnsSize(25,31,22,22);
         $pdf->displayTitle('<b><i>'.$LANG['common'][17],
                                     $LANG['common'][16],
                                     $LANG['common'][19],
                                     $LANG['common'][20].'</i></b>');
      }

      if (!$number) {
         $pdf->displayLine($LANG['search'][15]);
      } else {
         for ($i=0 ; $i < $number ; $i++) {
            $type=$DB->result($result, $i, "itemtype");
            if (!class_exists($type)) {
               continue;
            }
            if ($item->canView()) {
               $column="name";
               $table = getTableForItemType($type);
               $items = new $type();
               
               $query = "SELECT `".$table."`.*, `glpi_entities`.`id` AS entity "
               ." FROM `glpi_plugin_webapplications_webapplications_items`, `".$table
               ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
               ." WHERE `".$table."`.`id` = `glpi_plugin_webapplications_webapplications_items`.`items_id` 
                  AND `glpi_plugin_webapplications_webapplications_items`.`itemtype` = '$type' 
                  AND `glpi_plugin_webapplications_webapplications_items`.`plugin_webapplications_webapplications_id` = '$ID' ";
               if ($type!='User')
                  $query.= getEntitiesRestrictRequest(" AND ",$table,'','',$items->maybeRecursive()); 

               if ($items->maybeTemplate()) {
                  $query.=" AND `".$table."`.`is_template` = '0'";
               }
               $query.=" ORDER BY `glpi_entities`.`completename`, `".$table."`.`$column`";
               
               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)) {
                     
                     while ($data=$DB->fetch_assoc($result_linked)) {
                        if (!$items->getFromDB($data["id"])) {
                           continue;
                        }
                         $items_id_display="";

                        if ($_SESSION["glpiis_ids_visible"]||empty($data["name"])) $items_id_display= " (".$data["id"].")";
                           if ($type=='User')
                              $name=Html::clean(getUserName($data["id"])).$items_id_display;
                           else
                              $name=$data["name"].$items_id_display;
                        
                        if ($type!='User') {
                              $entity=Html::clean(Dropdown::getDropdownName("glpi_entities",$data['entity']));
                           } else {
                              $entity="-";
                           }
                           
                        if (Session::isMultiEntitiesMode()) {
                           $pdf->setColumnsSize(12,27,25,18,18);
                           $pdf->displayLine(
                              $items->getTypeName(),
                              $name,
                              $entity,
                              (isset($data["serial"])? "".$data["serial"]."" :"-"),
                              (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
                              );
                        } else {
                           $pdf->setColumnsSize(25,31,22,22);
                           $pdf->displayTitle(
                              $items->getTypeName(),
                              $name,
                              (isset($data["serial"])? "".$data["serial"]."" :"-"),
                              (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
                              );
                        }
                     } // Each device
                  } // numrows device
            } // type right
         } // each type
      } // numrows type
   }


   /**
    * show for PDF the webapplications associated with a device
    *
    * @param $pdf
    * @param $item
    *
   **/
   static function PdfFromItems(PluginPdfSimplePDF $pdf, CommonGLPI $item){
      global $DB,$CFG_GLPI, $LANG;

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.$LANG['plugin_webapplications'][21].'</b>');

      $ID         = $item->getField('id');
      $itemtype   = get_Class($item);
      $canread    = $item->can($ID,'r');
      $canedit    = $item->can($ID,'w');
      $PluginWebapplicationsWebapplication = new PluginWebapplicationsWebapplication();

      $query = "SELECT `glpi_plugin_webapplications_webapplications`.* "
      ." FROM `glpi_plugin_webapplications_webapplications_items`,`glpi_plugin_webapplications_webapplications` "
      ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_webapplications_webapplications`.`entities_id`) "
      ." WHERE `glpi_plugin_webapplications_webapplications_items`.`items_id` = '".$ID."' 
         AND `glpi_plugin_webapplications_webapplications_items`.`itemtype` = '".$itemtype."' 
         AND `glpi_plugin_webapplications_webapplications_items`.`plugin_webapplications_webapplications_id` = `glpi_plugin_webapplications_webapplications`.`id` "
      . getEntitiesRestrictRequest(" AND ","glpi_plugin_webapplications_webapplications",'','',$PluginWebapplicationsWebapplication->maybeRecursive());
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (!$number) {
         $pdf->displayLine($LANG['search'][15]);
      } else {
         if (Session::isMultiEntitiesMode()) {
            $pdf->setColumnsSize(25,25,15,15,20);
            $pdf->displayTitle('<b><i>'.$LANG['plugin_webapplications'][0],
                                        $LANG['entity'][0],
                                        $LANG['plugin_webapplications'][3],
                                        $LANG['common'][35],
                                        $LANG['plugin_webapplications']['setup'][1].'</i></b>');
         } else {
            $pdf->setColumnsSize(30,30,20,20);
            $pdf->displayTitle('<b><i>'.$LANG['plugin_webapplications'][0],
                                        $LANG['plugin_webapplications'][3],
                                        $LANG['common'][35],
                                        $LANG['plugin_webapplications']['setup'][1].'</i></b>');
         }
         while ($data=$DB->fetch_array($result)) {
            $webapplicationsID = $data["id"];

            if (Session::isMultiEntitiesMode()) {
             $pdf->setColumnsSize(25,25,15,15,20);
             $pdf->displayLine($data["name"],
                               Html::clean(Dropdown::getDropdownName("glpi_entities",
                                                                     $data['entities_id'])),
                               Html::clean(getUsername("glpi_users", $data["users_id_tech"])),
                               Html::clean(Dropdown::getDropdownName("glpi_groups",
                                                                     $data["groups_id_tech"])),
                               Html::clean(Dropdown::getDropdownName("glpi_plugin_webapplications_webapplicationtypes",
                                                                     $data["plugin_webapplications_webapplicationtypes_id"])));
            } else {
               $pdf->setColumnsSize(50,25,25);
               $pdf->displayLine(
               $data["name"],
               Html::clean(getUsername("glpi_users", $data["users_id_tech"])),
               Html::clean(Dropdown::getDropdownName("glpi_groups", $data["groups_id_tech"])),
               Html::clean(Dropdown::getDropdownName("glpi_plugin_webapplications_webapplicationtypes",
                                                     $data["plugin_webapplications_webapplicationtypes_id"])));
            }
         }
      }
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         if ($item->getType()=='PluginWebapplicationsWebapplication'
             && count(PluginWebapplicationsWebapplication::getTypes(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry($LANG['document'][19], self::countForWebapplication($item));
            }
            return $LANG['document'][19];

         } else if (in_array($item->getType(), PluginWebapplicationsWebapplication::getTypes(true))
                    && plugin_webapplications_haveRight('webapplications', 'r')) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginWebapplicationsWebapplication::getTypeName(2), self::countForItem($item));
            }
            return PluginWebapplicationsWebapplication::getTypeName(2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
   
      $self = new self();
      
      if ($item->getType()=='PluginWebapplicationsWebapplication') {
         
         $self->showItemFromPlugin($item->getID());

      } else if (in_array($item->getType(), PluginWebapplicationsWebapplication::getTypes(true))) {
      
         $self->showPluginFromItems(get_class($item),$item->getField('id'));

      }
      return true;
   }
   
   static function countForWebapplication(PluginWebapplicationsWebapplication $item) {

      $types = implode("','", $item->getTypes());
      if (empty($types)) {
         return 0;
      }
      return countElementsInTable('glpi_plugin_webapplications_webapplications_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_webapplications_webapplications_id` = '".$item->getID()."'");
   }


   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_webapplications_webapplications_items',
                                  "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }


   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginWebapplicationsWebapplication') {
         self::ItemsPdf($pdf, $item);
      } else if (in_array($item->getType(), PluginWebapplicationsWebapplication::getTypes(true))) {
         self::PdfFromItems($pdf, $item);
      } else {
         return false;
      }
      return true;
   }

}
?>