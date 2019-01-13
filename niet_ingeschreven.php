<?
require('system.php');
require('html.php');

/*
$weken = db_query(<<<EOQ
SELECT DISTINCT CONCAT(time_year, 'wk', LPAD(time_week, 2, '0')) week FROM $voxdb.time
EOQ
);

$options = array();
while ($assoc = mysqli_fetch_assoc($weken)) {
	print_r($assoc);
}
 */

enforce_staff();


if (isset($_GET['week_id'])) {
	$week_id = db_single_field("SELECT week_id FROM $voxdb.weken WHERE week_id = ? AND rooster_zichtbaar= 1", $_GET['week_id']);
	if (!$week_id) fatal("week niet zichtbaar in rooster");
} else {
	$week_id = db_single_field("SELECT week_id FROM $voxdb.weken WHERE rooster_zichtbaar= 1 ORDER BY time_year DESC, time_week DESC");
}

if (!$week_id) { ?>
Geen lesweken zichtbaar in rooster op dit moment.
<? 		 exit;
}	

$default_week = db_single_field("SELECT CONCAT(time_year, 'wk', LPAD(time_week, 2, '0')) FROM voxdb.weken WHERE week_id = $week_id");

$weken = db_single_field(<<<EOQ
SELECT CONCAT('<select onchange="this.form.submit()" name="week_id">', GROUP_CONCAT(CONCAT('<option', IF(week_id = $week_id, ' selected', ''), ' value="', week_id, '">', time_year, 'wk', LPAD(time_week, 2, '0'), '</option>')), '</select>') FROM $voxdb.weken WHERE rooster_zichtbaar
EOQ
);

$rooster = db_query(<<<EOQ
SELECT time_hour uur, time_day-1 dag, CONCAT(ppl_forename, ' ', ppl_prefix, ' ', ppl_surname, ' (', ppl_login, ')') 'doc/vak'
FROM $voxdb.weken
JOIN $voxdb.time USING (time_year, time_week)
JOIN $voxdb.ppl
LEFT JOIN (
	SELECT claim.ppl_id, time_id, claim_id
	FROM $voxdb.claim
	JOIN $voxdb.avail USING (avail_id)
) AS bla USING (time_id, ppl_id)
WHERE week_id = $week_id AND ppl_type = 'leerling' AND claim_id IS NULL
-- GROUP BY time_id
ORDER BY uur, dag, ppl_surname, ppl_forename, ppl_prefix
EOQ
);

$dagen = db_single_field(<<<EOQ
SELECT GROUP_CONCAT(DISTINCT time_day-1 ORDER BY time_day)
FROM $voxdb.weken
JOIN $voxdb.time USING (time_year, time_week)
WHERE week_id = $week_id
EOQ
);

$uren = db_single_field(<<<EOQ
SELECT GROUP_CONCAT(DISTINCT time_hour ORDER BY time_hour)
FROM $voxdb.weken
JOIN $voxdb.time USING (time_year, time_week)
WHERE week_id = $week_id
EOQ
);

$dagnamen = array ('ma', 'di', 'wo', 'do', 'vr', 'za', 'zo');

if ($dagen == '' || $uren == '') fatal("geen uren/lesdagen in rooster");
$dagen = explode(',', $dagen);
$uren = explode(',', $uren);

html_start();

?>
<p>Niet ingeschreven leerlingen in <?=$weken?>.

<? $row = mysqli_fetch_assoc($rooster); ?>
<div class="tablemarkup">
<table>
<tr>
<th></th>
<? foreach ($dagen as $dag) { ?><th><?=$dagnamen[$dag]?></th>
<? } ?>
<? foreach ($uren as $uur) { ?><tr>
<td style="vertical-align: top;"><?=$uur?></td>
<? foreach ($dagen as $dag) { ?><td style="vertical-align: top;">
<? while ($row && $row['uur'] == $uur && $row['dag'] == $dag) { ?>
<?=$row['doc/vak']?><br>
<? $row = mysqli_fetch_assoc($rooster);
} ?>
</td>
<? } ?>
</tr>
<? } ?>
</tr>
</table>
</div>
<?

html_end();

?>