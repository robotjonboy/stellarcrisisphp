<?
#-----------------------------------------------------------------------------------------------------------------------------------------#
require_once('createEmpire.php');
require_once('gameList.php');
require_once('editProfile.php');
require_once('customSeries.php');
require_once('statViewer.php');
require_once('gameHistory.php');
require_once('tournaments.php');
#-----------------------------------------------------------------------------------------------------------------------------------------#

function mainAction($vars, $file)
{
	global $authenticated;

	if (!$authenticated and $vars['page'] != 'createEmpire')
		return loginFailed('Identity check failed.');
		
	$empire = getEmpire($vars['name']);
	$vars['empire_data'] = $empire;
	
	switch ($vars['action'])
		{
		case 'Password Games':	return passwordGameList($vars);
		case 'Custom Series':	return customSeries($vars);
		case 'Edit Profile':	return editProfile($vars);
		case 'Stat Viewer':	return statViewer($vars);
		case 'Game List':		return gameList($vars);
		case 'Game History':	return gameHistory($vars);
		case 'Administration':	return mainPage_admin($vars);
		case 'Tournaments':	return tournaments($vars);
		case 'Logout':		return mainPage();
		case 'View':		return viewTournament($vars);
		}
		
	switch ($vars['page'])
		{
		case 'gameList':
		case 'passwordGameList':	gameList_processing($vars);			break;
		case 'gameHistory':		gameHistory_processing($vars);		break;
		case 'statViewer':		statViewer_processing($vars);			break;
		case 'editProfile':		editProfile_processing($vars);		break;
		case 'iconUpload':		handleIconUpload($vars, $file);		break;
		case 'instantMessage':		sendMessage_processing($vars);		break;
		case 'customSeries':		customSeries_processing($vars);		break;
		case 'createCustomSeries':	createCustomSeries_processing($vars);	break;
		case 'createEmpire':		createEmpire_processing($vars);		break;
		case 'tournaments':		tournaments_processing($vars);		break; //tournaments.php
		}
}
?>
