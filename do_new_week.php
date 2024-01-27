<?php
require('system.php');
enforce_permission('WEEKBEHEER');

header('Content-type: text/plain');

print_r($_POST);
echo "hier 0";
$exists = db_single_field("SELECT week_id FROM $voxdb.weken WHERE time_year = ? AND time_week = ?", $_POST['year'], $_POST['week']);
echo "hier 1";
if ($exists) {
	// de regel hieronder was eerst zo, maar jaren worden niet gepost en dus gaat het mis:
	// $GLOBALS['session_state']['error_msg'] = "Week {$_POST['year']}wk{$_POST['week']} bestaat al.";
	$GLOBALS['session_state']['error_msg'] = "Week 24wk{$_POST['week']} bestaat al.";
	header('Location: weken.php?session_guid='.$session_guid);
	exit;
}
echo "hier 2";
// de regel hieronder was eerst zo, maar jaren worden niet gepost en dus gaat het mis:
// db_exec("INSERT INTO $voxdb.weken ( time_year, time_week ) VALUES ( ?, ? )", $_POST['year'], $_POST['week']);
db_exec("INSERT INTO $voxdb.weken ( time_year, time_week ) VALUES ( 24, ? )", $_POST['week']);
$week_id = mysqli_insert_id($GLOBALS['db']);
echo "hier 3";
// de regel hieronder was eerst zo, maar jaren worden niet gepost en dus gaat het mis:
// db_exec("INSERT INTO $voxdb.time ( time_year, time_week, time_day, time_hour ) SELECT 0, ?, time_day, time_hour FROM $voxdb.weken JOIN $voxdb.time USING (time_year, time_week) WHERE week_id = ?", $_POST['week'], $_POST['basis_week_id']);
db_exec("INSERT INTO $voxdb.time ( time_year, time_week, time_day, time_hour ) SELECT 24, ?, time_day, time_hour FROM $voxdb.weken JOIN $voxdb.time USING (time_year, time_week) WHERE week_id = ?", $_POST['week'], $_POST['basis_week_id']);

echo "hier 4";
echo("week_id=$week_id\n");
echo "hier 5";
header('Location: weken.php?session_guid='.$session_guid);

?>
