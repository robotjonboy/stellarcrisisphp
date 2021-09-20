<?php

function getJumpgateShipTypeOptions($series) {
	$result = [];

	if (array_key_exists('ship_type_options', $series)) {
		if (array_key_exists('Jumpgate', $series['ship_type_options'])) {
			$result = $series['ship_type_options']['Jumpgate'];
		}
	}

	return $result;
}

function getJumpgateStatus($series) {
	$result = 'Barred';

	$jumpgate_ship_type_options = getJumpgateShipTypeOptions($series);

	if (array_key_exists('status', $jumpgate_ship_type_options)) {
		$result = $jumpgate_ship_type_options['status'];
	}

	return $result;
}
?>
