<?php
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

enforce_logged_in();

$moment = db_single_row(<<<EOQ
	SELECT time_day, time_hour, ppl_login, CONCAT(time_year, 'wk', LPAD(time_week, 2, '0')) week, GROUP_CONCAT(subj_abbrev) vakken
	FROM $voxdb.weken
	JOIN $voxdb.time USING (time_year, time_week)
	JOIN $voxdb.avail USING (time_id)
	JOIN $voxdb.ppl USING (ppl_id)
	JOIN $voxdb.subj USING (subj_id)
	WHERE week_id = ?
	AND time_id = ?
	AND rooster_zichtbaar = 1
	AND ppl_id = ?
EOQ
, $_GET['week_id'], $_GET['time_id'], $_GET['ppl_id']);

if (!$moment) { 
	html_start();?>
	<a href="rooster.php?session_guid=<?php echo $session_guid?>&amp;q=<?php echo urlencode($_GET['q'])?>&amp;week_id=<?php echo urlencode($_GET['week_id'])?>">&lt;--- terug naar rooster</a>
	Klassenlijst niet zichtbaar, omdat het rooster dicht staat.
	<?php
	html_end();
}

// De onderstaande code was eerst zoals dit:
// $lln = db_query(<<<EOQ
// 	SELECT CONCAT(ppl_forename, ' ', ppl_prefix, ' ', ppl_surname, ' (', ppl_login, IFNULL(CONCAT('/', GROUP_CONCAT(DISTINCT tag_name ORDER BY tag_type SEPARATOR '')), ''), ')') naam FROM $voxdb.ppl
// 	JOIN $voxdb.claim USING (ppl_id)
// 	LEFT JOIN $voxdb.ppl2tag USING (ppl_id)
// 	LEFT JOIN $voxdb.tag USING (tag_id)
// 	JOIN $voxdb.avail USING (avail_id)
// 	WHERE time_id = ? AND avail.ppl_id = ? AND ( tag_type = 'NIVEAU' OR tag_type = 'LEERJAAR' OR tag_type IS NULL )
// 	GROUP BY ppl_login
// 	ORDER BY ppl_surname, ppl_forename, ppl_prefix
// EOQ
// , $_GET['time_id'], $_GET['ppl_id']);
$lln = db_query(<<<EOQ
	SELECT CONCAT(ppl_forename, ' ', ppl_prefix, ' ', ppl_surname, ' (', ppl_login, IFNULL(CONCAT('/', GROUP_CONCAT(DISTINCT tag_name ORDER BY tag_type SEPARATOR '')), ''), ')') "Naam (inlog/tags)" FROM $voxdb.ppl
	JOIN $voxdb.claim USING (ppl_id)
	LEFT JOIN $voxdb.ppl2tag USING (ppl_id)
	LEFT JOIN $voxdb.tag USING (tag_id)
	JOIN $voxdb.avail USING (avail_id)
	WHERE time_id = ? AND avail.ppl_id = ?
	GROUP BY ppl_login
	ORDER BY ppl_surname, ppl_forename, ppl_prefix
EOQ
, $_GET['time_id'], $_GET['ppl_id']);

// print_r($lln);
html_start();
?>
<a href="rooster.php?session_guid=<?php echo $session_guid?>&amp;q=<?php echo urlencode($_GET['q'])?>&amp;week_id=<?php echo urlencode($_GET['week_id'])?>">&lt;--- terug naar rooster</a>
<H4>Leerlingen die zich hebben ingeschreven op</h4>
	<?php echo $moment['week']?>/<?php echo $moment['time_day']?><?php echo $moment['time_hour']?>/<?php echo $moment['ppl_login']?>/<?php echo $moment['vakken']?>
<?php
if (!mysqli_num_rows($lln)) { 
	?>
	<h6>Op dit moment zijn er geen leerlingen ingeschreven voor deze docent op dit uur.</h6>
	<?php 
	} else db_dump_result($lln);
html_end();

?>
