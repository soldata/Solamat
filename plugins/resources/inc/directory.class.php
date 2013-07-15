<?php
/*
 * @version $Id: directory.class.php 480 2012-11-09 tsmr $
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

class PluginResourcesDirectory extends CommonDBTM {

	public $table="glpi_users";
	
	function canCreate() {
      return plugin_resources_haveRight('resources', 'w');
   }

   function canView() {
      return plugin_resources_haveRight('resources', 'r');
   }
   
	function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['common'][32];

      $tab[1]['table']='glpi_users';
      $tab[1]['field']='registration_number';
      $tab[1]['name']=$LANG['users'][17];
      $tab[1]['forcegroupby'] = true;
      //$tab[1]['nosearch'] = true;
      
      $tab[2]['table']='glpi_users';
      $tab[2]['field']='id';
      $tab[2]['name']=$LANG['common'][2]." ".$LANG['common'][34];
      $tab[2]['nosearch'] = true;
      
      $tab[34]['table']='glpi_users';
      $tab[34]['field']='realname';
      $tab[34]['name']=$LANG['common'][48];
      
      $tab[9]['table']='glpi_users';
      $tab[9]['field']='firstname';
      $tab[9]['name']=$LANG['common'][43];

      $tab[5]['table']         = 'glpi_useremails';
      $tab[5]['field']         = 'email';
      $tab[5]['name']          = $LANG['setup'][14];
      $tab[5]['datatype']      = 'email';
      $tab[5]['joinparams']    = array('jointype'=>'child');
      $tab[5]['forcegroupby']  = true;
      $tab[5]['massiveaction'] = false;

      $tab += Location::getSearchOptionsToAdd();

      $tab[6]['table']='glpi_users';
      $tab[6]['field']='phone';
      $tab[6]['name']=$LANG['help'][35];

      $tab[10]['table']='glpi_users';
      $tab[10]['field']='phone2';
      $tab[10]['name']=$LANG['help'][35]." 2";

      $tab[11]['table']='glpi_users';
      $tab[11]['field']='mobile';
      $tab[11]['name']=$LANG['common'][42];

      // FROM employee
      
      $tab[4313]['table']='glpi_plugin_resources_employers';
      $tab[4313]['field']='name';
      $tab[4313]['name']=$LANG['plugin_resources'][62];

      $tab[4314]['table']='glpi_plugin_resources_clients';
      $tab[4314]['field']='name';
      $tab[4314]['name']=$LANG['plugin_resources'][63];

      // FROM resources

      $tab[4315]['table']='glpi_plugin_resources_contracttypes';
      $tab[4315]['field']='name';
      $tab[4315]['name']=$LANG['plugin_resources'][20];

      $tab[4316]['table']='glpi_plugin_resources_managers';
      $tab[4316]['field']='name';
      $tab[4316]['name']=$LANG['plugin_resources'][47];
      $tab[4316]['searchtype'] = 'contains';
      
      $tab[4317]['table']='glpi_plugin_resources_resources';
      $tab[4317]['field']='date_begin';
      $tab[4317]['name']=$LANG['plugin_resources'][11];
      $tab[4317]['datatype']='date';

      $tab[4318]['table']='glpi_plugin_resources_resources';
      $tab[4318]['field']='date_end';
      $tab[4318]['name']=$LANG['plugin_resources'][13];
      $tab[4318]['datatype']='date';

      $tab[4319]['table']='glpi_plugin_resources_departments';
      $tab[4319]['field']='name';
      $tab[4319]['name']=$LANG['plugin_resources'][14];
      
      $tab[4320]['table']='glpi_plugin_resources_resourcestates';
      $tab[4320]['field']='name';
      $tab[4320]['name']=$LANG['plugin_resources'][24];
		
		return $tab;
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
      
      $itemtype = "PluginResourcesDirectory";
      $itemtable = $this->table;
      
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

      $options=Search::getCleanedOptions("PluginResourcesDirectory");
      $target=$CFG_GLPI["root_doc"]."/plugins/resources/front/directory.php";

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
      Bookmark::showSaveButton(Bookmark::SEARCH,$itemtype);
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
      
      $PluginResourcesResource = new PluginResourcesResource();
      $itemtype = "PluginResourcesDirectory";
      $itemtable = $this->table;
      
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

      $target=$CFG_GLPI["root_doc"]."/plugins/resources/front/directory.php";

      $limitsearchopt=Search::getCleanedOptions("PluginResourcesDirectory");
      
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
      
      $entity_restrict = $PluginResourcesResource->isEntityAssign();
      
      // Get the items to display
      $toview=Search::addDefaultToView("PluginResourcesDirectory");
      
      // Add items to display depending of personal prefs
      $displaypref=DisplayPreference::getForTypeUser("PluginResourcesDirectory",Session::getLoginUserID());
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
         $query.= self::addSelect("PluginResourcesDirectory",$val,$key,0);
      }
      
      $query .= "`glpi_plugin_resources_resources`.`id` AS id ";
      
      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $query.= " FROM `".$itemtable."`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables=array();
      // Put reference table
      array_push($already_link_tables,$itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin("PluginResourcesDirectory",$itemtable,$already_link_tables);
      $query .= $COMMONLEFTJOIN;

      $searchopt=array();
      $searchopt["PluginResourcesDirectory"]=&Search::getOptions("PluginResourcesDirectory");
      // Add all table for toview items
      foreach ($toview as $key => $val) {
         $query .= Search::addLeftJoin($itemtype,$itemtable,$already_link_tables,
                              $searchopt["PluginResourcesDirectory"][$val]["table"],
                              $searchopt["PluginResourcesDirectory"][$val]["linkfield"], 0, 0,
                                    $searchopt["PluginResourcesDirectory"][$val]["joinparams"]);
      }

      // Search all case :
      if (in_array("all",$p['field'])) {
         foreach ($searchopt["PluginResourcesDirectory"] as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               $query .= Search::addLeftJoin($itemtype,$itemtable,$already_link_tables,
                                    $searchopt["PluginResourcesDirectory"][$key]["table"],
                                    $searchopt["PluginResourcesDirectory"][$key]["linkfield"], 0, 0,
                                    $searchopt["PluginResourcesDirectory"][$val]["joinparams"]);
            }
         }
      }
      
      $ASSIGN=" `glpi_plugin_resources_resources`.`is_leaving` = 0 AND `glpi_users`.`is_active` = 1 AND ";
      //// 3 - WHERE

      // default string
      $COMMONWHERE = Search::addDefaultWhere($itemtype);
      $first=empty($COMMONWHERE);

      // Add deleted if item have it
      if ($PluginResourcesResource && $PluginResourcesResource->maybeDeleted()) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."`glpi_plugin_resources_resources`.`is_deleted` = '".$p['is_deleted']."' ";
      }

      // Remove template items
      if ($PluginResourcesResource && $PluginResourcesResource->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }
         $COMMONWHERE .= $LINK."`glpi_plugin_resources_resources`.`is_template` = '0' ";
      }

      // Add Restrict to current entities
      if ($entity_restrict) {
         $LINK= " AND " ;
         if ($first) {
            $LINK=" ";
            $first=false;
         }

         if (isset($CFG_GLPI["union_search_type"][$itemtype])) {

            // Will be replace below in Union/Recursivity Hack
            $COMMONWHERE .= $LINK." ENTITYRESTRICT ";
         } else {
            $COMMONWHERE .= getEntitiesRestrictRequest($LINK,"glpi_plugin_resources_resources",'','',$PluginResourcesResource->maybeRecursive());
         }
      }
      $WHERE="";
      $HAVING="";

      // Add search conditions
      // If there is search items
      if ($_SESSION["glpisearchcount"]["PluginResourcesDirectory"]>0 && count($p['contains'])>0) {
         for ($key=0 ; $key<$_SESSION["glpisearchcount"]["PluginResourcesDirectory"] ; $key++) {
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

                  if (isset($searchopt["PluginResourcesDirectory"][$p['field'][$key]]["usehaving"])) {
                     // Manage Link if not first item
                     if (!empty($HAVING)) {
                        $LINK=$tmplink;
                     }
                     // Find key
                     $item_num=array_search($p['field'][$key],$toview);
                     $HAVING .= Search::addHaving($LINK,$NOT,"PluginResourcesDirectory",$p['field'][$key],$p['searchtype'][$key],$p['contains'][$key],0,$item_num);
                  } else {
                     // Manage Link if not first item
                     if (!empty($WHERE)) {
                        $LINK=$tmplink;
                     }
                     $WHERE .= self::addWhere($LINK,$NOT,"PluginResourcesDirectory",$p['field'][$key],$p['searchtype'][$key],$p['contains'][$key]);
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
                     $items=$searchopt["PluginResourcesDirectory"];
                  } else { // toview case : populate toview
                     foreach ($toview as $key2 => $val2) {
                        $items[$val2]=$searchopt["PluginResourcesDirectory"][$val2];
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
                           $WHERE .= self::addWhere($tmplink,$NOT,"PluginResourcesDirectory",$key2,$p['searchtype'][$key],$p['contains'][$key]);
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
            $WHERE =' WHERE '.$ASSIGN.' '.$COMMONWHERE.(!empty($WHERE)?' AND ( '.$WHERE.' )':'');
         } else {
            $WHERE =' WHERE '.$ASSIGN.' '.$WHERE.' ';
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
            if (isset($searchopt["PluginResourcesDirectory"][$val2]["forcegroupby"])) {
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
                        Search::getArrayUrlLink("searchtype",$p['searchtype']).
                        Search::getArrayUrlLink("field2",$p['field2']).
                        Search::getArrayUrlLink("contains2",$p['contains2']).
                        Search::getArrayUrlLink("searchtype2",$p['searchtype2']).
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
               Html::printPager($p['start'],$numrows,$target,$parameters,"PluginResourcesDirectory");
            }
           
            //massive action
            $sel="";
            if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
            
            
            // Add toview elements
            $nbcols=$toview_count;
            
            // Form to massive actions
            $isadmin = ($PluginResourcesResource && $PluginResourcesResource->canUpdate());

            if ($isadmin && $output_type==HTML_OUTPUT) {
               echo "<form method='post' name='massiveaction_form' id='massiveaction_form' action=\"".
                     $CFG_GLPI["root_doc"]."/front/massiveaction.php\">";
            }

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
            if ($output_type==HTML_OUTPUT) { // HTML display - massive modif
               $search_config = "";
               if (Session::haveRight("search_config","w") || Session::haveRight("search_config_global","w")) {
                  $tmp = " class='pointer' onClick=\"var w = window.open('".$CFG_GLPI["root_doc"].
                        "/front/popup.php?popup=search_config&amp;itemtype=PluginResourcesDirectory' ,'glpipopup', ".
                        "'height=400, width=1000, top=100, left=100, scrollbars=yes'); w.focus();\"";

                  $search_config = "<img alt=\"".$LANG['setup'][252]."\" title=\"".$LANG['setup'][252].
                                    "\" src='".$CFG_GLPI["root_doc"]."/pics/options_search.png' ";
                  $search_config .= $tmp.">";
               }
               echo Search::showHeaderItem($output_type, $search_config, $header_num, "", 0,
                                         $p['order']);
            }
                       
            // Display column Headers for toview items
            foreach ($toview as $key => $val) {
               $linkto='';
               if (!isset($searchopt["PluginResourcesDirectory"][$val]['nosort'])
                     || !$searchopt["PluginResourcesDirectory"][$val]['nosort']) {
                  $linkto = "$target?itemtype=$itemtype&amp;sort=".$val."&amp;order=".($p['order']=="ASC"?"DESC":"ASC").
                           "&amp;start=".$p['start'].$globallinkto;
               }
               echo Search::showHeaderItem($output_type,$searchopt["PluginResourcesDirectory"][$val]["name"],
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
               
               if ($output_type==HTML_OUTPUT) { // HTML display - massive modif
                  $tmpcheck = "";
                  if ($isadmin) {
                     if ($item->maybeRecursive() && !in_array($data["entities_id"],
                                                                     $_SESSION["glpiactiveentities"])) {
                        $tmpcheck = "&nbsp;";

                     } else {
                        $sel = "";
                        if (isset($_GET["select"]) && $_GET["select"]=="all") {
                           $sel = "checked";
                        }
                        if (isset($_SESSION['glpimassiveactionselected'][$data["id"]])) {
                           $sel = "checked";
                        }
                        $tmpcheck = "<input type='checkbox' name='item[".$data["id"]."]' value='1'
                                      $sel>";
                     }
                  }
                  echo Search::showItem($output_type, $tmpcheck, $item_num, $row_num, "width='10'");
               }
               
               foreach ($toview as $key => $val) {
                  echo Search::showItem($output_type,Search::giveItem("PluginResourcesDirectory",$val,$data,$key),$item_num,
                                       $row_num,
                           Search::displayConfigItem("PluginResourcesDirectory",$val,$data,$key));
               }
           
               echo Search::showEndLine($output_type);
            }
            // Close Table
            $title="";
            // Create title
            if ($output_type==PDF_OUTPUT_PORTRAIT|| $output_type==PDF_OUTPUT_LANDSCAPE) {
               $title.=$LANG['plugin_resources'][66];
            }
           
            // Display footer
            echo Search::showFooter($output_type,$title);

            // Delete selected item
            if ($output_type==HTML_OUTPUT) {
               if ($isadmin) {
                  Html::openArrowMassives("massiveaction_form", false);
                  Dropdown::showForMassiveAction("PluginResourcesDirectory", $p['is_deleted']);
                  Html::closeArrowMassives(array());

                  // End form for delete item
                  Html::closeForm();
               } else {
                  echo "<br>";
               }
            }
            if ($output_type==HTML_OUTPUT) { // In case of HTML display
               Html::printPager($p['start'], $numrows, $target, $parameters);
            }
         } else {
            echo Search::showError($output_type);
         }
      }
   }
   
   /**
   * Generic Function to add select to a request
   *
   *@param $itemtype item type
   *@param $ID ID of the item to add
   *@param $num item num in the request
   *@param $meta is it a meta item ?
   *@param $meta_type meta type table ID
   *
   *@return select string
   *
   **/
   static function addSelect ($itemtype, $ID, $num, $meta=0, $meta_type=0) {

      $searchopt = &Search::getOptions($itemtype);
      $table     = $searchopt[$ID]["table"];
      $field     = $searchopt[$ID]["field"];
      $addtable  = "";
      $NAME      = "ITEM";

      /*if ($table != getTableForItemType($itemtype)
         && $searchopt[$ID]["linkfield"] != getForeignKeyFieldForTable($table)) {
         $addtable .= "_".$searchopt[$ID]["linkfield"];
      }*/

      if (isset($searchopt[$ID]['joinparams'])) {
         $complexjoin = Search::computeComplexJoinID($searchopt[$ID]['joinparams']);

         if (!empty($complexjoin)) {
            $addtable .= "_".$complexjoin;
         }
      }

      if ($meta) {
         $NAME = "META";
         if (getTableForItemType($meta_type)!=$table) {
            $addtable .= "_".$meta_type;
         }
      }

      // Plugin can override core definition for its type
      if ($plug=isPluginItemType($itemtype)) {
         $function = 'plugin_'.$plug['plugin'].'_addSelect';
         if (function_exists($function)) {
            $out = $function($itemtype,$ID,$num);
            if (!empty($out)) {
               return $out;
            }
         }
      }

      switch ($table.".".$field) {
         case "glpi_users_validation.name" :
         case "glpi_users.name" :
            if ($itemtype != 'User') {
               if ((isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"])) {
                  return " GROUP_CONCAT(DISTINCT `$table$addtable`.`id` SEPARATOR '$$$$')
                              AS ".$NAME."_".$num.",";
               }
               return " `$table$addtable`.`$field` AS ".$NAME."_$num,
                       `$table$addtable`.`realname` AS ".$NAME."_".$num."_2,
                       `$table$addtable`.`id`  AS ".$NAME."_".$num."_3,
                       `$table$addtable`.`firstname` AS ".$NAME."_".$num."_4, ";
            }
            break;

         case "glpi_groups.name" :
            if ($itemtype != 'Group' && $itemtype != 'User') {
               return " `$table$addtable`.`$field` AS ".$NAME."_$num, ";
            }
            break;
         case "glpi_tickets.count" :
         case "glpi_ticketfollowups.count" :
         case "glpi_tickettasks.count" :
            return " COUNT(DISTINCT `$table$addtable`.`id`) AS ".$NAME."_".$num.", ";

         case "glpi_networkports.mac" :
            $port = " GROUP_CONCAT(DISTINCT `$table$addtable`.`$field` SEPARATOR '$$$$')
                         AS ".$NAME."_$num, ";
            if ($itemtype == 'Computer') {
               $port .= " GROUP_CONCAT(DISTINCT `glpi_computers_devicenetworkcards`.`specificity`
                                       SEPARATOR '$$$$') AS ".$NAME."_".$num."_2, ";
            }
            return $port;

         case "glpi_complete_entities.completename" :
            if ($itemtype == 'User' && $ID == 80) {
               return " GROUP_CONCAT(`$table$addtable`.`completename` SEPARATOR '$$$$')
                           AS ".$NAME."_$num,
                        GROUP_CONCAT(`glpi_profiles_users`.`profiles_id` SEPARATOR '$$$$')
                           AS ".$NAME."_".$num."_2,
                        GROUP_CONCAT(`glpi_profiles_users`.`is_recursive` SEPARATOR '$$$$')
                           AS ".$NAME."_".$num."_3,";
            }
            break;

         case "glpi_entities.completename" :
            return " `$table$addtable`.`completename` AS ".$NAME."_$num,
                     `$table$addtable`.`id` AS ".$NAME."_".$num."_2, ";

      }

      //// Default cases
      // Link with plugin tables
      if (preg_match("/^glpi_plugin_([a-z0-9]+)/", $table, $matches)) {
         if (count($matches)==2) {
            $plug = $matches[1];
            $function = 'plugin_'.$plug.'_addSelect';
            if (function_exists($function)) {
               $out = $function($itemtype, $ID, $num);
               if (!empty($out)) {
                  return $out;
               }
            }
         }
      }

      $tocompute = "`$table$addtable`.`$field`";

      if (isset($searchopt[$ID]["computation"])) {
         $tocompute = $searchopt[$ID]["computation"];
         $tocompute = str_replace("TABLE", "`$table.$addtable`", $tocompute);
      }

      // Preformat items
      if (isset($searchopt[$ID]["datatype"])) {
         switch ($searchopt[$ID]["datatype"]) {
            case "date_delay" :
               $add_minus = '';
               if (isset($searchopt[$ID]["datafields"][3])) {
                  $add_minus = "-`$table$addtable`.`".$searchopt[$ID]["datafields"][3]."`";
               }
               if ($meta
                   || (isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"])) {
                  return " GROUP_CONCAT(DISTINCT
                                        CONCAT(`$table$addtable`.`".$searchopt[$ID]["datafields"][1]."`,
                                               ',',
                                               `$table$addtable`.`".$searchopt[$ID]["datafields"][2]."`
                                                   $add_minus) SEPARATOR '$$$$')
                              AS ".$NAME."_$num, ";
               }
               return "CONCAT(`$table$addtable`.`".$searchopt[$ID]["datafields"][1]."`,
                              ',',
                              `$table$addtable`.`".$searchopt[$ID]["datafields"][2]."`$add_minus)
                           AS ".$NAME."_$num, ";

            case "itemlink" :
               if ($meta
                  || (isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"])
                  || (empty($searchopt[$ID]["linkfield"])
                      && isset($searchopt[$ID]["itemlink_type"])
                      && $searchopt[$ID]["itemlink_type"] != $itemtype)) {
                  return " GROUP_CONCAT(DISTINCT CONCAT(`$table$addtable`.`$field`, '$$' ,
                                                        `$table$addtable`.`id`) SEPARATOR '$$$$')
                              AS ".$NAME."_$num, ";
               }
               return " $tocompute AS ".$NAME."_$num,
                        `$table$addtable`.`id` AS ".$NAME."_".$num."_2, ";
         }
      }

      // Default case
      if ($meta
         || (isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"])) {
         return " GROUP_CONCAT(DISTINCT $tocompute SEPARATOR '$$$$') AS ".$NAME."_$num, ";
      }
      return "$tocompute AS ".$NAME."_$num, ";
   }
   
   /**
    * Generic Function to add where to a request
    *
    * @param $link link string
    * @param $nott is it a negative serach ?
    * @param $itemtype item type
    * @param $ID ID of the item to search
    * @param $searchtype searchtype used (equals or contains)
    * @param $val item num in the request
    * @param $meta is a meta search (meta=2 in search.class.php)
    *
    * @return select string
   **/
   static function addWhere($link, $nott, $itemtype, $ID, $searchtype, $val, $meta=0) {
      global $LANG;

      $searchopt = &Search::getOptions($itemtype);
      $table     = $searchopt[$ID]["table"];
      $field     = $searchopt[$ID]["field"];

      $inittable = $table;
      $addtable = '';
      /*if ($table != getTableForItemType($itemtype)
          && $searchopt[$ID]["linkfield"] != getForeignKeyFieldForTable($table)) {
         $addtable = "_".$searchopt[$ID]["linkfield"];
         $table .= $addtable;
      }*/

      if (isset($searchopt[$ID]['joinparams'])) {
         $complexjoin = Search::computeComplexJoinID($searchopt[$ID]['joinparams']);

         if (!empty($complexjoin)) {
            $table .= "_".$complexjoin;
         }
      }


      if ($meta && getTableForItemType($itemtype)!=$table) {
         $table .= "_".$itemtype;
      }

      // Hack to allow search by ID on every sub-table
      if (preg_match('/^\$\$\$\$([0-9]+)$/',$val,$regs)) {
         return $link." (`$table`.`id` ".($nott?"<>":"=").$regs[1]." ".
                ($regs[1]==0?" OR `$table`.`id` IS NULL":'').") ";
      }

      // Preparse value
      if (isset($searchopt[$ID]["datatype"])) {
         switch ($searchopt[$ID]["datatype"]) {
            case "datetime" :
            case "date" :
            case "date_delay" :
               $format_use = "Y-m-d";
               if ($searchopt[$ID]["datatype"]=='datetime') {
                  $format_use = "Y-m-d H:i:s";
               }
               // Parsing relative date
               if ($val=='NOW') {
                  $val = date($format_use);
               }
               if (preg_match("/^(-?)(\d+)(\w+)$/",$val,$matches)) {
                  if (in_array($matches[3], array('YEAR', 'MONTH', 'WEEK', 'DAY', 'HOUR'))) {
                     $nb = intval($matches[2]);
                     if ($matches[1]=='-') {
                        $nb = -$nb;
                     }
                     // Use it to have a clean delay computation (MONTH / YEAR have not always the same duration)
                     $hour   = date("H");
                     $minute = date("i");
                     $second = 0;
                     $month  = date("n");
                     $day    = date("j");
                     $year   = date("Y");

                     switch ($matches[3]) {
                        case "YEAR" :
                           $year += $nb;
                           break;

                        case "MONTH" :
                           $month += $nb;
                           break;

                        case "WEEK" :
                           $day += 7*$nb;
                           break;

                        case "DAY" :
                           $day += $nb;
                           break;

                        case "HOUR" :
                           $hour += $nb;
                           break;
                     }
                     $val = date($format_use, mktime ($hour, $minute, $second, $month, $day, $year));
                  }
               }
               break;
         }
      }

      switch ($searchtype) {
         case "contains" :
            $SEARCH = Search::makeTextSearch($val, $nott);
            break;

         case "equals" :
            if ($nott) {
               $SEARCH = " <> '$val'";
            } else {
               $SEARCH = " = '$val'";
            }
            break;

         case "notequals" :
            if ($nott) {
               $SEARCH = " = '$val'";
            } else {
               $SEARCH = " <> '$val'";
            }
            break;
      }

      // Plugin can override core definition for its type
      if ($plug=isPluginItemType($itemtype)) {
         $function = 'plugin_'.$plug['plugin'].'_addWhere';
         if (function_exists($function)) {
            $out = $function($link,$nott,$itemtype,$ID,$val);
            if (!empty($out)) {
               return $out;
            }
         }
      }

      switch ($inittable.".".$field) {
//          case "glpi_users_validation.name" :
         case "glpi_users.name" :
            if ($itemtype == 'User') { // glpi_users case / not link table
               if (in_array($searchtype, array('equals', 'notequals'))) {
                  return " $link `$table`.`id`".$SEARCH;
               }
               return Search::makeTextCriteria("`$table`.`$field`", $val, $nott, $link);
            }
            if ($_SESSION["glpinames_format"]==FIRSTNAME_BEFORE) {
               $name1 = 'firstname';
               $name2 = 'realname';
            } else {
               $name1 = 'realname';
               $name2 = 'firstname';
            }

            if (in_array($searchtype, array('equals', 'notequals'))) {
               return " $link (`$table`.`id`".$SEARCH.
                               ($val==0?" OR `$table`.`id` IS NULL":'').') ';
            }
            return $link." (`$table`.`$name1` $SEARCH
                            OR `$table`.`$name2` $SEARCH
                            OR CONCAT(`$table`.`$name1`, ' ',
                                      `$table`.`$name2`) $SEARCH".
                            Search::makeTextCriteria("`$table`.`$field`",$val,$nott,'OR').") ";

         case "glpi_groups.name" :
            $linkfield = "";
            if (in_array($searchtype, array('equals', 'notequals'))) {
               return " $link (`$table`.`id`".$SEARCH.
                               ($val==0?" OR `$table`.`id` IS NULL":'').') ';
            }
            return Search::makeTextCriteria("`$table`.`$field`", $val, $nott, $link);
      }

      //// Default cases

      // Link with plugin tables
      if (preg_match("/^glpi_plugin_([a-z0-9]+)/", $inittable, $matches)) {
         if (count($matches)==2) {
            $plug = $matches[1];
            $function = 'plugin_'.$plug.'_addWhere';
            if (function_exists($function)) {
               $out = $function($link, $nott, $itemtype, $ID, $val);
               if (!empty($out)) {
                  return $out;
               }
            }
         }
      }

      $tocompute = "`$table`.`$field`";
      if (isset($searchopt[$ID]["computation"])) {
         $tocompute = $searchopt[$ID]["computation"];
         $tocompute = str_replace("TABLE", "`$table`", $tocompute);
      }

      // Preformat items
      if (isset($searchopt[$ID]["datatype"])) {
         switch ($searchopt[$ID]["datatype"]) {
            case "itemtypename" :
               if (in_array($searchtype, array('equals', 'notequals'))) {
                  return " $link (`$table`.`$field`".$SEARCH.') ';
               }

            case "datetime" :
            case "date" :
            case "date_delay" :

               if ($searchopt[$ID]["datatype"]=='datetime') {
                  // Specific search for datetime
                  if (in_array($searchtype, array('equals', 'notequals'))) {
                     $val = preg_replace("/:00$/",'',$val);
                     $val = '^'.$val;
                     if ($searchtype=='notequals') {
                        $nott = !$nott;
                     }
                     return Search::makeTextCriteria("`$table`.`$field`", $val, $nott, $link);
                  }
               }

               if ($searchtype=='lessthan') {
                 $val = '<'.$val;
               }
               if ($searchtype=='morethan') {
                 $val = '>'.$val;
               }

               if ($searchtype) {
                  $date_computation = $tocompute;
               }

               $search_unit = ' MONTH ';
               if (isset($searchopt[$ID]['searchunit'])) {
                  $search_unit = $searchopt[$ID]['searchunit'];
               }

               if ($searchopt[$ID]["datatype"]=="date_delay") {
                  $delay_unit = ' MONTH ';
                  if (isset($searchopt[$ID]['delayunit'])) {
                     $delay_unit = $searchopt[$ID]['delayunit'];
                  }
                  $date_computation = "ADDDATE(`$table`.".$searchopt[$ID]["datafields"][1].",
                                               INTERVAL `$table`.".$searchopt[$ID]["datafields"][2]."
                                               $delay_unit)";
               }
               if (in_array($searchtype, array('equals', 'notequals'))) {
                  return " $link ($date_computation ".$SEARCH.') ';
               }

               $search  = array("/\&lt;/","/\&gt;/");
               $replace = array("<",">");

               $val = preg_replace($search,$replace,$val);

               if (preg_match("/^\s*([<>=]+)(.*)/",$val,$regs)) {
                  if (is_numeric($regs[2])) {
                     return $link." $date_computation ".$regs[1]."
                            ADDDATE(NOW(), INTERVAL ".$regs[2]." $search_unit) ";
                  }
                  // ELSE Reformat date if needed
                  $regs[2] = preg_replace('@(\d{1,2})(-|/)(\d{1,2})(-|/)(\d{4})@','\5-\3-\1',
                                          $regs[2]);
                  if (preg_match('/[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}/', $regs[2])) {
                     return $link." $date_computation ".$regs[1]." '".$regs[2]."'";
                  }
                  return "";
               }
               // ELSE standard search
               // Date format modification if needed
               $val = preg_replace('@(\d{1,2})(-|/)(\d{1,2})(-|/)(\d{4})@','\5-\3-\1', $val);
               return Search::makeTextCriteria($date_computation, $val, $nott, $link);

            case "right" :
               if ($val=='NULL' || $val=='null') {
                  return $link." $tocompute IS ".($nott?'NOT':'')." NULL ";
               }
               return $link." $tocompute = '$val' ";

            case "bool" :
               if (!is_numeric($val)) {
                  if (strcasecmp($val,$LANG['choice'][0])==0) {
                     $val = 0;
                  } else if (strcasecmp($val,$LANG['choice'][1])==0) {
                     $val = 1;
                  }
               }
               // No break here : use number comparaison case

            case "number" :
            case "decimal" :
            case "timestamp" :
               $search = array("/\&lt;/",
                               "/\&gt;/");
               $replace = array("<",
                                ">");
               $val = preg_replace($search, $replace, $val);

               if (preg_match("/([<>])([=]*)[[:space:]]*([0-9]+)/", $val, $regs)) {
                  if ($nott) {
                     if ($regs[1]=='<') {
                        $regs[1] = '>';
                     } else {
                        $regs[1] = '<';
                     }
                  }
                  $regs[1] .= $regs[2];
                  return $link." ($tocompute ".$regs[1]." ".$regs[3].") ";
               }
               if (is_numeric($val)) {
                  if (isset($searchopt[$ID]["width"])) {
                     $ADD = "";
                     if ($nott && $val!='NULL' && $val!='null') {
                        $ADD = " OR $tocompute IS NULL";
                     }
                     if ($nott) {
                        return $link." ($tocompute < ".(intval($val) - $searchopt[$ID]["width"])."
                                        OR $tocompute > ".(intval($val) + $searchopt[$ID]["width"])."
                                        $ADD) ";
                     }
                     return $link." (($tocompute >= ".(intval($val) - $searchopt[$ID]["width"])."
                                      AND $tocompute <= ".(intval($val) + $searchopt[$ID]["width"]).")
                                     $ADD) ";
                  }
                  if (!$nott) {
                     return " $link ($tocompute = ".(intval($val)).") ";
                  }
                  return " $link ($tocompute <> ".(intval($val)).") ";
               }
               break;
         }
      }

      // Default case
      if (in_array($searchtype, array('equals', 'notequals'))) {
         $out = " $link (`$table`.`id`".$SEARCH;
         if ($searchtype=='notequals') {
            $nott = !$nott;
         }
         // Add NULL if $val = 0 and not negative search
         if ((!$nott && $val==0)) {
            $out .= " OR `$table`.`id` IS NULL";
         }
         $out .= ')';
         return $out;
      }
      return Search::makeTextCriteria($tocompute,$val,$nott,$link);
   }
}

?>