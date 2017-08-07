<?
function createEmpire($vars, $message = '')
{
	standardHeader('New Empire');
?>
<input type=hidden name=name value="<? echo $vars['name']; ?>">
<input type=hidden name=pass value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="createEmpire">

<div class=pageTitle>Create a New Empire</div>

<div style="margin-top: 10pt;">Please enter your password again to make sure it wasn't mistyped before, then click on the <span style="font-weight: bold; font-style: italic;">Create empire...</span> button to chosse an icon to represent you (or you can click on <span style="font-weight: bold; font-style: italic;">Cancel</span> if you don't want to create this empire). Real name and e-mail address are optional, and will appear in the player listings.</div>

<img class=spacerule src="images/spacerule.jpg" width="100%" height=10>

<div class=messageBold>Info for <? echo $vars['name']; ?></div>

<div style="text-align: center;">
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">Verify Password:</th>
		<td style="text-align: left;"><input type=password name="pass_check" size=20 maxlength=20></td>
	</tr>
	<tr>
		<th style="text-align: right;">Real Name:</th>
		<td style="text-align: left;"><input type=text name="real_name" size=20></td>
	</tr>
	<tr>
		<th style="text-align: right;">E-mail:</th>
		<td style="text-align: left;"><input type=text name="email" size=20></td>
	</tr>
	<tr>
		<th style="text-align: right; vertical-align: top;">Comments:</th>
		<td style="text-align: left;"><textarea name="comment" rows=5 cols=40></textarea></td>
	</tr>
</table>

<table style="margin-left: auto; margin-right: auto;">
	<tr style="vertical-align: top;">
		<td><input type=submit name=createEmpire value="Create empire..."></td>
		<td><input type=submit name=cancelCreation value="Cancel"></td>
	</tr>
</table>
</div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function createEmpire_processing($vars)
{
	global $server, $authenticated, $HTTP_SERVER_VARS;

	// If the person reloads the screen resulting from empire creation, go to the game list instead of triggering
	// a duplicate insert error.
	if ($empire = getEmpire($vars['name']))
		return gameList($vars);
		
	if ($vars['name'] == '' or $vars['pass'] == '')
		return loginFailed('You must enter a name and a password.');

	// Prevent people from faking empire names with extra spaces.
	$vars['name'] = ereg_replace('[[:space:]]+', ' ', $vars['name']);
	$vars['name'] = trim($vars['name']);

	// Filter out bad charactres.
	if (ereg('[\*\\\"\'<>%=]', $vars['name'].$vars['pass']))
		return loginFailed('Your name and/or password contains illegal characters (*, \\, \', \", &lt;, &gt;, %, =).');
	
	if ($server['require_valid_email'])
		{
		$result = mysql_query( 'SELECT * FROM blockedemail WHERE email="'.$vars['email'].'"' );
		
		if (mysql_num_rows($result))
			return createEmpire($vars, 'You have entered an email address that is blocked by our system.');
		if (preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+))*$/", $vars['email']) == 0)
			return createEmpire($vars, 'Invalid email address entered.');
		if ($vars['email'] != $vars['email2'])
			return createEmpire($vars, 'The email addresses you entered do not match.');
		}

	// Check terms of service - just stop if they didn't accept them
	if ($server['require_tos'])
		{
		if ( !$vars['accept_tos'])
			{
			echo 'We\'re sorry you don\'t agree with our terms of service. Thank you for your interest.';
			return;
			}
		}
	
	if ($vars['cancelCreation'])
	    return mainPage();

	if ($vars['pass'] == $vars['pass_check'])
		{
		$values = array();
		$values[] = 'name = "'.$vars['name'].'"';
		$values[] = 'real_name = "'.$vars['real_name'].'"';
		$values[] = 'email = "'.$vars['email'].'"';
		$values[] = 'email_visible = "0"';
		$values[] = 'icon = "alien1.gif"';
		$values[] = 'wins = 0';
		$values[] = 'nukes = 0';
		$values[] = 'nuked = 0';
		$values[] = 'ruined = 0';
		$values[] = 'max_economic_power = 0';
		$values[] = 'max_military_power = 0';
		$values[] = 'last_login = '.time();
		$values[] = 'last_ip = "'.$HTTP_SERVER_VARS['REMOTE_ADDR'].'"';
		$values[] = 'join_date = "'.time().'"';
		$values[] = 'tos_accepted = "'.($vars['accept_tos'] ? 1 : 0).'"';
		
		if ($server['require_valid_email'])
			{
			$temp_pass = randomString();
			
			$values[] = 'password = "'.$temp_pass.'"';
			$values[] = 'validation_info = "C/'.$vars['pass'].'"';
			
			$mailsent = mail($vars['email'], $server['servername']." Empire Creation", 
							 "Welcome to ".$server['servername']."! To complete the creation of your empire\n".
							 "please log in to the server using this temporary password:\n\n".$temp_pass."\n\n".
							 "We recommend using cut and paste to enter this password into your browser \n".
							 "as it is both case and numeral sensitive. Once you successfully login, your \n".
							 "password will be changed to the password you initally selected. You should then\n".
							 "select a player icon to represent you in the game.\n\n".
							 "If you have received this email in error, we apologize. If you have not recently\n".
							 "attempted to register on our server, you can ignore this email. To block future emails from\n".
							 "our server, please visit the following web page: \n\n".$server['server_URL']."?blockEmail=".$vars['email']."&tpass=".$temp_pass."\n\n".
							 "(Blocking your email address may prevent you from registering with us in the future.)\n\nThank You\n");
			
			if (!$mailsent)
				return loginFailed('Unable to send mail to '.$vars['email'].'.');
			}
		else
			{
			$values[] = 'password = "'.$vars['pass'].'"';
			$values[] = 'validation_info = ""';
			}
		
		$sql='INSERT INTO empires SET '.implode(', ', $values);
		sc_mysql_query($sql);
		
		if ($server['require_valid_email'])
			return newEmpireLogin($vars);
		else
			{
			sendEmpireMessage(getEmpire($vars['name']), 'Empire created.');
			return chooseIconPage($vars);
			}
		}
  	else 
  		return loginFailed('Your password did not match the first one you typed in.');
}

#----------------------------------------------------------------------------------------------------------------------#

function randomString()
{
	return substr(md5(rand()), 0, 8);
}

#----------------------------------------------------------------------------------------------------------------------#
# Adds an email address to a block list and deletes the related empire.
function blockEmail($address, $tpass)
{
	standardHeader('Email Address Blocking');
	
	$select = sc_mysql_query('SELECT * FROM empires WHERE email = "'.$address.'" AND NOT validation_info REGEXP "^C"');
	
	if (mysql_num_rows($select) > 0)
		echo 'You may not block the address of a registered empire.';
	else
		{
		$select = sc_mysql_query('SELECT * FROM empires WHERE email = "'.$address.'" AND password = "'.$tpass.'"');
		if (!mysql_num_rows($select))
			echo 'Invalid temporary password.'; 
		else
			{
			$empire = mysql_fetch_array($select);
			
			sc_mysql_query('DELETE FROM empires WHERE email = "'.$address.'"');
			
			$result = mysql_query('SELECT * FROM blockedemail WHERE email="'.$address.'"');
			
			if (!mysql_num_rows($result))
				sc_mysql_query('INSERT INTO blockedemail SET email = "'.$address.'", time = '.time().', empname = "'.$empire['name'].'"');
			
			echo $address.' added to our blocked email list.';
			}
		}

	footer();
}
?>
