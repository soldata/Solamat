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

$LANG['plugin_accounts']['title'][1] = "Accounts";

$LANG['plugin_accounts'][1] = "Konten";
$LANG['plugin_accounts'][2] = "Login";
$LANG['plugin_accounts'][3] = "Password";
$LANG['plugin_accounts'][5] = "The old or the new encryption key can not be empty";
$LANG['plugin_accounts'][6] = "Objekt verbunden ";
$LANG['plugin_accounts'][7] = "Name";
$LANG['plugin_accounts'][8] = "Konten verbunden ";
$LANG['plugin_accounts'][9] = "State";
$LANG['plugin_accounts'][10] = "Note";
$LANG['plugin_accounts'][11] = "Linked accounts list";
$LANG['plugin_accounts'][12] = "Sitz";
$LANG['plugin_accounts'][13] = "Expiration date";
$LANG['plugin_accounts'][14] = "Don't expire";
$LANG['plugin_accounts'][15] = "Empty for infinite";
$LANG['plugin_accounts'][16] = "Others";
$LANG['plugin_accounts'][17] = "Create date";
$LANG['plugin_accounts'][18] = "Affected User";
$LANG['plugin_accounts'][19] = "Affected Group";
$LANG['plugin_accounts'][20] = "Uncrypt";
$LANG['plugin_accounts'][21] = "Hash";
$LANG['plugin_accounts'][22] = "Wrong encryption key";
$LANG['plugin_accounts'][23] = "Encryption key";
$LANG['plugin_accounts'][24] = "You have not filled the password and encryption key";
$LANG['plugin_accounts'][25] = "Password will not be modified";
$LANG['plugin_accounts'][26] = "WARNING : a encrypted key already exist for this entity";
$LANG['plugin_accounts'][27] = "Generate hash with this encryption key";
$LANG['plugin_accounts'][28] = "The hash to insert into the next field for create crypt is : ";
$LANG['plugin_accounts'][29] = "Please fill the encryption key";
$LANG['plugin_accounts'][30] = "Warning : if you change used hash, the old accounts will use the old encryption key";
$LANG['plugin_accounts'][31] = "Generate";
$LANG['plugin_accounts'][32] = "Create a new account";
$LANG['plugin_accounts'][33] = "Warning : saving the encrypted key is a security hole";
$LANG['plugin_accounts'][34] = "Uncrypted";
$LANG['plugin_accounts'][35] = "There is no encrypted key for this entity";
$LANG['plugin_accounts'][36] = "Modification of the encrypted key for all password";
$LANG['plugin_accounts'][37] = "New encrypted key";
$LANG['plugin_accounts'][38] = "Old encrypted key";
$LANG['plugin_accounts'][39] = "You wan to change the key : ";
$LANG['plugin_accounts'][40] = " by the key : ";
$LANG['plugin_accounts'][41] = "Warning : if you make a mistake in entering the old or the new key, you could no longer decrypt your passwords. It is STRONGLY recommended that you make a backup of the database before.";
$LANG['plugin_accounts'][42] = "Encrypted keys";
$LANG['plugin_accounts'][43] = "Encrypted key modified";
$LANG['plugin_accounts'][44] = "Save the encrypted key";

$LANG['plugin_accounts']['upgrade'][0] = "Upgrade";
$LANG['plugin_accounts']['upgrade'][1] = "1. Define the ecryption key and create hash";
$LANG['plugin_accounts']['upgrade'][2] = "2. Migrate accounts";
$LANG['plugin_accounts']['upgrade'][3] = "Update";
$LANG['plugin_accounts']['upgrade'][4] = "Account names";
$LANG['plugin_accounts']['upgrade'][5] = "Uncrypted password";
$LANG['plugin_accounts']['upgrade'][6] = "3. If all accounts are migrated, the upgrade is finished";

$LANG['plugin_accounts']['profile'][0] = "berichtigt management";
$LANG['plugin_accounts']['profile'][7] = "See all accounts";
$LANG['plugin_accounts']['profile'][8] = "See accounts of my groups";

$LANG['plugin_accounts']['mailing'][0] = "Accounts expired";
$LANG['plugin_accounts']['mailing'][1] = "Accounts expired or accounts which expires";
$LANG['plugin_accounts']['mailing'][2] = "Accounts which expires";
$LANG['plugin_accounts']['mailing'][3] = "Comptes expirés depuis plus de";
$LANG['plugin_accounts']['mailing'][4] = "New account";
$LANG['plugin_accounts']['mailing'][5] = "An account have been created";
$LANG['plugin_accounts']['mailing'][6] = "Direct link to created account";
$LANG['plugin_accounts']['mailing'][7] = "Comptes qui vont expirer dans moins de";

$LANG['plugin_accounts']['setup'][1] = "Plugin Setup";
$LANG['plugin_accounts']['setup'][2] = "Typ von Konten";
$LANG['plugin_accounts']['setup'][3] = "Type view";
$LANG['plugin_accounts']['setup'][4] = "Select the wanted account type";
$LANG['plugin_accounts']['setup'][5] = "Please do not use special characters like / \ ' \" & in encryption keys, or you cannot change it after.";
$LANG['plugin_accounts']['setup'][9] = "Verbinden";
$LANG['plugin_accounts']['setup'][10] = "Trennen";
$LANG['plugin_accounts']['setup'][11] = "Time of checking of of expiration of accounts";
$LANG['plugin_accounts']['setup'][12] = "days";
$LANG['plugin_accounts']['setup'][18] = "Associate to account";
$LANG['plugin_accounts']['setup'][19] = "Unused status <br> for expiration mailing";
$LANG['plugin_accounts']['setup'][20] = "Add a snused status <br> for expiration mailing";

?>