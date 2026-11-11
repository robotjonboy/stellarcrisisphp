<?php
# file: debug.php
#
# Error reports append to $server['error_log_path'] (private file outside the
# docroot). Admins read them via Administration → View Errors, not a public URL.
# See docs/design-private-error-log.md and docs/shared-hosting-error-log.md.

function sc_errorHandler($errno, $errstr, $errfile, $errline)
{	
	// We know about this warning. When a deadlock is encountered, a query won't be able to get a result.
	// We re-issue these queries and they eventually get processed so we don't need to know about this warning.
	// A failed query will still trigger an error.
	if ($errno == 2 and preg_match('/Unable to save result set/i', $errstr)) return;
		
	if ($errno != E_NOTICE)
		reportError($errno, $errstr, $errfile, $errline);
}

#----------------------------------------------------------------------------------------------------------------------#
# Absolute filesystem path for the error log, or '' if unset.
# Relative paths resolve against the application directory (this file's directory).
#

function sc_error_log_path()
{
	global $server;

	if (empty($server['error_log_path']))
		return '';

	$path = (string)$server['error_log_path'];
	if ($path === '')
		return '';

	// Absolute Unix or Windows path.
	if ($path[0] === '/' || (strlen($path) > 2 && ctype_alpha($path[0]) && $path[1] === ':'))
		return $path;

	return __DIR__.DIRECTORY_SEPARATOR.str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
}

# True when the log path resolves under the application directory (typically the web root).
#

function sc_error_log_is_under_app($path = null)
{
	if ($path === null)
		$path = sc_error_log_path();
	if ($path === '')
		return false;

	$app = realpath(__DIR__);
	if ($app === false)
		return false;

	$parent = dirname($path);
	$real_parent = realpath($parent);
	if ($real_parent === false)
		$real_parent = $parent;

	$app_n = rtrim(str_replace('\\', '/', $app), '/');
	$parent_n = rtrim(str_replace('\\', '/', $real_parent), '/');
	return ($parent_n === $app_n || strpos($parent_n.'/', $app_n.'/') === 0);
}

# Stream the error log to the current admin HTTP response (no public URL).
#

function sc_serve_error_log_to_admin()
{
	$path = sc_error_log_path();

	header('Content-Type: text/html; charset=ISO-8859-1');
	header('X-Content-Type-Options: nosniff');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Pragma: no-cache');

	echo '<!DOCTYPE html><html><head><meta charset="ISO-8859-1"><title>SC error log</title></head><body>';
	echo '<p style="font:12pt sans-serif;margin:10px;">';
	echo '<strong>Stellar Crisis error log</strong> (admin only)';
	if ($path !== '')
		echo ' - path configured';
	if ($path !== '' && sc_error_log_is_under_app($path))
		echo ' - <span style="color:#922;">WARNING: log path is under the app/web directory; move it outside the docroot (see docs/shared-hosting-error-log.md)</span>';
	echo '</p><hr>';

	if ($path === '')
		{
		echo '<p style="font:12pt sans-serif;margin:10px;">error_log_path is not set in serverconfig.php.</p></body></html>';
		return;
		}

	if (!is_readable($path))
		{
		$php_user = function_exists('posix_geteuid') ? posix_getpwuid(posix_geteuid()) : false;
		$php_user = is_array($php_user) && isset($php_user['name']) ? $php_user['name'] : 'unknown';
		echo '<p style="font:12pt sans-serif;margin:10px;">No error log found or not readable at the configured path.</p>';
		echo '<p style="font:10pt monospace;margin:10px;">Path: '.htmlspecialchars($path, ENT_QUOTES, 'ISO-8859-1').'<br>';
		echo 'PHP user: '.htmlspecialchars($php_user, ENT_QUOTES, 'ISO-8859-1').'<br>';
		echo 'exists: '.(file_exists($path) ? 'yes' : 'no').'<br>';
		echo 'Hint: every directory on the path must be executable (traverse) by the PHP/Apache user. ';
		echo 'On Debian, <code>chmod 711</code> on the private parent dir is common when the log dir is owned by www-data. ';
		echo 'See docs/shared-hosting-error-log.md.</p></body></html>';
		return;
		}

	$ok = @readfile($path);
	if ($ok === false)
		echo '<p style="font:12pt sans-serif;margin:10px;">Failed to read the error log.</p>';

	echo '</body></html>';
}

#----------------------------------------------------------------------------------------------------------------------#
# This function reports an error either by sending an email to the administrator of by appending the information to
# a log file. If we are using PHP 4.3 or later, a backtrace is available. Otherwise, we contruct a fake (but
# informative) one to maintain code compatibility).
#

function reportError($error_number, $error_string, $file, $line)
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

	ob_start();
	debug_print_backtrace();
	$backtrace = ob_get_contents();
	ob_end_clean();

	$error_report = '<tr><td class=greyBox>'.
					 '<div>'.$backtrace.
					 '</div>'.
					 '</td></tr>';

	$buffer = str_replace('***ERROR_REPORT***', $error_report, $buffer);
	
	ob_start();
echo "
<style>
	table
		{
		font: 8pt \"Trebuchet MS\";
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

	pre { font: 8pt \"Trebuchet MS\"; color: black; }

	.bold		{ font-weight: bold; }
	.function	{ font-weight: bold; color: #229; }
	.arguments	{ font-weight: bold; color: #161; }
</style>";

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
			{
			$log_path = sc_error_log_path();
			if ($log_path !== '')
				cjp_error_log($buffer, 3, $log_path, $error_number);
			}
		}
}
//adapeted from 1&1 code
   function cjp_error_log($errmsg, $desttype, $errorfile, $errno) 
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
                         1024 => "User Notice",
												 8192 => "Deprecated");
      $errlevel=isset($errortype[$errno]) ? $errortype[$errno] : 'Error';
 
      // Append HTML report to the configured private log file.
      $errfile=@fopen($errorfile,"a"); 
      
			if ($errfile) {
				fputs($errfile,$errmsg); 
	      fclose($errfile);
				// Strip world rwx only. Do not force 0640 — that resets the ACL
				// mask and can drop write for the Apache user on shared hosts.
				clearstatcache(true, $errorfile);
				$perms = @fileperms($errorfile);
				if ($perms !== false && ($perms & 0007))
					@chmod($errorfile, $perms & ~0007);
			}
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
