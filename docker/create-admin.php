<?php
/**
 * Insert the admin empire if that name is not already present.
 * Usage: php create-admin.php
 * Reads SC_ADMIN_NAME, SC_ADMIN_PASSWORD, SC_ADMIN_EMAIL from the environment.
 */

$name = getenv('SC_ADMIN_NAME');
if ($name === false || $name === '')
	$name = 'admin';
$password = getenv('SC_ADMIN_PASSWORD');
$email = getenv('SC_ADMIN_EMAIL');
if ($email === false || $email === '')
	$email = 'admin@localhost';

if ($password === false || $password === '') {
	fwrite(STDERR, "create-admin: SC_ADMIN_PASSWORD is empty\n");
	exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($hash === false) {
	fwrite(STDERR, "create-admin: password_hash failed\n");
	exit(1);
}

echo $name, "\n", $hash, "\n", $email, "\n";
