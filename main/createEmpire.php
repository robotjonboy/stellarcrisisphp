<?php
function createEmpire($vars, $message = '')
{
	standardHeader('New Empire');
?>
<input type=hidden name=name value="<?php echo $vars['name']; ?>">
<input type=hidden name=pass value="<?php echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="createEmpire">

<div class=pageTitle>Create a New Empire</div>

<div style="margin-top: 10pt;">Please enter your password again to make sure it wasn't mistyped before, then click on the <span style="font-weight: bold; font-style: italic;">Create empire...</span> button to chosse an icon to represent you (or you can click on <span style="font-weight: bold; font-style: italic;">Cancel</span> if you don't want to create this empire). Real name and e-mail address are optional, and will appear in the player listings.</div>

<img class=spacerule src="images/spacerule.jpg" width="100%" height=10>

<div class=messageBold>Info for <?php echo $vars['name']; ?></div>

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
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function createEmpire_processing($vars)
{
	global $server, $authenticated, $authenticated_as_admin, $auth_response_token, $mysqli;

	// If the person reloads the screen resulting from empire creation, go to the game list instead of triggering
	// a duplicate insert error.
	if ($empire = getEmpire($vars['name']))
		return gameList($vars);
		
	if ($vars['name'] == '' or $vars['pass'] == '')
		return loginFailed('You must enter a name and a password.');

	// Prevent people from faking empire names with extra spaces.
	$vars['name'] = preg_replace('/[[:space:]]+/', ' ', (string)$vars['name']);
	$vars['name'] = trim($vars['name']);
	$vars['pass'] = (string)$vars['pass'];

	// Re-check after trim; also enforce DB column sizes. Non-strict MySQL will
	// silently truncate varchar(20) on INSERT, then getEmpire($fullName) returns null.
	if ($vars['name'] === '' or $vars['pass'] === '')
		return loginFailed('You must enter a name and a password.');
	if (strlen($vars['name']) > 20 or strlen($vars['pass']) > 20)
		return loginFailed('Empire name and password must be 20 characters or fewer.');

	// Filter out bad charactres.
	if (preg_match('/[\*\\\"\'<>%=]/', $vars['name'].$vars['pass']))
		return loginFailed('Your name and/or password contains illegal characters (*, \\, \', \", &lt;, &gt;, %, =).');
	
	if ($server['require_valid_email'])
		{
		$result = sc_query('SELECT * FROM blockedemail WHERE email="'.sc_escape($vars['email']).'"');
		
		if ($result->num_rows)
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
	
	if (array_key_exists('cancelCreation', $vars) && $vars['cancelCreation'])
	    return mainPage();

	if ($vars['pass'] == $vars['pass_check'])
		{
		$real_name = array_key_exists('real_name', $vars) ? (string)$vars['real_name'] : '';
		$email = array_key_exists('email', $vars) ? (string)$vars['email'] : '';
		$comment = array_key_exists('comment', $vars) ? (string)$vars['comment'] : '';

		$values = array();
		$values[] = 'name = "'.$mysqli->real_escape_string($vars['name']).'"';
		$values[] = 'real_name = "'.$mysqli->real_escape_string($real_name).'"';
		$values[] = 'email = "'.$mysqli->real_escape_string($email).'"';
		$values[] = 'email_visible = "0"';
		$values[] = 'icon = "alien1.gif"';
		$values[] = 'wins = 0';
		$values[] = 'nukes = 0';
		$values[] = 'nuked = 0';
		$values[] = 'ruined = 0';
		$values[] = 'max_economic_power = 0';
		$values[] = 'max_military_power = 0';
		$values[] = 'last_login = '.time();
		$values[] = 'last_ip = "'.$_SERVER['REMOTE_ADDR'].'"';
		$values[] = 'join_date = "'.time().'"';
		$values[] = 'tos_accepted = "'.((array_key_exists('accept_tos', $vars) && $vars['accept_tos']) ? 1 : 0).'"';
		$values[] = 'comment = "'.$mysqli->real_escape_string($comment).'"';
		$values[] = 'password = NULL';
		// Leave rss_token NULL here; sc_ensure_rss_token() fills it on first Game List
		// visit. Avoids hard-failing create when upgrade_rss_tokens.sql is not applied.
		
		if ($server['require_valid_email'])
			{
			$temp_pass = randomString();
			$temp_hash = password_hash($temp_pass, PASSWORD_DEFAULT);
			
			$values[] = 'password_hash = "'.$mysqli->real_escape_string($temp_hash).'"';
			$values[] = 'validation_info = "C/'.$mysqli->real_escape_string($vars['pass']).'"';
			
			$mailsent = mail($email, $server['servername']." Empire Creation", 
							 "Welcome to ".$server['servername']."! To complete the creation of your empire\n".
							 "please log in to the server using this temporary password:\n\n".$temp_pass."\n\n".
							 "We recommend using cut and paste to enter this password into your browser \n".
							 "as it is both case and numeral sensitive. Once you successfully login, your \n".
							 "password will be changed to the password you initally selected. You should then\n".
							 "select a player icon to represent you in the game.\n\n".
							 "If you have received this email in error, we apologize. If you have not recently\n".
							 "attempted to register on our server, you can ignore this email. To block future emails from\n".
							 "our server, please visit the following web page: \n\n".$server['server_URL']."?blockEmail=".$email."&tpass=".$temp_pass."\n\n".
							 "(Blocking your email address may prevent you from registering with us in the future.)\n\nThank You\n");
			
			if (!$mailsent)
				return loginFailed('Unable to send mail to '.$email.'.');
			}
		else
			{
			$pass_hash = password_hash($vars['pass'], PASSWORD_DEFAULT);
			$values[] = 'password_hash = "'.$mysqli->real_escape_string($pass_hash).'"';
			$values[] = 'validation_info = ""';
			}
		
		$sql='INSERT INTO empires SET '.implode(', ', $values);
		sc_query($sql);

		$affected = (int)$mysqli->affected_rows;
		$new_id = (int)$mysqli->insert_id;

		// Prefer insert_id over re-query by name (avoids charset/name mismatches
		// after insert). Fall back to name lookup if insert_id is unavailable.
		$empire = $new_id > 0 ? getEmpireByID($new_id) : null;
		if (!$empire)
			$empire = getEmpire($vars['name']);

		if (!$empire)
			{
			reportError(
				E_WARNING,
				'Empire create inserted but could not reload row. '.
				'insert_id='.$new_id.
				' affected_rows='.$affected.
				' name_hex='.bin2hex((string)$vars['name']).
				' name_len='.strlen((string)$vars['name']).
				"\n".$sql,
				__FILE__,
				__LINE__
			);
			return loginFailed('Empire creation failed to finalize. Please contact the administrator.');
			}
		
		if ($server['require_valid_email'])
			return newEmpireLogin($vars);
		else
			{
			$authenticated = true;
			$authenticated_as_admin = false;
			$auth_response_token = auth_issue_token($empire);
			sendEmpireMessage($empire, 'Empire created.');
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
	global $mysqli;

	standardHeader('Email Address Blocking');
	
	$select = sc_query('SELECT * FROM empires WHERE email = "'.$mysqli->real_escape_string($address).'" AND NOT validation_info REGEXP "^C"');
	
	if ($select->num_rows > 0)
		echo 'You may not block the address of a registered empire.';
	else
		{
		$select = sc_query('SELECT * FROM empires WHERE email = "'.$mysqli->real_escape_string($address).'" AND validation_info REGEXP "^C"');
		$ok = false;
		$empire = null;
		while ($row = $select->fetch_assoc())
		{
			if (auth_verify_password($row, $tpass))
			{
				$ok = true;
				$empire = $row;
				break;
			}
		}
		if (!$ok)
			echo 'Invalid temporary password.'; 
		else
			{
			sc_query('DELETE FROM empires WHERE email = "'.$mysqli->real_escape_string($address).'"');
			
			$result = sc_query('SELECT * FROM blockedemail WHERE email="'.sc_escape($address).'"');
			
			if (!$result->num_rows)
				sc_query('INSERT INTO blockedemail SET email = "'.sc_escape($address).'", time = '.time().', empname = "'.sc_escape($empire['name']).'"');
			
			echo $address.' added to our blocked email list.';
			}
		}

	footer();
}
?>
