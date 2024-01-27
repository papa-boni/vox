<?php
require('system.php');
require('html.php');

enforce_logged_in();

html_start(); 
?>
<form action="do_edit_password.php?session_guid=<?php echo($session_guid); ?>" method="POST" accept-charset="UTF-8">
    <?php
    if (!check_su()) { 
    //if (check_su()) { // dit is aangepast zodat wachtwoord aangepast kan worden
        ?>
        <br>Huidig wachtwoord: <input class="password" type="password" name="old_password">
        <?php
    } else { 
        ?>
        <br>vanwege 'Switch User' is het niet nodig het oude wachtwoord op te geven
        <input type="hidden" name="old_password" value="">
    <?php 
    } 
    ?>
    <br>Nieuw wachtwoord: <input class="password" type="password" name="new_password">
    <br>Herhaal nieuw wachtwoord: <input class="password" type="password" name="new_password2">
    <br> <input class="btn" type="submit" value="opslaan">
</form>
<?php 
html_end();
?>
