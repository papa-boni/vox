<?php
require('system.php');
require('html.php');

enforce_permission("CONFIGS");

$res = db_query(<<<EOQ
SELECT 
  config_key AS gebruiker,
  config_value AS super,
  CONCAT('<a href="edit_config.php?session_guid=$session_guid&amp;log_id=', log_id, '">[edit]</a>') edit
FROM config
EOQ
);

html_start(); ?>
<h4>Super personeel</h4>
<form action="edit_config.php" method="get">
  <input type="hidden" name="session_guid" value="<?php echo $session_guid ?>">
  <button class="btn" type="submit">Super personeel toewijzen</button>
</form>
<?php db_dump_result_resp($res, false);
html_end();
?>
