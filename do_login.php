<?php
require('system.php');

// true == accepted, false == rejected, null == error
function ext_check_basic($username, $password) {
	global $auth;

	if (!($ch = curl_init($auth['url']))) {
		warning('error initializing cURL');
		return NULL;
	}

	if (!curl_setopt($ch, CURLOPT_USERPWD, $auth['prefix'].$username.':'.$password)) goto error;

	if (!curl_setopt($ch, CURLOPT_RETURNTRANSFER, true)) goto error;

	if (!curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true)) goto error;
 
	if (!curl_setopt($ch, CURLOPT_TIMEOUT, 5)) goto error;

	if (!curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false)) goto error;

	if (!curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false)) goto error;

	if (curl_exec($ch) === false) goto error;

	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	// server returns 200 on 'access granted'
	// server returns 401 on 'access denied'
	// in case of something else (server error), the
	// server hopefully returns something else...
	if ($status == 200) return true;
	else if ($status == 401) return false;
	return NULL;
	error:
		warning_curl($ch);
		return NULL;
}

// true == accepted, false == rejected, null == error
function ext_check_form($username, $password) {
	global $auth;

	if (!($ch = curl_init($auth['url'].'?username='.urlencode($auth['prefix'].$username).'&password='.urlencode($password)))) {
		warning('error initializing cURL');
		return NULL;
	}

	if (!curl_setopt($ch, CURLOPT_RETURNTRANSFER, true)) goto error;

	if (!curl_setopt($ch, CURLOPT_TIMEOUT, 5)) goto error;

	if (!curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false)) goto error;

	if (!curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false)) goto error;

	if (($verdict = curl_exec($ch)) === false) goto error;

	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($verdict == 'access granted') return true;
	else if ($verdict == 'access denied') return false;

	return NULL;

	error:
		warning_curl($ch);
		return NULL;
}

$gebruikersnaam = '';
$paswoordhash = '';

function ext_check_local($username, $password) {
	$password_hash = db_single_field("SELECT password_hash FROM passwords WHERE auth_user = ?", $_POST['username']);

	if (!$password_hash || !hash_equals($password_hash, crypt($_POST['password'], $password_hash))) return false;
	return true;
}

if (!check_username($_POST['username'])) {
	$GLOBALS['session_state']['error_msg'] = 'Gebruikersnaam bevat niet-toegestane tekens.';
	goto exitlabel;
}

if ($GLOBALS['session_state']['auth_user']) {
	$GLOBALS['session_state']['error_msg'] = 'Deze sessie is reeds ingelogd.';
	goto exitlabel;
}

// Een switch-statement op basis van de waarde van $auth['method']
switch ($auth['method']) {
    case 'Basic':
        // Als de authenticatiemethode 'Basic' is, roep dan de functie ext_check_basic aan
        $res = ext_check_basic($_POST['username'], $_POST['password']);
        break;
    case 'Form':
        // Als de authenticatiemethode 'Form' is, roep dan de functie ext_check_form aan
        $res = ext_check_form($_POST['username'], $_POST['password']);
        break;
    case 'Local':
        // Als de authenticatiemethode 'Local' is, roep dan de functie ext_check_local aan
		$res = ext_check_local($_POST['username'], $_POST['password']);
        break;
    default:
        // Als de authenticatiemethode onbekend is, genereer dan een fatale fout met een foutbericht
        fatal('unknown auth method specified in config file: '.$auth['method']);
}

if ($res === true) {
	$auth_user = htmlenc($_POST['username']);
	$ppl_id = db_single_field("SELECT ppl_id FROM $voxdb.ppl WHERE ppl_login = ?", $auth_user);
	if (!$ppl_id) {
		$GLOBALS['session_state']['error_msg'] = 'Gebruiker is onbekend in '.$voxdb.'.ppl. Neem contact op met de beheerder als dat niet klopt.';
		header('Location: '.$GLOBALS['session_state']['request_uri']);
		exit;
		//fatal('gebruiker '.htmlenc($_POST['username']).' is onbekend in '.$voxdb.'.ppl, vraag de beheerder');
	}

	$ppl_active = db_single_field("SELECT ppl_active FROM $voxdb.ppl WHERE ppl_login = ?", $auth_user);
	if (!$ppl_active) {
		$GLOBALS['session_state']['error_msg'] = 'Gebruiker is gedeactiveerd. Neem contact op met de beheerder als dat niet klopt.';
		header('Location: '.$GLOBALS['session_state']['request_uri']);
		exit;
	}
	$GLOBALS['session_state']['auth_user'] = $auth_user;
	$GLOBALS['session_state']['ppl_id'] = $ppl_id;
	if ($auth['method'] != 'Local' && !ext_check_local($_POST['username'], $_POST['password'])) 
		upsert_password($_POST['username'], $_POST['password']);
} else if ($res === false) {
	$GLOBALS['session_state']['error_msg'] = 'Ongeldige combinatie van gebruikersnaam en wachtwoord. Auth_user:' . $auth_user . " ppl_id" . $ppl_id;
} else if ($res === null) {
	$GLOBALS['session_state']['error_msg'] = 'Inloggen niet mogelijk door storing in authenticatieserver.';
}

exitlabel:

// voegt session_guide aan request_uri toe als dat niet al gebeurd is 
if (!preg_match('/\?session_guid=/', $GLOBALS['session_state']['request_uri']))
	$GLOBALS['session_state']['request_uri'] .= '?session_guid='.$GLOBALS['session_guid'];

header('Location: '.$GLOBALS['session_state']['request_uri']);
echo($GLOBALS['session_state']['request_uri']);
print_r ($GLOBALS['session_state']);

?>
