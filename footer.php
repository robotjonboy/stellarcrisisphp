<?
global $server, $start_time, $start_memory;

# Page footer; appended to pages sent back to the user.
# This file is meant to be included via a require() call at the appropriate place since we are 
# refering to some server variables and a style sheet.
?>
<div style="text-align: center;">
	<div><img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg"></div>
 	<div style="font-weight: bold;"><a href="<? echo $server['sc_wiki_url']; ?>">Help</a> is available.</div>
<?
if ($server['discussion_board'])
	echo '<div style="margin: 5pt;">Ideas, questions, talk? We have a <a href="'.$server['discussion_board'].'">discussion board</a>.</div>';
?>
	<div><img src="images/sclogo_neo.gif" alt="sclogo_neo.gif"></div>
</div>

<?
# The following is acknowledeges the OS we are running.
# Originally running on Mac OS X. :)
# Now running on Debian Linux. 

/*<div style="text-align: center;">
	<span title="Running on Debian Linux">
	<a href="http://www.debian.org">
		<img src="images/debian_black2.jpg">
	</a>
	</span>
<!--	<a href="http://www.debian.org"><img src="../icons/debian/openlogo-50.jpg"></a>  -->
</div>*/?>

<div class=requestStats>
<?
// Show how much time has elapsed for the entire processing of the user's request.
// $start_memory and $start_time are global variables that are defined in sc.php.
if ($server['show_memory_usage']) //local setting
{
   if( function_exists('memory_get_usage') ) // but don't blow up
   {
	echo round((memory_get_usage()-$start_memory)/1024).' kB / ';
   }
 }

echo round((utime()-$start_time)/1000).' ms';
?>
</div>
</form>
</body>
</html>
