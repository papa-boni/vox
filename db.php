<?php
// kijk of $mysqli_info de verwachte waardes heeft 'good practice
if (!checksetarray($mysqli_info, array ('host', 'username', 'passwd', 'dbname')))
	fatal('$mysql_info array not defined or complete '.
			'(host, username, passwd, dbnane) in '.$configfile);

// kijk of er verbinding gemaakt kan worden met de db, good practice
if (!($GLOBALS['db'] = mysqli_connect($mysqli_info['host'],
		$mysqli_info['username'], $mysqli_info['passwd'],
		$mysqli_info['dbname']))) {
	fatal("mysqli_connect (".mysqli_connect_errno()."): ".
			mysqli_connect_error());
}

function fatal_mysqli($function_name) {
	fatal($function_name.' ('.mysqli_errno($GLOBALS['db']).'): '.mysqli_error($GLOBALS['db']));
}

if (!mysqli_set_charset($db, "utf8"))
	fatal_mysqli('mysqli_set_charset');

// create & execute statement. Voorbereiden SQL statement
function db_vce_stmt($query, $args) {
    // Array voor referenties
    $refs = array();

    // Voorbereiden van de statement
    if (!($refs[0] = mysqli_prepare($GLOBALS['db'], $query))) fatal_mysqli('mysqli_prepare');
    $refs[1] = '';

    // Controleren op het juiste aantal parameters in de prepared statement
    if (mysqli_stmt_param_count($refs[0]) != count($args))
        fatal('prepared statement expects '.mysqli_stmt_param_count($refs[0]).' parameter(s) but gets '.count($args).' parameter(s): '.$query);

    // Parameters binden als die er zijn
    if (count($args)) {
        foreach ($args as &$arg) {
            $refs[] = &$arg;
            $type = gettype($arg);
            switch ($type) {
                case 'integer':
                    $refs[1] .= 'i';
                    break;
                case 'string':
                    $refs[1] .= 's';
                    break;
                case 'NULL':
                    $refs[1] .= 'i';
                    break;
                default:
                    fatal('unsupported type ('.$type.') for MySQL query (only integer, NULL and string supported)');
            }
        }

        // Parameters binden aan de statement
        if (!call_user_func_array('mysqli_stmt_bind_param', $refs)) fatal_mysqli('mysqli_bind_param');
    }

    // Statement uitvoeren
    if (!mysqli_stmt_execute($refs[0])) fatal_mysqli('mysqli_stmt_execute');

    // Geef de statement terug
    return $refs[0];
}

function db_ce_stmt($query) {
	// get all arguments, and discard the first
	$args = func_get_args();
	array_shift($args);

	return db_vce_stmt($query, $args);
}

function db_vquery($query, $args) {
	$stmt = db_vce_stmt($query, $args);

	if (!($res = mysqli_stmt_get_result($stmt)))
		fatal_mysqli('mysqli_stmt_get_result');

	if (!mysqli_stmt_close($stmt))
		fatal_mysqli('mysqli_stmt_close');

	return $res;
}

function db_query($query) {
	// get all arguments, and discard the first
	$args = func_get_args();
	array_shift($args);

	return db_vquery($query, $args);
}

function db_direct($query) {
	if (!(mysqli_query($GLOBALS['db'], $query))) fatal_mysqli('mysqli_query');
}

function db_vall_assoc_rekey($query, $args) {
	$res = db_vquery($query, $args);

	$out = array();
	while ($row = mysqli_fetch_assoc($res)) {
		$key = array_shift($row);
		if (count($row) == 1) {
			$out[$key] = array_shift($row);
		} else $out[$key] = $row;
	}

	return $out;
}

function db_all_assoc_rekey($query) {
	// get all arguments, and discard the first
	$args = func_get_args();
	array_shift($args);

	return db_vall_assoc_rekey($query, $args);
}

function db_vsingle_row($query, $args) {
	$res = db_vquery($query.' LIMIT 1', $args);

	$row = mysqli_fetch_assoc($res);

	mysqli_free_result($res);

	return $row;
}

function db_single_row($query) {
	// get all arguments, and discard the first
	$args = func_get_args();
	array_shift($args);

	return db_vsingle_row($query, $args);
}

function db_vsingle_field($query, $args) {
    // Haal een enkele rij op met db_vsingle_row
    $row = db_vsingle_row($query, $args);

    // Controleer of $row een array is
    if (!is_array($row)) return false; // Geen rij gevonden

    // Controleer of de rij-array elementen bevat
    if (!count($row)) fatal('first element of result row array not set?!?!?');

    // Reset geeft het eerste element van de array terug
    return reset($row);
}

function db_single_field($query) {
	// get all arguments, and discard the first
	$args = func_get_args();
	array_shift($args);

	return db_vsingle_field($query, $args);
}

function db_vexec($query, $args) {
	$stmt = db_vce_stmt($query, $args);

	if (($affected_rows = mysqli_stmt_affected_rows($stmt)) < 0)
		fatal_mysqli('mysqli_affected_rows');

	mysqli_stmt_close($stmt);
	
	return $affected_rows;
}

function db_exec($query) {
	$args = func_get_args();
	array_shift($args);

	return db_vexec($query, $args);
}

// Deze functie haalt een ID op uit de database op basis van opgegeven criteria.
// Deze functie lijkt een veilige manier te bieden om een nieuw ID op te halen of toe te voegen aan een database op basis van bepaalde criteria.
function db_get_id($id_name, $table) {
    // Haal alle argumenten op, behalve de eerste twee (id_name en table).
    $args = func_get_args();
    array_shift($args);
    array_shift($args);

    // Controleer of het aantal argumenten na $table even is.
    $argc = count($args);
    if ($argc % 2) fatal('number of args to db_get_id after $table must be even');

    // Bouw de insert- en select-query's op.
    $insert = $id_name.' = NULL';
    $select = 'TRUE';
    $values = array();

    // Loop door de argumenten en bouw de queries en waarden op.
    for ($i = 0; $i < $argc; $i += 2) {
        $insert .= ', '.$args[$i].' = ?';
        $select .= ' AND '.$args[$i].' = ?';
        $values[] = &$args[$i+1];
    }

    // Zet een schrijfvergrendeling op de tabel.
    db_direct("LOCK TABLES $table WRITE");

    // Haal het ID op dat aan de voorwaarden voldoet.
    $id = db_vsingle_field('SELECT '.$id_name.' FROM '.$table.' WHERE '.$select, $values);

    // Als er geen overeenkomend ID is gevonden, voeg een nieuw record toe aan de tabel.
    if (!$id) {
        // Voer een INSERT-query uit en controleer of precies 1 rij is beïnvloed.
        if (db_vexec('INSERT INTO '.$table.' SET '.$insert, $values) != 1)
            fatal('expected INSERT to affect precisely 1 row, but it did not');
        
        // Haal het automatisch gegenereerde ID op.
        $id = mysqli_insert_id($GLOBALS['db']);
    }

    // Verwijder de schrijfvergrendeling van de tabel.
    db_direct('UNLOCK TABLES');

    // Geef het opgehaalde ID terug.
    return $id;
}


// Deze functie lijkt ook een veilige manier te bieden om een useragent-ID op te halen of toe te voegen aan een database op basis van een useragent-string. Het maakt gebruik van een hash van de useragent-string om unieke identificatie mogelijk te maken en gebruikt transacties om atomiciteit te behouden tijdens het lezen en schrijven naar de database.
// Deze functie haalt een useragent-ID op uit de database op basis van een useragent-string.
function db_get_useragent_id($useragent_string) {
    // Genereer een hash van de useragent-string met het SHA-256 algoritme.
    $hash = hash('sha256', $useragent_string);

    // Zet een schrijfvergrendeling op de useragents-tabel.
    db_direct('LOCK TABLES useragents WRITE');

    // Haal het useragent-ID op dat overeenkomt met de hash.
    $id = db_single_field('SELECT useragent_id FROM useragents WHERE useragent_hash = ?', $hash);

    // Als er geen overeenkomend useragent-ID is gevonden, voeg een nieuw record toe aan de tabel.
    if (!$id) {
        // Voer een INSERT-query uit en controleer of precies 1 rij is beïnvloed.
        if (db_exec('INSERT INTO useragents ( useragent_string, useragent_hash ) VALUES ( ?, ? )', $useragent_string, $hash) != 1)
            fatal('expected INSERT to affect precisely 1 row, but it did not');
        
        // Haal het automatisch gegenereerde useragent-ID op.
        $id = mysqli_insert_id($GLOBALS['db']);
    }

    // Verwijder de schrijfvergrendeling van de useragents-tabel.
    db_direct('UNLOCK TABLES');

    // Geef het opgehaalde useragent-ID terug.
    return $id;
}

function db_dump_result($res, $show_table_names = 0) {
    // Begin met het weergeven van de tabel
    echo("<table>\n<thead>\n<tr>");

    // Loop door alle velden in het resultaat
    while (($finfo = $res->fetch_field())) {
        // Voeg een kop (header) toe voor elk veld
        echo('<th>');
        // Voeg eventueel de tabelnaam toe aan de kop
        if ($show_table_names) echo($finfo->table.'<br>');
        // Voeg de naam van het veld toe aan de kop
        echo($finfo->name.'</th>');        
    }
    // Sluit de tabelkop af
    echo("</thead>\n<tbody>\n");

    // Loop door alle rijen in het resultaat
    while (($row = mysqli_fetch_array($res, MYSQLI_NUM))) {
        // Begin een nieuwe rij
        echo('<tr>');
        // Loop door alle gegevens in de rij
        foreach ($row as $data) {
            // Als het gegeven NULL is, toon het als cursief "NULL"
            if ($data === NULL) echo('<td><i>NULL</i></td>');
            // Anders, toon het gegeven in een cel
            else echo('<td>'.$data.'</td>');
        }
        // Sluit de rij af
        echo("</tr>\n");
    }
    // Sluit de tabelinhoud af
    echo("</tbody>\n");
    // Sluit de tabel af
    echo("</table>\n");

    // Reset het resultaat, zodat het opnieuw kan worden doorlopen
    mysqli_field_seek($res, 0);
    mysqli_data_seek($res, 0);
}
function db_dump_result_resp($res, $show_table_names = 0) {
    // Begin met het weergeven van de tabel
    echo("<table class='highlight responsive-table'>\n<thead>\n<tr>");

    // Loop door alle velden in het resultaat
    while (($finfo = $res->fetch_field())) {
        // Voeg een kop (header) toe voor elk veld
        echo('<th>');
        // Voeg eventueel de tabelnaam toe aan de kop
        if ($show_table_names) echo($finfo->table.'<br>');
        // Voeg de naam van het veld toe aan de kop
        echo($finfo->name.'</th>');        
    }
    // Sluit de tabelkop af
    echo("</thead>\n<tbody>\n");

    // Loop door alle rijen in het resultaat
    while (($row = mysqli_fetch_array($res, MYSQLI_NUM))) {
        // Begin een nieuwe rij
        echo('<tr>');
        // Loop door alle gegevens in de rij
        foreach ($row as $data) {
            // Als het gegeven NULL is, toon het als cursief "NULL"
            if ($data === NULL) echo('<td><i>NULL</i></td>');
            // Anders, toon het gegeven in een cel
            else echo('<td>'.$data.'</td>');
        }
        // Sluit de rij af
        echo("</tr>\n");
    }
    // Sluit de tabelinhoud af
    echo("</tbody>\n");
    // Sluit de tabel af
    echo("</table>\n");

    // Reset het resultaat, zodat het opnieuw kan worden doorlopen
    mysqli_field_seek($res, 0);
    mysqli_data_seek($res, 0);
}

function db_dump_result_trans($res, $show_table_names = 0) {
    // Begin met het weergeven van de tabel
    echo("<table>\n<thead>\n<tr>");

    // Sluit de tabelkop af
    echo("</thead>\n<tbody>\n");

    // Haal de veldnamen op
    $fieldNames = mysqli_fetch_fields($res);

    // Loop door alle kolommen in het resultaat
    for ($i = 0; $i < mysqli_num_fields($res); $i++) {
        // Begin een nieuwe rij
        echo('<tr>');
        // Loop door alle rijen en haal het gegeven op
        mysqli_data_seek($res, 0);
        echo('<td><b>' . $fieldNames[$i]->name . '</b></td>');
        while ($row = mysqli_fetch_array($res, MYSQLI_NUM)) {
            // Als het gegeven NULL is, toon het als cursief "NULL"
            if ($row[$i] === NULL) {
                echo('<td><i>NULL</i></td>');
            } else {
                // Anders, toon het gegeven in een cel
                echo('<td>' . $row[$i] . '</td>');
            }
        }
        // Sluit de rij af
        echo("</tr>\n");
    }

    
    // Sluit de tabelinhoud af
    echo("</tbody>\n");
    // Sluit de tabel af
    echo("</table>\n");

    // Reset het resultaat, zodat het opnieuw kan worden doorlopen
    mysqli_field_seek($res, 0);
    mysqli_data_seek($res, 0);
}