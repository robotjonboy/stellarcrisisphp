<?php 

$str = 'blah';

echo "blah preg_match: " . preg_match('/errorHandler|reportError|trigger_error/i', $str) . "<br/>";

$str = "errorhandler";

echo "str: " . $str. "<br/>";
echo "blah preg_match: " . preg_match('/errorHandler|reportError|trigger_error/i', $str) . "<br/>";

$str = "reporterror";

echo "str: " . $str. "<br/>";
echo "blah preg_match: " . preg_match('/errorHandler|reportError|trigger_error/i', $str) . "<br/>";

$str = "trigger_erRor";

echo "str: " . $str. "<br/>";
echo "blah preg_match: " . preg_match('/errorHandler|reportError|trigger_error/i', $str) . "<br/>";

echo "testing day of week match<br/>";

$str = "Sat";

echo "str: " . $str. "<br/>";
echo "blah preg_match: " . preg_match('/Sat|Sun/i', $str) . "<br/>";

$str = "Sun";

echo "str: " . $str. "<br/>";
echo "blah preg_match: " . preg_match('/Sat|Sun/i', $str) . "<br/>";

$str = "Mon";

echo "str: " . $str. "<br/>";
echo "blah preg_match: " . preg_match('/Sat|Sun/i', $str) . "<br/>";

echo "testing Unable to save result set<br/>";

$str = "Unable to save result set";

echo "str: " . $str. "<br/>";
echo "blah preg_match: " . preg_match('/Unable to save result set/i', $str) . "<br/>";

$str = "Unable";

echo "str: " . $str. "<br/>";
echo "blah preg_match: " . preg_match('/Unable to save result set/i', $str) . "<br/>";

?>