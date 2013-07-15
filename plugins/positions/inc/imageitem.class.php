<?php
/*
 * @version $Id: HEADER 15930 2013-02-07 09:47:55Z tsmr $
 -------------------------------------------------------------------------
 Positions plugin for GLPI
 Copyright (C) 2003-2011 by the Positions Development Team.

 https://forge.indepnet.net/projects/positions
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Positions.

 Positions is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Positions is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Positions. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginPositionsImageItem extends CommonDBTM {
   
   function canCreate() {
      return plugin_positions_haveRight('positions', 'w');
   }

   function canView() {
      return plugin_positions_haveRight('positions', 'r');
   }
   
   static function showAllItems($myname,$value_type=0,$value=0,$entity_restrict=-1,$types, $locations_id=-1) {
      global $DB,$LANG,$CFG_GLPI;
      
      $rand=mt_rand();

      echo "<table border='0'><tr><td>\n";

      echo "<select name='type' id='item_type$rand'>\n";
      echo "<option value='0;0'>".Dropdown::EMPTY_VALUE."</option>\n";
      
      if($myname=='type'){
         $newtypes = array_flip($types);
         unset($newtypes['Location']);
         unset($newtypes['Netpoint']);
         $types = array_flip($newtypes);
      }
      
      foreach ($types as $type => $label) {
         
         $item = new $label();

         if ($myname == 'type') {
            $table = getTableForItemType($label."Type");
         } else {
            $table = getTableForItemType($label);
         }
         echo "<option value='".$label.";".$table."'>".
               $item->getTypeName()."</option>\n";
      }

      echo "</select>";

      $params=array('typetable'       => '__VALUE__',
                    'value'           => $value,
                    'myname'          => $myname,
                    'entity_restrict' => $entity_restrict,
                    'locations_id' => $locations_id);

      Ajax::updateItemOnSelectEvent("item_type$rand", "show_$myname$rand",$CFG_GLPI["root_doc"].
                                    "/plugins/positions/ajax/dropdownAllItems.php",$params);

      echo "</td><td>\n"	;
      echo "<span id='show_$myname$rand'>&nbsp;</span>\n";
      echo "</td></tr></table>\n";

      if ($value>0) {
         echo "<script type='text/javascript' >\n";
         echo "document.getElementById('item_type$rand').value='".$value_type."';";
         echo "</script>\n";

         $params["typetable"]=$value_type;
         Ajax::updateItem("show_$myname$rand",$CFG_GLPI["root_doc"].
                           "/plugins/positions/ajax/dropdownAllItems.php",$params);
      }
      return $rand;
   }
   
   function getFromDBbyType($itemtype, $type) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `itemtype` = '$itemtype'
                      AND `type` = '$type'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
         return false;
      }
      return false;
   }


   function addItemImage($values) {
      global $DB;

      if ($values["type"] != '-1') {
         if ($this->GetfromDBbyType($values["itemtype"],$values["type"])) {
            $this->update(array('id'  => $this->fields['id'],
                                'img' => $values["img"]));
         } else {
            $this->add(array('itemtype' => $values["itemtype"],
                             'type'     => $values["type"],
                             'img'      => $values["img"]));
         }
      } else {
         $query = "SELECT * 
                   FROM `".getTableForItemType($values["itemtype"]."Type")."` ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         $i = 0;
         while ($i < $number) {
            $type_table = $DB->result($result, $i, "id");
            if ($this->GetfromDBbyType($values["itemtype"],$type_table)) {
            $this->update(array('id'  => $this->fields['id'],
                                'img' => $values["img"]));
           } else {
             $this->add(array('itemtype' => $values["itemtype"],
                              'type'     => $type_table,
                              'img'      => $values["img"]));
            }
            $i++;
         }
      }
   }


   function deleteItemImage($ID) {
      $this->delete(array('id' => $ID));
   }
   
   /**
    * Show dropdown of uploaded files
    *
    * @param $myname dropdown name
   **/
   static function showUploadedFilesDropdown($myname) {
      global $CFG_GLPI, $LANG;

      if (is_dir(GLPI_PLUGIN_DOC_DIR."/positions/pics")) {
         $uploaded_files = array();

         if ($handle = opendir(GLPI_PLUGIN_DOC_DIR."/positions/pics")) {
            while (false !== ($file = readdir($handle))) {
               if ($file != "." && $file != "..") {
                  $uploaded_files[] = $file;
               }
            }
            closedir($handle);
         }

         if (count($uploaded_files)) {
            echo "<select name='$myname' id='imageitem'>";
            echo "<option value=''>".Dropdown::EMPTY_VALUE."</option>";
            asort($uploaded_files);
            foreach ($uploaded_files as $key => $val) {
               echo "<option value='$val'>$val</option>";
            }
            echo "</select>";

         } else {
            echo $LANG['document'][37];
         }

      } else {
         echo $LANG['document'][35];
      }
   }

   function showConfigForm() {
      global $DB,$LANG,$CFG_GLPI;
      
      echo "<form method='post' action='./imageitem.form.php' name='imageitemform'>";
      
      echo "<table class='tab_cadre_fixe' cellpadding='5'><tr><th colspan='5'>";
      echo $LANG['plugin_positions']['setup'][2]." : </th></tr>";
      echo "<tr class='tab_bg_1'><td colspan='5' class='center'>";
      echo "<span class='upload' id='container'>";
      echo "&nbsp;<img src='../pics/select.png' id='pickfiles' title=\"".$LANG['plugin_positions']['setup'][12]."\">";
      echo "&nbsp;<span class='upload' id='filelist'></span>";
      echo "&nbsp;<img src='../pics/upload.png' id='uploadfiles' title=\"".$LANG['plugin_positions']['setup'][14]."\">";
      echo "&nbsp;<a href='".$_SERVER['PHP_SELF']."'><img src='../pics/refresh.png' title=\"".$LANG['buttons'][7]."\"></a>";
      echo "</span>";
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1'><td>";
      $types = PluginPositionsPosition::getTypes();
      self::showAllItems("type",0,0,$_SESSION["glpiactive_entity"],$types,-1);
      echo "</td><td>";
      Html::showToolTip(nl2br($LANG['plugin_positions']['setup'][3]));
      echo "<input type='hidden' name='_glpi_csrf_token' value=''>";
      echo "</td><td>";
      self::showUploadedFilesDropdown("img");
      echo "</td><td>";
      echo "<div id=\"imageitemPreview\"></div>";
      echo "</td><td>";
      echo "<div align='center'><input type='submit' name='add' value=\"".$LANG['buttons'][2].
            "\" class='submit' ></div></td></tr>";			
      echo "</table>";
      Html::closeForm();

      $query = "SELECT * 
                FROM `".$this->getTable()."` 
                ORDER BY `itemtype`,`type` ASC;";
      $i = 0;
      if ($result = $DB->query($query)) {
         $number = $DB->numrows($result);
         if ($number != 0) {
            echo "<form method='post' name='massiveaction_form' id='massiveaction_form' action='".
                  "./imageitem.form.php'>";
            echo "<div id='liste'>";
            echo "<table class='tab_cadre_fixe' cellpadding='5'>";
            echo "<tr>";
            echo "<th class='left'>".$LANG['plugin_positions']['setup'][4]."</th>";
            echo "<th class='left'>".$LANG['plugin_positions']['setup'][5]."</th>";
            echo "<th class='left'>".$LANG['plugin_positions']['setup'][6]."</th><th></th>";
            if ($number > 1) {
               echo "<th class='left'>".$LANG['plugin_positions']['setup'][4]."</th>";
               echo "<th class='left'>".$LANG['plugin_positions']['setup'][5]."</th>";
               echo "<th class='left'>".$LANG['plugin_positions']['setup'][6]."</th><th></th>";
            }
            echo "</tr>";

            while($ligne= mysql_fetch_array($result)) {
               $ID = $ligne["id"];
               if ($i  % 2==0 && $number>1) {
                  echo "<tr class='tab_bg_1'>";
               }
               if ($number == 1) {
                  echo "<tr class='tab_bg_1'>";
               }
               $item = new $ligne["itemtype"]();
               echo "<td>".$item->getTypeName()."</td>";
               $class = $ligne["itemtype"]."Type";
               $typeclass = new $class();
               $typeclass->getFromDB($ligne["type"]);
               $name = $ligne["type"];
               if (isset($typeclass->fields["name"]))
                  $name = $typeclass->fields["name"];
               echo "<td>".$name."</td>";
               echo "<td>";
               
               echo "<object data='".$CFG_GLPI['root_doc']."/plugins/positions/front/map.send.php?file=".$ligne["img"]."&type=pics'>
                      <param name='src' value='".$CFG_GLPI['root_doc'].
                       "/plugins/positions/front/map.send.php?file=".$ligne["img"]."&type=pics'>
                     </object> ";
               echo "<td>";
               echo "<input type='hidden' name='id' value='$ID'>";
               echo "<input type='checkbox' name='item[$ID]' value='1'>";
               echo "</td>";

               $i++;
               if (($i == $number) && ($number % 2 !=0) && $number >1) {
                  echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
               }
            }

            echo "<tr class='tab_bg_1'>";

            if ($number > 1) {
               echo "<td colspan='8' class='center'>";
            } else {
               echo "<td colspan='4' class='center'>";
            }
            echo "<a onclick= \"if (markCheckboxes ('massiveaction_form')) return false;\" 
                  href='#'>".$LANG['buttons'][18]."</a>";
            echo " - <a onclick= \"if ( unMarkCheckboxes ('massiveaction_form') ) return false;\" 
                  href='#'>".$LANG['buttons'][19]."</a> ";
            echo "<input type='submit' name='delete' value=\"".$LANG['buttons'][6].
                     "\" class='submit'>";
            echo "</td></tr>";
            echo "</table>";
            echo "</div>";
            Html::closeForm();
         }
      }
   }


   function displayItemImage($type,$itemtype) {
      global $DB;

      $path = GLPI_PLUGIN_DOC_DIR."/positions/pics";

      //$image_name = GLPI_ROOT."/plugins/positions/pics/nothing.png";
      $image_name = "";
      $query = "SELECT *
                FROM `glpi_plugin_positions_imageitems`
                WHERE `itemtype` = '$itemtype'";

      if ($result = $DB->query($query)) {
         while ($ligne= mysql_fetch_array($result)) {
            $config_img = $ligne["img"];
            if ($type == $ligne["type"]) {
               //$image_name = $path."/$config_img";
               $image_name = $config_img;
            }
         }
      }
      return $image_name;
   }

}

?>