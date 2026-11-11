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

# http(s) background image URL safe to place inside CSS url("...").
# Returns '' when the value could break out of the style block.
function sc_safe_bg_url($url) {
	$url = str_replace(' ', '%20', trim((string)$url));
	if ($url === '')
		return '';
	if (!preg_match('/\Ahttps?:\/\//i', $url))
		return '';
	if (preg_match('/[\s"\\\\<>()\x00-\x1F\x7F]/', $url))
		return '';
	return $url;
}

function sc_safe_bg_attachment($value) {
	$value = strtolower(trim((string)$value));
	return ($value === 'fixed') ? 'fixed' : 'scroll';
}

?>
