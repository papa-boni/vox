<?php
require('system.php');
require('html.php');

//enforce_logged_in();
enforce_permission('TAGBEHEER');

if (!isset($_GET['type'])) $type = 'ROOSTERLLN';
	else $type = $_GET['type'];

$check = db_single_field("SELECT DISTINCT tag_type FROM $voxdb.tag WHERE tag_type = ?", $type);

if (!$check) fatal("tags van type ".$type." bestaan niet");

$tags = db_all_assoc_rekey(<<<EOQ
	SELECT tag_id, tag_name FROM $voxdb.tag WHERE tag_type = ? ORDER BY tag_order, tag_name
EOQ
, $type);

$selecttags = db_single_field(<<<EOQ
	SELECT CONCAT('<div class="row"><label><select class="browser-default btn col s3 m3 l3" name="type">', GROUP_CONCAT(DISTINCT CONCAT('<option', IF(tag_type = ?, ' selected', ''), '></div>', tag_type, '</option>')), '</select><span></span></label>') FROM $voxdb.tag
EOQ
, $type);

if (!isset($_GET['filter']) || $_GET['filter'] == '') $_GET['filter'] = array();
else if (!is_array($_GET['filter'])) fatal("impossible");

$filter = db_all_assoc_rekey(<<<EOQ
	SELECT tag_type, GROUP_CONCAT(CONCAT(tag_id, '-', tag_name) ORDER BY tag_order) FROM $voxdb.tag WHERE tag_type = ? GROUP BY tag_type
EOQ
, $type);

$select = '';

foreach ($tags as $tag_id => $tag_name) {
	$tag_col = implode('<br>', explode('-', $tag_name)).'<br><label><input class="selectcolumn" type="checkbox" id="'.$tag_name.'"><span></span></label>';
	$select .= <<<EOS
		, CONCAT('<label><input class="$tag_name" type="checkbox" name="ppl2tag[]"', IF((SELECT ppl2tag_id FROM $voxdb.ppl2tag WHERE ppl2tag.ppl_id = ppl.ppl_id AND ppl2tag.tag_id = $tag_id), ' checked', ''),' value="', ppl_id, '-$tag_id"><span></span></label>') '$tag_col'
	EOS;
}
?>

<?php
html_start();
$where = array();
$qfilter = array();
?>
<h4>Leerlingentags</h4>
<form method="GET" accept-charset="UTF-8">
<input type="hidden" name="session_guid" value="<?php echo $session_guid?>">
Filter:<br>
<?php 
foreach ($filter as $soort => $list) {
	echo($soort);
	$where[$soort] = array();
	$tags = explode(',', $list);
	foreach ($tags as $tag_info) {
		$tmp = explode('-', $tag_info);
		$tag_id = db_single_field("SELECT tag_id FROM $voxdb.tag WHERE tag_id = ?", $tmp[0]);
		if (!$tag_id) fatal("tag id bestaat niet");
		array_shift($tmp);
		$tag_name = implode('-', $tmp);
		if (in_array($tag_id, $_GET['filter'])) {
			$where[$soort][] = "tag_id = $tag_id";
			$qfilter[] = '<input type="hidden" name="filter[]" value="'.$tag_id.'">';
		}
		?>
		<br><label><input type="checkbox" name="filter[]" value="<?php echo $tag_id?>"<?php echo in_array($tag_id, $_GET['filter'])?' checked':'' ?>><span></span></label><?php echo $tag_name ?>
		<?php
	}
	echo('<br>');
}
foreach ($where as $soort => $stuff) {
	$stuff = implode(' OR ', $stuff);
	if ($stuff == '') $where[$soort] = 'TRUE';
	else $where[$soort] = 'ppl_id IN ( SELECT ppl_id FROM '.$voxdb.'.ppl2tag WHERE '.$stuff.' )';
}
$where = implode(' AND ', $where);
$res = db_query(<<<EOQ
	SELECT CONCAT(ppl_login, '<input type="hidden" name="betreft[]" value="', ppl_id, '">') login,
	CONCAT(ppl_forename, ' ', ppl_prefix, ' ', ppl_surname) naam$select
	FROM $voxdb.ppl
	WHERE ppl_type = 'leerling' AND $where
	ORDER BY ppl_surname, ppl_forename, ppl_prefix
EOQ
);
?>
Soort tags:
<?php echo $selecttags?><br><br>
<input type="submit" class="btn" value="Wijzig filter/tagsoort">
<p>Niet opgeslagen wijzigingen in vinkjes gaan verloren!</p>
</form>
<p><form action="do_tags.php?session_guid=<?php echo $session_guid?>" accept-charset="UTF-8" method="POST">
	<?php echo implode('', $qfilter); ?>
	<div class="table-fixed"><?php db_dump_result($res, false); ?></div>
	<input type="hidden" name="type" value="<?php echo htmlenc($type); ?>">
	<input type="submit" class="btn" value="Opslaan">
</form>
<?php  

html_end();
?>
