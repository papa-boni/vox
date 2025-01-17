<?php
require('system.php');
require('html.php');

enforce_permission('PERMISSIONS');

if (isset($_GET['log_id'])) {
	$res = db_single_row("SELECT * FROM log JOIN log_permissions ON log_permissions.log_permissions_id = foreign_id WHERE log_id = ? AND foreign_table = 'log_permissions'", $_GET['log_id']);
	if (!$res) fatal("log_permissions entry with log_id = {$_GET['log_id']} not found");
	//print_r($res);
} else {
	$res = array('auth_user' => '', 'permission' => '');
}

html_start(); ?>
<h4>Permissies aanpassen</h4>
<form action="do_permission.php?session_guid=<?php echo($session_guid); ?>" method="POST" accept-charset="UTF-8">
<input type="text" name="auth_user" value="<?php echo $res['auth_user']?>">
<input type="text" name="permission" value="<?php echo $res['permission']?>">
<input type="submit" class="btn" name="submit" value="opslaan">
<?php if (isset($_GET['log_id'])) { ?>
<input type="hidden" name="log_id" value="<?php echo $_GET['log_id']?>">
<input type="submit" class="btn" name="submit" value="verwijder">
<?php } ?>
</form>
<?php html_end();
?>
