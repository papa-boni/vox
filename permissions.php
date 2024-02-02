<?php
require('system.php');
require('html.php');

enforce_permission("PERMISSIONS");

// $res = db_query(<<<EOQ
// SELECT *, CONCAT('<a href="permission.php?session_guid=$session_guid&amp;log_id=', log_id, '">[edit]</a>') edit
// FROM permissions
// EOQ
// );

$res = db_query(<<<EOQ
SELECT 
    user AS gebruiker,
    permission AS permissie,
    CONCAT('<a href="permission.php?session_guid=$session_guid&amp;log_id=', log_id, '">[edit]</a>') AS edit
FROM permissions
EOQ
);



html_start(); ?>
<h4>Permissies</h4>
<form action="permission.php" method="get">
  <input type="hidden" name="session_guid" value="<?php echo $session_guid ?>">
  <button class="btn" type="submit">Permissies toewijzen</button>
</form>

<?php db_dump_result_resp($res, false);
html_end();
?>
