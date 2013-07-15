<?php
/*
 * @version $Id: computerdisk.class.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Disk class
class ComputerDisk extends CommonDBChild {

   // From CommonDBChild
   public $itemtype  = 'Computer';
   public $items_id  = 'computers_id';
   public $dohistory = true;


   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['computers'][8];
      }
      return $LANG['computers'][0];
   }


   function canCreate() {
      return Session::haveRight('computer', 'w');
   }


   function canView() {
      return Session::haveRight('computer', 'r');
   }


   function prepareInputForAdd($input) {

      // Not attached to computer -> not added
      if (!isset($input['computers_id']) || $input['computers_id'] <= 0) {
         return false;
      }

      if (!isset($input['entities_id'])) {
         $input['entities_id'] = parent::getItemEntity('Computer', $input['computers_id']);
      }

      return $input;
   }


   function post_getEmpty() {

      $this->fields["totalsize"] = '0';
      $this->fields["freesize"]  = '0';
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      // can exists for template
      if ($item->getType() == 'Computer' && Session::haveRight("computer","r")) {
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry($LANG['computers'][8],
                                        countElementsInTable('glpi_computerdisks',
                                                             "computers_id = '".$item->getID()."'"));
         }
         return $LANG['computers'][8];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      self::showForComputer($item, $withtemplate);
      return true;
   }


   /**
    * Print the version form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target for the Form
    *     - computers_id ID of the computer for add process
    *
    * @return true if displayed  false if item not found or not right to display
   **/
   function showForm($ID, $options=array()) {
      global $CFG_GLPI,$LANG;

      $computers_id = -1;
      if (isset($options['computers_id'])) {
        $computers_id = $options['computers_id'];
      }

      if (!Session::haveRight("computer","w")) {
        return false;
      }

      $comp = new Computer();

      if ($ID > 0) {
         $this->check($ID,'r');
         $comp->getFromDB($this->fields['computers_id']);
      } else {
         $comp->getFromDB($computers_id);
         // Create item
         $input = array('entities_id' => $comp->getEntityID());
         $options['entities_id'] = $comp->getEntityID();
         $this->check(-1, 'w', $input);
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      if ($ID>0) {
        $computers_id=$this->fields["computers_id"];
      } else {
         echo "<input type='hidden' name='computers_id' value='$computers_id'>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['help'][25]."&nbsp;:</td>";
      echo "<td colspan='3'>".$comp->getLink()."</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][16]."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td><td>".$LANG['computers'][6]."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "device");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['computers'][5]."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "mountpoint");
      echo "</td><td>".$LANG['computers'][4]."&nbsp;:</td>";
      echo "<td>";
      Dropdown::show('FileSystem', array('value' => $this->fields["filesystems_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['computers'][3]."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "totalsize");
      echo "&nbsp;".$LANG['common'][82]."</td>";

      echo "<td>".$LANG['computers'][2]."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "freesize");
      echo "&nbsp;".$LANG['common'][82]."</td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;

   }


   /**
    * Print the computers disks
    *
    * @param $comp Computer
    * @param $withtemplate=''  boolean : Template or basic item.
    *
    * @return Nothing (call to classes members)
   **/
   static function showForComputer(Computer $comp, $withtemplate='') {
      global $DB, $LANG;

      $ID = $comp->fields['id'];

      if (!$comp->getFromDB($ID) || !$comp->can($ID, "r")) {
         return false;
      }
      $canedit = $comp->can($ID, "w");

      echo "<div class='center'>";

      $query = "SELECT `glpi_filesystems`.`name` AS fsname,
                       `glpi_computerdisks`.*
                FROM `glpi_computerdisks`
                LEFT JOIN `glpi_filesystems`
                          ON (`glpi_computerdisks`.`filesystems_id` = `glpi_filesystems`.`id`)
                WHERE (`computers_id` = '$ID')";

      if ($result=$DB->query($query)) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='7'>";
         if ($DB->numrows($result)==1) {
            echo $LANG['computers'][0];
         } else {
            echo $LANG['computers'][8];
         }
         echo "</th></tr>";

         if ($DB->numrows($result)) {
            echo "<tr><th>".$LANG['common'][16]."</th>";
            echo "<th>".$LANG['computers'][6]."</th>";
            echo "<th>".$LANG['computers'][5]."</th>";
            echo "<th>".$LANG['computers'][4]."</th>";
            echo "<th>".$LANG['computers'][3]."</th>";
            echo "<th>".$LANG['computers'][2]."</th>";
            echo "<th>".$LANG['computers'][1]."</th>";
            echo "</tr>";

            Session::initNavigateListItems('ComputerDisk',
                                           $LANG['help'][25]." = ".
                                             (empty($comp->fields['name']) ? "($ID)"
                                                                           : $comp->fields['name']));

            while ($data=$DB->fetch_assoc($result)) {
               echo "<tr class='tab_bg_2'>";
               if ($canedit) {
                  echo "<td><a href='computerdisk.form.php?id=".$data['id']."'>".
                             $data['name'].(empty($data['name'])?$data['id']:"")."</a></td>";
               } else {
                  echo "<td>".$data['name'].(empty($data['name'])?$data['id']:"")."</td>";
               }
               echo "<td>".$data['device']."</td>";
               echo "<td>".$data['mountpoint']."</td>";
               echo "<td>".$data['fsname']."</td>";
               echo "<td class='right'>".Html::formatNumber($data['totalsize'], false, 0)."&nbsp;".
                      $LANG['common'][82]."<span class='small_space'></span></td>";
               echo "<td class='right'>".Html::formatNumber($data['freesize'], false, 0)."&nbsp;".
                      $LANG['common'][82]."<span class='small_space'></span></td>";
               echo "<td>";
               $percent = 0;
               if ($data['totalsize']>0) {
                  $percent=round(100*$data['freesize']/$data['totalsize']);
               }
               Html::displayProgressBar('100', $percent, array('simple'       => true,
                                                               'forcepadding' => false));
               echo "</td>";
               echo "</tr>";
               Session::addToNavigateListItems('ComputerDisk',$data['id']);
            }

         } else {
            echo "<tr><th colspan='7'>".$LANG['search'][15]."</th></tr>";
         }

         if ($canedit &&!(!empty($withtemplate) && $withtemplate == 2)) {
            echo "<tr class='tab_bg_2'><th colspan='7'>";
            echo "<a href='computerdisk.form.php?computers_id=$ID&amp;withtemplate=".
                   $withtemplate."'>".$LANG['computers'][7]."</a></th></tr>";
         }
         echo "</table>";
      }
      echo "</div><br>";
   }

}

?>
