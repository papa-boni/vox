<?php
require('system.php');
require('html.php');
//enforce_logged_in();
enforce_permission('ACCOUNT');

$res = db_query(<<<EOQ
SELECT ppl_login login, CONCAT(ppl_forename, ' ', ppl_prefix, ' ', ppl_surname, IFNULL(CONCAT(' (', tags, ')'), '')) naam,
	CONCAT('<label><input type="checkbox"', 
		IF(password_hash IS NULL, ' checked', ''),
		' value="', ppl.ppl_id, '-', IFNULL(log_id, 'NULL'),
		'" name="pplpwlog[]"><span></span></label>') `genereer wachtwoord`,
		IFNULL(last_activity, 'nog nooit ingelogd') `laatste activiteit`

FROM $voxdb.ppl
LEFT JOIN (
	SELECT auth_user ppl_login, MAX(timestamp) last_activity
	FROM session_log
	GROUP BY auth_user
) AS last_activity USING (ppl_login)
LEFT JOIN (
	SELECT ppl_id, GROUP_CONCAT(tag_name ORDER BY tag_type SEPARATOR '') tags
	FROM $voxdb.ppl2tag
	JOIN $voxdb.tag USING (tag_id)
	WHERE tag_type = 'NIVEAU' OR tag_type = 'LEERJAAR'
	GROUP BY ppl_id
) AS tags USING (ppl_id)
LEFT JOIN passwords ON passwords.auth_user = ppl_login
WHERE ppl_active = 1
ORDER BY last_activity DESC, ppl_type, ppl_surname, ppl_forename, ppl_prefix
EOQ
);

html_start(); ?>
<h4>Accounts</h4>
<p>Gebruikers die nog geen wachtwoord hebben zijn automatisch aangevinkt.</p>
<form action="do_generate_passwords.php?session_guid=<?php echo $session_guid?>" accept-charset="UTF-8" method="POST">
<?php db_dump_result($res, false); ?>
<input type="submit" class="btn" value="Genereer wachtwoorden">
</form>

<?php  html_end();
?>
