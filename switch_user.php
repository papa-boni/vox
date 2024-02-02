<?php
require('system.php');
require('html.php');

enforce_logged_in();

html_start(); 
?>

<h4 class='black-text'> Switch naar: </h4>
<p>Voor het overschakelen naar een collega (mits voldoende rechten) of leerling</p>
<form method="POST" action="do_su.php?session_guid=<?php echo $GLOBALS['session_guid'] ?>">
    <div class="row">
        <div class="col 1">
            <input type="text" name="username" value="" class="input-field">
        </div>
    </div>
</form>
							
<?php
html_end();
?>
