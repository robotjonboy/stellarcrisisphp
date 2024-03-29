<?php
# file:sc.php
# 
# This file is a bag of functions
# Here they are sorted alphabetically
/*
main -general program switch to select function to call
function addExploredToFriends($player, $explored_id)
function array_random($array)
function array_remove($remove_value, $array)
function calculateBridier($winner_rank, $winner_index, $loser_rank, $loser_index)
function checkForOldPasswordedGames()
function checkForUpdates()
function chooseIconPage($vars)
function convertSharedHQToScoutingReports($series, $update, $player_1, $player_2_name)
function drawButtons($empire = array())
function footer()
function formatMessage($message)
function getEconomicPower($player)
function getMilitaryPower($player)
function getTeamDiplomacy($game)
function handleIconUpload($vars, $file)
function iconList($icon)
function iconUploadPage($vars)
function importExplored($ignorant_player, $other_player)
function inHolidayBreak()
function login($vars)
function loginFailed($message)
function mainPage()
function messageHeader($message)
function newEmpireLogin($vars)
function randomName()
function recalculateRatios($vars)
function sanitizeString($string)
function secondsToString($sec)
function sendEmpireMessage($empire, $message)
function seriesParameters($series_id)
function serverTime()
function spawnGame($series_name)
function sqlError($rollback_status)
function standardHeader($title, $empire = array())
function utime()

PrettyPrinter per this url
http://www.prettyprinter.de/module.php?name=PrettyPrinter
did not maintain if indent 
if ()
   line // this line was flush.
else
{
}
*/
#----------------------------------------------------------------------------------------------------------------------#
require('scfunctions.php');
require('server.php');
require('debug.php');
require('sql.php');
require('admin/admin.php');
require('ship_types.php');  //contains global $ship_types array
require('history.php');
require('main/main.php');
require('game/game.php');
require('update.php');
#----------------------------------------------------------------------------------------------------------------------#
# Set $debug to true to see what was sent to us.
# Think before doing this, people are possibly playing on the server.
# This my be useful elsewhere if you need to debug stuff: use this global variable.
#         NOTE: check out debug.php writing to errorlog.html instead

/*if ($debug == true) //cjp debug 
{
   echo 'POST:<pre style="text-align: left; color: red; font-weight: bold;">'; 
   print_r($_POST); 
   echo '</pre>';
   echo 'GET:<pre style="text-align: left; color: red; font-weight: bold;">'; 
   print_r($_GET); 
   echo '</pre>';
   echo 'FILES:<pre style="text-align: left; color: red; font-weight: bold;">'; 
   print_r($_FILES); 
   echo '</pre>';	
}*/

#----------------------------------------------------------------------------------------------------------------------#

ini_set('memory_limit', '128M');

// Report all errors except E_NOTICE, with our own error handler.
error_reporting(E_ALL ^ E_NOTICE);
set_error_handler('sc_errorHandler');

// Seed the pseudo-random number generator.
srand((int)((double)microtime()*1000000));

// Prevent people from crapping up the database (and their games) by interrupting form processing.
ignore_user_abort(true);

// Start timing the execution of the user's request. This ends up in the footer (see footer()).
$start_time = utime();

// Get our current memory usage so we can determine how much we consumed during execution.
// This is actually the usage for Apache and all loaded modules; treat accordingly.
// memory_get_usage() will only be defined if your PHP is compiled with the --enable-memory-limit configuration option.
if ($server['show_memory_usage']) //local setting
{
   if( function_exists('memory_get_usage') ) // but don't blow up
   {
     $start_memory = memory_get_usage();
   }
}

// Always check to see if games need updating, since this script does not run continuously and 
// thus cannot "know" when to update a game.
// We only do this upon form submissions, and when we're not in the administration screens to alleviate the load a bit.
if (sc_post('page') != 'admin') {
	checkForUpdates();
	checkForTournamentUpdates();
}

// Check to see how the submitter can be authenticated.
$authenticated = false;
$authenticated_as_admin = false;
if (strlen(sc_post('name')) and strlen(sc_post('pass')))
{
	$sql  = 'SELECT name, is_admin FROM empires ';
	$sql .= 'WHERE name = "'.$mysqli->real_escape_string(sc_post('name')).'" ';
	$sql .= 'AND password = "'.$mysqli->real_escape_string(sc_post('pass')).'"';
	$select = sc_query($sql);

	if ($authenticated = $select->num_rows)
	{
		$authenticated_as_admin = sc_result($select, 0, 1);
		
		// Make sure the name is capitalized correctly in the $_POST array from now on 
		// by taking the value from the database.
		$_POST['name'] = sc_result($select, 0, 0);
	}
}

#----------------------------------------------------------------------------------------------------------------------#
if (isset($_GET['seriesParameters']))
	//pop-up window - sc window untouched. - so do nothing else - just wait for next post.
	seriesParameters($_GET['seriesParameters']); 
else 
{
	switch (sc_post('section'))
	{		
		case 'login':		login($_POST);			break;
		case 'admin':		adminAction($_POST);		break;
		case 'main':		mainAction($_POST, $_FILES);	break;
		case 'game':		gameAction($_POST);		break; //defined in game.php
		case 'tournaments':	tournamentsAction($_POST);	break;
		default:    		mainPage(); // This should not happen.
	}

	// Processing ends here. Commit whatever we've done.
	sc_query('COMMIT');  //cjp stop commit on pop-up - adjust nesting
}

#----------------------------------------------------------------------------------------------------------------------#
# Standard header for non-game pages. An empire record can be passed by argument to access some user preferences.
#

function standardHeader($title, $empire = array())
{
	global $server;?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>SC <?php echo $server['version'].' @ '.$server['servername'].': '.$title; ?></title>
	<script type="text/javascript">
		var scServerName = '<?php echo $server['servername']; ?>';
		var scServerVersion = '<?php echo $server['version']; ?>';
	</script>
	<script src="sc.js" type="text/javascript"></script>
	<link rel="stylesheet" href="styles.css" type="text/css">
	<link rel="shortcut icon" href="/sc/favicon.ico" type="image/x-icon" />
<?php
//2008-04-05 added shortcut icon above
	if ($empire)
		{			
		// Override background style according to the user's profile.
		if (array_key_exists('draw_background', $empire) && $empire['draw_background'])
			{
			echo '<style type="text/css">body { '.
				 ($empire['custom_bg_url'] ? 'background: url("'.$empire['custom_bg_url'].'"); background-color: black; ' : '').
				 'background-attachment: '.$empire['background_attachment'].'; }</style>';
			}
		else
			echo '<style type="text/css">body { background: none; background-color: black; }</style>';
		}
?>
</head>
<body>
<form method=post action="sc.php">
<?php
	echo $server['standard_header_text'];
}

#----------------------------------------------------------------------------------------------------------------------#
# Navigation buttons present on non-game pages. The empire record SHOULD be passed.
#

function drawButtons($empire = array())
{
	global $server;
	
	echo '<div style="text-align: center; margin-top: 10pt;">'.
		 '<input type=submit name=action value="Game List">'.
		 '<input type=submit name=action value="Password Games">'.
		 ($server['custom_series_allowed'] ? '<input type=submit name=action value="Custom Series">' : '').
		 ($server['tournaments'] ? '<input type=submit name=action value="Tournaments">' : '').
		 '<input type=submit name=action value="Edit Profile">'.
		 '<input type=submit name=action value="Stat Viewer">'.
		 '<input type=submit name=action value="Game History">'.
		 ((array_key_exists('is_admin', $empire) && $empire['is_admin']) ? '<input type=submit name=action value="Administration">' : '').
		 '<input type=submit name=action value="Logout"></div>';
}

#----------------------------------------------------------------------------------------------------------------------#
# Calls to this function should be changed to require('footer.php') instead.
#

function footer()
{
	require('footer.php');
}

#----------------------------------------------------------------------------------------------------------------------#

function mainPage()
{
	global $server;

	standardHeader('Login');

//	$select_quickStats = sc_query('SELECT COUNT(DISTINCT players.name), COUNT(DISTINCT games.id) FROM players INNER JOIN games ON games.player_count > 0');
	$select_quickStats = sc_query
	('
		SELECT COUNT(DISTINCT players.name),
		COUNT(DISTINCT games.id) 
		FROM players 
		INNER JOIN games 
		ON games.player_count > 0
	');
	$select_motd = sc_query
	('
		SELECT text 
		FROM messages 
		WHERE type = "motd" 
		LIMIT 1'
	);
	$motd = sc_fetch_assoc($select_motd);
?>
	<input type=hidden name="section" value="login">

	<div class=pageTitle>
	   Stellar Crisis v<?php echo $server['version'].' @ '.$server['servername']; ?>
	</div>

<div>
<img class=spacerule src="images/spacerule.jpg" width="100%">
<table 	width="100%" 
		cellspacing=5 
		style="margin-bottom: 10pt;">
	<tr>
		<td style=" width: 250pt;">
			<table>
				<tr>
					<th style="text-align: right;">Empire Name:</th>
					<td>
					<input 	type="text" 
							name="name" 
							size=20 
							maxlength=20 
							value="<?php echo sc_cookie('sc_login'); ?>"
					>
				</tr>
				<tr>
					<th style="text-align: right;">Password:</th>
					<td>
					<input 	type="password" 
							name="pass" 
							size=20 
							maxlength=20
					>
					&nbsp;
					<input 	type="submit"	
							class="submit" 
							name="action"  
							value="Login"
					>
					</td>
				</tr>
			</table>
		</td>
		<th style="text-align: left; vertical-align: top;">
		Stellar Crisis is the web's first complete multi-player strategy game. 
		Players from all over the world compete to build megalithic galactic empires, 
		develop powerful new technologies, and fight pitched battles in far away star
		systems. It is free, absolutely and positively.
		It also comes with ABSOLUTELY NO WARRANTY. 
		For more information, please visit the 
		<a href="<?php echo $server['sc_wiki_url']; ?>">
		   Stellar Crisis Wiki
		</a>.
		</th>
	</tr>
	<tr>
		<td colspan=2 style="padding-top: 10pt; font-size: 9pt;">
			If you are a new player, enter the empire name and password you would like
			to use (you'll be asked to confirm it), and click the button labeled 
			<i>"Login"</i>.
		</td>
	</tr>
	<tr>
		<td colspan=2 style="padding-top: 10pt;">
			RocketEmpires is proud to host the annual Stellar Crisis Open Championship.
			Since 2011, the Stellar Crisis Open Championship has taken place every year
			beginning on the last Saturday of July. Registration is free and opens one 
			week before the tournament begins. To register, login, switch to the
			tournaments tab, click the view button, and then click register.  <a
			href="http://scopen.rocketempires.com">Click here to visit the tournament
			homepage.</a>
		</td>
	</tr>
</table>
</div>
<?php
	if (is_array($motd) && array_key_exists('text', $motd) && isset($motd['text'])) {
		echo stripslashes(urldecode($motd['text']));
	}
?>
<div class=quickStats>
   <?php echo sc_result($select_quickStats, 0, 0).
      ' players are currently in '.
      sc_result($select_quickStats, 0, 1).
      ' games'; 
   ?> 
   
</div>
<?php	
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function login($vars)
{
	global $server;

	if (!strlen($vars['name']) or !strlen($vars['pass'])) 
		return loginFailed('You must enter a name and a password.');

	// Prevent people from faking empire names with extra spaces.
	$vars['name'] = preg_replace('/[[:space:]]+/', ' ', trim($vars['name']) );

	// Filter out bad characters.
	if (preg_match('[\*\\\"\'<>%=,\$'.chr(173).']', $vars['name'].$vars['pass']))
		return loginFailed('Your name and/or password contains illegal characters.');# (*\\"\'&lt;&gt;%=,$).');

	if ($empire = getEmpire($vars['name']))
  	{
		if ($empire['password'] == $vars['pass'])
		{
    		$sql='UPDATE empires SET last_login = '.time().', ';
			$sql.='last_ip = "'.$_SERVER['REMOTE_ADDR'].'" ';
			$sql.='WHERE name = "'.$vars['name'].'"';
    		sc_query($sql);

			// Reset the cookie holding the login name for another 24 hours.
			setcookie('sc_login', $vars['name'], (time()+86400), '/');

			// Check to see if this is the completion of an empire creation
			if (preg_match('/^C/', $empire['validation_info']))
			{
				list($code, $newpass) = explode('/', $empire['validation_info']);
				sc_query
				('
					UPDATE empires 
					SET password = "'.$newpass.'", 
					validation_info = "" 
					WHERE name = "'.$vars['name'].'"
				');
				$vars['pass'] = $newpass;
				sendEmpireMessage($empire, 'Empire created.');
				return chooseIconPage($vars);
			}
			else
     			return gameList($vars);
		}
		else 
			return loginFailed('Incorrect password.');
	}
  	else 
	{
	   return createEmpire($vars);
	}
}

#----------------------------------------------------------------------------------------------------------------------#
# For use on the "Create Empire" and "Edit Profile" screens to display the default list of icons.
# The icon count should really be determined dynamically by reading the directory, although there might be a
# performance cost in this.
#

function iconList($icon)
{
	global $server;

	$counter = 0;

	echo '<div style="text-align: center;">';
	for ($i = 1; $i <= $server['icon_count']; $i++)
	{
		if ($counter++ % 20 == 0) echo '<br>';
		
		// We draw a white border around the specified $icon.
		echo '<input style="margin: 1pt;'.
			($icon == 'alien'.$i.'.gif' ? ' border: 1pt solid white;' : '').'" '.
			'type=image src="images/aliens/alien'.$i.'.gif" name="icon_'.$i.'">';
	}
	echo '</div>';
}

//#----------------------------------------------------------------------------------------------------------------------#

function newEmpireLogin($vars)
{
	standardHeader('New Empire Login');
?>
<input type=hidden name="section" value="login">

<div class=pageTitle>New Empire Login</div>
<div class=message>
An email message containing your temporary password has been sent to 
<?php echo $vars['email']; ?>.<br>
Please log in using this password to complete the creation of your empire.
</div>

<img class=spacerule src="images/spacerule.jpg" width="100%" alt="spacerule.jpg">

<div style="text-align: center;">
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">Empire Name:</th>
		<td>
		<input 	type="text" 
				name="name" 
				size=20 
				maxlength=20 
				value="<?php echo $cookie_name; ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Password:</th>
		<td>
		<input	type="password" 
				name="pass" 
				size=20 
				maxlength=20>
		&nbsp;
		<input	type="submit" 
				name="action" 
				value="Login">
		</td>
</table>
</div>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function loginFailed($message)
{
	global $server;

	standardHeader('Login Failure');
?>
<input type=hidden name="section" value="login">

<div class=pageTitle>Login Failed</div>
<div class=message>
<?php echo $message; ?><br>Please try again.
</div>

<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">

<div style="text-align: center;">
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">Empire Name:</th>
		<td>
		<input	type="text" 
				size=20 
				name="name" 
				maxlength=20 
				value="<?php echo sc_cookie('sc_login'); ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Password:</th>
		<td>
		<input	type="password" 
				size=20 
				name="pass" 
				maxlength=20>
		&nbsp;
		<input	type="submit" 
				name="action" 
				value="Login">
		</td>
</table>
</div>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#
# Called when an error occurs with a MySQL query. We die here.
#

function sqlError($rollback_status)
{
	global $server, $updating;

	standardHeader('MySQL Error');

	$rollback = (($rollback_status or $rollback_status == '') ? '<span class=green>rollback successful</span>' : '<span class=red>rollback failed!</span>');
?>
<div class=pageTitle>MySQL Error</div>
<div style="text-align: center;">An error occured while processing your request (<b><?php echo $rollback; ?></b>).
<br>Please <a href="mailto:<?php echo $server['admin_email']; ?>">contact the administrator</a> about this event.</div>
<?php	
	die();
}

#----------------------------------------------------------------------------------------------------------------------#

function iconUploadPage($vars)
{
	global $server;

	$empire = $vars['empire_data'];

	standardHeader('Upload Custom Icon', $empire);
?>
</form>
<form enctype="multipart/form-data" action="sc.php" method=post>
<input type=hidden name=name value="<?php echo $vars['name']; ?>">
<input type=hidden name=pass value="<?php echo $vars['pass']; ?>">
<input type=hidden name=section value="main">
<input type=hidden name=page value="iconUpload">
<div class=pageTitle>
	<?php echo $vars['name']; ?>: Upload Custom Icon
</div>
<?php
	echo drawButtons($empire).
		'<div class=message style="margin-top: 10pt;">
		   Local time and date: '.date('l, F j H:i:s T Y', time()).
		'</div>'.
		empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">
<div style="text-align: center;">
	Click on the <span style="color: white;">Browse...</span> button 
	to select an icon and then the <span style="color: white;">Upload</span> button.<br>
	Icons must be 40 by 40 pixels and weigh no more than 15kB.
</div>
<div style="text-align: center; margin-top: 10pt;">
	<input name="iconToUpload" type=file>
	&nbsp;
	<input type="submit" value="Upload">
</div>
</form>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function chooseIconPage($vars)
{
	global $server;

	$empire = getEmpire($vars['name']);
	
	standardHeader('Choose Icon', $empire);
?>
<div class=pageTitle><?php echo $vars['name']; ?>: Choose Icon</div>
<div>
<input type=hidden name=iconChoice value=yes>
<input type=hidden name=fromEmpireCreation value="<?php echo (array_key_exists('createEmpire', $vars) && strlen($vars['createEmpire']) ? 1 : 0); ?>">
<input type=hidden name=name value="<?php echo $vars['name']; ?>">
<input type=hidden name=pass value="<?php echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="editProfile">

<?php echo drawButtons($empire).serverTime().onlinePlayers().empireMissive($empire); ?>

<img class=spacerule src="images/spacerule.jpg" width="100%">
</div>
<div style="text-align: center;">
   Click on the icon you wish to use to represent your empire.
</div>
<?php
	iconList($empire['icon']);
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function handleIconUpload($vars, $file)
{
	$empire = $vars['empire_data'];

	if (is_uploaded_file($file['iconToUpload']['tmp_name']))
	{
		if ($file['iconToUpload']['size'] > 15*1024)
		{
			sendEmpireMessage($empire, 'This icon file is too large (limit is 15k).');
		}
		else
		{
			$filename = str_replace(' ', '_', $empire['name'].'.gif');
			if (copy($file['iconToUpload']['tmp_name'], 
				'images/aliens/custom/'.$filename))
			{
				sc_query('UPDATE empires SET icon = "custom/'.
								$filename.
								'" WHERE name = "'.$vars['name'].'"');
				sendEmpireMessage($empire, 'Custom icon uploaded successfully.');
			}
			else
			{
				sendEmpireMessage($empire, 
					'An error occured while saving your custom icon.');
			}
		}
	}
	else
		sendEmpireMessage($empire, 'An error occured while uploading your custom icon.');

	return editProfile($vars);
}


#----------------------------------------------------------------------------------------------------------------------#
# Spawns a new empty game for the given series. Usually called after killing a game or ending/closing one.
#

function spawnGame($series_name)
{
	global $server,$mysqli;
	
	$series = getSeriesByName($series_name);

	// Only spawn if the series is not halted.
	// If it is, a new game will be spawned as soon as it is un-halted, if no open ones exist.
	if (!$series['halted'])
		{
		$next_update = time()+$series['update_time'];
  		$last_update = time();

		sc_query('UPDATE series SET game_count = (game_count+1) WHERE id = '.$series['id'], __FILE__.'*'.__LINE__);

		$values = array();
		$values[] = 'series_id = '.$series['id'];
		$values[] = 'game_number = '.($series['game_count']+1);
		$values[] = 'version = "'.$server['version'].'"';
		$values[] = 'last_update  = '.$last_update;
		$values[] = 'update_time  = '.$series['update_time'];
		$values[] = 'weekend_updates  = "'.$series['weekend_updates'].'"';
		$values[] = 'max_allies  = '.((array_key_exists('max_allies', $series) && $series['max_allies']) ? $series['max_allies'] : 'NULL');
		$values[] = 'game_type = \''.$series['game_type'].'\'';

  		sc_query('INSERT INTO games SET '.implode(', ', $values), __FILE__.'*'.__LINE__);

		$game_id = $mysqli->insert_id;

		$result = sc_query('select * from series_ship_type_options where series_id = ' . $series['id']);

		while($line = $result->fetch_assoc()) {
			$values = array();
			$values[] = 'game_id = '.$game_id;
			$values[] = 'ship_type = \''.$line['ship_type'].'\'';
			$values[] = 'status = \''.$line['status'].'\'';
			if (array_key_exists('range_multiplier', $line) && isset($line['range_multiplier']) && strlen(trim($line['range_multiplier'])) > 0) $values[] = 'range_multiplier = '.$line['range_multiplier'];
			$values[] = 'loss = '.$line['loss'];
			$values[] = 'build_cost = '.$line['build_cost'];
			$values[] = 'maintenance_cost = '.$line['maintenance_cost'];

			$sql = 'insert into game_ship_type_options set ' . implode(', ', $values);

			sc_query($sql);
		}
  		
  		return $game_id;
		}
}

#----------------------------------------------------------------------------------------------------------------------#
# Converts a number of seconds into the "W days, X hrs, Y min, Z sec" format, skipping W, X, Y or Z if any are 0.
#

function secondsToString($sec)
{
	if ($days = floor($sec/86400)) $sec -= $days*86400;
	if ($hrs = floor($sec/3600)) $sec -= $hrs*3600;
	if ($min = floor($sec/60)) $sec -= $min*60;

	$chunks = array();

	if ($days) $chunks[] = $days.' day'.($days != 1 ? 's' : '');
	if ($hrs) $chunks[] = $hrs.' hrs';
	if ($min) $chunks[] = $min.' min';
	if ($sec) $chunks[] = $sec.' sec';

	if (!($days + $hrs + $min + $sec)) return '0 sec';

	return (count($chunks) ? implode(', ', $chunks) : 'unknown');
}

#----------------------------------------------------------------------------------------------------------------------#

function recalculateRatios($vars)
{
	$series = $vars['series_data'];
	$game   = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
	
	#########################
	# Ship costs.
	$fields = array();
	$fields[] = 'SUM( IF(orders = "build", build_cost, 0) )'; // Build costs
	$fields[] = 'SUM( IF(orders <> "build", maintenance_cost, 0) )'; // Maintenance
	$fields[] = 'SUM( IF(orders <> "build", fuel_cost, 0) )'; // Fuel use

  	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'owner = "'.$empire['name'].'"';

	$query = 'SELECT '.implode(',', $fields).' FROM ships WHERE '.implode(' AND ', $conditions);

	$select = sc_query($query, __FILE__.'*'.__LINE__);

  	$build = (sc_result($select, 0, 0) ? sc_result($select, 0, 0) : 0);
	$maintenance = (sc_result($select, 0, 1) ? sc_result($select, 0, 1) : 0);
	$fuel_use = (sc_result($select, 0, 2) ? sc_result($select, 0, 2) : 0);

	#########################
	# Resources counts.
	$fields = array();
	$fields[] = 'SUM( IF(population < mineral, population, mineral) )'; // Mineral count
	$fields[] = 'SUM( IF(population < fuel, population, fuel) )'; // Fuel count
	$fields[] = 'SUM( agriculture )';
	$fields[] = 'SUM( population )';
	$fields[] = 'SUM( max_population )';

	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'owner = "'.$empire['name'].'"';

	$select = sc_query('SELECT '.implode(',', $fields).' FROM systems WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

	$mineral = (sc_result($select, 0, 0) ? sc_result($select, 0, 0) : 0);
  	$fuel = (sc_result($select, 0, 1) ? sc_result($select, 0, 1) : 0);
  	$agriculture = (sc_result($select, 0, 2) ? sc_result($select, 0, 2) : 0);
	$population = (sc_result($select, 0, 3) ? sc_result($select, 0, 3) : 0);
	$max_population = (sc_result($select, 0, 4) ? sc_result($select, 0, 4) : 0);

	#########################
	# Trade agreements give a 10% increase per empire.
	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'empire = "'.$empire['name'].'"';
	$conditions[] = 'status > "3"';
	$select = sc_query('SELECT id FROM diplomacies WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
  	$trade = $select->num_rows;

	// Ok, so do trade agreements give a 10% bonus on base resource totals, or effective totals?
	// Additive or multiplicative?
  	$increase = 1.0+($trade*0.10); ## total + (10%*total) + (10%*total) + (10%*total) ... ?
  	#$increase = pow(1.1, $trade); ## total * 110%        * 110%        * 110%	    ... ?

	// Update $player values as well, so we won't need to refetch the player record from the database
	// to determine his economic power.
  	$player['mineral'] = $mineral = floor($mineral*$increase);
  	$player['fuel'] = $fuel = floor($fuel*$increase);
  	$player['agriculture'] = $agriculture = floor($agriculture*$increase);

	$mineral_use = $build+$maintenance;
  	$tech_development = ( ($mineral+$fuel) ? ($mineral+$fuel-$build-$maintenance-$fuel_use)/($mineral+$fuel) : 1 )*$series['tech_multiple'];

	$values = array();
	$values[] = 'build = '.$build;
	$values[] = 'maintenance = '.$maintenance;
	$values[] = 'fuel_use = '.$fuel_use;
	$values[] = 'mineral = '.$mineral;
	$values[] = 'fuel = '.$fuel;
	$values[] = 'agriculture = '.$agriculture;
	$values[] = 'population = '.$population;
	$values[] = 'max_population = '.$max_population;
	$values[] = 'mineral_ratio = '.($mineral_use ? $mineral/$mineral_use : 'NULL');
	$values[] = 'fuel_ratio = '.($fuel_use ? $fuel/$fuel_use : 'NULL');
	$values[] = 'agriculture_ratio = '.($population ? $agriculture/$population : 1);
	$values[] = 'tech_development = '.$tech_development;
	
	sc_query('UPDATE players SET '.implode(',', $values).' WHERE id = '.$player['id'], __FILE__.'*'.__LINE__);

	// We do this last, since the numbers here depend on what was done earlier.
	$economic_power = getEconomicPower($player);
  	$military_power = getMilitaryPower($player);

	// Has there been a change?
  	if ($economic_power > $empire['max_economic_power'] or $military_power > $empire['max_military_power'])
		{
		$fields = array();
		$fields[] = 'max_economic_power = '.max($empire['max_economic_power'], $economic_power);
		$fields[] = 'max_military_power = '.max($empire['max_military_power'], $military_power);
		
		sc_query('UPDATE empires SET '.implode(',', $fields).' WHERE id = '.$empire['id']);
		}

    sc_query('UPDATE players SET economic_power = '.$economic_power.', military_power = '.$military_power.' WHERE id = '.$player['id']);
}

#----------------------------------------------------------------------------------------------------------------------#

function getEconomicPower($player)
{
	return floor( ($player['mineral']+$player['fuel']+$player['agriculture'])/100 );
}

#----------------------------------------------------------------------------------------------------------------------#

function getMilitaryPower($player)
{
	$conditions = array();
	$conditions[] = 'player_id = '.$player['id'];
	$conditions[] = 'orders <> "build"';

	$select = sc_query('SELECT SUM(br*br) FROM ships WHERE '.implode(' AND ', $conditions));

	return floor( sc_result($select, 0, 0)/50 );
}

#----------------------------------------------------------------------------------------------------------------------#
# This function checks all eligible games to see if they need to be updated. In the case where multiple updates
# should have occured by now, we update as many times as needed, faking the temporal progression of events by
# incrementing the time at which the update occured.
#

function checkForUpdates()
{
	// Commit any previous work.
	sc_query('COMMIT');
	
	// Check for holiday breaks and pause long-term games accordingly.
	if (inHolidayBreak())
    	sc_query('UPDATE games SET last_update = UNIX_TIMESTAMP() WHERE update_time > 600');
    
	// Fix update times for non-weekend-updating games, if we are in the weekend.
	if (preg_match('/Sat|Sun/i', date('D', time())))
		sc_query('UPDATE games SET last_update = UNIX_TIMESTAMP() WHERE weekend_updates = "0"');
	
	// Fix update times for paused games. This feature is DISABLED for now.
	// sc_query('UPDATE games SET last_update = '.time().' WHERE on_hold = "1" AND closed = "0"');

	// Fix update times for non-filled/non-closed team games.
	$tables = 'games INNER JOIN series ON games.series_id = series.id';
	$conditions = array();
	$conditions[] = 'series.team_game = "1"';
	$conditions[] = 'games.closed = "0"';
	$conditions[] = 'games.player_count > 0';	
	sc_query('UPDATE '.$tables.' SET games.last_update = UNIX_TIMESTAMP() WHERE '.implode(' AND ', $conditions));

	// Kill stale passworded games.
	checkForOldPasswordedGames();
	
	$fields = array();
	$fields[] = 'games.id';
	$fields[] = 'IF(last_update+update_time < UNIX_TIMESTAMP(), 1, 0) AS update_required';

	// Get the next updateable game's ID.
	$next_updateable_game  = 'SELECT '.implode(',', $fields).' ';
	$next_updateable_game .= 'FROM games ';
	$next_updateable_game .= 'WHERE player_count > 1 ';
	$next_updateable_game .= 'HAVING update_required = 1 ';
	$next_updateable_game .= 'LIMIT 1';
	$select = sc_query($next_updateable_game);

	if ($select->num_rows) { // if have one row of data then we might get more
		sc_query('BEGIN'); // start transaction

		while ($select->num_rows) { // while we still have one more
			// Get the game we need to update and lock that one record FOR UPDATE.
			$row = sc_fetch_assoc($select);
			$select_next_game = sc_query('SELECT * FROM games WHERE id = '.$row['id'].' FOR UPDATE');
			$game = sc_fetch_assoc($select_next_game);

			// Maybe cache this?
			$series = getSeries($game['series_id']);
			
			while ((time()-$game['last_update']) > $game['update_time'])
			{
				// Ok, so the game needs at least one update. And if the game ends in the update, break out of this loop
				// $game is passed to update_game() by reference. When we get back here, it should be updated so
				// we don't need to fetch it again to continue updating if we need to.
				if (update_game($series, $game, ($game['last_update']+$game['update_time'])) == 0)
					break;
			}
		
			sc_query('BEGIN'); // COMMITs the previous game and sets up up for the next one
		
			// Re-issue query to get the next updateable game.
			$select = sc_query($next_updateable_game);
			}
		}
	
	sc_query('BEGIN'); // COMMIT and process the rest of the user's request.
}

#----------------------------------------------------------------------------------------------------------------------#
# This function checks all eligible games to see if they need to be updated. In the case where multiple updates
# should have occured by now, we update as many times as needed, faking the temporal progression of events by
# incrementing the time at which the update occured.
#

function checkForTournamentUpdates()
{
	global $mysqli;
	//find running tournaments
	$sql = 'select * from tournament where starttime < ' . time() . ' AND completed = false';
	$select = sc_query($sql);
	
	while ($tourney = $select->fetch_assoc()) {
		//are there any games?
		$sql = 'select count(*) as numberofgames from tournamentgame where tournament = ' . $tourney['id'];
		$result2 = sc_query($sql);
		$line = $result2->fetch_assoc();
		$numberofgames = $line['numberofgames'];
		
		if ($numberofgames == 0) {
			//setup the first round
			//fetch the series
			$sql2 = 'select * from series where id = ' . $tourney['series'];
			$result2 = sc_query($sql2);
			$series = $result2->fetch_assoc();
			
			//find out how many entrants we have
			$sql2 = 'select te.id as teid, name, e.id as empireid, rand() as r from tournamententrant te, empires e where tournamentid = ' . $tourney['id'] . ' AND te.empireid = e.id order by r';
			$result2 = sc_query($sql2);
			
			$numberOfEntrants = $result2->num_rows;
						
			if ($numberOfEntrants % 2 == 0) { //even
				$i = 0;
			} else { //odd
				//first entrant gets a bye
				$entrant = $result2->fetch_assoc();
				
				$sql3 = 'update tournamententrant set byes = 1 where id = ' . $entrant['teid'];
				sc_query($sql3);
				$i = 1;
			}
			
			for (;$i + 1 < $numberOfEntrants; $i += 2) {
				$firstPlayer = $result2->fetch_assoc();
				$secondPlayer = $result2->fetch_assoc();
				
				//setup the sc game
				$gameid = spawnGame($series['name']);
				$game = getGameByID($gameid);
				
				//first player joins
				$vars['series_data'] = $series;
				$vars['game_data'] = $game;
				$vars['name'] = $firstPlayer['name'];
				joinGame($vars, 0, false, false);
				
				//setup bridier
				$sql3 = "insert ignore into bridier (game_id, series_name, game_number, empire1) values (" . $gameid . ", '" . $series['name'] . "', " . $game['game_number'] .
				", '" . $firstPlayer['name'] . "')";
				sc_query($sql3);
				
				$sql3 = 'UPDATE games SET bridier = 0 WHERE id = '.$game['id'];
				sc_query($sql3);
				
				//second player joins
				//refresh game
				$vars['game_data'] = $game = getGameByID($gameid);
				$vars['name'] = $secondPlayer['name'];
				joinGame($vars, 0, false, false);
				
				//create the tournament game record
				$sql3 = 'insert into tournamentgame (tournament, game, round, firstempire, secondempire) values (' . $tourney['id'] . ', ' . $gameid . ', ' .
					  1 . ", '" . $firstPlayer['name'] . "', '" . $secondPlayer['name'] . "')";
				sc_query($sql3);
			}
		} else {
			//current round?
			$sql2 = 'select max(round) as currentround from tournamentgame where tournament = ' . $tourney['id'];
			$result2 = sc_query($sql2);
			$line = $result2->fetch_assoc();
			$currentround = $line['currentround'];
			
			//is the current round over?
			$sql2 = 'select count(*) as numberofunfinishedgames from tournamentgame where round = ' . $currentround . ' AND tournament = ' . $tourney['id'] . ' AND winner is null';
			$result2 = sc_query($sql2);
			$line = $result2->fetch_assoc();
			$numberofunfinishedgames = $line['numberofunfinishedgames'];
			
			if ($numberofunfinishedgames == 0) {
				//schedule next round
				//fetch the series
				$sql2 = 'select * from series where id = ' . $tourney['series'];
				$result2 = sc_query($sql2);
				$series = $result2->fetch_assoc();
			
				//find out how many entrants we have
				$sql2 = 'select te.id as teid, name, e.id as empireid, rand() as r from tournamententrant te, empires e where tournamentid = ' . $tourney['id'] . ' AND te.empireid = e.id AND eliminated = false order by byes asc, r';
				$result2 = sc_query($sql2);
			
				$numberOfEntrants = $result2->num_rows;

				if ($numberOfEntrants % 2 == 0) { //even
					$i = 0;
				} else { //odd
					//first entrant gets a bye
					$entrant = $result2->fetch_assoc();
				
					$sql3 = 'update tournamententrant set byes = byes + 1 where id = ' . $entrant['teid'];
					sc_query($sql3);
					$i = 1;
				}
			
				for (;$i + 1 < $numberOfEntrants; $i += 2) {
					$firstPlayer = $result2->fetch_assoc();
					$secondPlayer = $result2->fetch_assoc();
				
					//setup the sc game
					$gameid = spawnGame($series['name']);
					$game = getGameByID($gameid);
				
					//first player joins
					$vars['series_data'] = $series;
					$vars['game_data'] = $game;
					$vars['name'] = $firstPlayer['name'];
					joinGame($vars, 0, false, false);
					
					//setup bridier
					$sql3 = "insert ignore into bridier (game_id, series_name, game_number, empire1) values (" . $gameid . ", '" . $series['name'] . "', " . $game['game_number'] .
						  ", '" . $firstPlayer['name'] . "')";
					sc_query($sql3);
					
					$sql3 = 'UPDATE games SET bridier = 0 WHERE id = '.$game['id'];
					sc_query($sql3);
				
					//second player joins
					$vars['name'] = $secondPlayer['name'];
					$vars['game_data'] = $game = getGameByID($gameid);
					joinGame($vars, 0, true, false);
					
					//create the tournament game record
					$sql3 = 'insert into tournamentgame (tournament, game, round, firstempire, secondempire) values (' . $tourney['id'] . ', ' . $gameid . ', ' .
						  ($currentround + 1) . ", '" . $firstPlayer['name'] . "', '" . $secondPlayer['name'] . "')";
					sc_query($sql3);
				}	
			}
		}
		
	}
	
	
	sc_query('BEGIN'); // COMMIT and process the rest of the user's request.
}


#----------------------------------------------------------------------------------------------------------------------#
# Deletes stale passworded games.
#

function checkForOldPasswordedGames()
{
    $fields = array();
    $fields[] = 'series.name as series_name';
    $fields[] = 'series.team_game';
    $fields[] = 'games.game_number';
    $fields[] = 'games.player_count';
    $fields[] = 'games.bridier';
    $fields[] = 'games.id as game_id';

	$tables = 'games INNER JOIN series ON games.series_id = series.id';
    
    // - Passworded regular games with only one player expire after three update times.
    // - Passworded team games that haven't filled expire after five update times.
	//   Note that if they get a second player, they get a weekend extension..
    $conditions = array();
    $conditions[] = '(player_count = 1 AND password1 <> "" AND (UNIX_TIMESTAMP()-created_at) > 3*games.update_time)';
    $conditions[] = '(series.team_game = "1" AND password1 <> "" AND closed = "0" AND (UNIX_TIMESTAMP()-created_at) > 5*games.update_time)';

    $select_staleGames = sc_query('SELECT '.implode(',', $fields).' FROM '.$tables.' WHERE '.implode(' OR ', $conditions));
  	while ($row = sc_fetch_assoc($select_staleGames))
		{
		$message = '<span class=red>'.$row['series_name'].' '.$row['game_number'].'</span> was cancelled because ';

			 if ($row['player_count'] == 1)	$message .=	'no one joined your passworded game in 3 update times.';
		else if ($row['team_game'])			$message .=	'the game did not fill within 5 update times.';

		$tables = 'empires INNER JOIN players ON empires.name = players.name';

		$select_empires = sc_query('SELECT empires.* FROM '.$tables.' WHERE game_id = '.$row['game_id'].' AND team >= 0');
		while ($empire = sc_fetch_assoc($select_empires)) sendEmpireMessage($empire, $message);
		
		// Remove pending Bridier result for cancelled Bridier games.
		if ($row['bridier']) sc_query('DELETE FROM bridier WHERE game_id = '.$row['game_id']);
		
		eraseGame($row['game_id']);
		}
}

#----------------------------------------------------------------------------------------------------------------------#
# Removes the $remove_value value from the $array array. Used in update.php and game/makemap.php.
#

function array_remove($remove_value, $array)
{
	$new_array = array();

	foreach ($array as $element)
		if ($element != $remove_value) $new_array[] = $element;

	unset($array);

	return $new_array;
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns a random element from an array.
#

function array_random($array)
{
	return $array[ rand(0,count($array)-1) ];
}

#----------------------------------------------------------------------------------------------------------------------#

function calculateBridier($winner_rank, $winner_index, $loser_rank, $loser_index)
{
	$rank_delta = $winner_rank-$loser_rank;
	
		 if ($rank_delta <= 0 )  $stake = 10;
	else if ($rank_delta <= 10)  $stake = 9;
	else if ($rank_delta <= 20)  $stake = 8;
	else if ($rank_delta <= 30)  $stake = 7;
	else if ($rank_delta <= 40)  $stake = 6;
	else if ($rank_delta <= 60)  $stake = 5;
	else if ($rank_delta <= 80)  $stake = 4;
	else if ($rank_delta <= 100) $stake = 3;
	else if ($rank_delta <= 140) $stake = 2;
	else if ($rank_delta <= 200) $stake = 1;
	else						 $stake = 0;
	
	$winner_index /= 100;
	$loser_index /= 100;
	
	$winner_result = round(( 1 + 19*$winner_index - $loser_index - 3*$winner_index*$loser_index ) * $stake / 16);
	$loser_result = round(( 1 + 19*$loser_index - $winner_index - 3*$loser_index*$winner_index ) * $stake / 16);
	
	return array($winner_result, $loser_result);
}

#----------------------------------------------------------------------------------------------------------------------#
# Generates a random name of at most 8 characters. Can be used for fleets, ships or systems.
#

function randomName()
{
	$vowels = array('a', 'e', 'i', 'o', 'u', 'y');
	$consonants = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'ph', 'r', 's', 't', 'v', 'w');
	
	$string = '';
	
	for ($x = 1; $x <= 8; $x++)
		$string .= ($x % 2 ? $consonants[array_rand($consonants)] : $vowels[array_rand($vowels)]);
		
	return ucfirst($string);
}

#----------------------------------------------------------------------------------------------------------------------#
# Imports all of $other_player's explored planets to $ignorant_player's list. This is used for the Shared HQ feature.
#

function importExplored($ignorant_player, $other_player)
{
	// First, delete existing Shared HQ data coming from the other player.
	// The from_shared_hq field contains the player ID of the contributing player.
	sc_query('DELETE FROM explored '.
					'WHERE player_id = "'.$ignorant_player['id'].'" '.
					'AND from_shared_hq = '.$other_player['id']);
	
	// The only thing we're importing is planets that the other player has explored himself. We are excluding intelligence
	// he obtained from other potential Shared HQ friends. Hence the 'from_shared_hq = 0' condition.
	$select = sc_query(   'SELECT * FROM explored '.
								'WHERE player_id = '.$other_player['id'].' '.
								'AND from_shared_hq = 0');
	while ($explored = sc_fetch_assoc($select))
		{
		// First, delete any scouting reports for this planet, or they'll overwrite the explored record on the map.
		sc_query( 'DELETE FROM scouting_reports '.
						'WHERE player_id = '.$ignorant_player['id'].' '.
						'AND coordinates = "'.$explored['coordinates'].'"');
		
		$values = array();
		$values[] = 'series_id = '.$explored['series_id'];
		$values[] = 'game_number = '.$explored['game_number'];
		$values[] = 'game_id = '.$explored['game_id'];
		$values[] = 'empire = "'.$ignorant_player['name'].'"';
		$values[] = 'player_id = "'.$ignorant_player['id'].'"';
		$values[] = 'coordinates = "'.$explored['coordinates'].'"';
		$values[] = 'from_shared_hq = '.$other_player['id'];

		sc_query('INSERT IGNORE INTO explored SET '.implode(',', $values));
		}
}

#----------------------------------------------------------------------------------------------------------------------#
# Add explored record ID $explored_id to $player's friends.
# This is used to add a planet to Shared HQ friends during exploration.
#

function addExploredToFriends($player, $explored_id)
{
	// Exit if this ID is somehow invalid.
	if (!$explored = getExploredByID($explored_id)) return;

	$select = sc_query(	'SELECT opponent '.
								'FROM diplomacies '.
								'WHERE game_id = '.$player['game_id'].' '.
								'AND empire = "'.$player['name'].'" '.
								'AND status = "6"');
	while ($row = sc_fetch_assoc($select))
		{
		$recipient = getPlayer($player['game_id'], $row['opponent']);

		// First we get rid of an existing record that was contributed by this player.
		$conditions = array();
		$conditions[] = 'player_id = "'.$recipient['id'].'"';
		$conditions[] = 'coordinates = "'.$explored['coordinates'].'"';
		$conditions[] = 'from_shared_hq = "'.$player['id'].'"';
		
		sc_query('DELETE FROM explored WHERE '.implode(' AND ', $conditions));
			
		$values = array();
		$values[] = 'series_id = '.$explored['series_id'];
		$values[] = 'game_number = '.$explored['game_number'];
		$values[] = 'game_id = '.$explored['game_id'];
		$values[] = 'empire = "'.$recipient['name'].'"';
		$values[] = 'player_id = "'.$recipient['id'].'"';
		$values[] = 'coordinates = "'.$explored['coordinates'].'"';
		$values[] = 'from_shared_hq = '.$player['id'];

		sc_query('INSERT IGNORE INTO explored SET '.implode(',', $values));
		}
}

#----------------------------------------------------------------------------------------------------------------------#
# For player $player_1, convert all the Shared HQ data coming from $player_2 to scouting reports.
# This is done when one downgrades from "Shared HQ".
#

function convertSharedHQToScoutingReports($series, $update, $player_1, $player_2_name)
{
	$player_2 = getPlayer($player_1['game_id'], $player_2_name); //use name to return row from player table 
	                                                                                                    //but only use the player_id
	$fields = array();
	$fields[] = 'systems.game_number';
	$fields[] = 'systems.coordinates';
	$fields[] = 'systems.jumps';
	$fields[] = 'systems.name';
	$fields[] = 'systems.owner';
	$fields[] = 'systems.mineral';
	$fields[] = 'systems.fuel';
	$fields[] = 'systems.agriculture';
	$fields[] = 'systems.population';
	$fields[] = 'systems.annihilated';

	$from = 'explored INNER JOIN systems ON explored.game_id = systems.game_id AND explored.coordinates = systems.coordinates';

	$conditions = array();
	$conditions[] = 'explored.empire = "'.$player_1['name'].'"';
	$conditions[] = 'explored.from_shared_hq = '.$player_2['id'];

	$select = sc_query('SELECT '.implode(',', $fields).' FROM '.$from.' WHERE '.implode(' AND ', $conditions));
	while ($system = sc_fetch_assoc($select))
		{
		$values = array();
		$values[] = 'player_id = '.$player_1['id'];
		$values[] = 'coordinates = "'.$system['coordinates'].'"';
		$values[] = 'jumps = "'.$system['jumps'].'"';
		$values[] = 'name = "'.$system['name'].' ['.$update.']"';
		$values[] = 'mineral = "'.$system['mineral'].'"';
		$values[] = 'fuel = "'.$system['fuel'].'"';
		$values[] = 'agriculture = "'.$system['agriculture'].'"';
		$values[] = 'population = "'.$system['population'].'"';
		$values[] = 'annihilated = "'.$system['annihilated'].'"';
		$values[] = 'comment = "Previously explored through Shared HQ."';

		if (strlen($system['owner']))
			$values[] = 'owner = "'.$system['owner'].'"';

		$ship_inventory = array();
		$population_adjustement = 0;
		$conditions = array();
		$conditions[] = 'series_id = '.$series['id'];
		$conditions[] = 'game_number = '.$system['game_number'];
		$conditions[] = 'location = "'.$system['coordinates'].'"';
		$ship_select = sc_query('SELECT * FROM ships WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__  );
	
		while ($ship = sc_fetch_assoc($ship_select))
			{
			// Skip this ship if it's building
			if (!$series['visible_builds'] and $ship['orders'] == 'build')
				{
				if ($ship['type'] == 'Colony') $population_adjustement += 1;
				continue;
				}
	
			// Don't show any cloaked ships
			if ( !$ship['cloaked'] )
				$ship_inventory[$ship['owner']][$ship['type']]++;
			}
			
		$type = array();
		foreach (array_keys($ship_inventory) as $owner)
			foreach(array_keys($ship_inventory[$owner]) as $ship_type)
				$type[$ship_type] = true;
		
		$ships = '';
		foreach (array_keys($ship_inventory) as $owner)
			{
			$ships .= $owner.': ';
			foreach (array_keys($type) as $ship_type)
				if ($type[$ship_type])
					$ships .= ($ship_inventory[$owner][$ship_type] ? 
									'<font class=white>'.$ship_type.'</font> ('.$ship_inventory[$owner][$ship_type].') ' : '');

			$ships .= '<p>';
			}
		
		if (strlen($ships))
			$values[] = 'ships = "'.urlencode($ships).'"';
		
		sc_query('DELETE FROM scouting_reports WHERE player_id = '.$player_1['id'].' AND coordinates = "'.$system['coordinates'].'"', __FILE__.'*'.__LINE__);
		sc_query('INSERT INTO scouting_reports SET '.implode(',', $values), __FILE__.'*'.__LINE__);
		}
		
	sc_query('DELETE FROM explored WHERE player_id = '.$player_1['id'].' AND from_shared_hq = '.$player_2['id'], __FILE__.'*'.__LINE__);
}

#----------------------------------------------------------------------------------------------------------------------#
# The way this works is to loop through all the offers and take the highest number offered
# This means one vote for WAR outweighs everything else and one vote for DRAW outweighs surrender votes
# The team will only SURRENDER if all players agree, or DRAW if all players offer SURRENDER or DRAW.
#

function getTeamDiplomacy($game)
{
	$team_offer = array();
	
	// $team specifies the team being offered TO-- i.e. the opposing team from the emp making the offer
	for ($team = 1; $team <= 2; $team++)
		{
		$team_offer[$team] = 0;	// start at surrender-- we'll end up using the *highest* value found
		
		$select = sc_query('SELECT offer FROM diplomacies WHERE game_id = '.$game['id'].' AND opponent = "=Team'.$team.'="');
		while ($diplomacy = sc_fetch_assoc($select))
			if ($diplomacy['offer'] > $team_offer[$team]) $team_offer[$team] = $diplomacy['offer'];
		}
	
	return $team_offer;
}

#----------------------------------------------------------------------------------------------------------------------#

function messageHeader($message)
{
	$header = '<span class=messageHeader>'.$message['sender'];
	#$header_pre = '<pre class=messageHeader>'.$message['sender'];
	$time = date('D M j H:i:s T Y', $message['time']);

	// This condition here should be temporary, as the 'recipient' field was introduced later on for
	// private messages and scouting reports. It can be removed for new servers.
	$recipient = ((array_key_exists('recipient', $message) && $message['recipient']) ? 'to '.$message['recipient'].' ' : '');

	switch ($message['type'])
		{
		case 'broadcast':    return $header .= ' broadcasted at '.$time.':</span><div class=messageText>';
		case 'team':	     return $header .= ' sent the following message to your team at '.$time.':</span><div class=messageTextTeam>';
		case 'private':      return $header .= ' sent the following message '.$recipient.'at '.$time.':</span><div class=messageTextPrivate>';
		case 'scout':        return $header .= ' sent the following scouting report '.$recipient.'at '.$time.':</span><div class=scoutReport>';
		case 'update':       return $header .= ' occurred at '.$time.'.</span><div class=updateText>';
		case 'instant':      return $header .= ' sent you the following message at '.$time.':</span><div class=messageText>';
		case 'game_message': return $header = '<div class=gameMessage>';
		}
}

#----------------------------------------------------------------------------------------------------------------------#

function formatMessage($message)
{
	return messageHeader($message).wordwrap(stripslashes(urldecode($message['text'])), 90).'</div>';
}

#----------------------------------------------------------------------------------------------------------------------#

function sanitizeString($string)
{
	return addslashes(htmlspecialchars($string));
}

#----------------------------------------------------------------------------------------------------------------------#
# Pop-up window, describing a series on the game list screen.
#

function seriesParameters($series_id)
{
	global $server;
	
	$series = getSeries($series_id);
	
	$update_time = ($series['update_time'] % 3600 == 0 ? ($series['update_time']/3600).' hours' : ($series['update_time']/60).' minutes');

	switch ($series['map_type'])
		{
		case 'standard': 	$map = 'Classic';						break;
		case 'prebuilt':	$map = 'Prebuilt (random placement)';	break;
		case 'twisted':		$map = 'Twisted';						break;
		case 'mirror':		$map = 'Mirror';						break;
		case 'balanced':	$map = 'Balanced';						break;
		}

	$average_resources = 'Minerals: '.$series['avg_min'].'<br>Fuel: '.$series['avg_fuel'].'<br>Agriculture: '.$series['avg_ag'];
	
	if (!$series['min_wins'] and $series['max_wins'] == -1)
		$wins = 'Anyone can join';
    else if ($series['max_wins'] == -1)
    	$wins = $series['min_wins'].' or more wins needed to join';
    else
    	$wins = 'From '.$series['min_wins'].' to '.$series['max_wins'].' wins needed to join';
    
    switch ($series['diplomacy'])
		{
		case 2: $diplomacy = ' Truce, trade and alliances not allowed';	break;
		case 3: $diplomacy = ' Trade and alliances not allowed';		break;
		case 4: $diplomacy = ' No alliances allowed';					break;
		case 5:
		case 6: $diplomacy = ' Alliances allowed';						break;
		}
	
	standardHeader($series['name']);
?>
<style type="text/css">th { vertical-align: top; text-align: right; color: white; }</style>

<div style="color: red; font-size: 14pt;"><?php echo $series['name']; ?></div>
<?php echo ($series['custom'] == 'yes' ? '<div style="color: white; font-size: 8pt;">Created by '.$series['creator'].'</div>' : ''); ?>

<table cellspacing=10 style="margin-top: 10pt;">
	<tr>
		<th>Updates:</th>
		<td>Every <?php echo $update_time.(!$series['weekend_updates'] ? ', no weekend updates' : ''); ?></td>
	</tr>
	<tr>
		<th>Game Type:</th>
		<td><?php echo $series['game_type']?></td>
	</tr>
	<tr>
		<th>Visible Builds:</th>
		<td><?php echo $series['visible_builds'] ? 'Yes' : 'No'; ?></td>
	</tr>
	<tr>
		<th>Maximum players:</th>
		<td><?php echo $series['max_players'].' players'; ?></td>
	</tr>
	<tr valign=top>
		<th>Systems per player:</th>
		<td><?php echo $series['systems_per_player']; ?></td>
	</tr>
	<tr>
		<th>Tech development:</th>
		<td><?php echo number_format($series['tech_multiple'], 2, '.', ''); ?></td>
	</tr>
	<tr>
		<th>Map type:</th>
		<td><?php echo $map.(!$series['map_visible'] ? ', hidden until game starts' : '' ); ?></td>
	</tr>
	<tr>
		<th>Diplomacy:</th>
		<td><?php echo $diplomacy; ?></td>
	</tr>
	<tr>
		<th>Alliance limit:</th>
		<td><?php echo (array_key_exists('max_allies', $series) ? $series['max_allies'].' allies' : 'none'); ?></td>
	</tr>
	<tr>
		<th>Win restrictions:</th>
		<td><?php echo $wins; ?></td>
	</tr>
	<tr>
		<th>Team game:</th>
		<td><?php echo ($series['team_game'] ? '<span class=green><b>Yes</b></span>' : 'No'); ?></td>
	</tr>
	<tr>
		<th>Shared HQ:</th>
		<td><?php echo ($series['diplomacy'] == 6 ? '<span class=blue><b>Yes</b></span>' : 'No'); ?></td></tr>
	<tr>
		<th>Average resources:</th>
		<td><?php echo $average_resources; ?></td>
	</tr>
	<tr>
		<th>Cloakers:</th>
		<td><?php echo ($series['build_cloakers_cloaked'] ? 'Built cloaked. '.($series['cloakers_as_attacks'] ? 'Appear as attacks.' : '') : 'Built uncloaked'); ?></td>
	</tr><?php
	
	if ($series['game_type'] == 'sc3') {
		foreach ($series['ship_type_options'] as $ship_type_options) {
			if ($ship_type_options['ship_type'] == 'Jumpgate') {?>
				<tr>
					<th>Jumpgates:</th>
					<td><?php echo $ship_type_options['status']; ?></td>
				</tr>
				<tr>
					<th>Jumpgate Range:</th>
					<td>
						<?php if (array_key_exists('range_multiplier', $ship_type_options) && !is_null($ship_type_options['range_multiplier']) && strlen(trim($ship_type_options['range_multiplier'])) > 0) {
							echo $ship_type_options['range_multiplier'] . ' x BR';
	 					} else {
							echo 'Infinite';
						}?>
					</td>
				</tr>
				<tr>
					<th>Jumpgate Loss:</th>
					<td><?php echo $ship_type_options['loss']; ?></td>
				</tr>
				<tr>
					<th>Jumpgate Build Cost:</th>
					<td><?php echo $ship_type_options['build_cost']; ?></td>
				</tr>
				<tr>
					<th>Jumpgate Maintenance Cost:</th>
					<td><?php echo $ship_type_options['maintenance_cost']; ?></td>
				</tr>
			<?php }
		}
	}

?></table>
<?php
}

#----------------------------------------------------------------------------------------------------------------------#

function sendEmpireMessage($empire, $message)
{
	$values = array();
	$values[] = 'time = '.time();
	$values[] = 'empire_id = '.$empire['id'];
	$values[] = 'text = "'.addslashes($message).'"';

	sc_query('INSERT INTO messages SET '.implode(',', $values));
}

#----------------------------------------------------------------------------------------------------------------------#

function serverTime()
{
	return '<div class=message style="margin-top: 10pt;">Local time and date: '.date('Y l, F j ,  H:i:s T ', time()).'</div>';
}

#----------------------------------------------------------------------------------------------------------------------#

function inHolidayBreak()
{
	return (time() > mktime(0, 0, 0, 4, 1, 2005) and time() < mktime(23, 59, 59, 4, 10, 2005));
}

#=--------------------------------------------------------------------------------------=#
# Returns the current UNIX time, with microsecond precision.

function utime()
{
	list($microseconds, $seconds) = explode(' ', microtime());

	return number_format($microseconds+(double)$seconds, 6, '', '');
}
