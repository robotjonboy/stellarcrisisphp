<?
require('../server.php');
require('../debug.php');
require('../sql.php');
#----------------------------------------------------------------------------------------------------------------------#
require('createSeries.php');
require('dbCleanup.php');
require('editMessage.php');
require('editSeries.php');
require('haltSeries.php');
require('killGame.php');
require('killSeries.php');
require('resumeSeries.php');
#----------------------------------------------------------------------------------------------------------------------#

ini_set('memory_limit', '128M');

// Report all errors except E_NOTICE, with our own error handler.
error_reporting(E_ALL ^ E_NOTICE);
set_error_handler('sc_errorHandler');

// Seed the pseudo-random number generator.
srand((double)microtime()*1000000);

// Prevent people from crapping up the database (and their games) by interrupting form processing.
ignore_user_abort(true);

// Start timing the execution of the user's request. This ends in the footer (see footer()).
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

// Check to see how the submitter can be authenticated.
$authenticated = false;
$authenticated_as_admin = false;
if (isset($_POST['name']) and isset($_POST['pass']))
	{
	$sql  = 'SELECT name, is_admin FROM empires ';
	$sql .= 'WHERE name = "'.$_POST['name'].'" ';
	$sql .= 'AND password = "'.$_POST['pass'].'"';
	$select = sc_mysql_query($sql);

	if ($authenticated = mysql_num_rows($select))
		{
		$authenticated_as_admin = mysql_result($select, 0, 1);
		
		// Make sure the name is capitalized correctly in the $_POST array from now on 
		// by taking the value from the database.
		$_POST['name'] = mysql_result($select, 0, 0);
		}
	}
 
	adminAction($_POST);
	
	// Processing ends here. Commit whatever we've done.
	mysql_query('COMMIT');  //cjp stop commit on pop-up - adjust nesting


function adminAction($vars)
{
	global $authenticated_as_admin;
		
	if (!$authenticated_as_admin)
		return loginFailed('Identity check failed.');
	
	$empire = getEmpire($vars['name']);
	$vars['empire_data'] = $empire;

	//admin menu items
	switch ($vars['page'])
		{
		case 'main':			mainPage_admin_processing($vars);	break;
		case 'createSeries':	createSeries_processing($vars);		break;
		case 'editSeries':		editSeries_processing($vars);		break;
		case 'killGame':		killGame_processing($vars);			break;
		case 'killSeries':		killSeries_processing($vars);		break;
		case 'haltSeries':		haltSeries_processing($vars);		break;
		case 'resumeSeries':	resumeSeries_processing($vars);		break;
		case 'dbClean':			dbClean_processing($vars);			break;
		case 'editMessage':		editMessage_processing($vars);		break;
		case 'importProdData':	importProdData_processing($vars);	break;
		case 'viewErrs':		viewErrors($vars);					break;
		}
}

#----------------------------------------------------------------------------------------------------------------------#

function viewErrors($vars)
{
	echo "<a href='errorlog.html'> View Errors </a>";
}
function mainPage_admin($vars)
{
	$empire = $vars['empire_data'];

	standardHeader('Admin', $empire);

	echo "<div class=pageTitle>".$empire['name']." : Administration</div>";
?>
<input type=hidden name="name" value="<? echo $empire['name']; ?>">
<input type=hidden name="pass" value="<? echo $empire['password']; ?>">
<input type=hidden name="section" value="admin">
<input type=hidden name="empireID" value="<? echo $vars['empireID']; ?>"><!--cjp-->
<input type=hidden name="page" value="main">

<?
	echo drawButtons($empire).serverTime().onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%">

<div style="text-align: center;">
<select name="adminAction" onChange="document.forms[0].go.disabled = (document.forms[0].adminAction.value == 0)">
	<option value=0>Admin action...
	<option value="createSeries">Create a new series
	<option value="editSeries">Edit series
	<option value="haltSeries">Halt series
	<option value="resumeSeries">Resume series
	<option value="killSeries">Kill series
	<option value="killGame">Kill game
	<option value="dbClean">Database cleanup
	<option value="editMOTD">Edit message of the day
	<option value="editPrivacy">Edit privacy policy
	<option value="editPolicies">Edit policy statement
	<option value="editTOS">Edit terms of service
	<option value="editNews">Edit server news
	<option value="viewErrs">View Errors
</select>
<input type=submit name="go" value="Go" disabled>
</div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function mainPage_admin_processing($vars)
{
	switch ($vars['adminAction'])
		{
		case 'createSeries':	return createSeries($vars);
		case 'editSeries':		return editSeries($vars);
		case 'haltSeries':		return haltSeries($vars);
		case 'resumeSeries':	return resumeSeries($vars);
		case 'killSeries':		return killSeries($vars);
		case 'killGame':		return killGame($vars);
		case 'dbClean':			return dbClean($vars);
		case 'editMOTD':
			$vars['message_type'] = 'motd';
			return editMessage($vars);
		case 'editPrivacy':
			$vars['message_type'] = 'privacy';
			return editMessage($vars);
		case 'editPolicies':
			$vars['message_type'] = 'policy';
			return editMessage($vars);
		case 'editTOS':
			$vars['message_type'] = 'tos';
			return editMessage($vars);
		case 'editNews':
			$vars['message_type'] = 'news';
			return editMessage($vars);
		case 'viewErrs':		return viewErrors($vars);
		}

	sendEmpireMessage($vars['empire_data'], 'Invalid action.');	
	return mainPage_admin($vars);
}
?>
