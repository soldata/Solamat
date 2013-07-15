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

$LANG['plugin_accounts']['title'][1] = "Comptes";

$LANG['plugin_accounts'][1] = "Compte";
$LANG['plugin_accounts'][2] = "Login";
$LANG['plugin_accounts'][3] = "Mot de passe";
$LANG['plugin_accounts'][5] = "L'ancienne ou la nouvelle clé de cryptage ne peuvent pas être vides";
$LANG['plugin_accounts'][6] = "Elément(s) associé(s)";
$LANG['plugin_accounts'][7] = "Nom";
$LANG['plugin_accounts'][8] = "Compte(s) associé(s)";
$LANG['plugin_accounts'][9] = "Statut";
$LANG['plugin_accounts'][10] = "Commentaires";
$LANG['plugin_accounts'][11] = "Liste des comptes liés";
$LANG['plugin_accounts'][12] = "Type";
$LANG['plugin_accounts'][13] = "Date d'expiration";
$LANG['plugin_accounts'][14] = "N'expire pas";
$LANG['plugin_accounts'][15] = "Vide pour infini";
$LANG['plugin_accounts'][16] = "Autres";
$LANG['plugin_accounts'][17] = "Date de création";
$LANG['plugin_accounts'][18] = "Utilisateur concerné";
$LANG['plugin_accounts'][19] = "Groupe concerné";
$LANG['plugin_accounts'][20] = "Décrypter";
$LANG['plugin_accounts'][21] = "Hash";
$LANG['plugin_accounts'][22] = "Mauvaise clé de cryptage";
$LANG['plugin_accounts'][23] = "Clé de cryptage";
$LANG['plugin_accounts'][24] = "Veuillez renseigner le mot de passe et la clé de cryptage";
$LANG['plugin_accounts'][25] = "Le mot de passe ne sera pas modifié";
$LANG['plugin_accounts'][26] = "ATTENTION : Une clé de cryptage existe déjà pour cette entité.";
$LANG['plugin_accounts'][27] = "Générer le hash à partir de cette clé de cryptage";
$LANG['plugin_accounts'][28] = "Le hash à insérer dans le champ suivant pour créer le cryptage est : ";
$LANG['plugin_accounts'][29] = "Veuillez renseigner la clé de cryptage";
$LANG['plugin_accounts'][30] = "ATTENTION : si vous modifiez la hash utilisé, les anciens comptes utiliseront encore l'ancienne clé de cryptage";
$LANG['plugin_accounts'][31] = "Générer";
$LANG['plugin_accounts'][32] = "Créer un nouveau compte";
$LANG['plugin_accounts'][33] = "Attention : enregistrer la clé de cryptage est une faille de sécurité";
$LANG['plugin_accounts'][34] = "Décrypté";
$LANG['plugin_accounts'][35] = "Il n'existe pas de clé de cryptage pour cette entité";
$LANG['plugin_accounts'][36] = "Modification de la clé de cryptage";
$LANG['plugin_accounts'][37] = "Nouvelle clé de cryptage";
$LANG['plugin_accounts'][38] = "Ancienne clé de cryptage";
$LANG['plugin_accounts'][39] = "Voulez vous modifier la clé : ";
$LANG['plugin_accounts'][40] = " par la clé : ";
$LANG['plugin_accounts'][41] = "ATTENTION : si vous vous trompez dans la saisie de l'ancienne ou la nouvelle clé, vous ne pourrais plus décrypter vos mots de passe. Il est donc FORTEMENT conseillé de faire une sauvegarde de la base avant.";
$LANG['plugin_accounts'][42] = "Clés de cryptage";
$LANG['plugin_accounts'][43] = "Clé de cryptage modifiée";
$LANG['plugin_accounts'][44] = "Enregistrer la clé de cryptage";

$LANG['plugin_accounts']['upgrade'][0] = "Procédure d'upgrade";
$LANG['plugin_accounts']['upgrade'][1] = "1. Définir la clé de cryptage et créer le hash";
$LANG['plugin_accounts']['upgrade'][2] = "2. Migrer les comptes";
$LANG['plugin_accounts']['upgrade'][3] = "Mettre à jour";
$LANG['plugin_accounts']['upgrade'][4] = "Nom des comptes";
$LANG['plugin_accounts']['upgrade'][5] = "Mot de passe décrypté";
$LANG['plugin_accounts']['upgrade'][6] = "3. Si tous les comptes sont migrés, l'upgrade est terminé";

$LANG['plugin_accounts']['profile'][0] = "Gestion des droits";
$LANG['plugin_accounts']['profile'][7] = "Voir les comptes de tous";
$LANG['plugin_accounts']['profile'][8] = "Voir les comptes de mes groupes";

$LANG['plugin_accounts']['mailing'][0] = "Comptes expirés";
$LANG['plugin_accounts']['mailing'][1] = "Comptes expirés ou comptes qui vont expirer";
$LANG['plugin_accounts']['mailing'][2] = "Comptes qui vont expirer";
$LANG['plugin_accounts']['mailing'][3] = "Comptes expirés depuis plus de";
$LANG['plugin_accounts']['mailing'][4] = "Nouveau compte";
$LANG['plugin_accounts']['mailing'][5] = "Un nouveau compte a été créé";
$LANG['plugin_accounts']['mailing'][6] = "Lien direct vers le compte créé";
$LANG['plugin_accounts']['mailing'][7] = "Comptes qui vont expirer dans moins de";

$LANG['plugin_accounts']['setup'][1] = "Configuration du plugin";
$LANG['plugin_accounts']['setup'][2] = "Type de compte";
$LANG['plugin_accounts']['setup'][3] = "Vue par type";
$LANG['plugin_accounts']['setup'][4] = "Sélectionnez le type de compte souhaité";
$LANG['plugin_accounts']['setup'][5] = "Merci de ne pas utiliser de caractères spéciaux comme / \ ' \" & dans les clés de cryptage, sinon vous risquez de ne pas pouvoir les modifier ultérieurement";
$LANG['plugin_accounts']['setup'][9] = "Associer";
$LANG['plugin_accounts']['setup'][10] = "Dissocier";
$LANG['plugin_accounts']['setup'][11] = "Délai de vérification d'expiration des comptes";
$LANG['plugin_accounts']['setup'][12] = "jours";
$LANG['plugin_accounts']['setup'][18] = "Associer au compte";
$LANG['plugin_accounts']['setup'][19] = "Statuts non utilisés  <br>dans l'envoi des emails d'expiration";
$LANG['plugin_accounts']['setup'][20] = "Ajouter un statut non utilisé <br> dans l'envoi des emails d'expiration";

?>