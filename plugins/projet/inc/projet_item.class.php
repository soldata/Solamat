<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginProjetProjet_Item extends CommonDBTM {

	// From CommonDBRelation
   public $itemtype_1 = "PluginProjetProjet";
   public $items_id_1 = 'plugin_projet_projets_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';
   
   function canCreate() {
      return plugin_projet_haveRight('projet', 'w');
   }

   function canView() {
      return plugin_projet_haveRight('projet', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='PluginProjetProjet'
          && count(PluginProjetProjet::getTypes(false))) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry($LANG['document'][19], self::countForProjet($item, true));
         }
         return $LANG['document'][14];

      } else if (in_array($item->getType(), PluginProjetProjet::getTypes(true))
                 && $this->canView() && !$withtemplate) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(PluginProjetProjet::getTypeName(2), self::countForItem($item));
         }
         return PluginProjetProjet::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      $self = new self();
      $projet = new PluginProjetProjet();
      if ($item->getType()=='PluginProjetProjet') {
         $self->showItemFromPlugin($item->getID(),$withtemplate, true);
         
      } else if (in_array($item->getType(), PluginProjetProjet::getTypes(true))) {
         
         switch (get_class($item)) {
			case 'User' :
			case 'Group' :
				$projet->showUsers(get_class($item),$item->getID());
				$self->showPluginFromItems(get_class($item),$item->getID());
            break;
         case 'Ticket' :
			case 'Problem' :
				self::showForHelpdesk($item);
            break;
			default :
				if (in_array(get_class($item), PluginProjetProjet::getTypes())) {
					$self->showPluginFromItems(get_class($item),$item->getID());
				}
            break;
         }
      }
      return true;
   }
   
   static function countForProjet(PluginProjetProjet $item, $material = false) {

      $types = implode("','", PluginProjetProjet::getTypes());
      if (empty($types)) {
         return 0;
      }
      
      if ($material) {
            $select = "'User','Group','Supplier','Contract'";
            $in = "NOT IN";
      } else {
         $select = "'User','Group','Supplier'";
         $in = "IN";
      }

      return countElementsInTable('glpi_plugin_projet_projets_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_projet_projets_id` = '".$item->getID()."' 
                                   AND `itemtype` $in (".$select.")");
   }


   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_projet_projets_items',
                                  "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }

	function getFromDBbyProjetAndItem($plugin_projet_projets_id,$items_id,$itemtype) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."` " .
			"WHERE `plugin_projet_projets_id` = '" . $plugin_projet_projets_id . "' 
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
      
      if (!isset($options["plugin_projet_projets_id"]) 
            || $options["plugin_projet_projets_id"] <= 0) {
         return false;
      } else {
         $this->add(array('plugin_projet_projets_id'=>$options["plugin_projet_projets_id"],
                           'items_id'=>$options["items_id"],
                           'itemtype'=>$options["itemtype"]));

      }
   }
   
   function deleteItemByProjetAndItem($plugin_projet_projets_id,$items_id,$itemtype) {
    
      if ($this->getFromDBbyProjetAndItem($plugin_projet_projets_id,$items_id,$itemtype)) {
         $this->delete(array('id'=>$this->fields["id"]));
      }
   }
 
   function showItemFromPlugin($instID,$withtemplate='',$material=false) {
      global $DB,$CFG_GLPI,$LANG;

      if (!$this->canView())	return false;

      $PluginProjetProjet=new PluginProjetProjet();
      if ($PluginProjetProjet->getFromDB($instID)) {
      
         $canedit=$PluginProjetProjet->can($instID,'w');
         $rand=mt_rand();
         
         if ($material) {
            $select = "'User','Group','Supplier','Contract'";
            $in = "NOT IN";
         } else {
            $select = "'User','Group','Supplier'";
            $in = "IN";
         }
         $query = "SELECT DISTINCT `itemtype` 
             FROM `".$this->getTable()."` 
             WHERE `plugin_projet_projets_id` = '$instID' 
             AND `itemtype` $in (".$select.")
             ORDER BY `itemtype` ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
      
         if (Session::isMultiEntitiesMode()) {
            $colsup=1;
         } else {
            $colsup=0;
         }
      
         echo "<form method='post' name='projet_form$rand' id='projet_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php\">";
    
         echo "<div class='center'><table class='tab_cadre_fixe'>";
         if ($material) {
            echo "<tr><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".$LANG['document'][19].":</th></tr><tr>";
         } else {
            echo "<tr><th colspan='".($canedit?(3+$colsup):(2+$colsup))."'>".$LANG['plugin_projet'][5].":</th></tr><tr>";
         }
         if ($canedit && $withtemplate<2) {
            echo "<th>&nbsp;</th>";
         }
         echo "<th>".$LANG['common'][17]."</th>";
         echo "<th>".$LANG['common'][16]."</th>";
         if (Session::isMultiEntitiesMode())
            echo "<th>".$LANG['entity'][0]."</th>";
         if ($material) {
            echo "<th>".$LANG['common'][19]."</th>";
            echo "<th>".$LANG['common'][20]."</th>";
         }
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
          
               $query = "SELECT `".$table."`.*, `".$this->getTable()."`.`id` AS items_id, `glpi_entities`.`id` AS entity "
                ." FROM `".$this->getTable()."`, `".$table
                ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
                ." WHERE `".$table."`.`id` = `".$this->getTable()."`.`items_id` 
                AND `".$this->getTable()."`.`itemtype` = '$type' 
                AND `".$this->getTable()."`.`plugin_projet_projets_id` = '$instID' ";
               if ($type!='User')
                  $query.=getEntitiesRestrictRequest(" AND ",$table,'','',$item->maybeRecursive());

               if ($item->maybeTemplate()) {
                  $query.=" AND ".$table.".is_template='0'";
               }
               $query.=" ORDER BY `glpi_entities`.`completename`, `".$table."`.`$column` ";
          
               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)) {
                     Session::initNavigateListItems($type,$LANG['plugin_projet']['title'][1]." = ".$PluginProjetProjet->fields['name']);

                     while ($data=$DB->fetch_assoc($result_linked)) {
                        
                        $item->getFromDB($data["id"]);
                        
                        Session::addToNavigateListItems($type,$data["id"]);
                        
                        if ($type=='User')
                           $format=formatUserName($data["id"],$data["name"],$data["realname"],$data["firstname"],1);
                        else
                           $format=$data["name"];
                        
                        $ID="";
                        if($_SESSION["glpiis_ids_visible"]||empty($data["name"])) 
                           $ID = " (".$data["id"].")";
                        $link=Toolbox::getItemTypeFormURL($type);
                        $name= "<a href=\"".$link."?id=".$data["id"]."\">".$format;
                        if ($type!='User')
                           $name.= "&nbsp;".$ID;
                        $name.= "</a>";

                        echo "<tr class='tab_bg_1'>";

                        if ($canedit && $withtemplate<2) {
                           echo "<td width='10'>";
                           $sel="";
                           if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
                           echo "<input type='checkbox' name='item[".$data["items_id"]."]' value='1' $sel>";
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
                        
                        if ($material) {
                           echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                           echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                        }
                        echo "</tr>";
                     }
                  }
            }
         }
    
         if ($canedit && $withtemplate<2) {
            if ($material) {
               echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='right'>";
            } else {
               echo "<tr class='tab_bg_1'><td colspan='".(1+$colsup)."' class='right'>";
            }
            echo "<input type='hidden' name='plugin_projet_projets_id' value='$instID'>";
            
            if ($material) {
               $types=PluginProjetProjet::getTypes();
               $trans= array_flip($types);
               unset($trans['User']);
               unset($trans['Group']);
               unset($trans['Supplier']);
               unset($trans['Contract']);
               $types=array_flip($trans);

            } else {
               $types[]='User';
               $types[]='Group';
               $types[]='Supplier';
            }
            Dropdown::showAllItems("items_id",0,0,$PluginProjetProjet->fields['entities_id'],$types);

            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>" ;
            Html::openArrowMassives("projet_form$rand", true);
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
      
      $PluginProjetProjet=new PluginProjetProjet();
      
      $query = "SELECT `".$this->getTable()."`.`id` AS items_id,`glpi_plugin_projet_projets`.* "
       ." FROM `".$this->getTable()."`,`glpi_plugin_projet_projets` "
       ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_projet_projets`.`entities_id`) "
       ." WHERE `".$this->getTable()."`.`items_id` = '".$ID."' 
       AND `".$this->getTable()."`.`itemtype` = '".$itemtype."' 
       AND `".$this->getTable()."`.`plugin_projet_projets_id` = `glpi_plugin_projet_projets`.`id`  
       AND `glpi_plugin_projet_projets`.`is_template` = '0' ";
       //. getEntitiesRestrictRequest(" AND ","glpi_plugin_accounts_accounts",'','',$PluginProjetProjet->maybeRecursive());
       $query.= "ORDER BY `glpi_plugin_projet_projets`.`name`";
    
      $result = $DB->query($query);
      $number = $DB->numrows($result);
    
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
    
      if ($withtemplate!=2) echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php\">";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='".(6+$colsup)."'>".$LANG['plugin_projet'][50].":</th></tr>";
      echo "<tr><th>".$LANG['plugin_projet'][0]."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['plugin_projet'][10]."</th>";
      echo "<th>".$LANG['plugin_projet'][47]."</th>";
      echo "<th>".$LANG['search'][8]."</th>";
      echo "<th>".$LANG['search'][9]."</th>";
      if ($this->canCreate()) {
         echo "<th>&nbsp;</th>";
      }
      echo "</tr>";
      $used=array();
      while ($data=$DB->fetch_array($result)) {
         $used[]=$data["id"];
         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

         if ($withtemplate!=3 && $canread && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) || $data["is_recursive"])) {
            echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".$data["id"]."'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
            echo "</a></td>";
         } else {
            echo "<td class='center'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
            echo "</td>";
         }
         if (Session::isMultiEntitiesMode())
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
         
         echo "<td align='center'>".$data["description"]."</td>";
         echo "<td align='center'>".PluginProjetProjet::displayProgressBar('100',$data["advance"])."</td>";

         echo "<td class='center'>".Html::convdate($data["date_begin"])."</td>";
         if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"]))
            echo "<td class='center'><span class='red'>".Html::convdate($data["date_end"])."</span></td>";
         else
            echo "<td class='center'><span class='green'>".Html::convdate($data["date_end"])."</span></td>";

         if ($this->canCreate()) {
            echo "<td class='center tab_bg_2'>";
            Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/projet/front/projet.form.php',
                                    'deletedevice',
                                    $LANG['buttons'][6],
                                    array('id' => $data['items_id']));
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

         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'',$entities,false);
         
         $q="SELECT COUNT(*) 
           FROM `glpi_plugin_projet_projets` 
           WHERE `is_deleted` = '0' 
           AND `is_template` = '0'";
         if ($itemtype!='User')
            $q.=" $limit";
         $result = $DB->query($q);
         $nb = $DB->result($result,0,0);
      
         if ($nb>count($used)) {
        
            if ($this->canCreate()) {
          
               echo "<tr class='tab_bg_1'><td class='right' colspan='".(5+$colsup)."'>";
               echo "<input type='hidden' name='items_id' value='$ID'><input type='hidden' name='itemtype' value='$itemtype'>";
            
               if ($itemtype!='User') {
                  $PluginProjetProjet=new PluginProjetProjet(); 
                  $PluginProjetProjet->dropdownProjet("plugin_projet_projets_id",$entities,$used);
                  echo "</td><td class='center'>";
                  echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
                  echo "</td>";
                  echo "</tr>";
            
               } else {
            
                  $strict_entities=Profile_User::getUserEntities($ID,true);
                  if (!Session::haveAccessToOneOfEntities($strict_entities)&&!isViewAllEntities()) {
                     $canedit=false;
                  }
    
                  if (countElementsInTableForEntity("glpi_plugin_projet_projets",$strict_entities) > count($used)) {
                     
                     Dropdown::show('PluginProjetProjet', array('name' => "plugin_projet_projets_id",'used' => $used,'entity' => $strict_entities));	
                     echo "</td><td class='tab_bg_2 center'>";
                     echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
          
                  }
               }
            }
         }
      }
      if ($canedit)
         echo "<tr class='tab_bg_1'><td colspan='".(6+$colsup)."' class='right'><a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/setup.templates.php?add=1'>".$LANG['plugin_projet'][13]."</a></td></tr>";
      echo "</table></div>";
      Html::closeForm();
   }
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      if ($item->getType()=='PluginProjetProjet') {
         self::pdfForProjet($pdf, $item, true);

      } else if (in_array($item->getType(), PluginProjetProjet::getTypes(true))) {
         self::PdfFromItems($pdf, $item);

      } else {
         return false;
      }
      return true;
   }
   
   /**
    * Show for PDF an projet - associated devices
    * 
    * @param $pdf object for the output
    * @param $appli object of the projet
    */
   static function pdfForProjet(PluginPdfSimplePDF $pdf, PluginProjetProjet $appli, $material=false) {
      global $DB,$CFG_GLPI, $LANG;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_projet_haveRight("projet","r")) {
         return false;
      }

      $pdf->setColumnsSize(100);
      if ($material) {
         $pdf->displayTitle('<b>'.$LANG['document'][19].'</b>');
      } else {
         $pdf->displayTitle('<b>'.$LANG['plugin_projet'][5].'</b>');
      }
      
      if ($material) {
         $select = "'User','Group','Supplier','Contract'";
         $in = "NOT IN";
      } else {
         $select = "'User','Group','Supplier'";
         $in = "IN";
      }
      
      $query = "SELECT DISTINCT `itemtype` 
               FROM `glpi_plugin_projet_projets_items` 
               WHERE `plugin_projet_projets_id` = '$ID' 
               AND `itemtype` $in (".$select.")
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
               ." FROM `glpi_plugin_projet_projets_items`, `".$table
               ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
               ." WHERE `".$table."`.`id` = `glpi_plugin_projet_projets_items`.`items_id` 
                  AND `glpi_plugin_projet_projets_items`.`itemtype` = '$type' 
                  AND `glpi_plugin_projet_projets_items`.`plugin_projet_projets_id` = '$ID' ";
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
      }
      $pdf->displaySpace(); // numrows type
   }
   
   /** 
    * show for PDF the projet associated with a device
    * 
    * @param $ID of the device
    * @param $itemtype : type of the device
    * 
    */
   static function PdfFromItems($pdf, $item) {
      global $DB,$CFG_GLPI, $LANG;
      
      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.$LANG['plugin_projet'][50].'</b>');
      
      $ID = $item->getField('id');
      $itemtype = get_Class($item);
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');
      
      $PluginProjetProjet=new PluginProjetProjet(); 
      
      $query = "SELECT `glpi_plugin_projet_projets`.* "
      ." FROM `glpi_plugin_projet_projets_items`,`glpi_plugin_projet_projets` "
      ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_projet_projets`.`entities_id`) "
      ." WHERE `glpi_plugin_projet_projets_items`.`items_id` = '".$ID."' 
         AND `glpi_plugin_projet_projets_items`.`itemtype` = '".$itemtype."' 
         AND `glpi_plugin_projet_projets_items`.`plugin_projet_projets_id` = `glpi_plugin_projet_projets`.`id` "
      . getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'','',$PluginProjetProjet->maybeRecursive());
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (!$number) {
         $pdf->displayLine($LANG['search'][15]);
      } else {
         if (Session::isMultiEntitiesMode()) {
            $pdf->setColumnsSize(14,14,14,14,14,14,16);
            $pdf->displayTitle(
               '<b><i>'.$LANG['plugin_projet'][0],
               $LANG['entity'][0],
               $LANG['plugin_projet'][2],
               $LANG['plugin_projet'][10],
               $LANG['plugin_projet'][47],
               $LANG['search'][8],
               $LANG['search'][9].'</i></b>'
               );
         } else {
            $pdf->setColumnsSize(17,17,17,17,17,17);
            $pdf->displayTitle(
               '<b><i>'.$LANG['plugin_projet'][0],
               $LANG['plugin_projet'][2],
               $LANG['plugin_projet'][10],
               $LANG['plugin_projet'][47],
               $LANG['search'][8],
               $LANG['search'][9].'</i></b>'
               );
         }
         while ($data=$DB->fetch_array($result)) {
      
            if (Session::isMultiEntitiesMode()) {
               $pdf->setColumnsSize(14,14,14,14,14,14,16);
               $pdf->displayLine(
                  $data["name"],
                  Html::clean(Dropdown::getDropdownName("glpi_entities",$data['entities_id'])),
                  $data["comment"],
                  $data["description"],
                  PluginProjetProjet::displayProgressBar('100',$data["advance"],array("simple"=>true)),
                  Html::convdate($data["date_begin"]),
                  Html::convdate($data["date_end"])
                  );
            } else {
               $pdf->setColumnsSize(17,17,17,17,17,17);
               $pdf->displayLine(
                  $data["name"],
                  $data["comment"],
                  $data["description"],
                  PluginProjetProjet::displayProgressBar('100',$data["advance"],array("simple"=>true)),
                  Html::convdate($data["date_begin"]),
                  Html::convdate($data["date_end"])
                  );
            }
         }		
      }
   }
   
   /**
    * Show projet for a ticket / problem
    *
    * @param $item Ticket or Problem object
   **/
   static function showForHelpdesk($item) {
      global $DB, $CFG_GLPI, $LANG;

      $ID = $item->getField('id');
      if (!$item->can($ID,'r')) {
         return false;
      }

      $canedit = $item->can($ID,'w');

      $rand = mt_rand();
      echo "<form name='projetlink_form$rand' id='projetlink_form$rand' method='post'
             action='";
      echo Toolbox::getItemTypeFormURL("PluginProjetProjet")."'>";

      echo "<div class='center'><table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='7'>".$LANG['plugin_projet']['title'][4]."&nbsp;-&nbsp;";
      echo "<a href='".Toolbox::getItemTypeFormURL('PluginProjetProjet').
               "?helpdesk_id=$ID&helpdesk_itemtype=".$item->gettype()."'>";
      
      if ( $item->gettype() == "Ticket") {
         echo $LANG['plugin_projet'][8];
      } else {
         echo $LANG['plugin_projet'][16];
      }
      echo "</a>";
      echo "</th></tr>";

      $query = "SELECT `glpi_plugin_projet_projets_items`.`id` AS items_id,
                     `glpi_plugin_projet_projets`.* "
       ." FROM `glpi_plugin_projet_projets_items`,`glpi_plugin_projet_projets` "
       ." LEFT JOIN `glpi_entities` 
         ON (`glpi_entities`.`id` = `glpi_plugin_projet_projets`.`entities_id`) "
       ." WHERE `glpi_plugin_projet_projets_items`.`items_id` = '".$ID."' 
       AND `glpi_plugin_projet_projets_items`.`itemtype` = '".$item->gettype()."' 
       AND `glpi_plugin_projet_projets_items`.`plugin_projet_projets_id` = `glpi_plugin_projet_projets`.`id`  
       AND `glpi_plugin_projet_projets`.`is_template` = '0' ";
       $query.= "ORDER BY `glpi_plugin_projet_projets`.`name`";
       
      $result = $DB->query($query);

      $used = array();

      if ($DB->numrows($result) >0) {
      
         PluginProjetProjet::commonListHeader(HTML_OUTPUT, $canedit); 
         Session::initNavigateListItems('PluginProjetProjet',
                                        $LANG['job'][38] ." = ". $item->fields["name"]);
         $i=0;
         while ($data = $DB->fetch_array($result)) {
            $used[$data['id']] = $data['id'];
            Session::addToNavigateListItems('PluginProjetProjet', $data["id"]);
            
            
            echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

            if ($canedit 
                  && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) 
                     || $data["is_recursive"])) {
               echo "<td class='center'>";
               echo "<a href='".$CFG_GLPI["root_doc"].
                     "/plugins/projet/front/projet.form.php?id=".$data["id"]."'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</a></td>";
            } else {
               echo "<td class='center'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</td>";
            }
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center'>";
               echo Dropdown::getDropdownName("glpi_entities",$data['entities_id']);
               echo "</td>";
            }
            echo "<td align='center'>".$data["description"]."</td>";
            echo "<td align='center'>";
            echo PluginProjetProjet::displayProgressBar('100',$data["advance"]);
            echo "</td>";

            echo "<td class='center'>".Html::convdate($data["date_begin"])."</td>";
            if ($data["date_end"] <= date('Y-m-d') && !empty($data["date_end"])) {
               echo "<td class='center'>";
               echo "<span class='red'>".Html::convdate($data["date_end"])."</span></td>";
            } else {
               echo "<td class='center'>";
               echo "<span class='green'>".Html::convdate($data["date_end"])."</span></td>";
            }
            if ($canedit) {
               echo "<td class='center tab_bg_2'>";
               Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/projet/front/projet.form.php',
                                       'deletedevice',
                                       $LANG['buttons'][6],
                                       array('id' => $data['items_id']));
               echo "</td>";
            }
            echo "</tr>";
         
            $i++;
         }
      }

      if ($canedit) {
         echo "<tr class='tab_bg_2'><td class='right'  colspan='6'>";
         echo "<input type='hidden' name='items_id' value='$ID'>";
         echo "<input type='hidden' name='helpdesk_itemtype' value='".$item->gettype()."'>";
         echo "<input type='hidden' name='itemtype' value='".$item->gettype()."'>";
         Dropdown::show('PluginProjetProjet', array('used'   => $used,
                                         'entity' => $item->getEntityID(),
                                         'name' => 'plugin_projet_projets_id'));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
         echo "</td></tr>";
      }

      echo "</table></div>";

      /*if ($canedit) {
         Html::openArrowMassives("projetlink_form$rand", true);
         Html::closeArrowMassives(array('delete' => $LANG['buttons'][6]));
      }*/
      Html::closeForm();
   }
}

?>