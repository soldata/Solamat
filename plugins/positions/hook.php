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

function plugin_positions_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/positions/inc/profile.class.php");
   
   if (!TableExists("glpi_plugin_positions_positions")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/positions/sql/empty-2.0.0.sql");

   }
   //v1.0.0 to V2.0.0
   if (TableExists("glpi_plugin_positions_positions_items") 
         && !FieldExists("glpi_plugin_positions_positions_items","items_id")) {
      
      $query="ALTER TABLE `glpi_plugin_positions_positions` 
      ADD `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)';";
      $result=$DB->query($query);
      
      $query="ALTER TABLE `glpi_plugin_positions_positions` 
      ADD `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file';";
      $result=$DB->query($query);
      
      $query_="SELECT *
            FROM `glpi_plugin_positions_positions_items` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_positions_positions`
                  SET `items_id` = '".$data["items_id"]."',
                  `itemtype` = '".$data["itemtype"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="DROP TABLE `glpi_plugin_positions_positions_items`;";
      $result=$DB->query($query);
	}
	//v1.0.0 to V2.0.0
	if (!TableExists("glpi_plugin_positions_infos")) {
      
      $query="CREATE TABLE `glpi_plugin_positions_infos` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   `fields` text collate utf8_unicode_ci,
   `comment` text collate utf8_unicode_ci,
   `notepad` longtext collate utf8_unicode_ci,
   `date_mod` datetime NULL default NULL,
   `is_active` tinyint(1) NOT NULL DEFAULT '0',
   `is_deleted` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
      $result=$DB->query($query);
	}
	
	//to V3.0.0
	if (TableExists("glpi_plugin_positions_positions") 
         && FieldExists("glpi_plugin_positions_positions","documents_id")) {
      
      $query="ALTER TABLE `glpi_plugin_positions_positions` DROP `documents_id`;";
      $result=$DB->query($query);
	}
	
	$rep_files_positions = GLPI_PLUGIN_DOC_DIR."/positions";
	if (!is_dir($rep_files_positions))
      mkdir($rep_files_positions);
        
   PluginPositionsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_positions_uninstall() {
	global $DB;

	$tables = array("glpi_plugin_positions_positions",
                  "glpi_plugin_positions_positions_items",
                  "glpi_plugin_positions_profiles",
                  "glpi_plugin_positions_imageitems",
                  "glpi_plugin_positions_infos");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   $rep_files_positions = GLPI_PLUGIN_DOC_DIR."/positions";

	Toolbox::deleteDir($rep_files_positions);
	
	$tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs",
					"glpi_tickets");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginPositionsPosition' ;");
	
	return true;
}


// Define dropdown relations
function plugin_positions_getDatabaseRelations() {

	$plugin = new Plugin();

	if ($plugin->isActivated("positions"))
		return array ("glpi_profiles" => array ("glpi_plugin_positions_profiles" => "profiles_id"),
                     "glpi_entities"=>array("glpi_plugin_positions_positions"=>"entities_id"));
                     //"glpi_locations"=>array("glpi_plugin_positions_positions"=>"locations_id"),
                     
	else
		return array();
}

////// SEARCH FUNCTIONS ///////() {

function plugin_positions_getAddSearchOptions($itemtype) {
	global $LANG;
    
   $sopt=array();

   if (in_array($itemtype, PluginPositionsPosition::getTypes(true))) {
      if (plugin_positions_haveRight("positions","r")) {
         $sopt[4415]['table']='glpi_plugin_positions_positions';
         $sopt[4415]['field']='name';
         $sopt[4415]['linkfield']='';
         $sopt[4415]['name']=$LANG['plugin_positions']['title'][1]." - ".
                              $LANG['plugin_positions'][7];
         $sopt[4415]['forcegroupby']='1';
         $sopt[4415]['datatype']='itemlink';
         $sopt[4415]['itemlink_type']='PluginPositionsPosition';

      }
	}
	return $sopt;
}

function plugin_positions_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables) {

	switch ($new_table) {

		case "glpi_plugin_positions_positions" : // From items
			$out= " LEFT JOIN `glpi_plugin_positions_positions` 
                        ON (`$ref_table`.`id` = 
                              `glpi_plugin_positions_positions`.`items_id` 
                              AND `glpi_plugin_positions_positions`.`itemtype` = '$type') ";
			return $out;
			break;
	}

	return "";
}

function plugin_positions_giveItem($type,$ID,$data,$num) {
	global $CFG_GLPI, $DB,$LANG;

	$searchopt=&Search::getOptions($type);
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];

	switch ($table.'.'.$field) {
		case "glpi_plugin_positions_positions.items_id" :
			$query_device = "SELECT DISTINCT `itemtype`
							FROM `glpi_plugin_positions_positions`
							WHERE `id` = '".$data['id']."'
							ORDER BY `itemtype`";
			$result_device = $DB->query($query_device);
			$number_device = $DB->numrows($result_device);
			$y = 0;
			$out='';
			$positions_id=$data['id'];
			if ($number_device>0) {
				for ($i=0 ; $i < $number_device ; $i++) {
					$column = "name";
					$itemtype = $DB->result($result_device, $i, "itemtype");
					
					if (!class_exists($itemtype)) {
                  continue;
               }
					$item = new $itemtype();
					if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);

						$query = "SELECT `".$table_item."`.*, `glpi_plugin_positions_positions`.`id` AS items_id, `glpi_entities`.`id` AS entity "
						." FROM `glpi_plugin_positions_positions`, `".$table_item
						."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`) "
						." WHERE `".$table_item."`.`id` = `glpi_plugin_positions_positions`.`items_id`
						AND `glpi_plugin_positions_positions`.`itemtype` = '$itemtype'
						AND `glpi_plugin_positions_positions`.`id` = '".$positions_id."' "
						. getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

						if ($item->maybeTemplate()) {
							$query.=" AND `".$table_item."`.`is_template` = '0'";
                  }
						$query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column`";

						if ($result_linked = $DB->query($query))
							if ($DB->numrows($result_linked)) {
								$item = new $itemtype();
								while ($data = $DB->fetch_assoc($result_linked)) {
                           if ($item->getFromDB($data['id'])) {
                              $out .= $item->getTypeName()." - ".$item->getLink()."<br>";
                           }
								}
							} else
								$out.= ' ';
               } else
                  $out.=' ';
				}
			}
         return $out;
         break;

	}
	return "";
}

function plugin_positions_MassiveActions($type) {
	global $LANG;

	if (in_array($type, PluginPositionsPosition::getTypes(true))) {
			return array("plugin_positions_add_item"=>$LANG['plugin_positions'][1],
                      "plugin_positions_del_item"=>$LANG['plugin_positions'][14]);
		}
	return array();
}

function plugin_positions_MassiveActionsDisplay($options=array()) {
	global $LANG;
	
	if (in_array($options['itemtype'], PluginPositionsPosition::getTypes(true))) {
      switch ($options['action']) {
         //add item
         case "plugin_positions_add_item":
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".
                  $LANG['buttons'][2]."\" >";
            break;
         //delete item
         case "plugin_positions_del_item":
            echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".
                  $LANG['buttons'][2]."\" >";
            break;
      }            
   }
	return "";
}

function plugin_positions_MassiveActionsProcess($data) {
   global $CFG_GLPI;
   
   $pos=new PluginPositionsPosition();
			
	switch ($data['action']) {
      //add item
		case "plugin_positions_add_item":
         $i=0;
			foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $entity=$_SESSION["glpiactive_entity"];
               $item = new $data['itemtype'];
               $input = array('items_id'      => $key,
                              'itemtype'      => $data['itemtype'],
                              'entities_id'   => $entity,
                              'x_coordinates' => $i,
                              'massiveaction' => 1);
               
               $restrict = "`items_id` = '".$input["items_id"]."'
                     AND `itemtype` = '".$input["itemtype"]."'";
               if(countElementsInTable("glpi_plugin_positions_positions",$restrict) == 0) {
                  $pos->check(-1,'w',$input);
                  $pos->add($input);
                  $i=$i+35;
               }
            }
         }
         /*Html::redirect($CFG_GLPI["root_doc"].
               "/plugins/positions/front/map.php?documents_id=".$data['documents_id']);*/
         break;
      case "plugin_positions_del_item":
			foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('items_id'      => $key,
                              'itemtype'      => $data['itemtype']
                              );
               
               $restrict = "`items_id` = '".$input["items_id"]."'
                     AND `itemtype` = '".$input["itemtype"]."'";
               $items = getAllDatasFromTable("glpi_plugin_positions_positions", $restrict);
               if (!empty($items)) {
                  foreach ($items as $item) {
                     $input = array('id' => $item["id"],
                                    'delete' => 'delete');
                  }
                  $pos->check($input['id'],'w');
                  $pos->delete($input,1);
               }
            }
         }
         break;
	}
}

function plugin_positions_postinit() {
   global $CFG_GLPI, $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['positions'] = array();

   foreach (PluginPositionsPosition::getTypes(true) as $type) {

      $PLUGIN_HOOKS['item_purge']['positions'][$type]
         = array('PluginPositionsPosition','purgePositions');

      CommonGLPI::registerStandardTab($type, 'PluginPositionsPosition');
   }
}

?>