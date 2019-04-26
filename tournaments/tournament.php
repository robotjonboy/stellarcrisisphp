<?php

function tournamentsAction($vars) {
	global $server, $ship_types, $authenticated, $hwX, $hwY;

	if (!$authenticated)
		return loginFailed('Identity check failed.');
	
	
	
	echo 'test';
}

?>