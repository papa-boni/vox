<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
require('system.php');
require('html.php');

enforce_logged_in();
enforce_permission('WEEKBEHEER');

$weken = db_query(<<<EOQ
SELECT CONCAT(time_year, 'wk', LPAD(time_week, 2, '0')) week,
	IFNULL(GROUP_CONCAT(DISTINCT CONCAT(time_day, time_hour, " ") ORDER BY time_day, time_hour), 'geen lesuren') lesuren,
	CONCAT('<label><input type="checkbox" name="doc[]" value="', week_id, '"', IF(status_doc, ' checked', ''), '><span></span></label>') doc,
	CONCAT('<label><input type="checkbox" name="lln[]" value="', week_id, '"', IF(status_lln, ' checked', ''), '><span></span></label>') lln,
	CONCAT('<label><input type="checkbox" name="rst[]" value="', week_id, '"', IF(rooster_zichtbaar, ' checked', ''), '><span></span></label>') 'rooster zichtbaar',
	COUNT(DISTINCT time_id, avail.ppl_id) "doc-uur",
	COUNT(DISTINCT time_id, claim.ppl_id) "lln-uur",
	CONCAT('<a href="week_ops.php?session_guid=$session_guid&amp;week_id=', week_id, '">opties</a>') opties
FROM $voxdb.weken
LEFT JOIN $voxdb.time USING (time_year, time_week)
LEFT JOIN $voxdb.avail USING (time_id)
LEFT JOIN $voxdb.claim USING (avail_id)
GROUP BY week_id
ORDER BY time_year DESC, time_week DESC
EOQ
);

// $year_min = db_single_field("SELECT config_value FROM config WHERE config_key = 'YEAR_MIN'");
// $year_max = db_single_field("SELECT config_value FROM config WHERE config_key = 'YEAR_MAX'");

// $select = '<select name="year">';
// for ($i = $year_min; $i <= $year_max; $i++) {
// 	$select .= '<option>'.$i.'</option>';
// }
// $select .= '</select>';

$select_wk = '<div class="row"><select class="browser-default btn-small col s1 m2 l3" name="week">';
for ($i = 0; $i <= 54; $i++) {
	$select_wk .= '<option>'.$i.'</option>';
}
$select_wk .= '</select></div>';

$options = db_all_assoc_rekey(<<<EOQ
SELECT week_id, CONCAT(time_year, 'wk', LPAD(time_week, 2, '0')) week FROM $voxdb.weken ORDER BY time_year DESC, time_week DESC
EOQ
);

html_start(); ?>
<h4>Nieuwe week maken</h4>
<p>Met lesuren gebaseerd op bestaande week</p>
<form action="do_new_week.php?session_guid=<?php echo $session_guid?>" accept-charset="UTF-8" method="POST">
Maak nieuwe week <?php echo $select?>wk<?php echo $select_wk?> en baseer de lesuren op <br><div class="row"><select class="browser-default btn-small col s1 m2 l3" name="basis_week_id"><?php 
foreach ($options as $week_id => $week) { ?>
	<option value="<?php echo $week_id?>"><?php echo $week?></option>
<?php } ?></select><br><br><input class="btn" type="submit" value="Maak"></form>
<p>Als je andere lesuren wilt, dan moet je dat direct aanpassen in de database in de tabel time in de rijen van de betreffende week.

<h5>Bestaande weken</h5>
<form action="do_weken.php?session_guid=<?php echo $session_guid?>" accept-charset="UTF-8" method="POST">
<?php db_dump_result_resp($weken, false); ?>
<input type="submit" class="btn" value="Vinkjes opslaan">
</form>

<p>
De link in kolom opties leidt naar een scherm om
<ul>
<li>- docentbeschikbaarheid te kopi&euml;ren uit een andere week</li>
<li>- om getagde leerlingen automatisch in te schrijven</li>
<li>- alle leerlinginschrijvingen of alle docentbeschikbaarheden te verwijderen</li>
</ul>

<?php  
html_end();
?>
