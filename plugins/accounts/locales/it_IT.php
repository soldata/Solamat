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

$LANG['plugin_accounts'][1] = "Account";
$LANG['plugin_accounts'][2] = "Login";
$LANG['plugin_accounts'][3] = "Password";
$LANG['plugin_accounts'][5] = "The old or the new encryption key can not be empty";
$LANG['plugin_accounts'][6] = "Oggetti associati";
$LANG['plugin_accounts'][7] = "Nome";
$LANG['plugin_accounts'][8] = "account Associati";
$LANG['plugin_accounts'][9] = "Stato";
$LANG['plugin_accounts'][10] = "Commenti";
$LANG['plugin_accounts'][11] = "Linked accounts list";
$LANG['plugin_accounts'][12] = "Tipo";
$LANG['plugin_accounts'][13] = "Data di scadenza";
$LANG['plugin_accounts'][14] = "Nessuna scadenza";
$LANG['plugin_accounts'][15] = "Empty for infinite";
$LANG['plugin_accounts'][16] = "Altro";
$LANG['plugin_accounts'][17] = "Data di creazione";
$LANG['plugin_accounts'][18] = "Utente interessato";
$LANG['plugin_accounts'][19] = "Gruppo interessato";
$LANG['plugin_accounts'][20] = "Mostra";
$LANG['plugin_accounts'][21] = "Hash";
$LANG['plugin_accounts'][22] = "Chiave di criptaggio errata!";
$LANG['plugin_accounts'][23] = "Chiave di criptaggio";
$LANG['plugin_accounts'][24] = "Non hai riempito i campi Password e/o Chiave di criptaggio";
$LANG['plugin_accounts'][25] = "La password non sarà modificata";
$LANG['plugin_accounts'][26] = "WARNING : a encrypted key already exist for this entity";
$LANG['plugin_accounts'][27] = "Genera hash con questa chiave di criptaggio";
$LANG['plugin_accounts'][28] = "Il valore di hash, da inserire nel campo sottostante per criptare i dati, è: ";
$LANG['plugin_accounts'][29] = "Per favore inserisci la chiave di criptaggio";
$LANG['plugin_accounts'][30] = "Attenzione: se cambi l'hash in uso, i vecchi account continueranno ad utilizzare la vecchia chiave di criptaggio";
$LANG['plugin_accounts'][31] = "Genera";
$LANG['plugin_accounts'][32] = "Crea un nuovo account";
$LANG['plugin_accounts'][33] = "Attenzione: memorizzare la chiave di criptaggio è un pericolo per la sicurezza";
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

$LANG['plugin_accounts']['upgrade'][0] = "Aggiornamento";
$LANG['plugin_accounts']['upgrade'][1] = "1. Definisci la chiave di criptaggio e crea l'hash";
$LANG['plugin_accounts']['upgrade'][2] = "2. Migra gli account";
$LANG['plugin_accounts']['upgrade'][3] = "Aggiorna";
$LANG['plugin_accounts']['upgrade'][4] = "Nome account";
$LANG['plugin_accounts']['upgrade'][5] = "Password in chiaro";
$LANG['plugin_accounts']['upgrade'][6] = "3. Se tutti gli account sono stati migrati, l'aggiornamento è terminato";

$LANG['plugin_accounts']['profile'][0] = "Gestione diritti";
$LANG['plugin_accounts']['profile'][7] = "Mostra tutti gli account";
$LANG['plugin_accounts']['profile'][8] = "Mostra gli account dei miei gruppi";

$LANG['plugin_accounts']['mailing'][0] = "Account scaduti";
$LANG['plugin_accounts']['mailing'][1] = "Accounts expired or accounts which expires";
$LANG['plugin_accounts']['mailing'][2] = "Account in scadenza";
$LANG['plugin_accounts']['mailing'][3] = "Comptes expirés depuis plus de";
$LANG['plugin_accounts']['mailing'][4] = "New account";
$LANG['plugin_accounts']['mailing'][5] = "L'account è stato creato";
$LANG['plugin_accounts']['mailing'][6] = "Link diretto all'account creato";
$LANG['plugin_accounts']['mailing'][7] = "Comptes qui vont expirer dans moins de";

$LANG['plugin_accounts']['setup'][1] = "Plugin Setup";
$LANG['plugin_accounts']['setup'][2] = "Tipo di account";
$LANG['plugin_accounts']['setup'][3] = "Type view";
$LANG['plugin_accounts']['setup'][4] = "Select the wanted account type";
$LANG['plugin_accounts']['setup'][5] = "Please do not use special characters like / \ ' \" & in encryption keys, or you cannot change it after.";
$LANG['plugin_accounts']['setup'][9] = "Associa";
$LANG['plugin_accounts']['setup'][10] = "Dissocia";
$LANG['plugin_accounts']['setup'][11] = "Periodo di verifica della scadenza degli account";
$LANG['plugin_accounts']['setup'][12] = "giorni";
$LANG['plugin_accounts']['setup'][18] = "Associa all'account";
$LANG['plugin_accounts']['setup'][19] = "Stato di non utilizzo <br> per l'invio email di scadenza";
$LANG['plugin_accounts']['setup'][20] = "Aggiungi stato di non utilizzo <br> per l'invio email di scadenza";

?>