<?php

function sc_cookie($cookieName) {
	$cookieVal = '';

	if (is_array($_COOKIE) && array_key_exists($cookieName, $_COOKIE)) {
		$cookieVal = $_COOKIE[$cookieName];
	}

	return $cookieVal;
}

function sc_post($paramName) {
  $postVal = '';

  if (is_array($_POST) && array_key_exists($paramName, $_POST)) {
    $postVal = $_POST[$paramName];
  }

  return $postVal;
}

?>
