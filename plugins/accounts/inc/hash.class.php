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

class PluginAccountsHash extends CommonDBTM {
      
   public $dohistory=true;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_accounts'][42];
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
         switch ($item->getType()) {
            case __CLASS__ :
               $ong = array();
               $ong[2] = $LANG['plugin_accounts'][11];
               $ong[3]=$LANG['plugin_accounts'][36];
               return $ong;
         }
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         
         $key = PluginAccountsAesKey::checkIfAesKeyExists($item->getID());
         switch ($tabnum) {
            case 2 :
               if (!$key) {
                  self::showSelectAccountsList($item->getID());
               } else {
                  $parm = array("id" => $item->getID(), "aeskey" => $key);
                  $accounts = PluginAccountsReport::queryAccountsList($parm);
                  PluginAccountsReport::showAccountsList($parm, $accounts);
               }
               break;
            case 3 :
               self::showHashChangeForm($item->getID());
               break;
         }
      }
      return true;
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common']=$LANG['plugin_accounts'][42];

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'name';
      $tab[1]['name'] = $LANG['plugin_accounts'][7];
      $tab[1]['datatype']='itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      
      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'hash';
      $tab[2]['name'] = $LANG['plugin_accounts'][21];
      $tab[2]['massiveaction']=false;
      
      $tab[7]['table'] = $this->getTable();
      $tab[7]['field'] = 'comment';
      $tab[7]['name'] = $LANG['plugin_accounts'][10];
      
      $tab[11]['table'] = $this->getTable();
      $tab[11]['field'] = 'is_recursive';
      $tab[11]['name'] = $LANG['entity'][9];
      $tab[11]['datatype']='bool';
      
      $tab[14]['table']= $this->getTable();
      $tab[14]['field']='date_mod';
      $tab[14]['name']=$LANG['common'][26];
      $tab[14]['massiveaction'] = false;
      $tab[14]['datatype']='datetime';
      
      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = $LANG['entity'][0];
      
      return $tab;
   }
   
   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('PluginAccountsAesKey', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }
	
	function showForm ($ID, $options=array()) {
		global $CFG_GLPI,$LANG;

		if (!$this->canView()) return false;
      
      $restrict = getEntitiesRestrictRequest(" ","glpi_plugin_accounts_hashes",'','',$this->maybeRecursive());
      
      if($ID < 1 && countElementsInTable("glpi_plugin_accounts_hashes",$restrict) > 0) {
         echo "<div class='center red'>".$LANG['plugin_accounts'][26]."</div></br>";
      }

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
      // Create item
         $this->check(-1,'r');
         $this->getEmpty();
      }
      
      $options['colspan'] = 1;
      
      if (!$options['upgrade'] && $options['update']==1) {
			echo "<div class='center red'>".$LANG['plugin_accounts'][30]."</font><br><br>";
      }
      
      if (!$options['update']==1) {
         $this->showTabs($options);
      }
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['common'][16].": </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      echo "</tr>";
      
		if ($ID < 1 || ($ID == 1 && $options['update']==1)) {
         echo "<tr class='tab_bg_1'>";

         echo "<td>".$LANG['plugin_accounts'][23].": </td>";
         echo "<td>";
         echo "<input type='text' name='aeskey' id='aeskey' value='' class='' autocomplete='off'>";
         $message=$LANG['plugin_accounts'][28];
         echo "&nbsp;<input type='button' value='".$LANG['plugin_accounts'][27]."' 
         class='submit' onclick='if ((document.getElementById(\"aeskey\").value == \"\")) {
         alert(\"".$LANG['plugin_accounts'][29]."\");
         return false;
         };key = SHA256(SHA256(document.getElementById(\"aeskey\").value));
         document.getElementById(\"hash\").value = key;return true;'>";
         echo "</td>";
         echo "</tr>";
		}
      
		echo "<tr class='tab_bg_1'>";
		echo "<td>".$LANG['plugin_accounts'][21].": </td>";
      echo "<td>";
		echo "<input type='text' readonly='readonly' size='100' id='hash' name='hash' value='".$this->fields["hash"]."' autocomplete='off'>";
		echo "</td>";
		echo "</tr>";
		
		echo "<tr class='tab_bg_1'>";
		echo "<td valign='top'>".$LANG['plugin_accounts'][10].": </td>";
      echo "<td>";
      echo "<textarea cols='75' rows='3' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td>";
      echo "</tr>";
      
		echo "<tr class='tab_bg_1'>";
		echo "<td>";
      echo $LANG['common'][26].": </td>";
      echo "<td>";
      echo Html::convDateTime($this->fields["date_mod"]);
      echo "</td>";
      echo "</tr>";
      
      if ($ID < 1) {
         echo "<tr class='tab_bg_1 '>";
         echo "<td class='center red' colspan='2'>";
         echo $LANG['plugin_accounts']['setup'][5].": </td>";
         echo "</tr>";
      }
      
		if ($options['upgrade']) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='2'>";
         echo "<input type='hidden' name='id' value='1'>";
         echo "<input type='submit' name='upgrade' value=\"".$LANG['plugin_accounts'][31]."\" class='submit' >";
         echo "</td></tr>";
      }
      
      if (!$options['update']==1) {
         $this->showFormButtons($options);
		} else {
         echo "</table>";
         Html::closeForm();
         echo "</div>";
		}
      $this->addDivForTabs();

	}

	static function showSelectAccountsList($ID) {
      global $LANG,$CFG_GLPI;

      $rand = mt_rand();
      
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='2'>";
      echo $LANG['plugin_accounts'][11]." : </th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo $LANG['plugin_accounts'][29]." : </td>";
      echo "<td class='center'>";
      echo "<input type='password' autocomplete='off' name='key' id='key'>&nbsp;";
      echo "<input type='submit' name='select' value=\"".$LANG['buttons'][2]."\" 
      class='submit' onclick='javascript:showAccountsList". "$rand();' >";
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      
      $toupdate = "viewaccountslist". "$rand";
      $url = $CFG_GLPI["root_doc"]."/plugins/accounts/ajax/viewaccountslist.php";
      
      echo "<div id='viewaccountslist". "$rand'></div>\n";
      echo "<script type='text/javascript'>\n";
      echo "function showAccountsList". "$rand(){\n";
      echo "var key = document.getElementById('key').value;
         if (key == \"\") {
         alert(\"".$LANG['plugin_accounts'][29]."\");
         return false;
         };
         Ext.get('$toupdate').load({
         url: '$url',
         scripts: true,
         params:\"id=$ID&key=\" + Ext.get('key').dom.value,
         });";
      echo "};";
      echo "</script>\n";
 
   }
   
   static function showHashChangeForm ($hash_id) {
		global $LANG,$CFG_GLPI;
    
      echo "<div class='center red'>";
      echo "<b>".$LANG['plugin_accounts'][41]."</b></div><br><br>";
      echo "<form method='post' action='./hash.form.php'>";
      echo "<table class='tab_cadre_fixe' cellpadding='5'><tr><th colspan='2'>";
      echo $LANG['plugin_accounts'][38]." : </th></tr>";
      echo "<tr class='tab_bg_1 center'><td>";
      $aesKey=new PluginAccountsAesKey();
      $key = "";
      if ($aesKey->getFromDBByHash($hash_id) && isset($aesKey->fields["name"]))
         $key = "value='".$aesKey->fields["name"]."' ";
      echo "<input type='password' autocomplete='off' name='aeskey' id= 'aeskey' $key >";
      echo "</td></tr>";
      echo "<tr><th>";
      echo $LANG['plugin_accounts'][37]." : </th></tr>";
      echo "<tr class='tab_bg_1 center'><td>";
      echo "<input type='password' autocomplete='off' name='aeskeynew' id= 'aeskeynew'>";
      echo "</td></tr>";
      echo "<tr class='tab_bg_1 center'><td>";
      $message=$LANG['plugin_accounts'][39];
      $message2=$LANG['plugin_accounts'][40];
      echo "<input type='hidden' name='ID' value='".$hash_id."'>";
      echo "<input type='submit' name='updatehash' value=\"".$LANG['plugin_accounts']['upgrade'][3]."\" class='submit' 
      onclick='return (confirm(\"$message\" +  document.getElementById(\"aeskey\").value + \"$message2\" + document.getElementById(\"aeskeynew\").value)) '>";
      echo "</td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
	}
	
	static function updateHash($oldaeskey, $newaeskey, $hash_id) { 
      global $DB;
      
      $self=new self();
      $self->getFromDB($hash_id);
      $entities = getSonsOf('glpi_entities', $self->fields['entities_id']);
      
      $account=new PluginAccountsAccount();
      $aeskey=new PluginAccountsAesKey();
      
      $oldhash = hash ( "sha256" ,$oldaeskey);
      $newhash = hash ( "sha256" ,$newaeskey);
      $newhashstore = hash ( "sha256" ,$newhash);
      // uncrypt passwords for update
      $query_="SELECT * 
            FROM `glpi_plugin_accounts_accounts`
            WHERE ";
      $query_.= getEntitiesRestrictRequest(" ","glpi_plugin_accounts_accounts",'',$entities);

      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0){

         while ($data=$DB->fetch_array($result_)){
         
            $oldpassword=addslashes(plugin_accounts_AESDecryptCtr($data['encrypted_password'], $oldhash, 256));
            $newpassword=addslashes(plugin_accounts_AESEncryptCtr($oldpassword, $newhash, 256));
            
            $account->update(array(
            'id'=>$data["id"],
            'encrypted_password'=>$newpassword));
         }
         $self->update(array('id'=>$hash_id,'hash'=>$newhashstore));
         
         if ($aeskey->getFromDBByHash($hash_id) && isset($aeskey->fields["name"])) {
            $values["id"] = $aeskey->fields["id"];
            $values["name"] = $newaeskey;
            $aeskey->update($values);
         }
      }	
   }
}

?>