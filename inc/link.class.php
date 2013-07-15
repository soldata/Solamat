<?php
/*
 * @version $Id: link.class.php 20130 2013-02-04 16:55:15Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// CLASSES link
class Link extends CommonDBTM {

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['title'][33];
      }
      return $LANG['title'][32];
   }


   function canCreate() {
      return Session::haveRight('link', 'w');
   }


   function canView() {
      return Session::haveRight('link', 'r');
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (Session::haveRight("link","r")) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry($LANG['title'][34],
                                        countElementsInTable('glpi_links_itemtypes',
                                                             "`itemtype` = '".$item->getType()."'"));
         }
         return $LANG['title'][34];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      self::showForItem($item);
      return true;
   }


   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $this->addStandardTab('Link_ItemType', $ong, $options);

      return $ong;
   }


   function cleanDBonPurge() {
      global $DB;

      $query2 = "DELETE FROM `glpi_links_itemtypes`
                 WHERE `links_id` = '".$this->fields['id']."'";
      $DB->query($query2);
   }


   /**
   * Print the link form
   *
   * @param $ID integer ID of the item
   * @param $options array
   *     - target filename : where to go when done.
   *
   *@return Nothing (display)
   *
   **/
   function showForm($ID, $options=array()) {
      global $LANG;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td height='23'>".$LANG['links'][6]."&nbsp;:</td>";
      echo "<td colspan='3'>[LOGIN], [ID], [NAME], [LOCATION], [LOCATIONID], [IP], [MAC], [NETWORK],
                            [DOMAIN], [SERIAL], [OTHERSERIAL], [USER], [GROUP]</td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['common'][16]."&nbsp;:</td>";
      echo "<td colspan='3'>";
      Html::autocompletionTextField($this, "name", array('size' => 84));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['links'][1]."&nbsp;:</td>";
      echo "<td colspan='2'>";
      Html::autocompletionTextField($this, "link", array('size' => 84));
      echo "</td><td width='1'></td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['links'][9]."&nbsp;:</td>";
      echo "<td colspan='3'>";
      echo "<textarea name='data' rows='10' cols='96'>".$this->fields["data"]."</textarea>";
      echo "</td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common'] = $LANG['common'][32];

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = $LANG['common'][16];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      $tab[1]['massiveaction'] = false;

      $tab[2]['table']         = $this->getTable();
      $tab[2]['field']         = 'id';
      $tab[2]['name']          = $LANG['common'][2];
      $tab[2]['massiveaction'] = false;

      $tab[3]['table']     = $this->getTable();
      $tab[3]['field']     = 'link';
      $tab[3]['name']      = $LANG['links'][1];
      $tab[3]['datatype']  = 'string';

      $tab[80]['table']         = 'glpi_entities';
      $tab[80]['field']         = 'completename';
      $tab[80]['name']          = $LANG['entity'][0];
      $tab[80]['massiveaction'] = false;

      return $tab;
   }


   /**
   * Generate link
   *
   * @param $link string : original string content
   * @param $item CommonDBTM : item used to make replacements
   *
   * @return array of link contents (may have several when item have several IP / MAC cases)
   */
   static function generateLinkContents($link, CommonDBTM $item) {
      global $DB;

      if (strstr($link,"[ID]")) {
         $link = str_replace("[ID]", $item->fields['id'],$link);
      }
      if (strstr($link,"[LOGIN]") && isset($_SESSION["glpiname"])) {
         if (isset($_SESSION["glpiname"])) {
            $link = str_replace("[LOGIN]", $_SESSION["glpiname"],$link);
         }
      }

      if (strstr($link,"[NAME]")) {
         $link = str_replace("[NAME]", $item->getName(), $link);
      }
      if (strstr($link,"[SERIAL]")) {
         if ($item->isField('serial')) {
            $link = str_replace("[SERIAL]", $item->getField('serial'), $link);
         }
      }
      if (strstr($link,"[OTHERSERIAL]")) {
         if ($item->isField('otherserial')) {
            $link=str_replace("[OTHERSERIAL]",$item->getField('otherserial'),$link);
         }
      }
      if (strstr($link,"[LOCATIONID]")) {
         if ($item->isField('locations_id')) {
            $link = str_replace("[LOCATIONID]", $item->getField('locations_id'), $link);
         }
      }
      if (strstr($link,"[LOCATION]")) {
         if ($item->isField('locations_id')) {
            $link = str_replace("[LOCATION]",
                                Dropdown::getDropdownName("glpi_locations",
                                                          $item->getField('locations_id')), $link);
         }
      }
      if (strstr($link,"[NETWORK]")) {
         if ($item->isField('networks_id')) {
            $link = str_replace("[NETWORK]",
                                Dropdown::getDropdownName("glpi_networks",
                                                          $item->getField('networks_id')), $link);
         }
      }
      if (strstr($link,"[DOMAIN]")) {
         if ($item->isField('domains_id')) {
            $link = str_replace("[DOMAIN]",
                                Dropdown::getDropdownName("glpi_domains",
                                                          $item->getField('domains_id')), $link);
         }
      }
      if (strstr($link,"[USER]")) {
         if ($item->isField('users_id')) {
            $link = str_replace("[USER]",
                                Dropdown::getDropdownName("glpi_users",
                                                          $item->getField('users_id')), $link);
         }
      }
      if (strstr($link,"[GROUP]")) {
         if ($item->isField('groups_id')) {
            $link = str_replace("[GROUP]",
                                Dropdown::getDropdownName("glpi_groups",
                                                          $item->getField('groups_id')), $link);
         }
      }
      $ipmac = array();

      if (!strstr($link,"[IP]") && !strstr($link,"[MAC]")) {
         return array($link);

      } else { // Return sevral links id several IP / MAC
         $links = array();
         $query2 = "SELECT `id`, `ip`, `mac`, `logical_number`
                    FROM `glpi_networkports`
                    WHERE `items_id` = '".$item->fields['id']."'
                          AND `itemtype` = '".get_class($item)."'
                    ORDER BY `logical_number`";
         $result2 = $DB->query($query2);

         if (get_class($item) == 'NetworkEquipment') {
            $ipmac[0]['ip']     = $item->getField('ip');
            $ipmac[0]['mac']    = $item->getField('mac');
            $ipmac[0]['number'] = '';
         }
         if ($DB->numrows($result2)>0) {
            while ($data2=$DB->fetch_array($result2)) {
               $ipmac[$data2['id']]['ip']     = $data2["ip"];
               $ipmac[$data2['id']]['mac']    = $data2["mac"];
               $ipmac[$data2['id']]['number'] = $data2["logical_number"];
            }
         }

         if (count($ipmac)>0) {
            foreach ($ipmac as $key => $val) {
               $tmplink = $link;
               $disp    = 1;
               if (strstr($link,"[IP]")) {
                  if (empty($val['ip'])) {
                     $disp = 0;
                  } else {
                     $tmplink = str_replace("[IP]", $val['ip'], $tmplink);
                  }
               }
               if (strstr($link,"[MAC]")) {
                  if (empty($val['mac'])) {
                     $disp = 0;
                  } else {
                     $tmplink = str_replace("[MAC]", $val['mac'], $tmplink);
                  }
               }

               if ($disp) {
                  $links[$key] = $tmplink;
               }
            }
         }

         if (count($links)) {
            return $links;
         }
         return array($link);
      }
   }


   /**
    * Show Links for an item
    *
    * @param $item CommonDBTM object
    * @param $withtemplate integer : withtemplate param
   **/
   static function showForItem(CommonDBTM $item, $withtemplate='') {
      global $DB, $LANG, $CFG_GLPI;

      if (!Session::haveRight("link","r")) {
         return false;
      }

      if ($item->isNewID($item->getID())) {
         return false;
      }

      $query = "SELECT `glpi_links`.`id`,
                       `glpi_links`.`link` AS link,
                       `glpi_links`.`name` AS name ,
                       `glpi_links`.`data` AS data
                FROM `glpi_links`
                INNER JOIN `glpi_links_itemtypes`
                     ON `glpi_links`.`id` = `glpi_links_itemtypes`.`links_id`
                WHERE `glpi_links_itemtypes`.`itemtype`='".$item->getType()."' " .
                      getEntitiesRestrictRequest(" AND", "glpi_links", "entities_id",
                                                 $item->getEntityID(), true)."
                ORDER BY name";

      $result = $DB->query($query);

      echo "<div class='spaced'><table class='tab_cadre_fixe'>";

      if ($DB->numrows($result)>0) {
         echo "<tr><th>".$LANG['title'][33]."</th></tr>";
         while ($data=$DB->fetch_assoc($result)) {
            $name = $data["name"];
            if (empty($name)) {
               $name = $data["link"];
            }
            $names = self::generateLinkContents($name, $item);

            $file = trim($data["data"]);

            if (empty($file)) {
               // Generate links
               $links = self::generateLinkContents($data['link'], $item);
               $i     = 1;
               foreach ($links as $key => $link) {
                  $name =  (isset($names[$key]) ? $names[$key] : reset($names));
                  echo "<tr class='tab_bg_2'>";
                  $url = $link;
                  echo "<td class='center'><a href='$url' target='_blank'>$name #$i : $link</a>";
                  echo "</td></tr>";
                  $i++;
               }
            } else {
               // Generate files
               $files = self::generateLinkContents($data['link'], $item);
               $links = self::generateLinkContents($data['data'], $item);
               $i     = 1;
               foreach ($links as $key => $link) {
                  $name =  (isset($names[$key]) ? $names[$key] : reset($names));
                  if (isset($files[$key])) {
                     // a different name for each file, ex name = foo-[IP].txt
                     $file = $files[$key];
                  } else {
                     // same name for all files, ex name = foo.txt
                     $file = reset($files);
                  }
                  echo "<tr class='tab_bg_2'>";
                  $url = $CFG_GLPI["root_doc"]."/front/link.send.php?lID=".$data['id'].
                         "&amp;itemtype=".$item->getType().
                         "&amp;id=".$item->getID()."&amp;rank=$key";
                  echo "<td class='center'><a href='$url' target='_blank'>$name #$i : $file</a>";
                  echo "</td></tr>";
                  $i++;
               }
            }
         }
         echo "</table></div>";

      } else {
         echo "<tr class='tab_bg_2'><th>".$LANG['title'][33]."</th></tr>";
         echo "<tr class='tab_bg_2'><td class='center b'>".$LANG['links'][7]."</td></tr>";
         echo "</table></div>";
      }
   }
}
?>