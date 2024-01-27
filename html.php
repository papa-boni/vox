<?php

function html_start($script = '') {
	global $voxdb;

	?>
	<!DOCTYPE html>
	<html>
	<head>
	<meta charset="UTF-8">
	<!-- voor ccs style in materialize gebruikt -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
	<!--Import Google Icon Font-->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href='https://fonts.googleapis.com/css?family=Sofia' rel='stylesheet'>
	<!-- eigen stylesheet -->
	<link rel="stylesheet" href="css/style.css">
	<!-- Toevoegen van verschillende icoontjes en resources -->
	<link rel="apple-touch-icon" sizes="120x120" href="/vox/images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/vox/images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/vox/images/favicon-16x16.png">
	<link rel="manifest" href="/vox/images/site.webmanifest">
	<link rel="mask-icon" href="/vox/images/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="/vox/images/favicon.ico">
	<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
	<meta name="msapplication-TileColor" content="#da532c">
	<meta name="msapplication-config" content="/vox/images/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>VOX inschrijfsysteem</title>
	</head>
	<body class="cyan lighten-4">
	<header>
		<nav class="cyan darken-1">
			<div class="nav-wrapper">
				<a href="#" class="sidenav-trigger" data-target="mobile-menu">
					<i class="material-icons">menu</i>
				</a>
				<!-- menubalk -->
				<ul class="left hide-on-med-and-down" id="menu">
					<?php 
						if (check_staff_rights()) { ?>
							<li><a href="niet_ingeschreven.php?session_guid=<?php echo $GLOBALS['session_guid']?>">ni</a></li><?php 
						} 
						if (check_permission('TAGBEHEER') ) { ?>
							<li><a href="tags.php?session_guid=<?php echo $GLOBALS['session_guid']?>">tg</a></li><?php 
						}
						if (check_permission('WEEKBEHEER')) { ?>
							<li><a href="weken.php?session_guid=<?php echo $GLOBALS['session_guid']?>">we</a></li><?php 
						}
						if (check_permission('ACCOUNT') ) { ?>
							<li><a href="account.php?session_guid=<?php echo $GLOBALS['session_guid']?>">ac</a></li><?php 
						} 
						if (check_permission('PERMISSIONS') ) { ?>
							<li><a href="permissions.php?session_guid=<?php echo $GLOBALS['session_guid']?>">pb</a></li><?php 
						}
						if (check_permission('CONFIGS') ) { ?>
							<li><a href="configs.php?session_guid=<?php echo $GLOBALS['session_guid']?>">cb</a></li><?php 
						}
 ?>
				</ul>
				<ul class="right hide-on-med-and-down" id="menu">

					<?php
					if (check_logged_in()) { 
						if (!preg_match("/index.php/", $_SERVER['PHP_SELF']) && !preg_match("/klassenlijst.php/", $_SERVER['PHP_SELF'])) { ?>
							<li>
								<a href="index.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">HOME</a>
							</li>
							<?php 
						}
						if (!preg_match("/rooster.php/", $_SERVER['PHP_SELF']) && !preg_match("/klassenlijst.php/", $_SERVER['PHP_SELF'])) { ?>
							<li>
								<a href="rooster.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">ROOSTER</a>
							</li>
							<?php 
						}
						if (!preg_match("/edit_password.php/", $_SERVER['PHP_SELF'])) { ?>
							<li><a href="edit_password.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">WACHTWOORD</a></li>
							<?php 
						} 
						?>
						<li>
							<a href="#" onclick="logout('<?php echo $GLOBALS["session_guid"]; ?>')">LOGOUT</a>
						</li>
						<li class='black-text'>Ingelogd als:</li><li>  
							<?php echo $GLOBALS['session_state']['auth_user'];?>
						</li><?php
						if (check_su()) { ?>
							<li class="black-text">Overgeschakeld naar </li>
							<li>
								<form method="POST" action="do_su.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">
									<?php echo db_single_field("SELECT ppl_login FROM $voxdb.ppl WHERE ppl_id = ?", $GLOBALS['session_state']['ppl_id']); ?>
									<input type="hidden" name="username" value="<?php echo $GLOBALS['session_state']['auth_user']; ?>">
									<input class="btn-small" type="submit" value="switch terug">
								</form>
							</li>
							<?php 
						} else if (check_staff()) { ?>
						<li class='col m1'></li>
							<li class='black-text'> Overschakelen naar: 
							</li>
							<li>
								<form method="POST" action="do_su.php?session_guid=<?php echo $GLOBALS['session_guid'] ?>">
									<div class="row">
										<div class="col 1">
											<input type="text" name="username" value="" class="white-text input-field">
										</div>
									</div>
									</li>

								</form>
							</li>							
						<?php 
						} 
					} ?>
				</ul>
			</div>
		</nav>
		<ul class="sidenav cyan darken-3" id="mobile-menu">
			<?php 
			if (check_logged_in()) { 
				if (!preg_match("/index.php/", $_SERVER['PHP_SELF']) && !preg_match("/klassenlijst.php/", $_SERVER['PHP_SELF'])) { ?>
					<li>
						<a class="white-text" href="index.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">HOME</a>
					</li>
					<?php 
				}
				if (!preg_match("/rooster.php/", $_SERVER['PHP_SELF']) && !preg_match("/klassenlijst.php/", $_SERVER['PHP_SELF'])) { ?>
					<li><a class="white-text" href="rooster.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">ROOSTER</a></li>
					<?php 
				}
				if (!preg_match("/edit_password.php/", $_SERVER['PHP_SELF'])) { ?>
					<li><a class="white-text" href="edit_password.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">WACHTWOORD</a></li>
					<?php 
				} ?>
				<li>
					<a class="white-text" href="#" onclick="logout('<?php echo $GLOBALS["session_guid"]; ?>')">LOGOUT</a>
				</li>
				<li>
					Ingelogd als:<br><div class="white-text"><?php echo $GLOBALS['session_state']['auth_user']; ?> </div>
				</li>
				<?php 
				if (check_su()) { ?>
					<li>
						<form method="POST" action="do_su.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">
							Overgeschakeld naar: <br><div class="white-text"><?php echo db_single_field("SELECT ppl_login FROM $voxdb.ppl WHERE ppl_id = ?", $GLOBALS['session_state']['ppl_id']); ?></div>
							<input type="hidden" name="username" value="<?php echo $GLOBALS['session_state']['auth_user']; ?>"><br>
							<input class="waves-effect waves-light btn-small" type="submit" value="switch terug">
						</form>
					</li>
					<?php 
				} else if (check_staff()) { ?>
					<li>
						<form method="POST" action="do_su.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">
						Overschakelen naar: <br><input class="white-text" type="text" name="username" value="">
							<!-- <input class="btn" type="submit" value="switch user"> -->
						</form>
					</li>
					<?php 
				}
					
				if (check_staff_rights()) { ?>
					<li><a class="white-text" href="niet_ingeschreven.php?session_guid=<?php echo $GLOBALS['session_guid']?>">ni</a></li><?php 
				} 
				if (check_permission('TAGBEHEER') ) { ?>
					<li><a class="white-text" href="tags.php?session_guid=<?php echo $GLOBALS['session_guid']?>">tg</a></li><?php 
				}
				if (check_permission('WEEKBEHEER')) { ?>
					<li><a class="white-text" href="weken.php?session_guid=<?php echo $GLOBALS['session_guid']?>">we</a></li><?php 
				}
				if (check_permission('ACCOUNT') ) { ?>
					<li><a class="white-text" href="account.php?session_guid=<?php echo $GLOBALS['session_guid']?>">ac</a></li><?php 
				} 
				if (check_permission('PERMISSIONS') ) { ?>
					<li><a class="white-text" href="permissions.php?session_guid=<?php echo $GLOBALS['session_guid']?>">pb</a></li><?php 
				}
				if (check_permission('CONFIGS') ) { ?>
					<li><a class="white-text" href="configs.php?session_guid=<?php echo $GLOBALS['session_guid']?>">cb</a></li><?php 
				}

			} ?>
    	</ul>
	</header>  
	<main>
		<div class="container cyan lighten-5">
			<?php 
			if (!check_logged_in()) { ?>
				<h4>Vox keuzeuur inschrijfsysteem</h4>
				<p>Log in om je in te schrijven voor keuzeuren.</p>
				<form class="col s12" method="POST" action="do_login.php?session_guid=<?php echo $GLOBALS['session_guid']; ?>">
					<div class="row">
        				<div class="input-field col s6">
							<input type="text" name="username">
							<label for="username">Gebruikersnaam</label>
						</div>
						<div class="input-field col s6">
							<input type="password" name="password">
							<input class="btn-small" type="submit" value="login">
							<label for="password">Paswoord</label>
        				</div>
					</div>
				</form>
				<?php 
			} 
			
			if ($GLOBALS['session_state']['success_msg']) { ?>
				<div id="successmsg"><span class="textual">success:</span>
				<?php echo(($GLOBALS['session_state']['success_msg'])?$GLOBALS['session_state']['success_msg']:'<i>NULL</i>'); ?></div>
				<?php $GLOBALS['session_state']['success_msg'] = NULL;
			} ?>
			<?php 
			if ($GLOBALS['session_state']['error_msg']) { ?>
				<div id="errormsg"><span class="textual">error:</span>
				<?php echo(($GLOBALS['session_state']['error_msg'])?$GLOBALS['session_state']['error_msg']:'<i>NULL</i>'); ?></div>
				<?php  $GLOBALS['session_state']['error_msg'] = NULL;
			} ?>
			<?php
}

function  html_end(){
	?>
	</main>
	<!-- Voegt de footer toe met informatie en links -->
	<footer class="cyan page-footer">
    	<!-- <div class="footer-copyright"> -->
		<!-- <div id="footer">-->
		<div id="footerlogo">
			<img src="images/AGPLv3_Logo.svg">
		</div>
		<div id="footertext">
			VOX Inschrijfsysteem &copy; 2018-2019 Rik Snel &lt;rik@snel.it&gt;.<br>
			Released as <a href="http://www.gnu.org/philosophy/free-sw.html">free software</a> without warranties under <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html">GNU AGPL v3</a>.<br>
			Sourcecode: git clone <a href="https://github.com/rsnel/vox/">https://github.com/rsnel/vox/</a>
		</div>
	</footer>
	<!-- Materialize JavaScript -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
	<!-- eigen scripts  -->
	<script src="js/script.js" defer></script>

	</body>
	</html>
	<?php  
}

?>
