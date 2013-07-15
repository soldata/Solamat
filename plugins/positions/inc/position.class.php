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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginPositionsPosition extends CommonDBTM
{

    public $dohistory = true;

    static $types = array('Computer',
        'Monitor',
        'NetworkEquipment',
        'Peripheral',
        'Printer',
        'Phone',
        'Location',
        'Netpoint'
    );

    static function getTypeName()
    {
        global $LANG;

        return $LANG['plugin_positions']['title'][1];
    }

    function canCreate()
    {
        return plugin_positions_haveRight('positions', 'w');
    }

    function canView()
    {
        return plugin_positions_haveRight('positions', 'r');
    }

    //if item deleted
    static function purgePositions($item)
    {
        $temp = new self();

        $type = get_class($item);
        $temp->deleteByCriteria(array('itemtype' => $type,
            'items_id' => $item->getField('id')), 1);
        return true;
    }

    /**
     * For other plugins, add a type to the linkable types
     *
     * @param $type string class name
     **/
    static function registerType($type)
    {
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
    static function getTypes($all = false)
    {

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
            /*if (!$item->canView()) {
               unset($types[$key]);
            }*/
        }
        return $types;
    }

    function prepareInputForAdd($input)
    {
        global $LANG;

        if (!isset ($input["items_id"]) || !isset($input["itemtype"])) {
            Session::addMessageAfterRedirect($LANG['plugin_positions'][16], false, ERROR);
            return array();
        }
        if (isset ($input["items_id"]) && isset($input["itemtype"])) {
            $restrict = "`items_id` = '" . $input["items_id"] . "'
                     AND `itemtype` = '" . $input["itemtype"] . "'";
            if (countElementsInTable("glpi_plugin_positions_positions", $restrict) != 0) {
                Session::addMessageAfterRedirect($LANG['plugin_positions'][17], false, ERROR);
                return array();
            }
        }

        if (!isset ($input["name"]) || empty($input["name"])) {
            $item = new $input['itemtype'];
            if ($item->getFromDB($input['items_id'])) {
                $input['name'] = $item->fields["name"];
            }
        }
        if (!isset ($input["x_coordinates"]) || empty($input["x_coordinates"]) || !isset ($input["y_coordinates"]) || empty($input["y_coordinates"])) {
            $input["x_coordinates"] = '-1000';
            $input["y_coordinates"] = '-225';
        }
        return $input;
    }

    function post_addItem()
    {
        global $CFG_GLPI, $LANG;

        if (!isset($this->input["massiveaction"])) {
         
         //$item = new $this->input["itemtype"]();
         //$item->getFromDB($this->input["items_id"]);
         //if (!empty($item->fields['locations_id']) && ($this->input["itemtype"])!='Location') {
             Html::redirect($CFG_GLPI["root_doc"] .
                 "/plugins/positions/front/coordinates.form.php?id=" . $this->getField('id'));
         //} else if(($this->input["itemtype"])!='Location') {
             //Session::addMessageAfterRedirect($LANG['plugin_positions']['setup'][8], false, ERROR);
         //}
      }
    }

    function getSearchOptions()
    {
        global $LANG;

        $tab = array();

        $tab['common'] = $LANG['plugin_positions']['title'][1];

        $tab[1]['table'] = $this->getTable();
        $tab[1]['field'] = 'name';
        $tab[1]['name'] = $LANG['plugin_positions'][7];
        $tab[1]['datatype'] = 'itemlink';
        $tab[1]['itemlink_type'] = $this->getType();

        $tab[3]['table'] = $this->getTable();
        $tab[3]['field'] = 'x_coordinates';
        $tab[3]['name'] = $LANG['plugin_positions'][2];

        $tab[4]['table'] = $this->getTable();
        $tab[4]['field'] = 'y_coordinates';
        $tab[4]['name'] = $LANG['plugin_positions'][3];

        $tab[5]['table'] = $this->getTable();
        $tab[5]['field'] = 'items_id';
        $tab[5]['name'] = $LANG['plugin_positions'][6];
        $tab[5]['massiveaction'] = false;
        $tab[5]['nosearch'] = true;

        $tab[6]['table'] = $this->getTable();
        $tab[6]['field'] = 'date_mod';
        $tab[6]['name'] = $LANG['common'][26];
        $tab[6]['datatype'] = 'datetime';

        $tab[7]['table'] = $this->getTable();
        $tab[7]['field'] = 'is_recursive';
        $tab[7]['name'] = $LANG['entity'][9];
        $tab[7]['datatype'] = 'bool';

        $tab[30]['table'] = $this->getTable();
        $tab[30]['field'] = 'id';
        $tab[30]['name'] = $LANG['common'][2];

        $tab[80]['table'] = 'glpi_entities';
        $tab[80]['field'] = 'completename';
        $tab[80]['name'] = $LANG['entity'][0];

        return $tab;
    }

    function defineTabs($options = array())
    {
        global $LANG;

        $ong = array();
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('Note', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        global $LANG;

        if (!$withtemplate) {
            if ($item->getType() == __CLASS__) {

                return self::getTypeName();

            } else if ($item->getType() == "Location") {

                return $LANG['Menu'][27];

            } else if (in_array($item->getType(), self::getTypes(true))
                && plugin_positions_haveRight('positions', 'r')
            ) {

                return self::getTypeName();

            }
        }

        return '';

    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        $self = new self();

        if ($item->getType() == __CLASS__) {

            self::showAddPosition($item);

        } else if ($item->getType() == "Location") {

            Document::showAssociated($item, $withtemplate);

        } else if (in_array($item->getType(), self::getTypes(true))) {

            $self->showPluginFromItems(get_class($item), $item->getField('id'));

        }
        return true;
    }

    /**
     * Return the SQL command to retrieve linked object
     *
     * @return a SQL command which return a set of (itemtype, items_id)
     */
    function getSelectLinkedItem()
    {
        return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_positions_positions`
              WHERE `id`='" . $this->fields['id'] . "'";
    }

    /**
     * Affiche la carte en png pour la couper selon les différentes pièces
     *
     */
   static function  showMapCreateLocation($options = array()) {
      global $DB, $CFG_GLPI, $LANG;
      
      $document_id         = $options["document_id"];
      $locations_idParent  = $options["locations_id"];
      $test = '';

      if (!plugin_positions_haveRight('positions', 'r')) return false;

      $alert = $LANG['plugin_positions'][24];
      echo "<script type='text/javascript'>
         jQuery(function($){
         $('#target').Jcrop({
            onChange:   showCoords,
            onSelect:   showCoords,
            onRelease:  clearCoords
            });
         });
      </script>";

      echo "<script type='text/javascript'>
         function showCoords(c) {
         $('#x1').val(c.x);
         $('#y1').val(c.y);
         $('#x2').val(c.x2);
         $('#y2').val(c.y2);
         $('#w').val(c.w);
         $('#h').val(c.h);
         $('#document_id').val($document_id);
      };
         function clearCoords(){
         $('coords input').val('');
      };

         function checkCoords()
      {
         if (parseInt(jQuery('#w').val())>0) return true;
         if ('#name'=='') return true;
         alert('$alert');
         return false;
      }

         function showlist(divName,etat)
      {
         if(divName == 'existLocation'){
            document.getElementById(divName).style.visibility=etat;
            document.getElementById('newLocation').style.visibility='hidden';
            $('#test').val('existLocation');
         }
         else if(divName == 'newLocation'){
            document.getElementById(divName).style.visibility=etat;
            document.getElementById('existLocation').style.visibility='hidden';
            $('#test').val('newLocation');
            $('#locations_idParent').val($locations_idParent);
         }
      }
      </script>";

      $Doc = new Document();
      if ($Doc->getFromDB($document_id)) {
      
         $entities_id = $Doc->fields["entities_id"];
         $path = GLPI_DOC_DIR . "/" . $Doc->fields["filepath"];
         $img = $CFG_GLPI['root_doc'] . "/plugins/positions/front/map.send.php?docid=" . $document_id;
         $dim = getimagesize($path);
         $extension = pathinfo($path, PATHINFO_EXTENSION);

         if ($extension == 'PNG' 
               || $extension == 'JPEG' 
                  || $extension == 'JPG' 
                     || $extension == 'GIF') {
           
            echo"<form action=\"crop.form.php\" method=\"post\" onsubmit=\"return checkCoords();\" >";

            //Liste des lieux existants
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'><th colspan='2'>";
            echo $LANG['plugin_positions'][19];
            echo "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo "<input type=\"radio\" name=\"choice\" value=\"existLocation\" 
                     onclick=\"showlist('existLocation','visible')\">";
            echo $LANG['plugin_positions'][20];
            echo "</td>";

            echo "<td id=\"existLocation\" class='left' style=\"visibility:hidden\">";
            $query = "SELECT `id`,`name`
                            FROM `glpi_locations`
                            WHERE `locations_id` = '" . $locations_idParent . "'
                            AND `name` NOT IN
                                ( SELECT `name` FROM `glpi_documents`
                                 )
                                ";
            $result = $DB->query($query);
            $number = $DB->numrows($result);

            $locations = array();
            while ($data = $DB->fetch_assoc($result)) {

                $locations[] = $data['id'];
            }
            $condition = "";
            if (!empty($locations)) {

               $condition = "`id` IN (" . implode(',', $locations) . ")";

               Dropdown::show('Location', array('value' => $locations_idParent,
                    'entity' => $_SESSION["glpiactive_entity"],
                    'condition' => $condition));
            } else {
                echo $LANG['plugin_positions'][22];
            }
            echo "</td>";
            echo "</tr>";
               
            //Ajout d'un nouveau lieu
            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo "<input type=\"radio\" name=\"choice\" value=\"newLocation\" 
                     onclick=\"showlist('newLocation','visible')\">";
            echo $LANG['plugin_positions'][21];
            echo "</td>";

            echo "<td id=\"newLocation\" class='left' style=\"visibility:hidden\">";
            echo $LANG['common'][16]." : ";
            echo "<input type=\"text\" size=\"50\" id=\"name\" name=\"name\" />";
            echo "</td>";
            echo "</tr>";

            //Validation
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' colspan=2>";
            echo "<input type=\"hidden\" id=\"x1\" name=\"x1\" />";
            echo "<input type=\"hidden\" id=\"y1\" name=\"y1\" />";
            echo "<input type=\"hidden\" id=\"x2\" name=\"x2\" />";
            echo "<input type=\"hidden\" id=\"y2\" name=\"y2\" />";
            echo "<input type=\"hidden\" id=\"w\" name=\"w\" />";
            echo "<input type=\"hidden\" id=\"h\" name=\"h\" />";
            echo "<input type=\"hidden\" id=\"extension\" name=\"extension\" value=\"$extension\" />  ";
            echo "<input type=\"hidden\" id=\"test\" name=\"test\" value=\"$test\" />";
            echo "<input type=\"hidden\" id=\"document_id\" name=\"document_id\" />";
            echo "<input type=\"hidden\" id=\"locations_idParent\" name=\"locations_idParent\" value=\"$locations_idParent\" />";
            echo "<input type=\"hidden\" id=\"entities_id\" name=\"entities_id\" value=\"$entities_id\" />";
            
            //Case à cocher pour continuer l'ajout
            echo "<input type=\"checkbox\" id=\"continueAdd\" name=\"continueAdd\">&nbsp;";
            echo $LANG['plugin_positions'][23];
            echo "&nbsp;&nbsp;<input type=\"submit\" name=\"valid\" value='".$LANG['buttons'][2]."' class='submit'>";
            echo "</td>";
            echo "</tr>";

            echo "</table>";

            echo "<table class='tab_cadre'>";
            echo "<tr class='tab_bg_1'><th>";
            echo $Doc->fields["name"];
            echo "<div id=\"imageCrop\"> ";
            echo "<img src='" . $img . "' id=\"target\" alt=\"$document_id\" width=\"$dim[0]\" height=\"$dim[1]\" />";
            echo "</div> ";
            echo "</th></tr>";
            echo "</table>";
               
            Html::closeForm();
         }
     } else {

         Html::redirect($CFG_GLPI["root_doc"] .
             "/plugins/positions/front/map.php?locations_id=" . $locations_idParent);
     }
   }

    /**
     * Résultat de la création du sous-lieu + ajout dans la base de données
     */
   static function showFormResCreateLocation($opt) {
      global $CFG_GLPI, $DB, $LANG;

      $filename = $opt["document_id"] . $opt["name"] . "." . $opt["extension"];
      $filepath = "/_uploads/" . $filename;

      $id = 0;
      if ($opt["test"] == "existLocation") {
         $query = "SELECT `id`,`name`
                  FROM `glpi_locations`
                  WHERE `id` = '" . $opt["locations_id"] . "'";

         $result = $DB->query($query);

         while ($data = $DB->fetch_assoc($result)) {
            $name = $data['name'];
            $id = $data['id'];
         }
      } else if ($opt["test"] == "newLocation") {
         $locations_id = $opt["locations_idParent"];
      }

      $params = array(
         "name"         => $opt["name"],
         "document_id"  => $opt["document_id"],
         "filepath"     => $filepath,
         "filename"     => $filename,
         "entities_id"  => $opt["entities_id"],
         "locations_id" => $opt["locations_id"],
         "id"           => $id,
         "itemtype"     => "Location",
      );

      //FONCTION QUI PERMET D'AJOUTER LE LIEU DANS LA BASE DE DONNEES
      $dropdown = new Location();

      //AJOUT DU LIEU
      if ($opt["test"] == 'newLocation') {
         if ($newID = $dropdown->add($params)) {
            $dropdown->refreshParentInfos();
         }
         $opt["locations_id"] = $newID;
      }
      
      if ($opt["locations_id"] != $opt["locations_idParent"]) {
         //AJOUT DU DOC ASSOCIE AU LIEU
         $doc = new Document();
         $documentitem = new Document_Item();
         $self = new self();

         $input = array();
         $input["entities_id"] = $opt["entities_id"];
         $input["name"] = $opt["name"];
         $input["upload_file"] = $filename;
         //$input["documentcategories_id"]=$options["rubrique"];
         //$input["mime"]="text/html";
         $input["date_mod"] = date("Y-m-d H:i:s");
         $input["users_id"] = Session::getLoginUserID();

         $newdoc = $doc->add($input);
         //$newID = $doc->add($_POST);
            
         if ($opt["test"] == 'newLocation') {
            $documentitem->add(array('documents_id' => $newdoc,
                                       'itemtype' => 'Location',
                                       'items_id' => $newID));
            $param = array(
                "items_id" => $newID,
                "entities_id" => $opt["entities_id"],
                "locations_id" => $opt["locations_idParent"],
                "itemtype" => "Location",
                "x_coordinates" => -800,
                "y_coordinates" => -150
            );
            $self->add($param);

            if ($opt["checked"] == 'on') {
               self::showMapCreateLocation($opt);
            }
            else if ($opt["checked"] == 'off') {
                Html::redirect($CFG_GLPI["root_doc"] .
                    "/plugins/positions/front/map.php?locations_id=" . $opt["locations_id"]);
            }

         } else if ($opt["test"] == 'existLocation') {
         
            $documentitem->add(array('documents_id' => $newdoc,
                'itemtype' => 'Location',
                'items_id' => $id));

            $param = array(
                "items_id" => $id,
                "entities_id" => $opt["entities_id"],
                "locations_id" => $opt["locations_idParent"],
                "itemtype" => "Location",
                "x_coordinates" => -800,
                "y_coordinates" => -150
            );
            $self->add($param);

            if ($opt["checked"] == 'on') {
               self::showMapCreateLocation($opt);
            }
            else if ($opt["checked"] == 'off') {
                Html::redirect($CFG_GLPI["root_doc"] .
                    "/plugins/positions/front/map.php?locations_id=" . $opt["locations_id"]);
            }
         }
      } else {
         Session::addMessageAfterRedirect($LANG['plugin_positions'][17], false, ERROR);
         Html::redirect($CFG_GLPI["root_doc"] .
                    "/plugins/positions/front/map.php?locations_id=" . $opt["locations_id"]);
      }
   }

    function showForm($ID, $options = array())
    {
        global $LANG;

        if (!$this->canView()) return false;

        if ($ID > 0) {
            $this->check($ID, 'r');
        } else {
            // Create item
            $this->check(-1, 'w');
            $this->getEmpty();
        }

        $this->showTabs($options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . $LANG['plugin_positions'][7] . ": </td>";
        echo "<td>";
        Html::autocompletionTextField($this, "name");
        echo "</td>";

        echo "<td>" . $LANG['plugin_positions'][2] . ": </td>";
        echo "<td>";
        if ($ID > 0)
            Html::autocompletionTextField($this, "x_coordinates", array('size' => "10"));
        echo "</td>";

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . $LANG['plugin_positions'][6] . ": </td>";
        echo "<td>";

        if ($ID < 0 || (!$this->fields['itemtype'] && !$this->fields['items_id'])) {
            $types = self::getTypes();

            Dropdown::showAllItems("items_id", 0, 0, (
            $this->fields['is_recursive'] ? -1 :
                $this->fields['entities_id']), self::getTypes());
        } else {
            if ($this->fields['itemtype'] && $this->fields['items_id']) {
                if (class_exists($this->fields['itemtype'])) {
               $item = new $this->fields['itemtype']();
               $item->getFromDB($this->fields['items_id']);
               echo $item->getTypeName() . " - ";
               echo $item->getLink() . " - ";
               echo Dropdown::getDropdownName("glpi_locations", $item->fields['locations_id']);
            }
            }
        }
        echo "</td>";

        echo "<td>" . $LANG['plugin_positions'][3] . ": </td>";
        echo "<td>";
        if ($ID > 0) {
            Html::autocompletionTextField($this, "y_coordinates", array('size' => "10"));
        }
        echo "</td>";

        echo "</tr>";

        $this->showFormButtons($options);
        $this->addDivForTabs();

        return true;
    }

    static function showAddPosition($item)
    {
        global $LANG;

        if ($item->getField('id') > 0
            && plugin_positions_haveRight('positions', 'r')
        ) {
            echo "<div class='center'><table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo "<a href='./coordinates.form.php?id=" . $item->getField('id') . "'>" .
                $LANG['plugin_positions']['setup'][9] . "</a>";
            echo "</td>";
            echo "</tr>";
            echo "</table></div>";
        }
    }

    static function getDocument($locations_id)
    {
        global $DB;

        $documents_id = 0;
        $query = "SELECT `documents_id`
                FROM `glpi_documents_items`
                WHERE `items_id` = '" . $locations_id . "'
                      AND `itemtype` = 'Location' ";

        $result = $DB->query($query);
        while ($data = $DB->fetch_assoc($result)) {
            $documents_id = $data['documents_id'];
        }
        return $documents_id;
    }


    static function getItems($locations_id)
    {

        $items = array();
        foreach (self::getTypes() as $key => $item) {
            $table = getTableForItemType($item);
            $itemclass = new $item();
            $restrict = "`is_template` = '0' AND `is_deleted` = '0'";
            $restrict .= " AND `locations_id` = '" . $locations_id . "'";
            $restrict .= getEntitiesRestrictRequest(" AND ", $table, '', '',
                $itemclass->maybeRecursive());
            $datas = getAllDatasFromTable($table, $restrict);
            if (!empty($datas)) {
                foreach ($datas as $data) {
                    $items[$item][] = $data["id"];
                }
            }
        }
        return $items;
    }

    static function getMapItems($locations_id)
    {

        $itemsMap = array();
        $restrict = getEntitiesRestrictRequest(" ", "glpi_plugin_positions_positions", '', '',
            true);
        $datas = getAllDatasFromTable("glpi_plugin_positions_positions", $restrict);
        if (!empty($datas)) {
            foreach ($datas as $data) {
                $itemsMap[$data['itemtype']][] = $data;
            }
        }
        return $itemsMap;
    }

    /**
     * @static function showMap : affiche tous les éléments de la carte (menus, onglets...)
     * @param $options
     */
      static function showMap($options) {
        global $CFG_GLPI, $LANG;

        if (!$options['locations_id']) {

            $self = new self();
            $self->getFromDB($options["id"]);
            if (isset($self->fields["itemtype"])) {

               $itemclass = new $self->fields["itemtype"]();
               $itemclass->getFromDB($self->fields["items_id"]);
               $options['locations_id'] = $itemclass->fields['locations_id'];
            }
        }
        if ($options['locations_id']) {

            $documents_id = self::getDocument($options['locations_id']);

            $Doc = new Document();
            if (isset($documents_id) && $Doc->getFromDB($documents_id)) {

                $params = array("locations_id" => $options['locations_id'],
                    "id" => $options['id'],
                    "itemtype" => $options['itemtype'],
                    "target" => $options['target']
                );

                $params['docid'] = $documents_id;
                $path = GLPI_DOC_DIR . "/" . $Doc->fields["filepath"];

                if ($handle = fopen($path, "r")) {
                    $infos_image = @getImageSize($path);
                    $largeur = $infos_image[0]; // largeur de l'image
                    $hauteur = $infos_image[1]; // hauteur de l'image

                    $params["largeur"] = $largeur;
                    $params["hauteur"] = $hauteur;

                    if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
                        $params["download"] = 0;
                    } else {
                        $params["download"] = 1;
                    }

                    echo "<div class='center'><table class='plugin_positions_tab_cadre_fixe'>";
                    echo "<tr class='tab_bg_2' valign='top'>";

                    $items = self::getMapItems($params['locations_id']);

                    if (!isset($options['menuoff'])) {
                        echo "<td>";
                        self::showLocationForm($params["locations_id"]);
                        //self::showLink($params);
                        echo "</td>";
                    }
                    if (plugin_positions_haveRight('positions', 'w') && !isset($options['menuoff'])) {
                        echo "<td>";
                        self::showAddFromPlugin($params['locations_id']);
                        echo "</td>";
                        echo "<td>";
                        self::selectDisplay($params, $items);
                        echo "</td>";
                    }
                    echo "</tr>";
                    echo "</table></div>";

                    if (isset($options['menuoff'])) $params["menuoff"] = $options['menuoff'];

                    if ((plugin_positions_haveRight('positions', 'w')) && (!isset($options['menuoff']))) {
                        echo "<form method='post' name='pointform' id='pointform' action=\"" .
                            $CFG_GLPI["root_doc"] . "/plugins/positions/front/position.form.php\">";

                        echo "<div class='center'>";
                        echo "<table class='plugin_positions_tab_cadre_fixe' width='30%'>";

                        if ($options['id']) {
                            echo "<tr class='tab_bg_2'>";
                            $form = Toolbox::getItemTypeFormURL($self->getType());
                            echo "<td colspan='4' class='center'>" .
                                $self->getLink();
                            echo "</td></tr>";
                        }
                        echo "<tr class='tab_bg_2'>";
                        echo "<td colspan='2' class='center'>";
                        echo "<input type='submit' name='update' value=\"" .
                            $LANG['plugin_positions']['setup'][9] . "\" class='submit'>";
                        echo "</td>";
                        //création d'un nouveau bouton pour la création de nouvelles pièces
                        if (Session::haveRight('entity_dropdown', 'w')) {
                           echo "<td colspan='2' class='center'>";
                           echo "<input type='submit' name='addLocation' value=\"" .
                                $LANG['plugin_positions']['setup'][16] . "\" class='submit'>";
                           echo "</td>";
                        }
                        echo "<input type='hidden' name='locations_id' value='" .
                            $options['locations_id'] . "'>";
                        echo "<input type='hidden' name='id' value='" . $options['id'] . "'>";
                        echo "<input type='hidden' name ='x_coordinates'>";
                        echo "<input type='hidden' name ='y_coordinates'>";
                        echo "<input type='hidden' name ='multi'>";
                        echo "<input type='hidden' name ='referrer' value='" . $options['id'] . "'>";
                        echo "</tr>";
                        echo "</table></div>";
                    }

                    echo "<div class='center'><table class='tab_cadre_fixe'>";

                    echo "<tr class='tab_bg_1'><th>";
                    echo $Doc->fields["name"];
                    echo "</th></tr>";

                    echo "<tr class='tab_bg_1'><td>";

                    self::displayMap($items, $params);
                    echo "</td></tr>";
                    echo "</table>";

                  if ((plugin_positions_haveRight('positions', 'w') && (!isset($options['menuoff'])))) {
                     Html::closeForm();
                  }
               } else {
                  echo "<div class='center'>";
                  echo $LANG['plugin_positions']['setup'][7];
                  echo "</div>";
               }
            } else {
               echo "<div class='center'>";
               echo $LANG['plugin_positions'][12] . "<br><br>";
               Html::displayBackLink();
               echo "</div>";
            }
         } else {
            echo "<div class='center'>";
            echo $LANG['plugin_positions']['setup'][8];
            echo "</div>";
         }
      }

    /**
     * @static function showLocationForm : affiche le formulaire contenant la liste des lieux
     * @param $locations_id : id du lieu
     */
    static function showLocationForm($locations_id)
    {
        global $DB, $LANG;

        $locations = array();

        $target = "map.php";
        echo "<form method='post' id='locationform' action='$target'>";
        echo "<table class='tab_cadre'>";
        echo "<tr class='tab_bg_1'><td class='center'>";

        $query = "SELECT `items_id`
                FROM `glpi_documents_items`
                WHERE `itemtype` = 'Location' ";

        $result = $DB->query($query);
        $number = $DB->numrows($result);

        while ($data = $DB->fetch_assoc($result)) {
            $locations[] = $data['items_id'];
        }
        $condition = "";
        if (!empty($locations)) {
            $condition = "`id` IN (" . implode(',', $locations) . ")";

            Dropdown::show('Location', array('value' => $locations_id,
                'entity' => $_SESSION["glpiactive_entity"],
                'condition' => $condition));
            echo "</td></tr><tr><td class='center'>";
            echo "<input type='submit' name='export' value=\"" .
                $LANG['plugin_positions'][11] . "\" class='submit'>";
            echo "</td></tr>";
            echo "</table>";
            Html::closeForm();
        } else {
            echo $LANG['plugin_positions']['setup'][7];
            echo "</td></tr>";
            echo "</table>";
            Html::closeForm();
        }
    }

    static function showAddFromPlugin($locations_id)
    {
        global $CFG_GLPI, $LANG;

        if (!plugin_positions_haveRight('positions', 'w')) return false;

        echo "<div align='center'>";
        echo "<form method='post' action=\"" . $CFG_GLPI["root_doc"] .
            "/plugins/positions/front/position.form.php\" name='addfromplugin' id='addfromplugin'>";

        $entity = $_SESSION["glpiactive_entity"];

        echo "<table class='tab_cadre' width='30%'>";
        echo "<tr><th colspan='3'>" . $LANG['plugin_positions'][1] . " :</th></tr>";

        echo "<tr class='tab_bg_1'><td>";
        echo $LANG['plugin_positions'][6] . "</td>";
        echo "<td>";
        PluginPositionsImageItem::showAllItems("items_id", 0, 0, $entity, self::getTypes(), $locations_id);
        echo "</td>";

        echo "<td>";
        echo "<input type='hidden' name='locations_id' value='" . $locations_id . "'>";
        echo "<input type='hidden' name='entities_id' value='$entity'>";
        echo "<input type='submit' name='add' value=\"" . $LANG['buttons'][8] .
            "\" class='submit'>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }

    static function selectDisplay($params, $items)
    {
        global $LANG, $CFG_GLPI;

        $colspan = count($items);
        if (!empty($items)) {
            $colspan = count($items);
            $colspan = ($colspan * 2) + 1;
            echo "<form method='post' name='massiveaction_form' id='massiveaction_form' action='" . $params["target"] . "'>";
            echo "<table class='tab_cadre' cellpadding='5'>";
            echo "<tr class='tab_bg_1'>";
            echo "<th colspan='" . $colspan . "'>";
            echo $LANG['plugin_positions'][15] . " : ";
            echo "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1'>";
            foreach ($items as $classe => $ids) {
                if ($classe) {
                    if (!class_exists($classe)) {
                        continue;
                    }
                    $item = new $classe();

                    echo "<td>";
                    echo "<input type='checkbox' ";
                    if (!empty($params["itemtype"])) {
                        if (in_array($classe, $params["itemtype"])) {
                            echo "checked ";
                        }
                    }
                    echo "name='itemtype[]' value='" . $classe . "'>";
                    echo "</td>";
                    echo "<td>" . $item->getTypeName() . "</td>";
                }
            }
            echo "<td class='center'>";
            echo "<input type='submit' name='affich' value=\"" . $LANG['buttons'][7] .
                "\" class='submit'>";
            echo "</td></tr>";
            echo "</table>";
            Html::closeForm();
        }
    }

    /**
     * @static function displayMap : permet de récuperer tous les éléments de  la base de données pour pouvoir
     * les afficher sur la carte
     * @param $items
     * @param $params
     */
    static function displayMap($items, $params)
    {
        global $CFG_GLPI, $LANG, $DB;

        if (isset($params['itemtype']) && is_array($params['itemtype'])) {
            foreach ($items as $classe => $ids) {
                if (!in_array($classe, $params['itemtype'])) {
                    unset($items[$classe]);
                }
            }
        }
        $image = new PluginPositionsImageItem();
        $tableImage = $image->getTable();

        $plugin = new Plugin();

        $objects = array();
        if (!empty($items)) {

            foreach ($items as $classe => $ids) {
                foreach ($ids as $key => $val) {

                    $itemclass = new $val['itemtype']();
                    $itemclass->getFromDB($val['items_id']);

                    if ($itemclass->getFromDB($val['items_id'])) {
                        if (($itemclass->fields['locations_id'] == $params['locations_id'])) {
                            $val['picture'] = null;
                            $val['img'] = null;

                            $canvas_id = count($objects);

                            $text = self::displayItemTitle($itemclass);


                            if ($plugin->isActivated("resources") && ($val['itemtype'] == 'PluginResourcesResource')) {

                                $val['picture'] = $itemclass->fields['picture'];

                                $img = $CFG_GLPI['url_base'] . '/plugins/resources/pics/nobody.png';

                                if (!($val['picture'] == null)) {

                                    $img = $CFG_GLPI['url_base'] . '/plugins/resources/front/picture.send.php?file=' . $val['picture'];
                                }
                                $objects[$val['id']] = array('canvas_id' => $canvas_id,
                                    'name' => $text,
                                    'id' => $val['id'],
                                    'items_id' => $val['items_id'],
                                    'itemtype' => $val['itemtype'],
                                    'width' => 25,
                                    'height' => 30,
                                    'x_coordinates' => $val['x_coordinates'],
                                    'y_coordinates' => $val['y_coordinates'],
                                    'img' => $img);

                            } else if (($val['itemtype'] == 'Location')) {

                                $img = $CFG_GLPI['url_base'] . '/plugins/positions/pics/door.png';

                                $objects[$val['id']] = array('canvas_id' => $canvas_id,
                                    'name' => $text,
                                    'id' => $val['id'],
                                    'items_id' => $val['items_id'],
                                    'itemtype' => $val['itemtype'],
                                    'width' => 25,
                                    'height' => 30,
                                    'x_coordinates' => $val['x_coordinates'],
                                    'y_coordinates' => $val['y_coordinates'],
                                    'img' => $img);
                            } else if (($val['itemtype'] == 'Netpoint')) {

                                $img = $CFG_GLPI['url_base'] . '/plugins/positions/pics/socket.png';

                                $objects[$val['id']] = array('canvas_id' => $canvas_id,
                                    'name' => $text,
                                    'id' => $val['id'],
                                    'items_id' => $val['items_id'],
                                    'itemtype' => $val['itemtype'],
                                    'width' => 25,
                                    'height' => 30,
                                    'x_coordinates' => $val['x_coordinates'],
                                    'y_coordinates' => $val['y_coordinates'],
                                    'img' => $img);
                            } else {

                                $itemtype = $val['itemtype'] . "Type";
                                $typefield = getForeignKeyFieldForTable(getTableForItemType($itemtype));
                                $imgitem = new PluginPositionsImageItem();
                                if (isset($itemclass->fields[$typefield]))
                                    $val['img'] = $imgitem->displayItemImage($itemclass->fields[$typefield], $classe);

                                $img = $CFG_GLPI['url_base'] . '/plugins/positions/pics/nothing.png';

                                if (!($val['img'] == null)) {

                                    $img = $CFG_GLPI['url_base'] . '/plugins/positions/front/map.send.php?type=pics&file=' . $val['img'];
                                }

                                $objects[$val['id']] = array('canvas_id' => $canvas_id,
                                    'name' => $text,
                                    'id' => $val['id'],
                                    'items_id' => $val['items_id'],
                                    'itemtype' => $val['itemtype'],
                                    'width' => 30,
                                    'height' => 30,
                                    'x_coordinates' => $val['x_coordinates'],
                                    'y_coordinates' => $val['y_coordinates'],
                                    'img' => $img);
                            }
                            $canvas_id++;
                        }
                    }
                }
            }

            self::drawCanvas($objects, $params);

        } else {

            //tests sur les droits
            $eventless = false;
            if (!plugin_positions_haveRight('positions', 'w') || (isset($params['menuoff']) && $params['menuoff'] == 1)) {
                $eventless = true;
            }

            $objects[] = array('canvas_id' => '0',
                'name' => '',
                'id' => '',
                'items_id' => '',
                'itemtype' => '',
                'width' => 0.5,
                'height' => 0.5,
                'x_coordinates' => -300,
                'y_coordinates' => 80,
                'testEvent' => $eventless,
                'hideLabel' => true,
                'img' => $CFG_GLPI['url_base'] . '/plugins/positions/pics/nothing.png');
            self::drawCanvas($objects, $params);
        }

    }


    /**
     * @static function drawCanvas : permet de dessiner le canvas avec les éléments récupérés en paramètre
     * @param $items
     * @param $params
     */
    public static function drawCanvas($items, $params)
    {

        global $CFG_GLPI;

        $nodes = array();
        //tests sur les droits
        $eventless = false;
        if (!plugin_positions_haveRight('positions', 'w') || (isset($params['menuoff']) && $params['menuoff'] == 1)) {
            $eventless = true;
        }

        //image de fond
        $input = array();
        $input['id'] = "Fond";
        $input['shape'] = 'image';
        $input['eventless'] = true;
        $input['width'] = $params['largeur'];
        $input['height'] = $params['hauteur'];
        $input['imagePath'] = $CFG_GLPI['url_base'] . '/plugins/positions/front/map.send.php?docid=' . $params['docid'];
        $input['color'] = 'black';
        $input['testEvent'] = $eventless;
        $input['x'] = 250;
        $input['y'] = 250;
        $input['hideLabel'] = true;
        $nodes['nodes'][] = $input;

        $highlight = array();

        foreach ($items as $id => $node) {

            //création des noeuds
            $input = array();
            $input['id'] = $node['id'];
            $input['itemtype'] = $node['itemtype'];
            $input['name'] = $node['name'];
            $input['shape'] = 'image';
            $input['width'] = $node['width'];
            $input['height'] = $node['height'];
            $input['imagePath'] = $node['img'];
            $input['x'] = $node['x_coordinates'] + 1;
            $input['y'] = $node['y_coordinates'] + 1;
            $input['items_id'] = $node['items_id'];
            $input['testEvent'] = $eventless;
            $input['locations_id'] = $params['locations_id'];
            $nodes['nodes'][] = $input;
        }

        if (isset($node['id']) == $params['id'] && isset($node['id']) != '') {
            $highlight[] = $params['id'];
        }

        //configuration du canvas
        $canvas_config = array('graphType' => 'Network',
            'backgroundGradient2Color' => 'white', //couleur du fond 1
            'backgroundGradient1Color' => 'white', //couleur du fond 2
            'gradient' => false, //dégradé
            'networkFreezeOnLoad' => true,
            'nodeFontColor' => 'rgb(29,34,43)', //couleur des Noeuds
            'imageDir' => $CFG_GLPI['url_base'] . "/plugins/positions/lib/canvas/images/",
            //'zoom' => 1.0, //zoom de depart
            //'zoomStep' => 0.5, //coefficient pour le zoom
            'calculateLayout' => false,
            'disableConfigurator' => true,
            'showCode' => false, //affichage du code
            'nodeFontSize' => 6,
            'fontName' => 'Verdana',
            'resizable' => false
        );


        //Création du canvas et des évènements
        echo "<script>
         Ext.onReady(function(){
            Ext.QuickTips.init();
               var panel = new Ext.canvasXpress({
               renderTo: 'Carte',
               frame: false,
               id: 'graph',
               width: 1300,
               height: 550,
               highlightArray: " . json_encode($highlight) . ",
               showExampleData: false,
               imgDir: '../lib/canvas/images/',
               data: " . json_encode($nodes) . ",
               _glpi_csrf_token: " . json_encode(Session::getNewCSRFToken()) . ",
               options:" . json_encode($canvas_config) . ",
               events:{
                  dblclick : function(obj){
                     if (navigator.appName == 'Microsoft Internet Explorer')
                          {
                               var ua = navigator.userAgent;
                               var re  = new RegExp(\"MSIE ([0-9]{1,}[\.0-9]{0,})\");
                               if (re.exec(ua) != null){
                                 rv = parseFloat( RegExp.$1 );
                               }
                               if ( rv == 9.0 ){
                                 var n = obj.nodes[0];
                                 var win = new Ext.Window({
                                    id:n.id,
                                    title:'Informations - '+n.name,
                                    width:'600px',
                                    height:'800px',
                                    modal:false,
                                    layout:'fit',
                                    resizable:false,
                                    autoLoad: function(n){
                                                url: 'showinfos.php?items_id='+n.items_id+'&id='+n.id+'&name='+n.name+
                                            '&img='+n.imagePath+'&itemtype='+n.itemtype}

                                 });
                                 win.show();
                                 if( n.itemtype =='Location'){
                                    win.hide();
                                 }
                               }
                               else{
                                 alert('Download Internet Explorer v9.0');
                               }
                          }
                  },
                  click: function(obj) {
                     var n = obj.nodes[0];
                         var win = new Ext.Window({
                            id:n.id,
                            title:'Informations - '+n.name,
                            width:'600px',
                            height:'800px',
                            modal:false,
                            layout:'fit',
                            resizable:false,
                            autoLoad: {
                            url: 'showinfos.php?items_id='+n.items_id+'&id='+n.id+'&name='+n.name+
                            '&img='+n.imagePath+'&itemtype='+n.itemtype}
                         });
                         win.show();
                         if( n.itemtype =='Location'){
                            win.hide();
                         }
                  }
               }
            });
         });
      </script>";

        echo "<div id='Carte'></div>";

    }

    static function displayItemTitle($itemclass)
    {

        $text = "";
        if (isset($itemclass->fields["name"])
            && !empty($itemclass->fields["name"])
        ) {

            $plugin = new Plugin();
            if ($plugin->isActivated("resources")
                && $itemclass->getType() == 'PluginResourcesResource'
            ) {
                $text .= PluginResourcesResource::getResourceName($itemclass->getID());
            } else {
                $text .= $itemclass->fields["name"];
            }
        }
        if ($_SESSION['glpiactiveprofile']['interface'] != 'central') {
            $text .= PluginPositionsInfo::getCallValue($itemclass, true);
        }
        return $text;
    }

    /*Fonction qui permet de récupérer les informations à afficher dans le popup*/
    static function showOverlay($localID, $srcimg, $itemclass, $infos)
    {
        global $CFG_GLPI, $LANG;

        $defaultheight = 50;
        $height = 0;
        $addheight = 0;

        if (!empty($infos)) {
            foreach ($infos as $info) {
                if ($itemclass->getType() == $info['itemtype']) {
                    $fields = explode(',', $info['fields']);
                    $nb = 0;
                    for ($i = 0; $i < count($fields); $i++) {
                        if (!empty($itemclass->fields[$fields[$i]])) {
                            $nb++;
                        }
                    }
                    $height = 30 * $nb;
                }
            }
            $height = $defaultheight + $height;
            if ($itemclass->getType() == 'Phone') {
                $height = $height + 80;
            } else if ($itemclass->getType() == 'PluginResourcesResource') {
                $resID = $itemclass->fields['id'];
                $restrict = "`plugin_resources_resources_id` = $resID AND `itemtype` = 'User' ";
                $datas = getAllDatasFromTable('glpi_plugin_resources_resources_items', $restrict);
                if (!empty($datas)) {
                    foreach ($datas as $data) {
                        if (isset($data['items_id']) && ($data['items_id'] > 0)) {
                            $userid = $data['items_id'];
                            $entitiesID = $itemclass->fields['entities_id'];
                            $condition = "`users_id` = '$userid'
                                    AND `is_deleted` = '0' 
                                    AND `is_template` = '0' 
                                    AND `entities_id` = '$entitiesID'
                                    AND `contact_num` != 0 ";
                            if (($number = countElementsInTable("glpi_phones", $condition)) > 1) {
                                $addheight = 30 * $number;
                            }
                            $height = $height + $addheight;
                        }
                    }
                }
            }
        } else {
            $height = $defaultheight + 30;
        }

        if (plugin_positions_haveRight('positions', 'w') && $itemclass->canView()) {
            echo "<a class='config' target='_blank' title=\"" . $LANG['plugin_positions']['setup'][11] . "\"
                              href='" . $CFG_GLPI["root_doc"] .
                "/plugins/positions/front/info.php'></a>";
        }
        $width = 450;

        if ($itemclass->getType() != 'PluginResourcesResource'
            && $itemclass->getType() != 'Location'
               && $itemclass->getType() != 'Netpoint') {

            $img = "<img src='" . GLPI_ROOT . "/plugins/positions/pics/nothing.png' width='30' height='30'>";

            if (!preg_match("/nothing.png/", $srcimg)) {
                $path = GLPI_PLUGIN_DOC_DIR . "/positions/pics/" . $srcimg;
                $sizes = getimagesize($path);
                $largeur = $sizes[0];
                $hauteur = $sizes[1];
                $img = "<object width='" . $largeur . "' height='" . $hauteur . "' data='" . $CFG_GLPI['root_doc'] . "/plugins/positions/front/map.send.php?file=" . $srcimg . "&type=pics'>
             <param name='src' value='" . $CFG_GLPI['root_doc'] .
                    "/plugins/positions/front/map.send.php?file=" . $srcimg . "&type=pics'>
            </object> ";
            }
        } else {
            $plugin = new Plugin();
            if ($plugin->isActivated("resources")
               && $itemclass->getType() != 'Location'
                  && $itemclass->getType() != 'Netpoint') {

                $img = "<img src='" . GLPI_ROOT . "/plugins/resources/pics/nobody.png' width='90' height='90'>";
                $res = new PluginResourcesResource();
                if ($res->getFromDB($itemclass->fields["id"])) {
                    if (isset($res->fields["picture"])) {
                        $path = GLPI_PLUGIN_DOC_DIR . "/resources/" . $res->fields["picture"];
                        if (file_exists($path)) {
                            $sizes = getimagesize($path);
                            $largeur = $sizes[0];
                            $hauteur = $sizes[1];
                            $img = "<object width='" . $largeur . "' height='" . $hauteur . "' data='" . $CFG_GLPI['root_doc'] . "/plugins/resources/front/picture.send.php?file=" . $res->fields["picture"] . "'>
                <param name='src' value='" . $CFG_GLPI['root_doc'] .
                                "/plugins/resources/front/picture.send.php?file=" . $res->fields["picture"] . "'>
               </object> ";
                        }
                    }
                }
                $width = $width - 75;
            } //si c'est un lieu
            else {
                $img = '';
            }
        }
        echo $img;
        echo "<div class='details' style='width:480px;'>";
        if (!empty($infos)) {
            foreach ($infos as $info) {
                if ($itemclass->getType() == $info['itemtype']) {
                    PluginPositionsInfo::showFields($info, $itemclass);
                }
            }
        }
        if ($_SESSION['glpiactiveprofile']['interface'] == 'central' || $itemclass->getType() == 'Location') {
            PluginPositionsInfo::getDirectLink($itemclass);
        }

        //end details
        echo "</div>";

        echo "<div class='call' style='width:480px;'>";
        PluginPositionsInfo::getCallValue($itemclass);
        //end call
        echo "</div>";

    }

    /**
     * Send a file (not a document) to the navigator
     * See Document->send();
     *
     * @param $file string: storage filename
     * @param $filename string: file title
     *
     * @return nothing
     **/
    static function sendFile($file, $filename, $type)
    {

        // Test securite : document in DOC_DIR
        $tmpfile = str_replace(GLPI_PLUGIN_DOC_DIR . "/positions/" . $type . "/", "", $file);

        if (strstr($tmpfile, "../") || strstr($tmpfile, "..\\")) {
            Event::log($file, "sendFile", 1, "security",
                $_SESSION["glpiname"] . " try to get a non standard file.");
            die("Security attack !!!");
        }

        if (!file_exists($file)) {
            die("Error file $file does not exist");
        }

        $splitter = explode("/", $file);
        $mime = "application/octet-stream";

        if (preg_match('/\.(....?)$/', $file, $regs)) {
            switch ($regs[1]) {
                case "png" :
                    $mime = "image/png";
                    break;

                case "jpeg" :
                    $mime = "image/jpeg";
                    break;

                case "jpg" :
                    $mime = "image/jpg";
                    break;

                case "gif" :
                    $mime = "image/gif";
                    break;
            }
        }

        // Now send the file with header() magic
        header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
        header('Pragma: private'); /// IE BUG + SSL
        header('Cache-control: private, must-revalidate'); /// IE BUG + SSL
        header("Content-disposition: filename=\"$filename\"");
        header("Content-type: " . $mime);

        readfile($file) or die ("Error opening file $file");
    }

    //from items

    static function showPluginFromItems($itemtype, $ID, $withtemplate = '')
    {
        global $DB, $CFG_GLPI, $LANG;

        $item = new $itemtype();
        $canread = $item->can($ID, 'r');
        $canedit = $item->can($ID, 'w');

        $self = new self();

        $query = "SELECT `glpi_plugin_positions_positions`.* "
            . "FROM `glpi_plugin_positions_positions` "
            . " LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` =
                        `glpi_plugin_positions_positions`.`entities_id`) "
            . " WHERE `glpi_plugin_positions_positions`.`items_id` = '" . $ID . "'
                  AND `glpi_plugin_positions_positions`.`itemtype` = '" . $itemtype . "' "
            . getEntitiesRestrictRequest(" AND ", "glpi_plugin_positions_positions", '', '',
                $self->maybeRecursive());
        $query .= " ORDER BY `glpi_plugin_positions_positions`.`name` ";

        $result = $DB->query($query);
        $number = $DB->numrows($result);

        if (Session::isMultiEntitiesMode()) {
            $colsup = 1;
        } else {
            $colsup = 0;
        }
        if ($number) {
            echo "<form method='post' action=\"" . $CFG_GLPI["root_doc"] .
                "/plugins/positions/front/position.form.php\" name='pointform' id='pointform'>";
            echo "<div align='center'><table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='" . (4 + $colsup) . "'>" . $LANG['plugin_positions'][8] . ":</th></tr>";
            echo "<tr><th>" . $LANG['plugin_positions'][7] . "</th>";
            if (Session::isMultiEntitiesMode())
                echo "<th>" . $LANG['entity'][0] . "</th>";
            echo "<th>" . $LANG['plugin_positions'][2] . "</th>";
            echo "<th>" . $LANG['plugin_positions'][3] . "</th>";
            if (plugin_positions_haveRight('positions', 'w'))
                echo "<th>&nbsp;</th>";
            echo "</tr>";
            $used = array();

            while ($data = $DB->fetch_array($result)) {
                $positionsID = $data["id"];

                $used[] = $positionsID;
                echo "<tr class='tab_bg_1" . ($data["is_deleted"] == '1' ? "_2" : "") . "'>";

                if ($withtemplate != 3 && $canread
                    && (in_array($data['entities_id'],
                        $_SESSION['glpiactiveentities']) || $data["is_recursive"])
                ) {
                    echo "<td class='center'><a href='" . $CFG_GLPI["root_doc"] .
                        "/plugins/positions/front/position.form.php?id=" . $data["id"] . "'>" . $data["name"];
                    if (($_SESSION["glpiis_ids_visible"]) || (empty($data["name"]))) echo " (" . $data["id"] . ")";
                    echo "</a>";
                    self::showGeolocLink($itemtype, $ID, $data["id"]);
                    echo "</td>";
                } else {
                    echo "<td class='center'>" . $data["name"];
                    if ($_SESSION["glpiis_ids_visible"]) echo " (" . $data["id"] . ")";
                    echo "</td>";
                }
                if (Session::isMultiEntitiesMode())
                    echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities",
                        $data['entities_id']) . "</td>";
                echo "<td class='center'>" . $data["x_coordinates"] . "</td>";
                echo "<td class='center'>" . $data["y_coordinates"] . "</td>";

                if (plugin_positions_haveRight('positions', 'w')) {
                    if ($withtemplate < 2) {
                        if ($data["is_deleted"] != 1) {
                           echo "<td class='center tab_bg_2'>";
                           Html::showSimpleForm($CFG_GLPI['root_doc'].'/plugins/positions/front/position.form.php',
                                    'delete_item',
                                    $LANG['buttons'][6],
                                    array('id' => $positionsID));
                           echo "</td>";
                        } else {
                            echo "<td class='tab_bg_2 center'></td>";
                        }
                    }
                }
                echo "</tr>";
            }

            echo "</table></div>";
            Html::closeForm();
        } else {
            self::showAddFromItem($itemtype, $ID);
        }
    }
   
   static function showGeolocLink($itemtype,$id, $positions_id=0) {
      global $CFG_GLPI;
      
      if ($itemtype != 'User'
            && $itemtype != 'PluginResourcesResource') {
         $item = new $itemtype();
         $item->getFromDB($id);
         $documents_id = self::getDocument($item->fields['locations_id']);
         $locations_id = $item->fields['locations_id'];
         
      } else {
         
         //si plugin ressource active
         $plugin = new Plugin();
         if ($plugin->isActivated("resources")) {
            //recherche de la ressource lie a ce user
            
            if ($itemtype != 'PluginResourcesResource') {
               $condition = "`items_id`= '".$id."' AND `itemtype` = 'User'";
            
               $infos = getAllDatasFromTable('glpi_plugin_resources_resources_items',$condition);
               if (!empty($infos)) {
                  foreach ($infos as $info) {
                     $ressource     = new PluginResourcesResource();
                     $ressource->getFromDB($info['plugin_resources_resources_id']);

                     $restrict = "`items_id` = '".$ressource->fields['id']."'
                                 AND `is_deleted` = '0' 
                                 AND `entities_id` = '".$ressource->fields['entities_id']."'
                                 AND `itemtype` = '".$ressource->getType()."'" ;
                     $datas = getAllDatasFromTable('glpi_plugin_positions_positions',$restrict);
                     if (!empty($datas)) {
                        foreach ($datas as $data) {
                           if (isset($data['id'])) {
                              if (isset($ressource->fields['locations_id']) 
                                          && ($ressource->fields['locations_id']>0)) {
                                 $documents_id = self::getDocument($ressource->fields['locations_id']);
                                 $positions_id = $data['id'];
                                 $locations_id = $ressource->fields['locations_id'];
                              }
                           }
                        }
                     }
                  }
               }
            } else {

               $ressource     = new PluginResourcesResource();
               if($ressource->getFromDB($id)) {
                  $restrict = "`items_id` = '".$ressource->fields['id']."'
                              AND `is_deleted` = '0' 
                              AND `entities_id` = '".$ressource->fields['entities_id']."'
                              AND `itemtype` = '".$ressource->getType()."'" ;
                  $datas = getAllDatasFromTable('glpi_plugin_positions_positions',$restrict);
                  if (!empty($datas)) {
                     foreach ($datas as $data) {
                        if (isset($data['id'])) {
                           if (isset($ressource->fields['locations_id']) 
                                       && ($ressource->fields['locations_id']>0)) {
                              $documents_id = self::getDocument($ressource->fields['locations_id']);
                              $positions_id = $data['id'];
                              $locations_id = $ressource->fields['locations_id'];
                           }
                        }
                     }
                  }
               }
            }
         }
      }
      
      $Doc  = new Document();
      if(isset($documents_id) && $Doc->getFromDB($documents_id)) {

      //emgi => récupération de l'id
      echo "<a href='#' onClick=\"var w = window.open('".$CFG_GLPI['root_doc'].
            "/plugins/positions/front/geoloc.php?positions_id=".
         $positions_id."&amp;download=1&amp;locations_id=".$locations_id.
            "' ,'glpipopup', 
            'height=650, width=1400, top=100, left=100, scrollbars=yes' );
            w.focus();\" ><img src='".$CFG_GLPI["root_doc"].
            "/plugins/positions/pics/sm_globe.png'></a>&nbsp;";
      }
   }
   
    static function showGeolocLocation($itemtype, $id, $positions_id = 0)
    {
        global $CFG_GLPI;

        $documents_id = self::getDocument($id);
        $locations_id = $id;


        $Doc = new Document();
        if (isset($documents_id) && $Doc->getFromDB($documents_id)) {
            
            $target = $CFG_GLPI["root_doc"]."/plugins/positions/front/geoloc.php?positions_id=" .
                $positions_id . "&amp;download=1&amp;locations_id=" . $locations_id;
            echo "<script type='text/javascript'>
                Position.openWindow('$target');
            </script>";
        }
    }


    static function showAddFromItem($itemtype, $items_id)
    {
        global $CFG_GLPI, $LANG;

        if (!plugin_positions_haveRight('positions', 'r')) return false;

        $itemclass = new $itemtype();
        $itemclass->getFromDB($items_id);

        echo "<div align='center'>";
        echo "<form method='post' action=\"" . $CFG_GLPI["root_doc"] .
            "/plugins/positions/front/position.form.php\" name='pointform' id='pointform'>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='4'>" . $LANG['plugin_positions'][1] . " :</th></tr>";

        echo "<tr class='tab_bg_1'><td>";

        echo $LANG['plugin_positions'][7] . "</td>";
        echo "<td>";
        $value = $itemclass->fields["name"];
        if (isset($itemclass->fields["contact_num"])
            && !empty($itemclass->fields["contact_num"])
        ) {
            $value = $itemclass->fields["contact_num"];
        }
        $entity = $itemclass->fields["entities_id"];
        Html::autocompletionTextField($itemclass, "name", array('value' => $value,
            'entity' => $entity));
        echo "</td>";
        echo "<td>";
        $location = $itemclass->fields["locations_id"];
        if (isset($location)
            && !empty($location)
        ) {
            echo Dropdown::getDropdownName("glpi_locations", $location);
        } else {
            echo "<div class='red'>" . $LANG['plugin_positions']['setup'][8] . "</div>";
        }
        echo "</td>";

        if (empty($ID)) {
            if (plugin_positions_haveRight('positions', 'w')) {
                echo "<td>";
                echo "<input type='hidden' name='items_id' value='$items_id'>";
                echo "<input type='hidden' name='itemtype' value='$itemtype'>";
                echo "<input type='hidden' name='entities_id' value='" . $entity . "'>";
                echo "<input type='submit' name='additem' value=\"" . $LANG['buttons'][8] .
                    "\" class='submit'>";
                echo "</td>";

            }
        }
        echo "</tr>";
        echo "</table>";
        Html::closeForm();
        echo "</div>";

    }

    static function showConfigForm()
    {
        global $CFG_GLPI, $LANG;

        echo "<div class='center'><table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th>";
        echo $LANG['common'][12];
        echo "</th>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='center'>";
        echo "<a href='./imageitem.form.php'>" .
            $LANG['plugin_positions']['setup'][10] . "</a>";
        echo "</td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='center'>";
        echo "<a href='./info.php'>" .
            $LANG['plugin_positions']['setup'][11] . "</a>";
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
    }

}

?>