<?
#----------------------------------------------------------------------------------------------------------------------#
require('createSeries.php');
require('dbCleanup.php');
require('editMessage.php');
require('editSeries.php');
require('haltSeries.php');
require('killGame.php');
require('killSeries.php');
require('resumeSeries.php');
require('addTournament.php');
#----------------------------------------------------------------------------------------------------------------------#

function adminAction($vars)
{
	global $authenticated_as_admin;
		
	if (!$authenticated_as_admin)
		return loginFailed('Identity check failed.');
	
	$empire = getEmpire($vars['name']);
	$vars['empire_data'] = $empire;

	//standard menu bar
	switch ($vars['action'])
		{
		case 'Password Games':	return passwordGameList($vars);
		case 'Custom Series':	return customSeries($vars);
		case 'Edit Profile':	return editProfile($vars);
		case 'Stat Viewer':		return statViewer($vars);
		case 'Game List':		return gameList($vars);
		case 'Game History':	return gameHistory($vars);
		case 'Administration':	return mainPage_admin($vars);
		case 'Logout':			return mainPage();
		}

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
		case 'viewErrs':		viewErrors($vars);			break;
		case 'addTournament':	addTournament_processing($vars);	break;
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
	<option value="addTournament">Add Tournament
	<option value="editTournament">Edit Tournament
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
		case 'addTournament':	return addTournament($vars);
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
