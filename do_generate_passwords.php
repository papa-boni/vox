<?php
require('system.php');
enforce_permission('ACCOUNT');

header('Content-type: text/plain');

// paswoordgenerator
function generatePassword() {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_";
    return substr(str_shuffle($chars), 0, 8);
}

// Aantal gegenereerde wachtwoorden
$numPasswords = count($_POST['pplpwlog']);

// Array om gegenereerde wachtwoorden op te slaan
$output = [];

// Genereren van wachtwoorden
for ($i = 0; $i < $numPasswords; $i++) {
    $output[$i] = generatePassword();
}

// Database-update en weergave van gegenereerde wachtwoorden
foreach ($_POST['pplpwlog'] as $idx => $info) {
    $infos = explode('-', $info);
    $username = db_single_field("SELECT ppl_login FROM $voxdb.ppl WHERE ppl_id = ?", $infos[0]);
    $name = db_single_field("SELECT CONCAT(ppl_forename, IF(ppl_prefix = '', '', CONCAT(' ', ppl_prefix)), ' ', ppl_surname) FROM $voxdb.ppl WHERE ppl_id = ?", $infos[0]);
    upsert_password($username, $output[$idx], ($infos[1] == 'NULL') ? NULL : $infos[1]);
    echo('Beste '.$name.', met inlognaam '.$username.', je paswoord is: '.$output[$idx]."\n\n") . ".<br><br>";
}
?>

