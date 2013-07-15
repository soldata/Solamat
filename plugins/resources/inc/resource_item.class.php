<?php
/*
 * @version $Id: resource_item.class.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginResourcesResource_Item extends CommonDBTM {
	
	// From CommonDBRelation
   public $itemtype_1 = "PluginResourcesResource";
   public $items_id_1 = 'plugin_resources_resources_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';
   
   function canCreate() {
      return plugin_resources_haveRight('resources', 'w');
   }

   function canView() {
      return plugin_resources_haveRight('resources', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='PluginResourcesResource'
          && count(PluginResourcesResource::getTypes(false))) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry($LANG['document'][19], self::countForResource($item));
         }
         return $LANG['document'][14];

      } else if (in_array($item->getType(), PluginResourcesResource::getTypes(true))
                 && $this->canView() && !$withtemplate) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(PluginResourcesResource::getTypeName(2), self::countForItem($item));
         }
         return PluginResourcesResource::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      $self = new self();
      if ($item->getType()=='PluginResourcesResource') {
         $self->showItemFromPlugin($item->getID(),$withtemplate);

      } else if (in_array($item->getType(), PluginResourcesResource::getTypes(true))) {
         if ($item->getType() == 'User') {
            $self->showEmployeeFromUser(get_class($item),$item->getField('id'));
         } else {
            $self->showPluginFromItems(get_class($item),$item->getID());
         }
      }
      return true;
   }
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginResourcesResource') {
         self::pdfForResource($pdf, $item);

      } else if (in_array($item->getType(), PluginResourcesResource::getTypes(true))) {
         self::PdfFromItems($pdf, $item);

      } else {
         return false;
      }
      return true;
   }
   
   static function countForResource(PluginResourcesResource $item) {

      $types = implode("','", PluginResourcesResource::getTypes());
      if (empty($types)) {
         return 0;
      }
      return countElementsInTable('glpi_plugin_resources_resources_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_resources_resources_id` = '".$item->getID()."'");
   }


   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_resources_resources_items',
                                  "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }

	function getFromDBbyResourcesAndItem($plugin_resources_resources_id,$items_id,$itemtype) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."` " .
			"WHERE `plugin_resources_resources_id` = '" . $plugin_resources_resources_id . "' 
			AND `itemtype` = '" . $itemtype . "'
			AND `items_id` = '" . $items_id . "'";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
   
	function addItem($options) {
      
      if (!isset($options["plugin_resources_resources_id"]) 
            || $options["plugin_resources_resources_id"] <= 0) {
         return false;
      } else {
         $this->add(array('plugin_resources_resources_id'=>$options["plugin_resources_resources_id"],
                           'items_id'=>$options["items_id"],
                           'itemtype'=>$options["itemtype"]));

         if ($options["itemtype"] == 'User') {
         
            $values["id"] = $options["items_id"];
            $item = new PluginResourcesResource();
            $item->getFromDB($options["plugin_resources_resources_id"]);

            if (isset($item->fields["locations_id"])) {
               $values["locations_id"] = $item->fields["locations_id"];
            } else {
               $values["locations_id"] = 0;
            }
            $this->updateLocation($values,$options["itemtype"]);
            
         }
      }
   }

   function updateItem($ID,$comment) {
    
      if ($ID>0) {
         $values["id"] = $ID;
         $values["comment"] = $comment;
         $this->update($values);
      }
   }

   function deleteItem($ID) {

      $this->delete(array('id'=>$ID));
   }

   function deleteItemByResourcesAndItem($plugin_resources_resources_id,$items_id,$itemtype) {
    
      if ($this->getFromDBbyResourcesAndItem($plugin_resources_resources_id,$items_id,$itemtype)) {
         $this->delete(array('id'=>$this->fields["id"]));
      }
   }
  
   function updateLocation($values,$itemtype) {
      global $DB,$LANG;
      
      $id = 0;
      if ($itemtype == "PluginResourcesResource") {
         $restrict = "`itemtype` = 'User' 
                     AND `plugin_resources_resources_id` = '".$values["id"]."'";
         $resources = getAllDatasFromTable($this->getTable(),$restrict);

         if (!empty($resources)) {
            foreach ($resources as $resource) {
              $id = $resource["items_id"];
            }
         }
      } else if ($itemtype == "User") {
         $id = $values["id"];
      }
      if (isset($id) && $id > 0 && isset($values["locations_id"]) && $values["locations_id"] > 0) {
         
         $item = new User();
         $update["id"] = $id;
         $update["locations_id"] = $values["locations_id"];
         if ($itemtype == "PluginResourcesResource")
            $update["_UpdateFromResource_"] = 1;
         if ($item->update($update))
            Session::addMessageAfterRedirect($LANG['plugin_resources']['mailing'][17],true);
      }
   }
  
   function searchAssociatedBadge($ID) {
    
      $plugin = new Plugin();
      $PluginResourcesResource = new PluginResourcesResource();
      
      if ($plugin->isActivated("badges")) {

         //search is the user have a linked badge
         $restrict = "`itemtype` = 'User' 
                     AND `plugin_resources_resources_id` = '".$ID."'";
         $resources = getAllDatasFromTable($this->getTable(),$restrict);

         if (!empty($resources)) {
            foreach ($resources as $resource) {
               $restrictbadge = "`users_id` = '".$resource["items_id"]."'";
               $badges = getAllDatasFromTable("glpi_plugin_badges_badges",$restrictbadge);
               //if the user have a linked badge, send email for his badge
               if (!empty($badges)) {
                  foreach ($badges as $badge)
                     return $badge["id"];
               } else {
                  return 0;
               }
            }
         }
      }
   }
  
   function dropdownItems($ID,$used=array()) {
      global $DB,$CFG_GLPI, $LANG;

      $restrict = "`plugin_resources_resources_id` = '$ID'";
      $resources = getAllDatasFromTable($this->getTable(),$restrict);

      echo "<select name='item_item'>";
      echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>";

      if (!empty($resources)) {

        foreach ($resources as $resource) {
            
            $table = getTableForItemType($resource["itemtype"]);
             
            $query = "SELECT `".$table."`.*
                     FROM `".$this->getTable()."`
                     INNER JOIN `".$table."` ON (`".$table."`.`id` = `".$this->getTable()."`.`items_id`)
                     WHERE `".$this->getTable()."`.`itemtype` = '".$resource["itemtype"]."'
                     AND `".$this->getTable()."`.`items_id` = '".$resource["items_id"]."' ";
            if (count($used)) {
               $query .= " AND `".$table."`.`id` NOT IN (0";
               foreach ($used as $ID)
                  $query .= ",$ID";
               $query .= ")";
            }
            $query .= " ORDER BY `".$table."`.`name`";
            $result_linked=$DB->query($query);

            if ($DB->numrows($result_linked)) {

               if ($data=$DB->fetch_assoc($result_linked)) {
                  $name=$data["name"];
                  if ($resource["itemtype"]=='User')
                     $name=getUserName($data["id"]);
                  echo "<option value='".$data["id"].",".$resource["itemtype"]."'>".$name;
                  if (empty($data["name"]) || $_SESSION["glpiis_ids_visible"] == 1 ) {
                     echo " (";
                     echo $data["id"].")";
                     }
                  echo "</option>";
               }
            }
         }
      }
      echo "</select>";
   }
   
   function showItemFromPlugin($instID,$withtemplate='') {
      global $DB,$CFG_GLPI,$LANG;

      if (!$this->canView())	return false;

      $PluginResourcesResource=new PluginResourcesResource();
      if ($PluginResourcesResource->getFromDB($instID)) {
      
         $canedit=$PluginResourcesResource->can($instID,'w');
         $rand=mt_rand();
      
         $query = "SELECT DISTINCT `itemtype` 
             FROM `".$this->getTable()."` 
             WHERE `plugin_resources_resources_id` = '$instID' 
             ORDER BY `itemtype` ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
      
         if (Session::isMultiEntitiesMode()) {
            $colsup=1;
         } else {
            $colsup=0;
         }
      
         echo "<form method='post' name='resourcesitem_form$rand' id='resourcesitem_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php\">";
    
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='".($canedit?(6+$colsup):(5+$colsup))."'>";
         echo $LANG['plugin_resources'][7].":</th>";
         echo "</tr>";
         echo "<tr>";
         if ($canedit && $withtemplate<2) {
            echo "<th>&nbsp;</th>";
            echo "<th>&nbsp;</th>";
         }
         echo "<th>".$LANG['common'][17]."</th>";
         echo "<th>".$LANG['common'][16]."</th>";
         if (Session::isMultiEntitiesMode())
            echo "<th>".$LANG['entity'][0]."</th>";
         echo "<th>".$LANG['common'][19]."</th>";
         echo "<th>".$LANG['common'][20]."</th>";

         echo "</tr>";
      
         for ($i=0 ; $i < $number ; $i++) {
            $type=$DB->result($result, $i, "itemtype");
            if (!class_exists($type)) {
               continue;
            }           
            $item = new $type();
            if ($item->canView()) {
               $column="name";
               $table = getTableForItemType($type);
          
               $query = "SELECT `".$table."`.*, 
                                 `".$this->getTable()."`.`id` AS items_id, 
                                 `".$this->getTable()."`.`comment` AS comment, 
                                 `glpi_entities`.`id` AS entity "
                ." FROM `".$this->getTable()."`, `".$table
                ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
                ." WHERE `".$table."`.`id` = `".$this->getTable()."`.`items_id` 
                AND `".$this->getTable()."`.`itemtype` = '$type' 
                AND `".$this->getTable()."`.`plugin_resources_resources_id` = '$instID' ";
               if ($type!='User')
                  $query.=getEntitiesRestrictRequest(" AND ",$table,'','',$item->maybeRecursive());

               if ($item->maybeTemplate()) {
                  $query.=" AND ".$table.".is_template='0'";
               }
               $query.=" ORDER BY `glpi_entities`.`completename`, `".$table."`.`$column` ";
          
               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)) {
                     Session::initNavigateListItems($type,$LANG['plugin_resources']['title'][1]." = ".$PluginResourcesResource->fields['name']);

                     while ($data=$DB->fetch_assoc($result_linked)) {
                        
                        $item->getFromDB($data["id"]);
                        
                        Session::addToNavigateListItems($type,$data["id"]);
                        
                        if ($type=='User') {
                           $format=formatUserName($data["id"],$data["name"],$data["realname"],$data["firstname"],1);
                        } else {
                           $format=$data["name"];
                        }
                        $ID="";
                        if($_SESSION["glpiis_ids_visible"]||empty($data["name"])) 
                           $ID = " (".$data["id"].")";
                        $link=Toolbox::getItemTypeFormURL($type);
                        $name= "<a href=\"".$link."?id=".$data["id"]."\">".$format;
                        if ($type!='User')
                           $name.= "&nbsp;".$ID;
                        $name.= "</a>";
                        
                        $items_id=$data["items_id"];
                        
                        echo "<tr class='tab_bg_1'>";

                        if ($canedit && $withtemplate<2) {
                           echo "<td width='10'>";
                           $sel="";
                           if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
                           echo "<input type='checkbox' name='item[".$data["items_id"]."]' value='1' $sel>";
                           echo "</td>";
                           echo "<td width='10'>";
                           
                           echo "<img src='".$CFG_GLPI["root_doc"]."/pics/expand.gif' onclick=\"plugin_resources_show_item('comment$items_id$rand',this,'".$CFG_GLPI["root_doc"]."/pics/collapse.gif');\">";
                           echo "</td>";
                        }
                        echo "<td class='center'>".$item->getTypeName()."</td>";
                        echo "<td class='center' ".(isset($data['is_deleted'])&&$data['is_deleted']?"class='tab_bg_2_2'":"").">".$name."</td>";
                        if (Session::isMultiEntitiesMode())
                           if ($type!='User') {
                              echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entity'])."</td>";
                           } else {
                              echo "<td class='center'>-</td>";
                           }
                        echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                        echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";

                        echo "</tr>";

                        echo "<tr class='tab_bg_1'>";
                        $class = "class='plugin_resources_show'";
                        
                        if (!isset($data["comment"]) || empty($data["comment"])) {
                           $data["comment"]='';
                           $class = "class='plugin_resources_hide'";
                        }
                        echo "<td colspan='7' id='comment$items_id$rand' $class >";
                        echo $LANG['common'][25];
                        echo "<br><textarea cols='100' rows='1' name='comment$items_id' >";
                        echo $data["comment"];
                        echo "</textarea><br><br>";
                        echo "<input type='hidden' name='items_id' value='".$data["items_id"]."'>";
                        if($canedit && $withtemplate<2) {
                           if (!isset($data["comment"]) || empty($data["comment"]))
                              echo "<input type='submit' name='updatecomment[".$items_id."]' value=\"".$LANG['buttons'][8]."\" class='submit'>";
                           else
                              echo "<input type='submit' name='updatecomment[".$items_id."]' value=\"".$LANG['buttons'][7]."\" class='submit'>";			
                        }
                        echo "</td>";
                        echo "</tr>";
                     }
                  }
            }
         }
    
         if ($canedit && $withtemplate<2) {
            echo "<tr class='tab_bg_1'><td colspan='".(4+$colsup)."' class='right'>";
            echo "<input type='hidden' name='plugin_resources_resources_id' value='$instID'>";
            $types=PluginResourcesResource::getTypes();
            $plugin = new Plugin();
            if ($plugin->isActivated("badges"))
               $types[]='PluginBadgesBadge';			
            Dropdown::showAllItems("items_id",0,0,$PluginResourcesResource->fields['entities_id'],$types);

            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>" ;
            Html::openArrowMassives("resourcesitem_form$rand", true);
            Html::closeArrowMassives(array('deleteitem' => $LANG['buttons'][6]));

         } else {
    
            echo "</table></div>";
         }
         Html::closeForm();
      }
   }
  
   function showPluginFromItems($itemtype,$ID,$withtemplate='') {
      global $DB,$CFG_GLPI,$LANG;
    
      $item = new $itemtype();
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');
      
      $PluginResourcesResource=new PluginResourcesResource();
      
      $query = "SELECT `".$this->getTable()."`.`id` AS items_id,`glpi_plugin_resources_resources`.* "
       ." FROM `".$this->getTable()."`,`glpi_plugin_resources_resources` "
       ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_resources_resources`.`entities_id`) "
       ." WHERE `".$this->getTable()."`.`items_id` = '".$ID."' 
       AND `".$this->getTable()."`.`itemtype` = '".$itemtype."' 
       AND `".$this->getTable()."`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id`  
       AND `glpi_plugin_resources_resources`.`is_template` = '0' ";
       //. getEntitiesRestrictRequest(" AND ","glpi_plugin_accounts_accounts",'','',$PluginResourcesResource->maybeRecursive());
       $query.= "ORDER BY `glpi_plugin_resources_resources`.`name`";
    
      $result = $DB->query($query);
      $number = $DB->numrows($result);
    
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
    
      if ($withtemplate!=2) echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php\">";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='".(9+$colsup)."'>".$LANG['plugin_resources'][9].":</th></tr>";
      echo "<tr><th>".$LANG['plugin_resources'][8]."</th>";
      echo "<th>".$LANG['plugin_resources'][18]."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['plugin_resources'][5]."</th>";
      echo "<th>".$LANG['plugin_resources'][20]."</th>";
      echo "<th>".$LANG['plugin_resources'][14]."</th>";
      echo "<th>".$LANG['plugin_resources'][11]."</th>";
      echo "<th>".$LANG['plugin_resources'][13]."</th>";
    
      if ($this->canCreate()) {
         echo "<th>&nbsp;</th>";
      }
      echo "</tr>";
      $used=array();
      while ($data=$DB->fetch_array($result)) {
         $resourcesID=$data["id"];
         $used[]=$resourcesID;
         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

         if ($withtemplate!=3 && $canread && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) || $data["is_recursive"])) {
            echo "<td class='center'>";
            echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php?id=".$data["id"]."'>";
            echo $data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
            echo "</a></td>";
         } else {
            echo "<td class='center'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
            echo "</td>";
         }
         echo "<td class='center'>".$data['firstname']."</td>";
         if (Session::isMultiEntitiesMode()) {
            echo "<td class='center'>";
            echo Dropdown::getDropdownName("glpi_entities",$data['entities_id']);
            echo "</td>";
         }
         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_locations",$data["locations_id"]);
         echo "</td>";
         
         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_plugin_resources_contracttypes"
                                                   ,$data["plugin_resources_contracttypes_id"]);
         echo "</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_plugin_resources_departments"
                                                      ,$data["plugin_resources_departments_id"]);
         echo "</td>";

         echo "<td class='center'>".Html::convDate($data["date_begin"])."</td>";
         if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"])) {
            echo "<td class='center'>";
            echo "<span class='plugin_resources_date_color'>";
            echo Html::convDate($data["date_end"]);
            echo "</span>";
            echo "</td>";
         } else if (empty($data["date_end"])) {
            echo "<td class='center'>".$LANG['plugin_resources'][25]."</td>";
         } else {
            echo "<td class='center'>".Html::convDate($data["date_end"])."</td>";
         }
         if ($this->canCreate()) {
            echo "<td class='center' class='tab_bg_2'>";
            Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/resources/front/resource.form.php',
                                    'deleteresources',
                                    $LANG['buttons'][6],
                                    array('id' => $data["items_id"]));
            echo "</td>";
         } else {
            echo "<td></td>";
         }
         echo "</tr>";

      }
    
      if ($canedit) {
         
         $entities="";
         if ($item->isRecursive()) {
            $entities = getSonsOf('glpi_entities',$item->getEntityID());
         } else {
            $entities = $item->getEntityID();
         }   

         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_resources_resources",'',$entities,false);
         
         $q="SELECT COUNT(*) 
           FROM `glpi_plugin_resources_resources` 
           WHERE `is_deleted` = '0' 
           AND `is_template` = '0'";
         $q.=" $limit";
         
         $result = $DB->query($q);
         $nb = $DB->result($result,0,0);
      
         if ($nb>count($used)) {
        
            if ($this->canCreate()) {
          
               echo "<tr class='tab_bg_1'><td class='right' colspan='".(7+$colsup)."'>";
               echo "<input type='hidden' name='items_id' value='$ID'>";
               echo "<input type='hidden' name='itemtype' value='$itemtype'>";
            
               PluginResourcesResource::dropdown(array('entity' => $entities,
                                                         'used'   => $used));
               echo "</td><td class='center'>";
               echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
               echo "</td>";
               echo "</tr>";
            }
         }
      }
      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='".(9+$colsup)."' class='right'>";
         echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/setup.templates.php?add=1'>";
         echo $LANG['plugin_resources'][31];
         echo "</a>";
         echo "</td>";
         echo "</tr>";
      }
      
      echo "</table></div>";
      Html::closeForm();
   }
  
   function showEmployeeFromUser($itemtype,$ID) {
      global $DB,$CFG_GLPI,$LANG;

      $item = new $itemtype();
      $canedit = $item->can($ID,'w');

      $query = "SELECT `".$this->getTable()."`.`id` AS items_id,`glpi_plugin_resources_resources`.*
            FROM `".$this->getTable()."`,`glpi_plugin_resources_resources`
            WHERE `items_id` = '".$ID."'
            AND `itemtype` = '".$itemtype."'
            AND `".$this->getTable()."`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id`
            AND `glpi_plugin_resources_resources`.`is_template` = '0' ";

      if (!plugin_resources_haveRight("all","w"))
         $query.= " AND `glpi_plugin_resources_resources`.`users_id_recipient` = '".$_SESSION["glpiname"]."' ";

      $query.= " ORDER BY `glpi_plugin_resources_resources`.`name` ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php\">";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='9'>".$LANG['plugin_resources'][9].":</th></tr>";
      echo "<tr><th>".$LANG['plugin_resources'][8]."</th>";
      echo "<th>".$LANG['plugin_resources'][18]."</th>";
      echo "<th>".$LANG['plugin_resources'][5]."</th>";
      echo "<th>".$LANG['plugin_resources'][20]."</th>";
      echo "<th>".$LANG['plugin_resources'][14]."</th>";
      echo "<th>".$LANG['plugin_resources'][11]."</th>";
      echo "<th>".$LANG['plugin_resources'][13]."</th>";

      if ($this->canCreate()) {
         echo "<th>&nbsp;</th>";
      }
      echo "</tr>";
      $resourcesID=0;
      $used=array();
      while ($data=$DB->fetch_array($result)) {

         $resourcesID=$data["id"];
         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
         echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php?id=".$data["id"]."'>".$data["name"];
         if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
         echo "</a></td>";
         echo "<td class='center'>".$data['firstname']."</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_locations",$data["locations_id"]);
         echo "</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_plugin_resources_contracttypes",
                                                      $data["plugin_resources_contracttypes_id"]);
         echo "</td>";
         echo "<td class='center'>";
         echo Dropdown::getDropdownName("glpi_plugin_resources_departments",
                                                         $data["plugin_resources_departments_id"]);
         echo "</td>";

         echo "<td class='center'>".Html::convDate($data["date_begin"])."</td>";
         if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"])) {
            echo "<td class='center'>";
            echo "<span class='plugin_resources_date_color'>";
            echo Html::convDate($data["date_end"]);
            echo "</span>";
            echo "</td>";
         } else if (empty($data["date_end"])) {
            echo "<td class='center'>".$LANG['plugin_resources'][25]."</td>";
         } else {
            echo "<td class='center'>".Html::convDate($data["date_end"])."</td>";
         }
         if ($this->canCreate()) {
            echo "<td class='center tab_bg_2'>";
            Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/resources/front/resource.form.php',
                                    'deleteresources',
                                    $LANG['buttons'][6],
                                    array('id' => $data["items_id"]));
            echo "</td>";
         }
         echo "</tr>";

      }

      if ($number == 0){
         if ($canedit) {

            $entities = Profile_User::getUserEntities($ID,1);

            $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_resources_resources",'',$entities,false);

            $q="SELECT COUNT(*)
           FROM `glpi_plugin_resources_resources`
           WHERE `is_deleted` = '0'
           AND `is_template` = '0'";
            $q.=" $limit";

            $result = $DB->query($q);
            $nb = $DB->result($result,0,0);

            if ($nb>0) {
               if ($this->canCreate()) {

                  $restrict = "`itemtype` = 'User' ";
                  $resources = getAllDatasFromTable($this->getTable(),$restrict);
                  if(!empty($resources)){
                     foreach ($resources as $resource){
                        $used[]=$resource['plugin_resources_resources_id'];
                     }
                  }
                  echo "<tr class='tab_bg_1'><td class='right' colspan='7'>";
                  echo "<input type='hidden' name='items_id' value='$ID'>";
                  echo "<input type='hidden' name='itemtype' value='$itemtype'>";

                  PluginResourcesResource::dropdown(array('entity' => $entities,
                     'used'   => $used));

                  echo "</td><td class='tab_bg_2 center'>";
                  echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";

                  echo "</td>";
                  echo "</tr>";
               }
            }
         }
      }

      echo "</table></div>";
      Html::closeForm();

      $PluginResourcesEmployee=new PluginResourcesEmployee();
      $PluginResourcesEmployee->showForm($resourcesID,$ID,0);
   }
   
   /**
    * Show for PDF an resources - asociated devices
    * 
    * @param $pdf object for the output
    * @param $ID of the resources
    */
   static function pdfForResource(PluginPdfSimplePDF $pdf, PluginResourcesResource $appli) {
      global $DB,$CFG_GLPI, $LANG;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_resources_haveRight("resources","r")) {
         return false;
      }

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.$LANG['plugin_resources'][7].'</b>');
         
      $query = "SELECT DISTINCT `itemtype` 
               FROM `glpi_plugin_resources_resources_items` 
               WHERE `plugin_resources_resources_id` = '$ID' 
               ORDER BY `itemtype` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (Session::isMultiEntitiesMode()) {
         $pdf->setColumnsSize(12,27,25,18,18);
         $pdf->displayTitle(
            '<b><i>'.$LANG['common'][17],
            $LANG['common'][16],
            $LANG['entity'][0],
            $LANG['common'][19],
            $LANG['common'][20].'</i></b>'
            );
      } else {
         $pdf->setColumnsSize(25,31,22,22);
         $pdf->displayTitle(
            '<b><i>'.$LANG['common'][17],
            $LANG['common'][16],
            $LANG['common'][19],
            $LANG['common'][20].'</i></b>'
            );
      }

      if (!$number) {
         $pdf->displayLine($LANG['search'][15]);						
      } else { 
         for ($i=0 ; $i < $number ; $i++) {
            $type=$DB->result($result, $i, "itemtype");
            if (!($item = getItemForItemtype($type))) {
               continue;
            }
            if ($item->canView()) {
               $column="name";
               $table = getTableForItemType($type);
               $items = new $type();
               
               $query = "SELECT `".$table."`.*, `glpi_entities`.`id` AS entity "
               ." FROM `glpi_plugin_resources_resources_items`, `".$table
               ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
               ." WHERE `".$table."`.`id` = `glpi_plugin_resources_resources_items`.`items_id` 
                  AND `glpi_plugin_resources_resources_items`.`itemtype` = '$type' 
                  AND `glpi_plugin_resources_resources_items`.`plugin_resources_resources_id` = '$ID' ";
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
    * show for PDF the resources associated with a device
    * 
    * @param $ID of the device
    * @param $itemtype : type of the device
    * 
    */
   static function PdfFromItems($pdf, $item) {
      global $DB,$CFG_GLPI, $LANG;
      
      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.$LANG['plugin_resources'][9].'</b>');
      
      $ID = $item->getField('id');
      $itemtype = get_Class($item);
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');
      
      $PluginResourcesResource=new PluginResourcesResource(); 
      
      $query = "SELECT `glpi_plugin_resources_resources`.* "
      ." FROM `glpi_plugin_resources_resources_items`,`glpi_plugin_resources_resources` "
      ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_resources_resources`.`entities_id`) "
      ." WHERE `glpi_plugin_resources_resources_items`.`items_id` = '".$ID."' 
         AND `glpi_plugin_resources_resources_items`.`itemtype` = '".$itemtype."' 
         AND `glpi_plugin_resources_resources_items`.`plugin_resources_resources_id` = `glpi_plugin_resources_resources`.`id` "
      . getEntitiesRestrictRequest(" AND ","glpi_plugin_resources_resources",'','',$PluginResourcesResource->maybeRecursive());
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (!$number) {
         $pdf->displayLine($LANG['search'][15]);
      } else {
         if (Session::isMultiEntitiesMode()) {
            $pdf->setColumnsSize(14,14,14,14,14,14,16);
            $pdf->displayTitle(
               '<b><i>'.$LANG['plugin_resources'][18],
               $LANG['entity'][0],
               $LANG['plugin_resources'][5],
               $LANG['plugin_resources'][20],
               $LANG['plugin_resources'][14],
               $LANG['plugin_resources'][11],
               $LANG['plugin_resources'][13].'</i></b>'
               );
         } else {
            $pdf->setColumnsSize(17,17,17,17,17,17);
            $pdf->displayTitle(
               '<b><i>'.$LANG['plugin_resources'][18],
               $LANG['plugin_resources'][5],
               $LANG['plugin_resources'][20],
               $LANG['plugin_resources'][14],
               $LANG['plugin_resources'][11],
               $LANG['plugin_resources'][13].'</i></b>'
               );
         }
         while ($data=$DB->fetch_array($result)) {
            $resourcesID=$data["id"];
      
            if (Session::isMultiEntitiesMode()) {
               $pdf->setColumnsSize(14,14,14,14,14,14,16);
               $pdf->displayLine(
                  $data["name"],
                  Html::clean(Dropdown::getDropdownName("glpi_entities",$data['entities_id'])),
                  Html::clean(Dropdown::getDropdownName("glpi_locations",$data["locations_id"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_contracttypes",$data["plugin_resources_contracttypes_id"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_departments",$data["plugin_resources_departments_id"])),
                  Html::convDate($data["date_begin"]),
                  Html::convDate($data["date_end"])
                  );
            } else {
               $pdf->setColumnsSize(17,17,17,17,17,17);
               $pdf->displayLine(
                  $data["name"],
                  Html::clean(Dropdown::getDropdownName("glpi_locations",$data["locations_id"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_contracttypes",$data["plugin_resources_contracttypes_id"])),
                  Html::clean(Dropdown::getDropdownName("glpi_plugin_resources_departments",$data["plugin_resources_departments_id"])),
                  Html::convDate($data["date_begin"]),
                  Html::convDate($data["date_end"])
                  );
            }
         }		
      }
   }
}

?>