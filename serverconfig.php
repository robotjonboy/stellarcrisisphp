<?php
if ($debug) echo "serverconfig.php <p>";

/* local constants - adjust these to your system */
$server['admin_email'] = 'admin@localhost';
$server['domainname'] = 'localhost';
$server['app_path'] = 'sc/sc.php';
$server['servername'] = 'Rocket Empires';
$server['mysql_host'] ='localhost';
$server['mysql_user'] ='root';
//$server['mysql_password'] ='32mysql';
$server['mysql_password'] ='';
$server['mysql_database'] = 'sc';
$server['show_memory_usage'] = true;

/* universal */
$server['version'] = '3.0.1 ';
$server['faq_url'] = 'https://scopen.rocketempires.com/beginnerresources.html';
$server['sc_room_url'] = '../sc_room/SC_main.shtml';
$server['sc_wiki_url'] = '../pmwiki/pmwiki.php';

/* and alphabetical */
$server['builder_population'] = 50;
$server['custom_series_allowed'] = true;
$server['custom_series_per_wins_chunk'] = 1;
$server['custom_series_wins_chunk'] = 30;
#$server['discussion_board'] = 'http://discussions.'.$servername'.'/';#
$server['engineer_br_loss'] = 0.5;
$server['error_log_path'] = 'debug_data.html'; // filename; relative to server root
$server['error_log_type'] = 'file'; // file or email
$server['fleetNameSource'] = 'words'; // table name; leave blank to generate name on-the-fly.
$server['histories_per_page'] = 9;
$server['history_css_path'] = './'; // directory; relative to history directory
$server['history_image_path'] = '../images/'; // directory; relative to history directory
$server['history_read_URL'] = 'http://'.$server['domainname'].'/sc/history/'; // e.g., http://your.server/path/
$server['history_write_path'] = 'history/'; // directory; relative to server root
$server['icon_count'] = 152;
$server['icon_upload_allowed'] = true;
$server['local_coordinates'] = true;
$server['max_build_ships'] = 8;
$server['max_search_results'] = 50;
$server['minimap_threshold'] = 100;
$server['multiemp_warning'] = true;
$server['require_tos'] = false;
$server['require_valid_email'] = false;
#$server['server_URL'] = 'http://'.$server['servername'].'/';#
$server['standard_header_text'] = '';
$server['systemNameSource'] = 'words'; // table name; leave blank to generate name on-the-fly.
$server['top_lists_enabled'] = true;
$server['updates_to_close'] = 4;	//cjp was 5
$server['updates_to_idle'] = 2;		//cjp default idle 2
$server['updates_to_ruin'] = 12;	//cjp default 12
$server['tournaments'] = true; //default false

date_default_timezone_set('America/New_York');

?>
