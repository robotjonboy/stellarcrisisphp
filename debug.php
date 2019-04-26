<?
# file: debug.php
#
# cjp -Added view errors to admin menu
# Whenever there is a problem look at the errorlog.html file
# you can do this remotely 
# eg if the url you see is http://helium/sc/sc.php
#             change it to http://helium/sc/errorlog.html
# and examine the last entry. remember to refresh after each test.

function sc_errorHandler($errno, $errstr, $errfile, $errline, $errctx)
{	
	// We know about this warning. When a deadlock is encountered, a query won't be able to get a result.
	// We re-issue these queries and they eventually get processed so we don't need to know about this warning.
	// A failed query will still trigger an error.
	if ($errno == 2 and preg_match('/Unable to save result set/i', $errstr)) return;
		
	if ($errno != E_NOTICE)
		reportError($errno, $errstr, $errfile, $errline, $errctx);
}

#----------------------------------------------------------------------------------------------------------------------#
# This function reports an error either by sending an email to the administrator of by appending the information to
# a log file. If we are using PHP 4.3 or later, a backtrace is available. Otherwise, we contruct a fake (but
# informative) one to maintain code compatibility).
#

function reportError($error_number, $error_string, $file, $line, $context)
{
	global $server;
	
	$error_string = wordwrap($error_string, 150);

	$buffer = '<html><head><title>Bug report</title>***STYLE_DATA***</head>'.
			  '<body><div style="text-align: center;"><table cellspacing=0>'.
			  '<tr><th class=redBox>'.date('Y-m-d H:i:s', time()).'</th></tr>'.
			  '<tr><td class=darkGreyBox>'.
			  '<pre>Error #'.$error_number."\n\n".$error_string.'</pre>'.
			  '</td></tr>'.
			  '***ERROR_REPORT***</table></div></body></html>';

	if (versionCheck('4.3.0'))
		$backtrace = debug_backtrace();
	else
		{
		// Fake backtrace so that we don't split the code below.
		$backtrace = array(0 => array('line' => $line,
									  'file' => $file,
									  'function' => 'unknown',
									  'args' => array(0 => 'unknown - debug_backtrace() unavailable',
													  1 => $error_number,
													  2 => $error_string)));
		}

	for ($index = count($backtrace)-1; $index >= 0; $index--)
		{
		// Skip some functions; we know about them.
		if (preg_match('/errorHandler|reportError|trigger_error/i', $backtrace[$index]['function'])) continue;

		$arguments = ($backtrace[$index]['args'] ? htmlspecialchars(implode(', ', $backtrace[$index]['args'])) : '');

		$error_report .= '<tr><td class=greyBox>'.
						 '<div class=function>'.$backtrace[$index]['function'].'&nbsp;'.
						 '(&nbsp;<span class=arguments>'.$arguments.'</span>&nbsp;)</div>'.
						 '<div>Line <span class=bold>'.$backtrace[$index]['line'].'</span> of file '.
						 '<span class=bold>'.$backtrace[$index]['file'].'</span></div>'.
						 '</td></tr>';
		}

	$buffer = str_replace('***ERROR_REPORT***', $error_report, $buffer);
	
	ob_start();
?>
<style>
	table
		{
		font: 8pt "Trebuchet MS";
		border: 1pt solid black;
		margin-left: auto; margin-right: auto; margin-bottom: 10pt;
		}

	th, td
		{
		text-align: left;
		font-size: 8pt;
		border: 1pt solid;
		padding: 5pt;
		}

	.redBox
		{
		font-weight: bold;
		color: white; background-color: #922;
		border-top-color: #C55; border-right-color: #522;
		border-bottom-color: #522; border-left-color: #C55;
		}
	
	.greyBox
		{
		background-color: #EEE;
		border-top-color: #FFF; border-right-color: #888;
		border-bottom-color: #888; border-left-color: #FFF;
		}
		
	.darkGreyBox
		{
		background-color: #DDD;
		border-top-color: #EEE; border-right-color: #666;
		border-bottom-color: #666; border-left-color: #EEE;
		}

	pre { font: 8pt "Trebuchet MS"; color: black; }

	.bold		{ font-weight: bold; }
	.function	{ font-weight: bold; color: #229; }
	.arguments	{ font-weight: bold; color: #161; }
</style>
<?
	$style_data = ob_get_contents();
	ob_end_clean();

	$buffer = str_replace('***STYLE_DATA***', $style_data, $buffer);

	if (isset($server['error_log_type']))
		{
		if ($server['error_log_type'] == 'email')
			{
			$message = '<html><title>'.$subject.'</head><body>'.$buffer.'</body></html>';

			$headers  = 'MIME-Version: 1.0'."\r\n".
						'Content-type: text/html; charset=iso-8859-1'."\r\n";

			mail($server['admin_email'], 'SC @ '.$server['servername'].' - Error report', $message, $headers);
			}
		else if ($server['error_log_type'] == 'file')
			cjp_error_log($buffer, 3, 'errorlog.html');
#			error_log($buffer, 3, $server['error_log_path']);
		}
}
//adapeted from 1&1 code
   function cjp_error_log($errmsg, $desttype, $errorfile) 
   {
     $time=date("d M Y H:i:s"); 
     // Get the error type from the error number 
     $errortype = array (1    => "Error",
                         2    => "Warning",
                         4    => "Parsing Error",
                         8    => "Notice",
                         16   => "Core Error",
                         32   => "Core Warning",
                         64   => "Compile Error",
                         128  => "Compile Warning",
                         256  => "User Error",
                         512  => "User Warning",
                         1024 => "User Notice");
      $errlevel=$errortype[$errno];
 
      //Write error to log file (CSV format) 
      $errfile=fopen($errorfile,"a"); 
      fputs($errfile,$errmsg); 
      fclose($errfile);
 /*
      if($errno!=2 && $errno!=8)
     {
         //Terminate script if fatal errror
         die("A fatal error has occured. Script execution has been aborted");
      }
*/	  
   }

/* per 1&1 site
error_reporting(0); 
   $old_error_handler = set_error_handler("userErrorHandler");
 
   function userErrorHandler ($errno, $errmsg, $filename, $linenum,  $vars) 
   {
     $time=date("d M Y H:i:s"); 
     // Get the error type from the error number 
     $errortype = array (1    => "Error",
                         2    => "Warning",
                         4    => "Parsing Error",
                         8    => "Notice",
                         16   => "Core Error",
                         32   => "Core Warning",
                         64   => "Compile Error",
                         128  => "Compile Warning",
                         256  => "User Error",
                         512  => "User Warning",
                         1024 => "User Notice");
      $errlevel=$errortype[$errno];
 
      //Write error to log file (CSV format) 
      $errfile=fopen("errors.csv","a"); 
      fputs($errfile,"\"$time\",\"$filename: 
      $linenum\",\"($errlevel) $errmsg\"\r\n"); 
      fclose($errfile);
 
      if($errno!=2 && $errno!=8) {
         //Terminate script if fatal errror
         die("A fatal error has occured. Script execution has been aborted");
      } 
   }
  */
#----------------------------------------------------------------------------------------------------------------------#
# Returns true if $version is newer or equal to the current PHP version.
#

function versionCheck($version)
{
    $minimum = str_replace('.', '', $version);
    $current = str_replace('.', '', PHP_VERSION);

     return ($current >= $minimum);
}
?>
