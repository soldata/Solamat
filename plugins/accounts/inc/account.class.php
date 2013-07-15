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

class PluginAccountsAccount extends CommonDBTM {
	
	static $types = array('Computer','Monitor','NetworkEquipment','Peripheral',
         'Phone','Printer','Software','SoftwareLicense','Entity','Contract');
                         
	public $dohistory=true;
   
   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['plugin_accounts']['title'][1];
      }
      return $LANG['plugin_accounts'][1];
   }
   
   function canCreate() {
      return plugin_accounts_haveRight('accounts', 'w');
   }

   function canView() {
      return plugin_accounts_haveRight('accounts', 'r');
   }
   
	function cleanDBonPurge() {

      $temp = new PluginAccountsAccount_Item();
      $temp->deleteByCriteria(array('plugin_accounts_accounts_id' => $this->fields['id']));

	}
  
   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common']=$LANG['plugin_accounts']['title'][1];

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'name';
      $tab[1]['name'] = $LANG['plugin_accounts'][7];
      $tab[1]['datatype']='itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[1]['searchtype'] = 'contains';
      
      $tab[2]['table'] = 'glpi_plugin_accounts_accounttypes';
      $tab[2]['field'] = 'name';
      $tab[2]['name'] = $LANG['plugin_accounts'][12];
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[2]['searchtype'] = 'contains';
      
      $tab[3]['table'] = 'glpi_users';
      $tab[3]['field'] = 'name';
      $tab[3]['name'] = $LANG['plugin_accounts'][18];
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[3]['searchtype'] = 'contains';
      
      $tab[4]['table'] = $this->getTable();
      $tab[4]['field'] = 'login';
      $tab[4]['name'] = $LANG['plugin_accounts'][2];


      $tab[5]['table'] = $this->getTable();
      $tab[5]['field'] = 'date_creation';
      $tab[5]['name'] = $LANG['plugin_accounts'][17];
      $tab[5]['datatype']='date';

      $tab[6]['table'] = $this->getTable();
      $tab[6]['field'] = 'date_expiration';
      $tab[6]['name'] = $LANG['plugin_accounts'][13];
      //$tab[6]['datatype']='date'; //use getSpecificValueToDisplay

      $tab[7]['table'] = $this->getTable();
      $tab[7]['field'] = 'comment';
      $tab[7]['name'] = $LANG['plugin_accounts'][10];
      
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $tab[8]['table'] = 'glpi_plugin_accounts_accounts_items';
         $tab[8]['field'] = 'items_id';
         $tab[8]['nosearch']=true;
         $tab[8]['name'] = $LANG['plugin_accounts'][6];
         $tab[8]['forcegroupby']=true;
         $tab[8]['massiveaction'] = false;
         $tab[8]['joinparams']    = array('jointype' => 'child');      
      }
      
      $tab[9]['table'] = $this->getTable();
      $tab[9]['field'] = 'others';
      $tab[9]['name'] = $LANG['plugin_accounts'][16];

      $tab[10]['table'] = 'glpi_plugin_accounts_accountstates';
      $tab[10]['field'] = 'name';
      $tab[10]['name'] = $LANG['plugin_accounts'][9];
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[10]['searchtype'] = 'contains';
      
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $tab[11]['table'] = $this->getTable();
         $tab[11]['field'] = 'is_recursive';
         $tab[11]['name'] = $LANG['entity'][9];
         $tab[11]['datatype']='bool';
      }
      
      $tab[12]['table'] = 'glpi_groups';
      $tab[12]['field'] = 'name';
      $tab[12]['name'] = $LANG['common'][35];
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[12]['searchtype'] = 'contains';
      
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $tab[13]['table']= $this->getTable();
         $tab[13]['field']='is_helpdesk_visible';
         $tab[13]['name']=$LANG['software'][46];
         $tab[13]['datatype']='bool';
      }
      
      $tab[14]['table']= $this->getTable();
      $tab[14]['field']='date_mod';
      $tab[14]['name']=$LANG['common'][26];
      $tab[14]['massiveaction'] = false;
      $tab[14]['datatype']='datetime';
      
      /*$tab[15]['table']= $this->getTable();
      $tab[15]['field']='encrypted_password';
      $tab[15]['name']=$LANG['plugin_accounts'][3];
      $tab[15]['datatype']='password';
      $tab[15]['nosearch']=true;
      $tab[15]['massiveaction'] = false;
*/
      
      $tab[16]['table']        = 'glpi_locations';
      $tab[16]['field']        = 'completename';
      $tab[16]['name']         = $LANG['common'][15];
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[16]['searchtype'] = 'contains';
      
      $tab[17]['table']='glpi_users';
      $tab[17]['field']='name';
      $tab[17]['linkfield'] = 'users_id_tech';
      $tab[17]['name']=$LANG['common'][10];
      
      $tab[18]['table']='glpi_groups';
      $tab[18]['field']='name';
      $tab[18]['linkfield'] = 'groups_id_tech';
      $tab[18]['name']=$LANG['common'][109];
      
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name'] = $LANG['common'][2];
      
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $tab[80]['table'] = 'glpi_entities';
         $tab[80]['field'] = 'completename';
         $tab[80]['name'] = $LANG['entity'][0];
      }
      
      return $tab;
   }
	
	function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $this->addStandardTab('PluginAccountsAccount_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Document', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central')
         $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

	function prepareInputForAdd($input) {

		if (isset($input['date_creation']) && empty($input['date_creation'])) 
         $input['date_creation']='NULL';
		if (isset($input['date_expiration']) && empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';

		return $input;
	}
   
   function post_addItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["use_mailing"]) {
         NotificationEvent::raiseEvent("new",$this);
      }
   }
   
	function prepareInputForUpdate($input) {

		if (isset($input['date_creation']) && empty($input['date_creation'])) 
         $input['date_creation']='NULL';
		if (isset($input['date_expiration']) && empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';

		return $input;
	}
   
   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem () {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_accounts_accounts_items`
              WHERE `plugin_accounts_accounts_id`='" . $this->fields['id']."'";
   }
   
	function showForm ($ID, $options=array()) {
		global $CFG_GLPI,$LANG;

      if (!$this->canView()) return false;
      
      $hashclass = new PluginAccountsHash();
      
      $restrict = getEntitiesRestrictRequest(" ","glpi_plugin_accounts_hashes",'','',$hashclass->maybeRecursive());
      if($ID < 1 && countElementsInTable("glpi_plugin_accounts_hashes",$restrict) == 0) {
         echo "<div class='center'>".$LANG['plugin_accounts'][35]."<br><br>";
         echo "<a href='".Toolbox::getItemTypeSearchURL('PluginAccountsAccount')."'>";
         echo $LANG['buttons'][13]."</a></div>";
         return false;
      }
      
      if ($ID > 0) {
         $this->check($ID,'r');
         if (!plugin_accounts_haveRight("all_users","r")) {
            $access = 0;
            if (plugin_accounts_haveRight("my_groups","r")) {
               if ($this->fields["groups_id"]) {
                  if (count($_SESSION['glpigroups']) 
                     && in_array($this->fields["groups_id"],$_SESSION['glpigroups'])) {
                     $access = 1;
                  }
               }
               if ($this->fields["users_id"]) {
                  if ($this->fields["users_id"] == Session::getLoginUserID())
                     $access = 1;
               }
            }
            if (!plugin_accounts_haveRight("my_groups","r") 
                  && $this->fields["users_id"] == Session::getLoginUserID())
               $access = 1;
            
            if($access!=1)
               return false;
         }
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_accounts'][7].": </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      
      echo "<td>".$LANG['plugin_accounts'][9].": </td><td>";
      Dropdown::show('PluginAccountsAccountState', 
                     array('value' => $this->fields["plugin_accounts_accountstates_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_accounts'][2].": </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"login");
      echo "</td>";
      
      echo "<td>".$LANG['plugin_accounts'][12].":	</td><td>";
      Dropdown::show('PluginAccountsAccountType', 
                     array('value' => $this->fields["plugin_accounts_accounttypes_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      //hash
      $hash = 0;
      
      $restrict = getEntitiesRestrictRequest(" ","glpi_plugin_accounts_hashes",'',$this->getEntityID(),$hashclass->maybeRecursive());
      $hashes = getAllDatasFromTable("glpi_plugin_accounts_hashes",$restrict);
      if (!empty($hashes)) {
         foreach ($hashes as $hashe) {
            $hash = $hashe["hash"];
            $hash_id = $hashe["id"];
         }
         $alert = '';
      } else {
         $alert = $LANG['plugin_accounts'][35];
      }

      $aeskey=new PluginAccountsAesKey();
      
      //aeskey non enregistré
      if ($hash) {
         if (!$aeskey->getFromDBByHash($hash_id) || !$aeskey->fields["name"]) {
            echo "<td>".$LANG['plugin_accounts'][23].": </div></td><td>";
            echo "<input type='password' autocomplete='off' name='aeskey' id= 'aeskey' value='' class=''>";
            
            echo "<input type='hidden' name='encrypted_password' value='".$this->fields["encrypted_password"]."'>";
            
            if (!empty($ID) || $ID>0) {
               //echo "<script src='".GLPI_ROOT."/plugins/accounts/histoAccounts.js'></script>";
               $root = GLPI_ROOT;
               echo "&nbsp;<input type='button' name='decrypte' value='".$LANG['plugin_accounts'][20]."' 
               class='submit' onclick='var good_hash=\"$hash\";var hash=SHA256(SHA256(document.getElementById(\"aeskey\").value));
               if (hash != good_hash) {
               alert(\"".$LANG['plugin_accounts'][22]."\");
               return false;
               };document.getElementsByName(\"hidden_password\").item(0).value=AESDecryptCtr(document.getElementsByName(\"encrypted_password\").item(0).value,SHA256(document.getElementById(\"aeskey\").value), 256);
               var ret = callAjax(\"$root\", \"$ID\" , document.getElementsByName(\"name\").item(0).value);
               return false;'>";
            }
            
            echo "</td>";
         } else {
            echo "<td></td><td>";
            echo "</td>";
         }
      } else {
         echo "<td>".$LANG['plugin_accounts'][23].": </div></td><td><div class='red'>";
         echo $alert;
         echo "</div></td>";
      }
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         echo "<td>".$LANG['plugin_accounts'][18].":	</td><td>";
         if ($this->canCreate()) {
            User::dropdown(array('value'  => $this->fields["users_id"],
                              'entity' => $this->fields["entities_id"],
                              'right'  => 'all'));
         } else {
            echo getUserName($this->fields["users_id"]);
         }
         echo "</td>";
      } else {
         echo "<td>".$LANG['plugin_accounts'][18].":	</td><td>";
         echo getUserName($this->fields["users_id"]);
         echo "</td>";
      }
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_accounts'][3].": </td>";
      
      echo "<td>";
      //aeskey enregistré
      if (isset($hash_id) && $aeskey->getFromDBByHash($hash_id) && $aeskey->fields["name"]) {
         echo "<input type='hidden' name='aeskey' id= 'aeskey' value='".$aeskey->fields["name"]."' class='' autocomplete='off'>";
         echo "<input type='hidden' name='encrypted_password' value='".$this->fields["encrypted_password"]."'>";
         
         echo "<input type='text' name='hidden_password' value='' size='30' ><script language='javascript'>var good_hash=\"$hash\";var hash=SHA256(SHA256(document.getElementById(\"aeskey\").value));
         if (hash != good_hash) {
         document.getElementsByName(\"hidden_password\").item(0).value=\"".$LANG['plugin_accounts'][22]."\";
         } else {
         document.getElementsByName(\"hidden_password\").item(0).value=AESDecryptCtr(document.getElementsByName(\"encrypted_password\").item(0).value,SHA256(document.getElementById(\"aeskey\").value), 256)};</script>";

      } else {

         echo "<input type='text' name='hidden_password' value='' size='30' >";
      }
      
      echo "</td>";
      
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         echo "<td>".$LANG['plugin_accounts'][19].":	</td><td>";
         if ($this->canCreate()) {
            Dropdown::show('Group', array('value' => $this->fields["groups_id"],
                                          'condition' => '`is_itemgroup`'));
         } else {
            echo Dropdown::getDropdownName("glpi_groups",$this->fields["groups_id"]);
         }
         echo "</td>";
      } else {
         echo "<td>".$LANG['plugin_accounts'][19].":	</td><td>";
         echo Dropdown::getDropdownName("glpi_groups",$this->fields["groups_id"]);
         echo "</td>";
      }
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_accounts'][17].":	</td>";
      echo "<td>";
      Html::showDateFormItem("date_creation",$this->fields["date_creation"],true,true);
      echo "</td>";
      
      echo "<td>".$LANG['common'][10].":	</td>";
      echo "<td>";
      User::dropdown(array('name' => "users_id_tech",
                              'value'  => $this->fields["users_id_tech"],
                              'entity' => $this->fields["entities_id"],
                              'right'  => 'interface'));
      echo "</td>";

      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_accounts'][13].":&nbsp;";
      Html::showToolTip(nl2br($LANG['plugin_accounts'][15]));
      echo "</td>";
      echo "<td>";
      Html::showDateFormItem("date_expiration",$this->fields["date_expiration"],true,true);
      echo "</td>";
      
      echo "<td>" . $LANG['common'][109] . ":</td><td>";
      Dropdown::show('Group', array('name' => "groups_id_tech",
                                    'value' => $this->fields["groups_id_tech"],
                                    'condition' => '`is_assign`'));
      echo "</td>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_accounts'][16].":	</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"others");
      echo "</td>";
      
      echo "<td>" . $LANG['common'][15] . ":</td><td>";
      Dropdown::show('Location', array('value'  => $this->fields["locations_id"],
                                          'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo $LANG['plugin_accounts'][10].": </td></tr>";
      echo "<tr><td class='center'>";
      echo "<textarea cols='125' rows='3' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td></tr></table>";
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['software'][46] . ":</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td>";
      echo "<td colspan='2'>";
      $datestring = $LANG['common'][26].": ";
      $date = Html::convDateTime($this->fields["date_mod"]);
      echo $datestring.$date;
      echo "</td>";
      
      echo "</tr>";

      if ($this->canCreate()) {
         if (empty($ID)||$ID<0) {

            echo "<tr>";
            echo "<td class='tab_bg_2 top' colspan='4'>";
            echo "<div align='center'><input onclick='var good_hash=\"$hash\";
            var hash=SHA256(SHA256(document.getElementById(\"aeskey\").value));
            if ((document.getElementsByName(\"hidden_password\").item(0).value == \"\") || (document.getElementById(\"aeskey\").value == \"\")) {
            alert(\"".$LANG['plugin_accounts'][24]."\");
            return false;
            };
            if (hash != good_hash) {
            alert(\"".$LANG['plugin_accounts'][22]."\");
            return false;
            };
            document.getElementsByName(\"encrypted_password\").item(0).value=AESEncryptCtr(document.getElementsByName(\"hidden_password\").item(0).value,SHA256(document.getElementById(\"aeskey\").value), 256);' type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'></div>";
            echo "</td>";
            echo "</tr>";

         } else {

            echo "<tr>";
            echo "<td class='tab_bg_2'  colspan='4 top'><div align='center'>";
            echo "<input type='hidden' name='id' value=\"$ID\">";
            echo "<input onclick='var good_hash=\"$hash\";
            var hash=SHA256(SHA256(document.getElementById(\"aeskey\").value));
            if ((document.getElementsByName(\"hidden_password\").item(0).value == \"\") || (document.getElementById(\"aeskey\").value == \"\")) {
            alert(\"".$LANG['plugin_accounts'][25]."\");
            return true;
            } else {
            if (hash != good_hash) {
            alert(\"".$LANG['plugin_accounts'][22]."\");
            return false;
            };
            };
            document.getElementsByName(\"encrypted_password\").item(0).value=AESEncryptCtr(document.getElementsByName(\"hidden_password\").item(0).value,SHA256(document.getElementById(\"aeskey\").value), 256);' type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >";

            if ($this->fields["is_deleted"]=='0')
               echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"".$LANG['buttons'][6]."\" class='submit'></div>";
            else {
               echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"".$LANG['buttons'][21]."\" class='submit'>";
               echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"".$LANG['buttons'][22]."\" class='submit'></div>";
            }

            echo "</td>";
            echo "</tr>";

         }
      }
      echo "</table>";
      echo "</div>";
      Html::closeForm();
      
      $this->addDivForTabs();

      return true;
	}

   function showAccountsUpgrade($hash) {
      global $CFG_GLPI,$LANG;
      
      echo "<div align='center'><b>".$LANG['plugin_accounts']['upgrade'][2]."</b><br><br>";

      $rand=mt_rand();

      $restrict= " 1=1 ORDER BY `name` ";
      $accounts = getAllDatasFromTable($this->getTable(),$restrict);
      
      if (!empty($accounts)) {
         echo "<form method='post' name='massiveaction_form$rand' id='massiveaction_form$rand'  action=\"./account.upgrade.php\">";
         echo "<table class='tab_cadre' cellpadding='5'>";
         echo "<tr><th></th><th>".$LANG['plugin_accounts']['upgrade'][4]."</th><th>".$LANG['plugin_accounts']['upgrade'][5]."</th><th>".$LANG['plugin_accounts'][23]."</th></tr>";
         echo "</tr>";
         foreach ($accounts as $account) {
            $ID=$account["id"];
            if (!in_array($ID, $_SESSION['plugin_accounts']['upgrade'])) {
               echo "<tr class='tab_bg_1'>";
               echo "<td class='center'>";
               echo "<input type='hidden' name='update_encrypted_password' value='1'>";
               echo "<input type='checkbox' checked name='item[$ID]' value='1'>";
               echo "</td>";
               echo "<td>";
               echo $account["name"]."</td>";
               echo "<td><input type='hidden' name='encrypted_password$$ID' value=''><input type='text' name='hidden_password$$ID' value=\"".$account["encrypted_password"]."\"></td>";
               echo "<td><input type='text' name='aescrypted_key' id= 'aescrypted_key' value='".$_SESSION['plugin_accounts']['aescrypted_key']."' class='' autocomplete='off'>
               <script type='text/javascript'>var good_hash=\"$hash\";
                  var hash=SHA256(SHA256(document.getElementById(\"aescrypted_key\").value));
                  document.getElementsByName(\"encrypted_password$$ID\").item(0).value=AESEncryptCtr(document.getElementsByName(\"hidden_password$$ID\").item(0).value,SHA256(document.getElementById(\"aescrypted_key\").value), 256);</script></td>";
               echo "</td></tr>";
            }
         }

         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo "<input type='submit' name='upgrade_accounts[".$ID."]' value=\"".$LANG['plugin_accounts']['upgrade'][3]."\" class='submit' >";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
      }
      echo "<br><br><div align='center'><b>".$LANG['plugin_accounts']['upgrade'][6]."</b></div><br><br>";
   }
	
   function dropdownAccounts($myname,$entity_restrict='',$used=array()) {
      global $DB,$LANG,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `".$this->getTable()."`.`is_deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND",$this->getTable(),'',$entity_restrict,true);
      if (count($used)) {
         $where .= " AND id NOT IN (0";
         foreach ($used as $ID)
            $where .= ",$ID";
         $where .= ")";
      }

      $query="SELECT *
      FROM `glpi_plugin_accounts_accounttypes`
      WHERE `id` IN (
      SELECT DISTINCT `plugin_accounts_accounttypes_id`
      FROM `".$this->getTable()."`
      $where)
      GROUP BY `name`
      ORDER BY `name` ";
      $result=$DB->query($query);

      echo "<select name='_type' id='plugin_accounts_accounttypes_id'>\n";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";
      while ($data=$DB->fetch_assoc($result)) {
         echo "<option value='".$data['id']."'>".$data['name']."</option>\n";
      }
      echo "</select>\n";

      $params=array('plugin_accounts_accounttypes_id'=>'__VALUE__',
      'entity_restrict'=>$entity_restrict,
      'rand'=>$rand,
      'myname'=>$myname,
      'used'=>$used
      );

      Ajax::updateItemOnSelectEvent("plugin_accounts_accounttypes_id",
      "show_$myname$rand",$CFG_GLPI["root_doc"]."/plugins/accounts/ajax/dropdownTypeAccounts.php",$params);

      echo "<span id='show_$myname$rand'>";
      $_POST["entity_restrict"]=$entity_restrict;
      $_POST["plugin_accounts_accounttypes_id"]=0;
      $_POST["myname"]=$myname;
      $_POST["rand"]=$rand;
      $_POST["used"]=$used;
      include (GLPI_ROOT."/plugins/accounts/ajax/dropdownTypeAccounts.php");
      echo "</span>\n";

      return $rand;
   }
   
   // Cron action
   static function cronInfo($name) {
      global $LANG;
       
      switch ($name) {
         case 'AccountsAlert':
            return array (
               'description' => $LANG['plugin_accounts']['mailing'][1]);   // Optional
            break;
      }
      return array();
   }
     
   static function queryExpiredAccounts() {
      global $DB;
      
      $config=new PluginAccountsConfig();
      $notif= new PluginAccountsNotificationState();
      
      $config->getFromDB('1');
      $delay=$config->fields["delay_expired"];

      $query = "SELECT * 
         FROM `glpi_plugin_accounts_accounts`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > $delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) > 0 ";
      $query.= "AND `plugin_accounts_accountstates_id` NOT IN (999999";
      $query.= $notif->findStates();
      $query.= ") ";

      return $query;
   }
   
   static function queryAccountsWhichExpire() {
      global $DB;
      
      $config=new PluginAccountsConfig();
      $notif= new PluginAccountsNotificationState();
      
      $config->getFromDB('1');
      $delay=$config->fields["delay_whichexpire"];
      
      $query = "SELECT *
         FROM `glpi_plugin_accounts_accounts`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > -$delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) < 0 ";
      $query.= "AND `plugin_accounts_accountstates_id` NOT IN (999999";
      $query.= $notif->findStates();
      $query.= ") ";
      
      return $query;
   }
   /**
    * Cron action on accounts : ExpiredAccounts or AccountsWhichExpire
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronAccountsAlert($task=NULL) {
      global $DB,$CFG_GLPI,$LANG;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $query_expired = self::queryExpiredAccounts();
      $query_whichexpire = self::queryAccountsWhichExpire();
      
      $querys = array(Alert::NOTICE=>$query_whichexpire, Alert::END=>$query_expired);
      
      $account_infos = array();
      $account_messages = array();

      foreach ($querys as $type => $query) {
         $account_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"].": ".
                        Html::convdate($data["date_expiration"])."<br>\n";
            $account_infos[$type][$entity][] = $data;

            if (!isset($accounts_infos[$type][$entity])) {
               $account_messages[$type][$entity] = $LANG['plugin_accounts']['mailing'][0]."<br />";
            }
            $account_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($account_infos[$type] as $entity => $accounts) {
            Plugin::loadLang('accounts');
            
            if (NotificationEvent::raiseEvent(($type==Alert::NOTICE?"AccountsWhichExpire":"ExpiredAccounts"),
                                              new PluginAccountsAccount(),
                                              array('entities_id'=>$entity,
                                                    'accounts'=>$accounts))) {
               $message = $account_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                                                       $entity).":  $message\n");
                  $task->addVolume(1);
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                    $entity).":  $message");
               }

            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",$entity).
                             ":  Send accounts alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send accounts alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   static function configCron($target) {

      $notif=new PluginAccountsNotificationState();
      $config=new PluginAccountsConfig();

      $config->showForm($target,1);
      $notif->showForm($target);
      $notif->showAddForm($target);
    
   }
   
   /**
    * Display entities of the loaded profile
    *
   * @param $myname select name
    * @param $target target for entity change action
    */
   static function showSelector($target) {
      global $CFG_GLPI,$LANG;

      $rand=mt_rand();
      Plugin::loadLang('accounts');
      echo "<div class='center' ><span class='b'>".$LANG['plugin_accounts']['setup'][4]."</span><br>";
      echo "<a style='font-size:14px;' href='".$target."?reset=reset' title=\"".
             $LANG['buttons'][40]."\">".str_replace(" ","&nbsp;",$LANG['buttons'][40])."</a></div>";

      echo "<div class='left' style='width:100%'>";

      echo "<script type='javascript'>";
      echo "var Tree_Category_Loader$rand = new Ext.tree.TreeLoader({
         dataUrl:'".$CFG_GLPI["root_doc"]."/plugins/accounts/ajax/accounttreetypes.php'
      });";

      echo "var Tree_Category$rand = new Ext.tree.TreePanel({
         collapsible      : false,
         animCollapse     : false,
         border           : false,
         id               : 'tree_projectcategory$rand',
         el               : 'tree_projectcategory$rand',
         autoScroll       : true,
         animate          : false,
         enableDD         : true,
         containerScroll  : true,
         height           : 320,
         width            : 770,
         loader           : Tree_Category_Loader$rand,
         rootVisible     : false
      });";

      // SET the root node.
      echo "var Tree_Category_Root$rand = new Ext.tree.AsyncTreeNode({
         text     : '',
         draggable   : false,
         id    : '-1'                  // this IS the id of the startnode
      });
      Tree_Category$rand.setRootNode(Tree_Category_Root$rand);";

      // Render the tree.
      echo "Tree_Category$rand.render();
            Tree_Category_Root$rand.expand();";

      echo "</script>";

      echo "<div id='tree_projectcategory$rand' ></div>";
      echo "</div>";
   }
   
   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.3.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
   
   static function getSpecificValueToDisplay($field, $values, $options=array()) {
      global $LANG;

      if (!is_array($values)) {
         $values = array($field => $values);
      }
      switch ($field) {
         case 'date_expiration' :
            if (empty($values[$field]))
               return $LANG['plugin_accounts'][14];
            else
               return Html::convdate($values[$field]);
         break;
         /*case "glpi_plugin_accounts_accounts_items.items_id" :
               $query_device = "SELECT DISTINCT `itemtype`
                           FROM `glpi_plugin_accounts_accounts_items`
                           WHERE `plugin_accounts_accounts_id` = '" . $data['id'] . "'
                           ORDER BY `itemtype` ";
               $result_device = $DB->query($query_device);
               $number_device = $DB->numrows($result_device);
               $out = '';
               $accounts = $data['id'];
               if ($number_device > 0) {
                  for ($i=0 ; $i < $number_device ; $i++) {
                     $column = "name";
                     $itemtype = $DB->result($result_device, $i, "itemtype");
                     
                     if (!class_exists($itemtype)) {
                        continue;
                     }
                     $item = new $itemtype();
                     if ($item->canView()) {
                        $table_item = getTableForItemType($itemtype);
                        if ($itemtype!='Entity') {
                           $query = "SELECT `".$table_item."`.*, 
                                    `glpi_plugin_accounts_accounts_items`.`id` AS items_id, 
                                    `glpi_entities`.`id` AS entity "
                           ." FROM `glpi_plugin_accounts_accounts_items`, `".$table_item
                           ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`) "
                           ." WHERE `".$table_item."`.`id` = `glpi_plugin_accounts_accounts_items`.`items_id`
                           AND `glpi_plugin_accounts_accounts_items`.`itemtype` = '$itemtype'
                           AND `glpi_plugin_accounts_accounts_items`.`plugin_accounts_accounts_id` = '" . $accounts . "' ";
                           $query.=getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

                           if ($item->maybeTemplate()) {
                              $query.=" AND ".$table_item.".is_template='0'";
                           }
                           $query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column` ";
                       } else {
                           $query = "SELECT `".$table_item."`.*, 
                                          `glpi_plugin_accounts_accounts_items`.`id` AS items_id, 
                                          `glpi_entities`.`id` AS entity "
                           ." FROM `glpi_plugin_accounts_accounts_items`, `".$table_item
                           ."` WHERE `".$table_item."`.`id` = `glpi_plugin_accounts_accounts_items`.`items_id`
                           AND `glpi_plugin_accounts_accounts_items`.`itemtype` = '$itemtype'
                           AND `glpi_plugin_accounts_accounts_items`.`plugin_accounts_accounts_id` = '" . $accounts . "' "
                           . getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

                           if ($item->maybeTemplate()) {
                              $query.=" AND ".$table_item.".is_template='0'";
                           }
                           $query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column` ";
                        }

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
                           $out.= ' ';
                  }
               }
            return $out;
            break;*/
      }
      return '';
   }
}

?>