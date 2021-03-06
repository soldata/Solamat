<?php
/*
 * @version $Id: resourceholiday.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesResourceHoliday extends CommonDBTM {

	public $dohistory=true;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_resources']['title'][1];
   }
   
   function canCreate() {
      return plugin_resources_haveRight('holiday', 'w');
   }

   function canView() {
      return plugin_resources_haveRight('holiday', 'r');
   }
   
   function prepareInputForAdd($input) {
      global $LANG;
      
      if (!isset ($input["date_begin"]) || $input["date_begin"] == 'NULL') {
         Session::addMessageAfterRedirect($LANG['plugin_resources']['helpdesk'][19], false, ERROR);
         return array ();
      }
      if (!isset ($input["date_end"]) || $input["date_end"] == 'NULL') {
         Session::addMessageAfterRedirect($LANG['plugin_resources']['helpdesk'][20], false, ERROR);
         return array ();
      }

      return $input;
   }
   
   function post_addItem() {
		global $CFG_GLPI,$LANG;
		
		Session::addMessageAfterRedirect($LANG['plugin_resources']['mailing'][38]);
		
      $PluginResourcesResource = new PluginResourcesResource();
      if ($CFG_GLPI["use_mailing"]) {
         $options = array('holiday_id' => $this->fields["id"]);
         if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
            NotificationEvent::raiseEvent("newholiday",$PluginResourcesResource,$options);  
         }
      }
   }
   
   function prepareInputForUpdate($input) {
		global $LANG;
		
		if (!isset ($input["date_begin"]) || $input["date_begin"] == 'NULL') {
         Session::addMessageAfterRedirect($LANG['plugin_resources']['helpdesk'][19], false, ERROR);
         return array ();
      }
      if (!isset ($input["date_end"]) || $input["date_end"] == 'NULL') {
         Session::addMessageAfterRedirect($LANG['plugin_resources']['helpdesk'][20], false, ERROR);
         return array ();
      }

		//unset($input['picture']);
		$this->getFromDB($input["id"]);
		
		$input["_old_date_begin"]=$this->fields["date_begin"];
		$input["_old_date_end"]=$this->fields["date_end"];
		$input["_old_comment"]=$this->fields["comment"];
      
		return $input;
	}
   
   function post_updateItem($history=1) {
		global $CFG_GLPI,$LANG;
      
      if ($CFG_GLPI["use_mailing"] && count($this->updates)) {
         $options = array('holiday_id' => $this->fields["id"],
                           'oldvalues' => $this->oldvalues);
         $PluginResourcesResource = new PluginResourcesResource();
         if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
            NotificationEvent::raiseEvent("updateholiday",$PluginResourcesResource,$options);  
         }
      }
	}
	
	function pre_deleteItem() {
      global $CFG_GLPI;
      
      if ($CFG_GLPI["use_mailing"]) {
         $PluginResourcesResource = new PluginResourcesResource();
         $options = array('holiday_id' => $this->fields["id"]);
         if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
            NotificationEvent::raiseEvent("deleteholiday",$PluginResourcesResource,$options);  
         }
      }
      return true;
   }
	
	function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_resources']['title'][1];

      $tab[1]['table']="glpi_plugin_resources_resources";
      $tab[1]['field']='name';
      $tab[1]['name']=$LANG['plugin_resources'][8];
      $tab[1]['datatype']='itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      if (!plugin_resources_haveRight("all","r"))
         $tab[1]['searchtype'] = 'contains';

      $tab[2]['table']="glpi_plugin_resources_resources";
      $tab[2]['field']='firstname';
      $tab[2]['name']=$LANG['plugin_resources'][18];

      $tab[3]['table']=$this->getTable();
      $tab[3]['field']='date_begin';
      $tab[3]['name']=$LANG['plugin_resources'][34];
      $tab[3]['datatype']='date';

      $tab[4]['table']=$this->getTable();
      $tab[4]['field']='date_end';
      $tab[4]['name']=$LANG['plugin_resources'][35];
      $tab[4]['datatype']='date';
      
      $tab[5]['table']=$this->getTable();
      $tab[5]['field']='comment';
      $tab[5]['name']=$LANG['common'][25];
      $tab[5]['datatype']='text';
      
      $tab[30]['table']=$this->getTable();
      $tab[30]['field']='id';
      $tab[30]['linkfield']='';
      $tab[30]['name']=$LANG['common'][2];
		
		return $tab;
   }
   
   //Show form from helpdesk to add holiday of a resource
   function showForm($ID, $options=array()) {
      global $CFG_GLPI,$LANG;
      
      if (!$this->canView()) return false;
    
      if ($ID > 0) {
			$this->check($ID,'r');
		} else {
			// Create item 
			$this->check(-1,'w');
			$this->getEmpty();
		}
         
      echo "<div align='center'>";
      
      echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/resources/front/resourceholiday.form.php\">";
      
      echo "<table class='plugin_resources_wizard' style='margin-top:1px;'>";
      echo "<tr>";
      echo "<td class='plugin_resources_wizard_left_area' valign='top'>";
      echo "<div class='plugin_resources_presentation_logo'>";
      echo "<img src='../pics/holidayresource.png' alt='holidayresource' /></div>";
      echo "</td>";

      echo "<td class='plugin_resources_wizard_right_area' style='width: 400px' valign='top'>";
      
      if ($ID > 0) {
         $title = $LANG['plugin_resources']['helpdesk'][21];
      } else {
         $title = $LANG['plugin_resources']['helpdesk'][18];
      }
      
      echo "<div class='plugin_resources_wizard_title'>";
      echo $title;
      echo "</div>";
      
      echo "<table>";
      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td>".$LANG['plugin_resources']['helpdesk'][9]." :</td>";
      
      echo "<td class='left'>";
      PluginResourcesResource::dropdown(array('name'   => 'plugin_resources_resources_id',
         'value'  => $this->fields["plugin_resources_resources_id"],
         'entity' => $_SESSION['glpiactiveentities']));
      echo "</td></tr>";
      echo "<tr class='plugin_resources_wizard_explain'><td>";
      echo $LANG['plugin_resources'][34]." :</td>";
      echo "<td class='left'>";
      Html::showDateFormItem("date_begin",$this->fields["date_begin"],true,true);
      echo "</td></tr>";
      echo "<tr class='plugin_resources_wizard_explain'><td>";
      echo $LANG['plugin_resources'][35]." :</td>";
      echo "<td class='left'>";
      Html::showDateFormItem("date_end",$this->fields["date_end"],true,true);
      echo "</td></tr>";
      
      echo "<tr class='plugin_resources_wizard_explain'><td colspan='2'>";
      echo $LANG['common'][25].": </td></tr>";

      echo "<tr class='plugin_resources_wizard_explain'><td colspan='2'>";			
      echo "<textarea cols='70' rows='4' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";
      
      echo "</table>";
      echo "</div></td>";
      echo "</tr>";
      
      echo "<tr><td class='plugin_resources_wizard_button' colspan='2'>";
      echo "<div class='preview'>";
      echo "<a href=\"./resourceholiday.form.php\">";
      echo $LANG['plugin_resources']['helpdesk'][18];
      echo "</a>";
      echo "&nbsp;/&nbsp;<a href=\"./resourceholiday.php\">";
      echo $LANG['plugin_resources']['helpdesk'][23];
      echo "</a>";
      echo "</div>";
      echo "<div class='next'>";
      if ($ID > 0) {
         echo "<input type='hidden' name='id' value='".$ID."' />";
         echo "<input type='hidden' name='plugin_resources_resources_id' value='".$this->fields["plugin_resources_resources_id"]."' />";
         echo "<input type='submit' name='updateholidayresources' value=\"".$LANG['plugin_resources']['helpdesk'][22]."\" class='submit' />";
         echo "&nbsp;&nbsp;<input type='submit' name='deleteholidayresources' value=\"".$LANG['plugin_resources']['helpdesk'][24]."\" class='submit' />";
      } else {
         echo "<input type='submit' name='addholidayresources' value='".$LANG['plugin_resources']['helpdesk'][18]."' class='submit' />";
      }
      echo "</div>";
      echo "</td></tr></table>";
      Html::closeForm();

      echo "</div>";

   }

   /**
   * Print generic search form
   *
   *@param $itemtype type to display the form
   *@param $params parameters array may include field, contains, sort, is_deleted, link, link2, contains2, field2, type2
   *
   *@return nothing (displays)
   *
   **/
   function showGenericSearch($params) {
      global $LANG,$CFG_GLPI;
      
      $itemtype = $this->getType();
      $itemtable = $this->getTable();
      
      // Default values of parameters
      $p['link']        = array();//
      $p['field']       = array();
      $p['contains']    = array();
      $p['searchtype']  = array();
      $p['sort']        = '';
      $p['is_deleted']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';
      $p['field2']      = '';
      $p['itemtype2']   = '';
      $p['searchtype2']  = '';

      foreach ($params as $key => $val) {
         $p[$key]=$val;
      }

      $options=Search::getCleanedOptions("PluginResourcesResourceHoliday");
      //$target = Toolbox::getItemTypeSearchURL($itemtype);
      $target=$CFG_GLPI["root_doc"]."/plugins/resources/front/resourceholiday.php";
      // Instanciate an object to access method
      $item = NULL;
      if (class_exists($itemtype)) {
         $item = new $itemtype();
      }

      $linked =  Search::getMetaItemtypeAvailable($itemtype);

      echo "<form name='searchform$itemtype' method='get' action=\"$target\">";
      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo "<table>";

      // Display normal search parameters
      for ($i=0 ; $i<$_SESSION["glpisearchcount"][$itemtype] ; $i++) {
         echo "<tr><td class='left' width='50%'>";

         // First line display add / delete images for normal and meta search items
         if ($i==0) {
            echo "<input type='hidden' disabled  id='add_search_count' name='add_search_count' value='1'>";
            echo "<a href='#' onClick = \"document.getElementById('add_search_count').disabled=false;document.forms['searchform$itemtype'].submit();\">";
            echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/plus.png\" alt='+' title='".
                  $LANG['search'][17]."'></a>&nbsp;&nbsp;&nbsp;&nbsp;";
            if ($_SESSION["glpisearchcount"][$itemtype]>1) {
               echo "<input type='hidden' disabled  id='delete_search_count' name='delete_search_count' value='1'>";
               echo "<a href='#' onClick = \"document.getElementById('delete_search_count').disabled=false;document.forms['searchform$itemtype'].submit();\">";
               echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/moins.png\" alt='-' title='".
                     $LANG['search'][18]."'></a>&nbsp;&nbsp;&nbsp;&nbsp;";
            }
            if (is_array($linked) && count($linked)>0) {
               echo "<input type='hidden' disabled id='add_search_count2' name='add_search_count2' value='1'>";
               echo "<a href='#' onClick = \"document.getElementById('add_search_count2').disabled=false;document.forms['searchform$itemtype'].submit();\">";
               echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/meta_plus.png\" alt='+' title='".
                     $LANG['search'][19]."'></a>&nbsp;&nbsp;&nbsp;&nbsp;";
               if ($_SESSION["glpisearchcount2"][$itemtype]>0) {
                  echo "<input type='hidden' disabled  id='delete_search_count2' name='delete_search_count2' value='1'>";
                  echo "<a href='#' onClick = \"document.getElementById('delete_search_count2').disabled=false;document.forms['searchform$itemtype'].submit();\">";
                  echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/meta_moins.png\" alt='-' title='".
                        $LANG['search'][20]."'></a>&nbsp;&nbsp;&nbsp;&nbsp;";
               }
            }

            $itemtable=getTableForItemType($itemtype);
            /*if ($item && $item->maybeDeleted()) {
               echo "<input type='hidden' id='is_deleted' name='is_deleted' value='".$p['is_deleted']."'>";
               echo "<a href='#' onClick = \"toogle('is_deleted','','','');document.forms['searchform$itemtype'].submit();\">
                  <img src=\"".$CFG_GLPI["root_doc"]."/pics/showdeleted".(!$p['is_deleted']?'_no':'').".png\"
                  name='img_deleted'  alt='".
                  (!$p['is_deleted']?$LANG['common'][3]:$LANG['common'][81])."' title='".(!$p['is_deleted']?$LANG['common'][3]:$LANG['common'][81])."' ></a>";
               // Dropdown::showYesNo("is_deleted",$p['is_deleted']);
               echo '&nbsp;&nbsp;';
            }*/
         }

         // Display link item
         if ($i>0) {
            echo "<select name='link[$i]'>";
            echo "<option value='AND' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "AND") {
               echo "selected";
            }
            echo ">AND</option>\n";

            echo "<option value='OR' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "OR") {
               echo "selected";
            }
            echo ">OR</option>\n";

            echo "<option value='AND NOT' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "AND NOT") {
               echo "selected";
            }
            echo ">AND NOT</option>\n";

            echo "<option value='OR NOT' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "OR NOT") {
               echo "selected";
            }
            echo ">OR NOT</option>";
            echo "</select>&nbsp;";
         }


         // display select box to define serach item
         echo "<select id='Search$itemtype$i' name=\"field[$i]\" size='1'>";
         echo "<option value='view' ";
         if (is_array($p['field']) && isset($p['field'][$i]) && $p['field'][$i] == "view") {
            echo "selected";
         }
         echo ">".$LANG['search'][11]."</option>\n";

         reset($options);
         $first_group=true;
         $selected='view';
         foreach ($options as $key => $val) {
            // print groups
            if (!is_array($val)) {
               if (!$first_group) {
                  echo "</optgroup>\n";
               } else {
                  $first_group=false;
               }
               echo "<optgroup label='$val'>";
            } else {
               if (!isset($val['nosearch']) || $val['nosearch']==false) {
                  echo "<option title=\"".Html::cleanInputText($val["name"])."\" value='$key'";
                  if (is_array($p['field']) && isset($p['field'][$i]) && $key == $p['field'][$i]) {
                     echo "selected";
                     $selected=$key;
                  }
                  echo ">". Toolbox::substr($val["name"],0,28) ."</option>\n";
               }
            }
         }
         if (!$first_group) {
            echo "</optgroup>\n";
         }
         echo "<option value='all' ";
         if (is_array($p['field']) && isset($p['field'][$i]) && $p['field'][$i] == "all") {
            echo "selected";
         }
         echo ">".$LANG['common'][66]."</option>";
         echo "</select>&nbsp;\n";

         echo "</td><td class='left'>";
         echo "<div id='SearchSpan$itemtype$i'>\n";

         $_POST['itemtype']=$itemtype;
         $_POST['num']=$i;
         $_POST['field']=$selected;
         $_POST['searchtype']=(is_array($p['searchtype']) && isset($p['searchtype'][$i])?$p['searchtype'][$i]:"" );
         $_POST['value']=(is_array($p['contains']) && isset($p['contains'][$i])?stripslashes($p['contains'][$i]):"" );
         include (GLPI_ROOT."/ajax/searchoption.php");
         echo "</div>\n";

      $params = array('field'       => '__VALUE__',
                      'itemtype'    => $itemtype,
                      'num'         => $i,
                      'value'       => $_POST["value"],
                      'searchtype'  => $_POST["searchtype"]);
      Ajax::updateItemOnSelectEvent("Search$itemtype$i","SearchSpan$itemtype$i",
                                  $CFG_GLPI["root_doc"]."/ajax/searchoption.php",$params,false);

         echo "</td></tr>\n";
      }

      $metanames=array();

      if (is_array($linked) && count($linked)>0) {
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {
            echo "<tr><td class='left'>";
            $rand=mt_rand();

            // Display link item (not for the first item)
            echo "<select name='link2[$i]'>";
            echo "<option value='AND' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "AND") {
               echo "selected";
            }
            echo ">AND</option>\n";

            echo "<option value='OR' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "OR") {
               echo "selected";
            }
            echo ">OR</option>\n";

            echo "<option value='AND NOT' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "AND NOT") {
               echo "selected";
            }
            echo ">AND NOT</option>\n";

            echo "<option value='OR NOT' ";
            if (is_array($p['link2'] )&& isset($p['link2'][$i]) && $p['link2'][$i] == "OR NOT") {
               echo "selected";
            }
            echo ">OR NOT</option>\n";
            echo "</select>&nbsp;";

            // Display select of the linked item type available
            echo "<select name='itemtype2[$i]' id='itemtype2_".$itemtype."_".$i."_$rand'>";
            echo "<option value=''>".Dropdown::EMPTY_VALUE."</option>";
            foreach ($linked as $key) {
               if (!isset($metanames[$key])) {
                  $linkitem=new $key();
                  $metanames[$key]=$linkitem->getTypeName();
               }
               echo "<option value='$key'>".Toolbox::substr($metanames[$key],0,20)."</option>\n";
            }
            echo "</select>&nbsp;";
            echo "</td><td>";
            // Ajax script for display search met& item
            echo "<span id='show_".$itemtype."_".$i."_$rand'>&nbsp;</span>\n";

            $params=array('itemtype'=>'__VALUE__',
                        'num'=>$i,
                        'field'=>(is_array($p['field2']) && isset($p['field2'][$i])?$p['field2'][$i]:""),
                        'value'=>(is_array($p['contains2']) && isset($p['contains2'][$i])?$p['contains2'][$i]:""),
                        'searchtype2'=>(is_array($p['searchtype2']) && isset($p['searchtype2'][$i])?$p['searchtype2'][$i]:""));

            Ajax::updateItemOnSelectEvent("itemtype2_".$itemtype."_".$i."_$rand","show_".$itemtype."_".
                     $i."_$rand",$CFG_GLPI["root_doc"]."/ajax/updateMetaSearch.php",$params,false);

            if (is_array($p['itemtype2']) && isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i])) {
               $params['itemtype']=$p['itemtype2'][$i];
               Ajax::updateItem("show_".$itemtype."_".$i."_$rand",
                              $CFG_GLPI["root_doc"]."/ajax/updateMetaSearch.php",$params,false);
               echo "<script type='text/javascript' >";
               echo "window.document.getElementById('itemtype2_".$itemtype."_".$i."_$rand').value='".
                                                   $p['itemtype2'][$i]."';";
               echo "</script>\n";
            }
            echo "</td></tr></table>";
            echo "</td></tr>\n";
         }
      }
      echo "</table>\n";
      echo "</td>\n";

      echo "<td width='150px'>";
      echo "<table width='100%'>";

      // Display deleted selection

      echo "<tr>";

      // Display submit button
      echo "<td width='80' class='center'>";
      echo "<input type='submit' value=\"".$LANG['buttons'][0]."\" class='submit' >";
      echo "</td><td>";
      //Bookmark::showSaveButton(Bookmark::SEARCH,$itemtype);
      echo "<a href='$target?reset=reset' >";
      echo "&nbsp;&nbsp;<img title=\"".$LANG['buttons'][16]."\" alt=\"".$LANG['buttons'][16]."\" src='".
            $CFG_GLPI["root_doc"]."/pics/reset.png' class='calendrier'></a>";

      echo "</td></tr></table>\n";

      echo "</td></tr>";
      echo "</table>\n";

      // For dropdown
      echo "<input type='hidden' name='itemtype' value='$itemtype'>";

      // Reset to start when submit new search
      echo "<input type='hidden' name='start' value='0'>";
      Html::closeForm();
   }
   
   function showMinimalList($params) {
      global $DB,$CFG_GLPI,$LANG;
      
      // Instanciate an object to access method
      $item = NULL;
      
      $itemtype = $this->getType();
      $itemtable = $this->getTable();
      
      if (class_exists($itemtype)) {
         $item = new $itemtype();
      }

      // Default values of parameters
      $p['link']        = array();//
      $p['field']       = array();//
      $p['contains']    = array();//
      $p['searchtype']  = array();//
      $p['sort']        = '1'; //
      $p['order']       = 'ASC';//
      $p['start']       = 0;//
      $p['is_deleted']  = 0;
      $p['export_all']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';//
      $p['field2']      = '';//
      $p['itemtype2']   = '';
      $p['searchtype2']  = '';
      
      foreach ($params as $key => $val) {
            $p[$key]=$val;
      }

      if ($p['export_all']) {
         $p['start']=0;
      }
      
      // Manage defautlt seachtype value : for bookmark compatibility
      
      if (count($p['contains'])) {
         foreach ($p['contains'] as $key => $val) {
            if (!isset($p['searchtype'][$key])) {
               $p['searchtype'][$key]='contains';
            }
         }
      }
      if (is_array($p['contains2']) && count($p['contains2'])) {
         foreach ($p['contains2'] as $key => $val) {
            if (!isset($p['searchtype2'][$key])) {
               $p['searchtype2'][$key]='contains';
            }
         }
      }

      //$target = Toolbox::getItemTypeSearchURL($itemtype);
      $target=$CFG_GLPI["root_doc"]."/plugins/resources/front/resourceholiday.php";

      $limitsearchopt=Search::getCleanedOptions("PluginResourcesResourceHoliday");
      
      $LIST_LIMIT=$_SESSION['glpilist_limit'];
      
      // Set display type for export if define
      $output_type=HTML_OUTPUT;
      if (isset($_GET['display_type'])) {
         $output_type=$_GET['display_type'];
         // Limit to 10 element
         if ($_GET['display_type']==GLOBAL_SEARCH) {
            $LIST_LIMIT=GLOBAL_SEARCH_DISPLAY_COUNT;
         }
      }
      $PluginResourcesResource = new PluginResourcesResource();
      $entity_restrict = $PluginResourcesResource->isEntityAssign();
      
      // Get the items to display
      $toview=Search::addDefaultToView($itemtype);
      
      // Add items to display depending of personal prefs
      $displaypref=DisplayPreference::getForTypeUser("PluginResourcesResourceHoliday",Session::getLoginUserID());
      if (count($displaypref)) {
         foreach ($displaypref as $val) {
            array_push($toview,$val);
         }
      }
      
      // Add searched items
      if (count($p['field'])>0) {
         foreach($p['field'] as $key => $val) {
            if (!in_array($val,$toview) && $val!='all' && $val!='view') {
               array_push($toview,$val);
            }
         }
      }

      // Add order item
      if (!in_array($p['sort'],$toview)) {
         array_push($toview,$p['sort']);
      }
      
      // Clean toview array
      $toview=array_unique($toview);
      foreach ($toview as $key => $val) {
         if (!isset($limitsearchopt[$val])) {
            unset($toview[$key]);
         }
      }

      $toview_count=count($toview);
      
      //// 1 - SELECT
      $query = "SELECT ".Search::addDefaultSelect($itemtype);

      // Add select for all toview item
      foreach ($toview as $key => $val) {
         $query.= Search::addSelect($itemtype,$val,$key,0);
      }
      
      $query .= "`".$itemtable."`.`id` AS id ";
      
      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $query.= " FROM `".$itemtable."`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables=array();
      // Put reference table
      array_push($already_link_tables,$itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($itemtype,$itemtable,$already_link_tables);
      $query .= $COMMONLEFTJOIN;

      $searchopt=array();
      $searchopt[$itemtype]=&Search::getOptions($itemtype);
      // Add all table for toview items
      foreach ($toview as $key => $val) {
         $query .= Search::addLeftJoin($itemtype,$itemtable,$already_link_tables,
                              $searchopt[$itemtype][$val]["table"],
                              $searchopt[$itemtype][$val]["linkfield"]);
      }

      // Search all case :
      if (in_array("all",$p['field'])) {
         foreach ($searchopt[$itemtype] as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               $query .= Search::addLeftJoin($itemtype,$itemtable,$already_link_tables,
                                    $searchopt[$itemtype][$key]["table"],
                                    $searchopt[$itemtype][$key]["linkfield"]);
            }
         }
      }
      
      //// 3 - WHERE

      // default string
      $COMMONWHERE = Search::addDefaultWhere($itemtype);
      $first=empty($COMMONWHERE);

      // Add deleted if item have it
      if ($item && $item->maybeDeleted()) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_deleted` = '".$p['is_deleted']."' ";
      }

      // Remove template items
      if ($item && $item->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_template` = '0' ";
      }

      // Add Restrict to current entities
      if ($entity_restrict) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }

         if ($itemtype == 'Entity') {
            $COMMONWHERE .= getEntitiesRestrictRequest($LINK,$itemtable,'id','',true);
         } else if (isset($CFG_GLPI["union_search_type"]["PluginResourcesResource"])) {

            // Will be replace below in Union/Recursivity Hack
            $COMMONWHERE .= $LINK." ENTITYRESTRICT ";
         } else {
            $COMMONWHERE .= getEntitiesRestrictRequest($LINK,"glpi_plugin_resources_resources",'','',$PluginResourcesResource->maybeRecursive());
         }
      }
      
      ///R�cup�ration des groupes de l'utilisateur connect�
      $who=Session::getLoginUserID();
      
      if (!plugin_resources_haveRight("all","r")) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."(`glpi_plugin_resources_resources`.`users_id_recipient` = '$who' OR `glpi_plugin_resources_resources`.`users_id` = '$who') ";
      }
      
      $WHERE="";
      $HAVING="";

      // Add search conditions
      // If there is search items
      if ($_SESSION["glpisearchcount"][$itemtype]>0 && count($p['contains'])>0) {
         for ($key=0 ; $key<$_SESSION["glpisearchcount"][$itemtype] ; $key++) {
            // if real search (strlen >0) and not all and view search
            if (isset($p['contains'][$key]) && strlen($p['contains'][$key])>0) {
               // common search
               if ($p['field'][$key]!="all" && $p['field'][$key]!="view") {
                  $LINK=" ";
                  $NOT=0;
                  $tmplink="";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     if (strstr($p['link'][$key],"NOT")) {
                        $tmplink=" ".str_replace(" NOT","",$p['link'][$key]);
                        $NOT=1;
                     } else {
                        $tmplink=" ".$p['link'][$key];
                     }
                  } else {
                     $tmplink=" AND ";
                  }

                  if (isset($searchopt[$itemtype][$p['field'][$key]]["usehaving"])) {
                     // Manage Link if not first item
                     if (!empty($HAVING)) {
                        $LINK=$tmplink;
                     }
                     // Find key
                     $item_num=array_search($p['field'][$key],$toview);
                     $HAVING .= Search::addHaving($LINK,$NOT,$itemtype,$p['field'][$key],$p['searchtype'][$key],$p['contains'][$key],0,$item_num);
                  } else {
                     // Manage Link if not first item
                     if (!empty($WHERE)) {
                        $LINK=$tmplink;
                     }
                     $WHERE .= Search::addWhere($LINK,$NOT,$itemtype,$p['field'][$key],$p['searchtype'][$key],$p['contains'][$key]);
                  }

               // view and all search
               } else {
                  $LINK=" OR ";
                  $NOT=0;
                  $globallink=" AND ";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     switch ($p['link'][$key]) {
                        case "AND" :
                           $LINK=" OR ";
                           $globallink=" AND ";
                           break;

                        case "AND NOT" :
                           $LINK=" AND ";
                           $NOT=1;
                           $globallink=" AND ";
                           break;

                        case "OR" :
                           $LINK=" OR ";
                           $globallink=" OR ";
                           break;

                        case "OR NOT" :
                           $LINK=" AND ";
                           $NOT=1;
                           $globallink=" OR ";
                           break;
                     }
                  } else {
                     $tmplink=" AND ";
                  }

                  // Manage Link if not first item
                  if (!empty($WHERE)) {
                     $WHERE .= $globallink;
                  }
                  $WHERE.= " ( ";
                  $first2=true;

                  $items=array();
                  if ($p['field'][$key]=="all") {
                     $items=$searchopt[$itemtype];
                  } else { // toview case : populate toview
                     foreach ($toview as $key2 => $val2) {
                        $items[$val2]=$searchopt[$itemtype][$val2];
                     }
                  }

                  foreach ($items as $key2 => $val2) {
                     if (is_array($val2)) {
                        // Add Where clause if not to be done in HAVING CLAUSE
                        if (!isset($val2["usehaving"])) {
                           $tmplink=$LINK;
                           if ($first2) {
                              $tmplink=" ";
                              $first2=false;
                           }
                           $WHERE .= Search::addWhere($tmplink,$NOT,$itemtype,$key2,$p['searchtype'][$key],$p['contains'][$key]);
                        }
                     }
                  }
                  $WHERE.=" ) ";
               }
            }
         }
      }

      if (!empty($WHERE) || !empty($COMMONWHERE)) {
         if (!empty($COMMONWHERE)) {
            $WHERE =' WHERE '.$COMMONWHERE.(!empty($WHERE)?' AND ( '.$WHERE.' )':'');
         } else {
            $WHERE =' WHERE '.$WHERE.' ';
         }
         $first=false;
      }
      $query.=$WHERE;

      //// 7 - Manage GROUP BY
      $GROUPBY = "";
      // Meta Search / Search All / Count tickets
      if (in_array('all',$p['field'])) {
         $GROUPBY = " GROUP BY `".$itemtable."`.`id`";
      }

      if (empty($GROUPBY)) {
         foreach ($toview as $key2 => $val2) {
            if (!empty($GROUPBY)) {
               break;
            }
            if (isset($searchopt[$itemtype][$val2]["forcegroupby"])) {
               $GROUPBY = " GROUP BY `".$itemtable."`.`id`";
            }
         }
      }
      $query.=$GROUPBY;
      //// 4 - ORDER
      $ORDER=" ORDER BY `id` ";
      foreach($toview as $key => $val) {
         if ($p['sort']==$val) {
            $ORDER= Search::addOrderBy($itemtype,$p['sort'],$p['order'],$key);
         }
      }
      $query.=$ORDER;

      // Get it from database	
      
      if ($result = $DB->query($query)) {
         $numrows =  $DB->numrows($result);
         
         $globallinkto = Search::getArrayUrlLink("field",$p['field']).
                        Search::getArrayUrlLink("link",$p['link']).
                        Search::getArrayUrlLink("contains",$p['contains']).
                        Search::getArrayUrlLink("field2",$p['field2']).
                        Search::getArrayUrlLink("contains2",$p['contains2']).
                        Search::getArrayUrlLink("itemtype2",$p['itemtype2']).
                        Search::getArrayUrlLink("link2",$p['link2']);

         $parameters = "sort=".$p['sort']."&amp;order=".$p['order'].$globallinkto;
         
         if ($output_type==GLOBAL_SEARCH) {
            if (class_exists($itemtype)) {
               echo "<div class='center'><h2>".$this->getTypeName();
               // More items
               if ($numrows>$p['start']+GLOBAL_SEARCH_DISPLAY_COUNT) {
                  echo " <a href='$target?$parameters'>".$LANG['common'][66]."</a>";
               }
               echo "</h2></div>\n";
            } else {
               return false;
            }
         }
           
         if ($p['start']<$numrows) {

            // Pager
            if ($output_type==HTML_OUTPUT) {
               Html::printPager($p['start'],$numrows,$target,$parameters,$itemtype);
            }
           
            //massive action
            $sel="";
            if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";

            // Add toview elements
            $nbcols=$toview_count;

            if ($output_type==HTML_OUTPUT) { // HTML display - massive modif
               $nbcols++;
            }

            // Define begin and end var for loop
            // Search case
            $begin_display=$p['start'];
            $end_display=$p['start']+$LIST_LIMIT;

            // Export All case
            if ($p['export_all']) {
               $begin_display=0;
               $end_display=$numrows;
            }

            // Display List Header
            echo Search::showHeader($output_type,$end_display-$begin_display+1,$nbcols);
            
            $header_num=1;
            // Display column Headers for toview items
            echo Search::showNewLine($output_type);
                       
            // Display column Headers for toview items
            foreach ($toview as $key => $val) {
               $linkto='';
               if (!isset($searchopt[$itemtype][$val]['nosort'])
                     || !$searchopt[$itemtype][$val]['nosort']) {
                  $linkto = "$target?itemtype=$itemtype&amp;sort=".$val."&amp;order=".($p['order']=="ASC"?"DESC":"ASC").
                           "&amp;start=".$p['start'].$globallinkto;
               }
               echo Search::showHeaderItem($output_type,$searchopt[$itemtype][$val]["name"],
                                          $header_num,$linkto,$p['sort']==$val,$p['order']);
            }
            
            // End Line for column headers		
            echo Search::showEndLine($output_type);

            $DB->data_seek($result,$p['start']);
           
            // Define begin and end var for loop
            // Search case
            $i=$begin_display;

            // Init list of items displayed
            if ($output_type==HTML_OUTPUT) {
               Session::initNavigateListItems($itemtype);
            }

            // Num of the row (1=header_line)
            $row_num=1;
            // Display Loop
            while ($i < $numrows && $i<($end_display)) {
               
               $item_num=1;
               $data=$DB->fetch_array($result);
               $i++;
               $row_num++;
               
               echo Search::showNewLine($output_type,($i%2));
               
               Session::addToNavigateListItems($itemtype,$data['id']);
               
               foreach ($toview as $key => $val) {
                  echo Search::showItem($output_type,Search::giveItem($itemtype,$val,$data,$key),$item_num,
                                       $row_num,
                           Search::displayConfigItem($itemtype,$val,$data,$key));
               }
           
               echo Search::showEndLine($output_type);
            }
            // Close Table
            $title="";
            // Create title
            if ($output_type==PDF_OUTPUT_PORTRAIT|| $output_type==PDF_OUTPUT_LANDSCAPE) {
               $title.=$LANG['plugin_resources']['helpdesk'][23];
            }
           
            // Display footer
            echo Search::showFooter($output_type,$title);

            // Pager
            if ($output_type==HTML_OUTPUT) {
               echo "<br>";			
               Html::printPager($p['start'],$numrows,$target,$parameters);
            }
         } else {
            echo Search::showError($output_type);
         }
      }
   }
}

?>