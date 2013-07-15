<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Accounts plugin for GLPI
 Copyright (C) 2003-2011 by the accounts Development Team.

 https://forge.indepnet.net/projects/accounts
 -------------------------------------------------------------------------

 LICENSE

 This file is part of accounts.

 accounts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 accounts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with accounts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginAccountsAesKey extends CommonDBTM {
	
	static function getTypeName() {
      global $LANG;

      return $LANG['plugin_accounts'][23];
   }
   
   function canCreate() {
      return plugin_accounts_haveRight('accounts', 'w');
   }

   function canView() {
      return plugin_accounts_haveRight('accounts', 'r');
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         if ($item->getType()=='PluginAccountsHash') {
            return $LANG['plugin_accounts'][44];

         }
      }
      return '';
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
   
      $self = new self();
      
      if ($item->getType()=='PluginAccountsHash') {
         $key = self::checkIfAesKeyExists($item->getID());
         if ($key) {
            PluginAccountsAesKey::showAesKey($item->getID());
         }
         if (!$key)
            $self->showForm("", array('plugin_accounts_hashes_id' => $item->getID()));

      }
      return true;
   }
   
   function getFromDBByHash($plugin_accounts_hashes_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `plugin_accounts_hashes_id` = '" . $plugin_accounts_hashes_id . "' ";
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
   
   static function checkIfAesKeyExists($plugin_accounts_hashes_id) {
      
      $aeskey = false;
      if ($plugin_accounts_hashes_id) {
         $devices = getAllDatasFromTable("glpi_plugin_accounts_aeskeys",
                                 "`plugin_accounts_hashes_id` = '$plugin_accounts_hashes_id' ");
         if (!empty($devices)) {
            foreach ($devices as $device) {
               $aeskey = $device["name"];
               return $aeskey;
            }
         } else
            return false;
      }
   }
   
   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }
   
	function showForm($ID, $options=array()) {
		global $LANG;
      
      if (!$this->canView()) return false;
      
      $hash = new PluginAccountsHash();
      $restrict = getEntitiesRestrictRequest(" ","glpi_plugin_accounts_hashes",'','',$hash->maybeRecursive());
      if(countElementsInTable("glpi_plugin_accounts_hashes",$restrict) == 0) {
         echo "<div class='center red'>".$LANG['plugin_accounts'][43]."</div></br>";
      }
      
      $plugin_accounts_hashes_id= -1;
      if (isset($options['plugin_accounts_hashes_id'])) {
         $plugin_accounts_hashes_id = $options['plugin_accounts_hashes_id'];
      }
        
      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         $input=array('plugin_accounts_hashes_id' => $options['plugin_accounts_hashes_id']);
         $this->check(-1,'w',$input);
      }
      
      echo "<div class='center red'>".$LANG['plugin_accounts'][33]."</div></br>";
      
      $options['colspan'] = 1;
      if ($ID > 0)
         $this->showTabs($options);
      $this->showFormHeader($options);
      
      $hash->getFromDB($plugin_accounts_hashes_id);
      echo "<input type='hidden' name='plugin_accounts_hashes_id' value='$plugin_accounts_hashes_id'>";
      
      echo "<tr class='tab_bg_2'><td>";
      echo $LANG['plugin_accounts'][23];
      echo "</td><td>";
      echo "<input type='password' autocomplete='off' name='name' value='".$this->fields["name"]."'>";
      echo "</td>";
      echo "</tr>";
      $options['candel'] = false;
      $this->showFormButtons($options);
	}
	
	function prepareInputForAdd($input) {
      // Not attached to hash -> not added
      if (!isset($input['plugin_accounts_hashes_id']) || $input['plugin_accounts_hashes_id'] <= 0) {
         return false;
      }
      return $input;
   }
   
   static function showAesKey($ID) {
      global $LANG, $DB, $CFG_GLPI;

      $hash = new PluginAccountsHash();
      $hash->getFromDB($ID);

      Session::initNavigateListItems("PluginAccountsAesKey",$LANG['plugin_accounts'][21]." = ".$hash->fields["name"]);

      $candelete =$hash->can($ID,'w');
      $query = "SELECT * 
               FROM `glpi_plugin_accounts_aeskeys` 
               WHERE `plugin_accounts_hashes_id` = '$ID' ";
      $result = $DB->query($query);
      $rand=mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_aeskey$rand' id='show_aeskey$rand' action=\"./aeskey.form.php\">";
      echo "<input type='hidden' name='plugin_accounts_hashes_id' value='" . $ID . "'>";
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr><th colspan='5'>".$LANG['plugin_accounts'][23]."</th></tr>";
      echo "<tr><th>&nbsp;</th>";
      echo "<th>" . $LANG['plugin_accounts'][7] . "</th>";
      echo "</tr>";

      if ($DB->numrows($result) > 0) {

         while ($data = $DB->fetch_array($result)) {
            Session::addToNavigateListItems("PluginAccountsAesKey",$data['id']);
            echo "<input type='hidden' name='item[" . $data["id"] . "]' value='" . $ID . "'>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td width='10'>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all')
                  echo " checked ";
               echo ">";
            }
            echo "</td>";
            $link=Toolbox::getItemTypeFormURL("PluginAccountsAesKey");
            echo "<td><a href='".$link."?id=".$data["id"]."&plugin_accounts_hashes_id=".$ID."'>";
            echo $LANG['plugin_accounts'][23] . "</a></td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($candelete) {
            Html::openArrowMassives("show_aeskey$rand", true);
            Html::closeArrowMassives(array('delete' => $LANG['buttons'][6]));
         }
      } else {
         echo "</table>";
      }
      Html::closeForm();
      echo "</div>";
   }
	
}

?>