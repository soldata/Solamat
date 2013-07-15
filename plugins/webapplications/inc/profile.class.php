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

class PluginWebapplicationsProfile extends CommonDBTM {

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_webapplications']['profile'][0];
   }


   function canCreate() {
      return Session::haveRight('profile', 'w');
   }


   function canView() {
      return Session::haveRight('profile', 'r');
   }


   /**
    * if profile deleted
   **/
   static function purgeProfiles(Profile $prof) {

      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }


   function getFromDBByProfile($profiles_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `profiles_id` = '" . $profiles_id . "' ";

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

   static function createFirstAccess($ID) {

      $myProf = new self();
      if (!$myProf->getFromDBByProfile($ID)) {
         $myProf->add(array('profiles_id'     => $ID,
                            'webapplications' => 'w',
                            'open_ticket'     => '1'));
      }
   }


   function createAccess($profile) {
      return $this->add(array('profiles_id' => $profile->getField('id')));
   }


   static function changeProfile() {

      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_webapplications_profile"]       = $prof->fields;
         $_SESSION['glpiactiveprofile']['helpdesk_item_type'][] = "PluginWebapplicationsWebapplication";
      } else {
         unset($_SESSION["glpi_plugin_webapplications_profile"]);
         unset($_SESSION["glpiactiveprofile"]['helpdesk_item_type']['PluginWebapplicationsWebapplication']);
      }
   }


   /**
    * profiles modification
   **/
   function showForm($ID, $options=array()) {
      global $LANG;

      $target = $this->getFormURL();
      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if (!Session::haveRight("profile","r")) {
         return false;
      }

      $prof = new Profile();
      if ($ID) {
         $this->getFromDBByProfile($ID);
         $prof->getFromDB($ID);
      }

      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4' class='center b'>".$LANG['plugin_webapplications']['profile'][0]." ".
            Dropdown::getDropdownName("glpi_profiles", $ID)."</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>".$LANG['plugin_webapplications']['profile'][1]." : </td><td>";
      if ($prof->fields['interface'] != 'helpdesk') {
         Profile::dropdownNoneReadWrite("webapplications", $this->fields["webapplications"], 1, 1, 1);
      } else {
         echo $LANG['profiles'][12]; // No access;
      }
      echo "</td>";

      echo "<td>" . $LANG['setup'][352] . " - " . $LANG['plugin_webapplications'][4] . " : </td><td>";
      if ($prof->fields['create_ticket']) {
         Dropdown::showYesNo("open_ticket", $this->fields["open_ticket"]);
      } else {
         echo Dropdown::getYesNo(0);
      }
      echo "</td>";
      echo "</tr>";

      echo "<input type='hidden' name='profiles_id' value=".$this->fields["profiles_id"].">";
      echo "<input type='hidden' name='id' value=".$this->fields["id"].">";

      $options['candel'] = false;
      $this->showFormButtons($options);

   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType() == 'Profile' && !$withtemplate) {
         return $LANG['plugin_webapplications'][4];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         $prof = new self();
         $ID = $item->getField('id');
         if (!$prof->GetfromDB($ID)) {
            $prof->createAccess($item);
         }
         $prof->showForm($ID);
      }
      return true;
   }


   function getIndexName() {
      return "profiles_id";
   }

}
?>