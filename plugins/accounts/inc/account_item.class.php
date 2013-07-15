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

class PluginAccountsAccount_Item extends CommonDBRelation {
	
   // From CommonDBRelation
   public $itemtype_1 = "PluginAccountsAccount";
   public $items_id_1 = 'plugin_accounts_accounts_id';

   public $itemtype_2 = 'itemtype';
   public $items_id_2 = 'items_id';
   
   function canCreate() {
      return plugin_accounts_haveRight('accounts', 'w');
   }

   function canView() {
      return plugin_accounts_haveRight('accounts', 'r');
   }
   
   /**
    * Hook called After an item is uninstall or purge
    */
   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         array('itemtype' => $item->getType(),
               'items_id' => $item->getField('id'))
      );
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         if ($item->getType()=='PluginAccountsAccount'
             && count(PluginAccountsAccount::getTypes(false))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry($LANG['document'][19], self::countForAccount($item));
            }
            return $LANG['document'][19];

         } else if (in_array($item->getType(), PluginAccountsAccount::getTypes(true))
                    && plugin_accounts_haveRight('accounts', 'r')) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(PluginAccountsAccount::getTypeName(2), self::countForItem($item));
            }
            return PluginAccountsAccount::getTypeName(2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
   
      $self = new self();
      
      if ($item->getType()=='PluginAccountsAccount') {
         
         $self->showItemFromPlugin($item->getID());

      } else if (in_array($item->getType(), PluginAccountsAccount::getTypes(true))) {
      
         $self->showPluginFromItems(get_class($item),$item->getField('id'));

      }
      return true;
   }
   
   static function countForAccount(PluginAccountsAccount $item) {

      $types = implode("','", $item->getTypes());
      if (empty($types)) {
         return 0;
      }
      return countElementsInTable('glpi_plugin_accounts_accounts_items',
                                  "`itemtype` IN ('$types')
                                   AND `plugin_accounts_accounts_id` = '".$item->getID()."'");
   }


   static function countForItem(CommonDBTM $item) {

      return countElementsInTable('glpi_plugin_accounts_accounts_items',
                                  "`itemtype`='".$item->getType()."'
                                   AND `items_id` = '".$item->getID()."'");
   }

	function getFromDBbyAccountsAndItem($plugin_accounts_accounts_id,$items_id,$itemtype) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->getTable()."` " .
			"WHERE `plugin_accounts_accounts_id` = '" . $plugin_accounts_accounts_id . "' 
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
	
	function addItem($values) {

      $this->add(array('plugin_accounts_accounts_id'=>$values['plugin_accounts_accounts_id'],
                        'items_id'=>$values['items_id'],
                        'itemtype'=>$values['itemtype']));
    
   }
  
   function deleteItemByAccountsAndItem($plugin_accounts_accounts_id,$items_id,$itemtype) {
    
      if ($this->getFromDBbyAccountsAndItem($plugin_accounts_accounts_id,$items_id,$itemtype)) {
         $this->delete(array('id'=>$this->fields["id"]));
      }
   }
  
   function showItemFromPlugin($aID,$search='') {
      global $DB,$CFG_GLPI,$LANG;
      
      $account=new PluginAccountsAccount();
      
      if (!$account->canView()) return false;
      
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central') return false;
      
      $rand=mt_rand();
      
      if ($account->getFromDB($aID)) {

         $canedit=$account->can($aID,'w');

         $query = "SELECT DISTINCT `itemtype`
         FROM `".$this->getTable()."`
         WHERE `plugin_accounts_accounts_id` = '$aID'
         ORDER BY `itemtype` ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);

         if (Session::isMultiEntitiesMode()) {
            $colsup=1;
         } else {
            $colsup=0;
         }
         echo "<form method='post' name='accounts_form$rand' id='accounts_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.form.php\">";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".$LANG['plugin_accounts'][6].":</th></tr><tr>";
         if ($canedit) {
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
               if ($type!='Entity') {
                  $query = "SELECT `".$table."`.*, `".$this->getTable()."`.`id` AS items_id, `glpi_entities`.`id` AS entity "
                  ." FROM `".$this->getTable()."`, `".$table
                  ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table."`.`entities_id`) "
                  ." WHERE `".$table."`.`id` = `".$this->getTable()."`.`items_id`
                  AND `".$this->getTable()."`.`itemtype` = '$type'
                  AND `".$this->getTable()."`.`plugin_accounts_accounts_id` = '$aID' ";
                  $query.=getEntitiesRestrictRequest(" AND ",$table,'','',$item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query.=" AND ".$table.".is_template='0'";
                  }
                  $query.=" ORDER BY `glpi_entities`.`completename`, `".$table."`.`$column` ";
               } else {
                  $query = "SELECT `".$table."`.*, `".$this->getTable()."`.`id` AS items_id, `glpi_entities`.`id` AS entity "
                  ." FROM `".$this->getTable()."`, `".$table
                  ."` WHERE `".$table."`.`id` = `".$this->getTable()."`.`items_id`
                  AND `".$this->getTable()."`.`itemtype` = '$type'
                  AND `".$this->getTable()."`.`plugin_accounts_accounts_id` = '$aID' "
                  . getEntitiesRestrictRequest(" AND ",$table,'','',$item->maybeRecursive());

                  if ($item->maybeTemplate()) {
                     $query.=" AND ".$table.".is_template='0'";
                  }
                  $query.=" ORDER BY `glpi_entities`.`completename`, `".$table."`.`$column` ";
               }
               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)) {

                     Session::initNavigateListItems($type,$LANG['plugin_accounts']['title'][1]." = ".$account->fields['name']);
                     while ($data=$DB->fetch_assoc($result_linked)) {
                        $item->getFromDB($data["id"]);

                        Session::addToNavigateListItems($type,$data["id"]);
                        $ID="";

                        if ($_SESSION["glpiis_ids_visible"]||empty($data["name"])) $ID= " (".$data["id"].")";
                        $link=Toolbox::getItemTypeFormURL($type);
                        $name= "<a href=\"".$link."?id=".$data["id"]."\">"
                        .$data["name"]."$ID</a>";

                        echo "<tr class='tab_bg_1'>";

                        if ($canedit) {
                        echo "<td width='10'>";
                        $sel="";
                        if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
                        echo "<input type='checkbox' name='item[".$data["items_id"]."]' value='1' $sel>";
                        echo "</td>";
                        }
                        echo "<td class='center'>".$item->getTypeName()."</td>";

                        echo "<td class='center' ".(isset($data['is_deleted'])&&$data['is_deleted']?"class='tab_bg_2_2'":"").">".$name."</td>";

                        if (Session::isMultiEntitiesMode())
                           echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entity'])."</td>";

                        echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
                        echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";

                        echo "</tr>";
                     }
                  }
            }
         }

         if ($canedit)	{
            echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
            echo "<input type='hidden' name='plugin_accounts_accounts_id' value='$aID'>";
            Dropdown::showAllItems("items_id",0,0,($account->fields['is_recursive']?-1:$account->fields['entities_id']),PluginAccountsAccount::getTypes());
            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
            echo "</td></tr>";
            echo "</table></div>" ;
            Html::openArrowMassives("accounts_form$rand",true);
            Html::closeArrowMassives(array('deleteitem'=> $LANG['buttons'][6]));

         } else {
            echo "</table></div>";
         }
         Html::closeForm();
      }
   }
  
   //from items

   function showPluginFromItems($itemtype,$ID,$withtemplate='') {
      global $DB,$CFG_GLPI,$LANG;
      
      $item = new $itemtype();
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');
      
      $account=new PluginAccountsAccount();
      ///Récupération des groupes de l'utilisateur connecté
      $who=Session::getLoginUserID();

      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }

      if (count($_SESSION["glpigroups"]) && plugin_accounts_haveRight("my_groups","r")) {
         $first_groups=true;
         $groups="";
         foreach ($_SESSION['glpigroups'] as $val) {
            if (!$first_groups) $groups.=",";
            else $first_groups=false;
            $groups.="'".$val."'";
         }
         $ASSIGN="( `groups_id` IN ($groups) OR `users_id` = '$who') ";
      } else { // Only personal ones
         $ASSIGN=" `users_id` = '$who' ";
      }

      $query = "SELECT `".$this->getTable()."`.`id` AS items_id,`glpi_plugin_accounts_accounts`.* "
      ."FROM `".$this->getTable()."`,`glpi_plugin_accounts_accounts` "
      ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `glpi_plugin_accounts_accounts`.`entities_id`) "
      ." WHERE `".$this->getTable()."`.`items_id` = '".$ID."'
      AND `".$this->getTable()."`.`itemtype` = '".$itemtype."'
      AND `".$this->getTable()."`.`plugin_accounts_accounts_id` = `glpi_plugin_accounts_accounts`.`id` "
      . getEntitiesRestrictRequest(" AND ","glpi_plugin_accounts_accounts",'','',$account->maybeRecursive());

      if (!plugin_accounts_haveRight("all_users","r"))
         $query.= " AND $ASSIGN ";

      $query.= " ORDER BY `glpi_plugin_accounts_accounts`.`name` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.form.php\">";
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='".(8+$colsup)."'>".$LANG['plugin_accounts'][8].":</th></tr>";
      
      //hash
      $hashclass = new PluginAccountsHash();
      $hash = 0;
      $restrict = getEntitiesRestrictRequest(" ","glpi_plugin_accounts_hashes",'',$item->getEntityID(),$hashclass->maybeRecursive());
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
      if ($hash) {
         if (!$aeskey->getFromDBByHash($hash_id) || !$aeskey->fields["name"]) {
            echo "<tr><th colspan='".(8+$colsup)."'>";
            echo $LANG['plugin_accounts'][23].": ";
            echo "<input type='password' name='aescrypted_key' id= 'aescrypted_key' autocomplete='off'>";
            echo "</th></tr>";
         }
      } else {
         echo "<tr><th colspan='".(8+$colsup)."'>";
         echo $LANG['plugin_accounts'][23].": <div class='red'>";
         echo $alert;
         echo "</div>";
         echo "</th></tr>";
      }
      echo "<tr><th>".$LANG['plugin_accounts'][7]."</th>";
      if (Session::isMultiEntitiesMode())
         echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['plugin_accounts'][2]."</th>";
      echo "<th>".$LANG['plugin_accounts'][3]."</th>";
      echo "<th>".$LANG['plugin_accounts'][18]."</th>";
      echo "<th>".$LANG['plugin_accounts'][12]."</th>";
      echo "<th>".$LANG['plugin_accounts'][17]."</th>";
      echo "<th>".$LANG['plugin_accounts'][13]."</th>";
      if ($this->canCreate())
         echo "<th>&nbsp;</th>";
      echo "</tr>";
      $used=array();
      while ($data=$DB->fetch_array($result)) {

         $IDc=$data["id"];
         $used[]=$IDc;
         echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";
         if ($withtemplate!=3 
               && $canread 
                  && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) || $data["is_recursive"])) {
            echo "<td class='center'>";
            echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.form.php?id=".$data["id"]."'>";
            echo $data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
            echo "</a></td>";
         } else {
            echo "<td class='center'>".$data["name"];
            if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
            echo "</td>";
         }
         if (Session::isMultiEntitiesMode())
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
         echo "<td class='center'>".$data["login"]."</td>";
         echo "<td class='center'>";
         //hash

         if (isset($hash_id) && $aeskey->getFromDBByHash($hash_id) && $aeskey->fields["name"]) {
            echo "<input type='hidden' name='aeskey' id= 'aeskey' value='".$aeskey->fields["name"]."' class='' autocomplete='off'>";
            echo "<input type='hidden' autocomplete='off' name='encrypted_password$$IDc' value='".$data["encrypted_password"]."'>";
            echo "<input type='text' name='hidden_password$$IDc' value='' size='30' >";
            echo "<script language='javascript'>var good_hash=\"$hash\";var hash=SHA256(SHA256(document.getElementById(\"aeskey\").value));
         if (hash != good_hash) {
         document.getElementsByName(\"hidden_password$$IDc\").item(0).value=\"".$LANG['plugin_accounts'][22]."\";
         } else {
         document.getElementsByName(\"hidden_password$$IDc\").item(0).value=AESDecryptCtr(document.getElementsByName(\"encrypted_password$$IDc\").item(0).value,SHA256(document.getElementById(\"aeskey\").value), 256)};</script>";
         } else {
            $root = GLPI_ROOT;
            echo "&nbsp;<input type='button' name='decrypte' value='".$LANG['plugin_accounts'][20]."' class='submit' onClick='var good_hash=\"$hash\";var hash=SHA256(SHA256(document.getElementById(\"aescrypted_key\").value));
         if (hash != good_hash) {
         alert(\"".$LANG['plugin_accounts'][22]."\");
         return false;
         };alert(AESDecryptCtr(\"".$data['encrypted_password']."\",SHA256(document.getElementById(\"aescrypted_key\").value), 256));var ret = callAjax(\"$root\", \"$IDc\" , document.getElementsByName(\"name\").item(0).value);return false;'>";

         }
         echo "</td>";
         echo "<td class='center'>".getUsername($data["users_id"])."</td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_plugin_accounts_accounttypes",$data["plugin_accounts_accounttypes_id"])."</td>";
         echo "<td class='center'>".Html::convdate($data["date_creation"])."</td>";
         if ($data["date_expiration"] <= date('Y-m-d') && !empty($data["date_expiration"]))
            echo "<td class='center'><div class='deleted'>".Html::convdate($data["date_expiration"])."</div></td>";
         else if (empty($data["date_expiration"]))
            echo "<td class='center'>".$LANG['plugin_accounts'][14]."</td>";
         else
            echo "<td class='center'>".Html::convdate($data["date_expiration"])."</td>";

         $caneditaccounts=$account->can($IDc,'w');
         if ($this->canCreate() && $caneditaccounts) {
            if ($withtemplate<2) {
               echo "<td class='tab_bg_2 center'>";
               Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/accounts/front/account.form.php',
                                    'deleteaccounts',
                                    $LANG['buttons'][6],
                                    array('id' => $data['items_id']));

               echo "</td>";
            }
         } else {
            echo "<td></td>";
         }
         echo "</tr>";
      }

      if ($canedit) {

         $entities="";
         if ($itemtype!='Entity') {
             if ($item->isRecursive()) {
               $entities = getSonsOf('glpi_entities',$item->getEntityID());
            } else {
               $entities = $item->getEntityID();
            }   
         } else {
            $entities = $item->getEntityID();
         }
         $limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_accounts_accounts",'',$entities,true);

         $q="SELECT COUNT(*)
         FROM `glpi_plugin_accounts_accounts`
         WHERE `is_deleted` = '0' ";
         $q.=" $limit";
         $result = $DB->query($q);
         $nb = $DB->result($result,0,0);

         if ($nb>count($used)) {
            if ($this->canCreate()) {

               echo "<tr class='tab_bg_1'><td colspan='".(7+$colsup)."' class='right'>";
               echo "<input type='hidden' name='items_id' value='$ID'><input type='hidden' name='itemtype' value='$itemtype'>";
               $account->dropdownAccounts("plugin_accounts_accounts_id",$entities,$used);
               echo "</td><td class='center'>";
               echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
               echo "</td>";
               echo "</tr>";

            }
         }
      }
      if ($canedit) {
         echo "<tr class='tab_bg_1'><td colspan='".(8+$colsup)."' class='right'>";
         echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/accounts/front/account.form.php'>";
         echo $LANG['plugin_accounts'][32];
         echo "</a></td></tr>";
      }
      echo "</table></div>";
      Html::closeForm();
   }
}

?>