<?php
function generate_weken_select($week_id, $where, $onchange = "this.form.submit()") {
	global $voxdb;
	return db_single_field(<<<EOQ
	SELECT CONCAT('<div class="row"><select class="browser-default btn col s2 m2 l2" onchange="$onchange" name="week_id">', GROUP_CONCAT(CONCAT('<option', IF(week_id = $week_id, ' selected', ''), ' value="', week_id, '">', time_year, 'wk', LPAD(time_week, 2, '0'), IFNULL(CONCAT(' (', week_titel, ')'), ''), '</option>') ORDER BY time_year, time_week), '</select></div>') FROM $voxdb.weken WHERE $where
	EOQ
	);
}

?>
