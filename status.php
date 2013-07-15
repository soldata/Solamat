<?php
/*
 * @version $Id: status.php 20130 2013-02-04 16:55:15Z moyo $
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

define('DO_NOT_CHECK_HTTP_REFERER', 1);
define('GLPI_ROOT', '.');
include (GLPI_ROOT . "/inc/includes.php");

// Force in normal mode
$_SESSION['glpi_use_mode'] = Session::NORMAL_MODE;

// Need to be used using :
// check_http -H servername -u /glpi/status.php -s GLPI_OK


// Plain text content
header('Content-type: text/plain');

$ok_master = true;
$ok_slave  = true;
$ok        = true;

// Check slave server connection
if (DBConnection::isDBSlaveActive()) {
   $DBslave = DBConnection::getDBSlaveConf();
   if (is_array($DBslave->dbhost)) {
      $hosts = $DBslave->dbhost;
   } else {
      $hosts = array($DBslave->dbhost);
   }

   foreach ($hosts as $num => $name) {
      $diff = DBConnection::getReplicateDelay($num);
      if (abs($diff)> 1000000000) {
         echo "GLPI_DBSLAVE_${num}_OFFLINE\n";
         $ok_slave = false;
      } else if (abs($diff)> HOUR_TIMESTAMP) {
         echo "GLPI_DBSLAVE_${num}_PROBLEM\n";
         $ok_slave = false;
      } else {
         echo "GLPI_DBSLAVE_${num}_OK\n";
      }
   }
} else {
   echo "No slave DB\n";
}

// Check main server connection
if (DBConnection::establishDBConnection(false,true,false)) {
   echo "GLPI_DB_OK\n";
} else {
   echo "GLPI_DB_PROBLEM\n";
   $ok_master = false;
}

// Slave and master ok;
$ok = $ok_slave && $ok_master;

// Check session dir (usefull when NFS mounted))
if (is_dir(GLPI_SESSION_DIR) && is_writable(GLPI_SESSION_DIR)) {
   echo "GLPI_SESSION_DIR_OK\n";
} else {
   echo "GLPI_SESSION_DIR_PROBLEM\n";
   $ok = false;
}

// Reestablished DB connection
if (( $ok_master || $ok_slave )
   && $CFG_GLPI["use_ocs_mode"]
      && DBConnection::establishDBConnection(false,false,false)) {

   // Check OCS connections
   $query = "SELECT `id`, `name`
             FROM `glpi_ocsservers`
             WHERE `is_active` = '1'";

   if ($result=$DB->query($query)) {
      if ($DB->numrows($result)) {
         echo "Check OCS servers:";

         while ($data = $DB->fetch_assoc($result)) {
            echo " ".$data['name'];

            if (OcsServer::checkOCSconnection($data['id'])) {
               echo "_OK";
            } else {
               echo "_PROBLEM";
               $ok = false;
            }

            echo "\n";
         }

      } else {
         echo "No OCS server\n";
      }
   }

   // Check Auth connections
   $auth = new Auth();
   $auth->getAuthMethods();
   $ldap_methods = $auth->authtypes["ldap"];

   if (count($ldap_methods)) {
      echo "Check LDAP servers:";

      foreach ($ldap_methods as $method) {
         if ($method['is_active']) {
            echo " ".$method['name'];
            if (AuthLDAP::tryToConnectToServer($method, $method["rootdn"],
                                               Toolbox::decrypt($method["rootdn_passwd"],
                                               GLPIKEY))) {
               echo "_OK";
            } else {
               echo "_PROBLEM";
               $ok = false;
            }
            echo "\n";
         }
      }

   } else {
      echo "No LDAP server\n";
   }

   // TODO Check mail server : cannot open a mail connexion / only ping server ?

   // TODO check CAS url / check url using socket ?

}

echo "\n";

if ($ok) {
   echo "GLPI_OK\n";
} else {
   echo "GLPI_PROBLEM\n";
}

?>
