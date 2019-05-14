<?php
function editProfile($vars)
{
	global $server;

	$empire = $vars['empire_data'];

	standardHeader('Edit Profile', $empire);
?>
<div>
<input type=hidden name="name" value="<?php echo $empire['name']; ?>">
<input type=hidden name="pass" value="<?php echo $empire['password']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="editProfile">
<input type=hidden name="old_origin" value="<?php echo $empire['map_origin']; ?>">

<div class=pageTitle><?php echo $vars['name']; ?>: Edit Profile</div>
<?php
	echo drawButtons($empire).'<div class=message style="margin-top: 10pt;">Local time and date: '.date('l, F j H:i:s T Y', time()).'</div>'.
		 empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">

<div>
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">Password:</th>
		<td><input type=password name="pass_new" size=20 maxlength=20 value="<?php echo $empire['password']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Verify Password:</th>
		<td><input type=password name="pass_check" size=20 maxlength=20 value="<?php echo $empire['password']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Real Name:</th>
		<td><input type=text name="real_name" size=40 value="<?php echo $empire['real_name']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">E-mail:</th>
		<td><input type=text name="email" size=40 value="<?php echo $empire['email']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;"></th>
		<td><input type=checkbox name="show_email" <?php echo ($empire['email_visible'] ? ' checked' : ''); ?>>Show email address in Stat viewer</td>
	</tr>
	<tr>
		<th style="text-align: right;" valign=top>Comments:</th>
		<td><textarea name="comment" rows=5 cols=40><?php echo stripslashes($empire['comment']); ?></textarea></td>
	</tr>
	<tr>
		<th style="text-align: right; vertical-align: top;">Miscellaneous preferences:</th>
		<td>
			<div><input type=checkbox name="auto_update"<?php echo ($empire['auto_update'] ? ' checked' : ''); ?>>&nbsp;Auto-updates</div>
			<div><input type=checkbox name="show_coordinates"<?php echo ($empire['show_coordinates'] ? ' checked' : ''); ?>>&nbsp;Show coordinates on map screens</div>
			<div><input type=checkbox name="show_icons"<?php echo ($empire['show_icons'] ? ' checked' : ''); ?>>&nbsp;Show icons in player lists</div>
			<div style="margin-top: 10pt;">
				<input type=checkbox name="draw_background"<?php echo ($empire['draw_background'] ? ' checked' : ''); ?>>&nbsp;Draw page background...
				<select name="background_attachment">
					<option<?php echo ($empire['background_attachment'] == 'scroll' ? ' selected' : ''); ?>>Scroll
					<option<?php echo ($empire['background_attachment'] == 'fixed' ? ' selected' : ''); ?>>Fixed
				</select>
			</div>
			<div>Custom background image (URL):&nbsp;<input type=text size=30 maxlength=255 name="custom_bg_url" value="<?php echo $empire['custom_bg_url']; ?>"></div>
			<div style="margin-top: 10pt;">Order ships on ships screen by...
				<select name="list_ships_by">
					<option<?php echo ($empire['list_ships_by_system']? '' : ' selected'); ?>>Ship type
					<option<?php echo ($empire['list_ships_by_system']? ' selected' : ''); ?>>Location
				</select>
			</div>
			<div style="margin-top: 10pt;">Default map origin: <input type=text size=11 maxlength=11 name="map_origin" value="<?php echo $empire['map_origin']; ?>"></div>
		</td>
	</tr>
</table>
</div>

<div style="margin-top: 10pt;">
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<td><img src="images/aliens/<?php echo $empire['icon']; ?>" width=40 height=40 alt="<?php echo $empire['icon']; ?>"></td>
		<td><input type=submit name="chooseIcon" value="Choose Icon..."></td>
		<?php echo ($server['icon_upload_allowed'] ? '<td><input type=submit name="iconUpload" value="Upload Custom Icon..."></td>' : ''); ?>
		<td><input type=submit name="saveProfile" value="Save Changes"></td>
	</tr>
</table>
</div>
<?php
	footer();
}

#-----------------------------------------------------------------------------------------------------------------------------------------#

function editProfile_processing($vars)
{
	global $server;
	
	$empire = $vars['empire_data'];

	if ($vars['iconUpload'])
		return iconUploadPage($vars);
	else if ($vars['chooseIcon'])
		return chooseIconPage($vars);
	else if ($vars['pass_new'] == $vars['pass_check'])
		{
		for ($icon = 1; $icon <= $server['icon_count']; $icon++) if ($vars['icon_'.$icon.'_x'] != '') break;
	
		$values = array();

		if ($vars['iconChoice'])
			$values[] = 'icon = "alien'.$icon.'.gif"';
		else if (!$vars['iconUpload'])
			{
			$values[] = 'password = "'.$vars['pass_new'].'"';
			$values[] = 'real_name = "'.$vars['real_name'].'"';
			
			if ($vars['email'] != $empire['email']) 
				{
				if ($server['require_valid_email'])
					{
					$valid_code = randomString();
					
					$values[] = 'validation_info = "E/'.$valid_code.'/'.$vars['email'].'"';
					
					$message = 'This message is being sent to you because you requested a change of email address for your empire '.$vars['name'].'. '.
							   'To complete your change of address, please visit the following link:'."\n\n".
							   $server['server_URL'].'?changeEmail='.$vars['name'].'&vcode='.$valid_code."\n\n".
							   'to complete your change of email address.'."\n\n".
							   'Note: If you have word wrap turned on in your email program, the link may not work correctly. If you have problems, '.
							   'try cutting and pasting it, or typing it in manually.'."\n\n".
							   'Thank You.';
					
					if (!mail($vars['email'], $server['servername']." Email Address Change", $message))
						sendEmpireMessage($empire, 'Unable to send mail to '.$vars['email'].'.');
					else
						sendEmpireMessage($empire, 'An email message has been sent to '.$vars['email'].'. Please follow the instructions contained '.
												   'in that email to complete the change of your email address.');
					 }
				else
					$values[] = 'email = "'.$vars['email'].'"';
				}
			else
				$values[] = 'email = "'.$vars['email'].'"';
					
			$values[] = 'email_visible = "'.($vars['show_email'] ? 1 : 0).'"';
			$values[] = 'comment = "'.addslashes($vars['comment']).'"';
			$values[] = 'auto_update = "'.($vars['auto_update'] ? 1 : 0).'"';
			$values[] = 'show_coordinates = "'.($vars['show_coordinates'] ? 1 : 0).'"';
			$values[] = 'show_icons = "'.($vars['show_icons'] ? 1 : 0).'"';
			$values[] = 'draw_background = "'.($vars['draw_background'] ? 1 : 0).'"';
			$values[] = 'custom_bg_url = '.($vars['custom_bg_url'] ? '"'.str_replace(' ', '%20', $vars['custom_bg_url']).'"' : 'NULL');
			$values[] = 'background_attachment = "'.$vars['background_attachment'].'"';
			$values[] = 'list_ships_by_system = "'.($vars['list_ships_by'] == "Location" ? 1 : 0).'"';

			if ($vars['old_origin'] != $vars['new_origin'])
				{
				if (sscanf($vars['map_origin'], '%d,%d', $x, $y) == 2)
					{
					if ($x > -9999 and $x < 9999 and $y > -9999 and $y < 9999)
						$values[] = 'map_origin = "'.$vars['map_origin'].'"';
					}
				else
					$message = 'Invalid map origin entered.';
				}
			}
	
		if (count($values))
			{
			sc_mysql_query('UPDATE empires SET '.implode(', ', $values).' WHERE name = "'.$vars['name'].'"');
			sendEmpireMessage($empire, 'Profile Updated.');
			
			$vars['empire_data'] = getEmpire($vars['name']);
			}

		if ($vars['fromEmpireCreation'])
			{
			sendEmpireMessage($empire, 'Welcome to Stellar Crisis, '.$vars['name'].'.');
			return gameList($vars);
			}
		else if ($message)
			sendEmpireMessage($empire, $message);
		}
  	else
		sendEmpireMessage($empire, 'The two password fields were different. Please try again.');
	
	return editProfile($vars);
}

#-----------------------------------------------------------------------------------------------------------------------------------------#

function finishChangeEmail($empname, $vcode)
{
	$empire = getEmpire($empname);

	standardHeader('Email Address Change');

	echo "Change email: ";
	
	if (substr( $empire['validation_info'], 0, 1 ) == 'E')
		{
		list($code, $valcode, $email) = explode('/', $empire['validation_info']);
		
		if ($vcode == $valcode)
			{
			sc_mysql_query('UPDATE empires SET email="'.$email.'", validation_info="" WHERE name = "'.$empname.'"', __FILE__.'*'.__LINE__);
			echo "Email address updated.";
			}
		else
			echo "Validation code mismatch: $vcode.";
		}
	else
		echo "No email change is pending for that empire.";
	
	footer();
}
?>
