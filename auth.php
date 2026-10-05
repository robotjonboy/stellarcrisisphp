<?php
# Password hashing and session token helpers.
# See docs/design-password-security.md

# Raw token for the outgoing response forms (set by the gate / login / create / password change).
$auth_response_token = null;

#----------------------------------------------------------------------------------------------------------------------#

function auth_is_admin($empire)
{
	return (isset($empire['is_admin']) && ($empire['is_admin'] === '1' || $empire['is_admin'] === 1));
}

#----------------------------------------------------------------------------------------------------------------------#
# Verify a submitted password. On success with legacy plaintext, upgrade to password_hash and null password.
# Returns true/false. Updates $empire in place on upgrade/rehash.
#

function auth_verify_password(&$empire, $plain)
{
	global $mysqli;

	if ($plain === null || $plain === '')
		return false;

	$id = (int)$empire['id'];
	$hash = isset($empire['password_hash']) ? $empire['password_hash'] : null;
	$legacy = array_key_exists('password', $empire) ? $empire['password'] : null;

	if ($hash !== null && $hash !== '')
	{
		if (!password_verify($plain, $hash))
			return false;

		$updates = array();
		if (password_needs_rehash($hash, PASSWORD_DEFAULT))
		{
			$new_hash = password_hash($plain, PASSWORD_DEFAULT);
			$updates[] = 'password_hash = "'.$mysqli->real_escape_string($new_hash).'"';
			$empire['password_hash'] = $new_hash;
		}
		if ($legacy !== null && $legacy !== '')
		{
			$updates[] = 'password = NULL';
			$empire['password'] = null;
		}
		if ($updates)
			sc_query('UPDATE empires SET '.implode(', ', $updates).' WHERE id = '.$id);

		return true;
	}

	if ($legacy !== null && $legacy === $plain)
	{
		auth_store_password_hash($empire, $plain);
		return true;
	}

	return false;
}

#----------------------------------------------------------------------------------------------------------------------#
# Store a new hashed password; always nulls the plaintext column.
#

function auth_store_password_hash(&$empire, $plain)
{
	global $mysqli;

	$hash = password_hash($plain, PASSWORD_DEFAULT);
	sc_query(
		'UPDATE empires SET password_hash = "'.$mysqli->real_escape_string($hash).'", '.
		'password = NULL WHERE id = '.(int)$empire['id']
	);
	$empire['password_hash'] = $hash;
	$empire['password'] = null;
}

#----------------------------------------------------------------------------------------------------------------------#

function auth_hash_token($raw_token)
{
	return hash('sha256', $raw_token);
}

#----------------------------------------------------------------------------------------------------------------------#
# Issue a new session token for an empire. Returns the raw token for the hidden form field.
#

function auth_issue_token($empire)
{
	global $mysqli;

	$raw = bin2hex(random_bytes(32));
	$token_hash = auth_hash_token($raw);
	$now = time();
	$expires = $now + (365 * 24 * 60 * 60);

	sc_query(
		'INSERT INTO auth_tokens SET '.
		'empire_id = '.(int)$empire['id'].', '.
		'token_hash = "'.$mysqli->real_escape_string($token_hash).'", '.
		'created_at = '.$now.', '.
		'expires_at = '.$expires.', '.
		'last_used_at = NULL, '.
		'revoked_at = NULL'
	);

	return $raw;
}

#----------------------------------------------------------------------------------------------------------------------#
# Validate a presented token for this empire. Soft-updates last_used_at. Returns true/false.
#

function auth_validate_token($empire, $raw_token)
{
	global $mysqli;

	if ($raw_token === null || $raw_token === '')
		return false;

	$token_hash = auth_hash_token($raw_token);
	$now = time();

	$sql  = 'SELECT id FROM auth_tokens WHERE ';
	$sql .= 'token_hash = "'.$mysqli->real_escape_string($token_hash).'" ';
	$sql .= 'AND empire_id = '.(int)$empire['id'].' ';
	$sql .= 'AND revoked_at IS NULL ';
	$sql .= 'AND expires_at > '.$now.' LIMIT 1';

	$select = sc_query($sql);
	if (!$select->num_rows)
		return false;

	$row = $select->fetch_assoc();
	sc_query('UPDATE auth_tokens SET last_used_at = '.$now.' WHERE id = '.(int)$row['id']);
	return true;
}

#----------------------------------------------------------------------------------------------------------------------#
# Soft-revoke one token by raw value (logout). Never hard-deletes.
#

function auth_soft_revoke_token($raw_token)
{
	global $mysqli;

	if ($raw_token === null || $raw_token === '')
		return;

	$token_hash = auth_hash_token($raw_token);
	sc_query(
		'UPDATE auth_tokens SET revoked_at = '.time().' '.
		'WHERE token_hash = "'.$mysqli->real_escape_string($token_hash).'" '.
		'AND revoked_at IS NULL'
	);
}

#----------------------------------------------------------------------------------------------------------------------#
# Soft-revoke all tokens for an empire (password change). Never hard-deletes.
#

function auth_soft_revoke_all_tokens($empire)
{
	sc_query(
		'UPDATE auth_tokens SET revoked_at = '.time().' '.
		'WHERE empire_id = '.(int)$empire['id'].' AND revoked_at IS NULL'
	);
}

#----------------------------------------------------------------------------------------------------------------------#
# Emit hidden name + token fields for authenticated pages. Does not emit pass.
#

function authHiddenFields($name = null)
{
	global $auth_response_token;

	if ($name === null || $name === '')
		$name = sc_post('name');

	echo '<input type=hidden name="name" value="'.htmlspecialchars($name, ENT_QUOTES).'">'."\n";
	if ($auth_response_token)
		echo '<input type=hidden name="token" value="'.htmlspecialchars($auth_response_token, ENT_QUOTES).'">'."\n";
}

#----------------------------------------------------------------------------------------------------------------------#
# Dual-path gate: token or verifying password. Sets globals used by the rest of the request.
#

function auth_run_gate()
{
	global $authenticated, $authenticated_as_admin, $auth_response_token, $mysqli;

	$authenticated = false;
	$authenticated_as_admin = false;
	$auth_response_token = null;

	// login() owns credential checks for the login form (including unknown-name → createEmpire).
	if (sc_post('section') === 'login')
		return;

	$name = sc_post('name');
	if (!strlen($name))
		return;

	$empire = getEmpire($name);
	if (!$empire)
		return;

	// Canonical capitalization from DB.
	$_POST['name'] = $empire['name'];

	$token = sc_post('token');
	$pass = sc_post('pass');

	if (strlen($token))
	{
		if (auth_validate_token($empire, $token))
		{
			$authenticated = true;
			$authenticated_as_admin = auth_is_admin($empire);
			$auth_response_token = $token;
		}
		return;
	}

	if (strlen($pass))
	{
		if (auth_verify_password($empire, $pass))
		{
			$authenticated = true;
			$authenticated_as_admin = auth_is_admin($empire);
			$auth_response_token = auth_issue_token($empire);
		}
	}
}
