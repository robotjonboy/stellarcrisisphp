<?
require('server.php');
require('sql.php');

$word_count = $argv[1];

$start_t = utime();

for ($x = 0; $x < $word_count; $x++)
	nameSystem($foo)."\n";

echo "Elapsed time for a loop of ".$word_count." words: ".number_format(utime()-$start_t, 3, '.', '')." seconds\n";

$start_t = utime();
randomNames($word_count);
echo "Elapsed time for a call to randomNames() for ".$word_count." words: ".number_format(utime()-$start_t, 3, '.', '')." seconds\n";

#----------------------------------------------------------------------------------------------------------------------#

function nameSystem(&$big_map)
{
        global $server;

        if ($server['systemNameSource'] == 'random')
                return randomName();
        else if ($server['systemNameSource'] != '')
                {
                // If the source is another string, it's the name of the SQL table where we'll find the name.
                // Of course, don't name your table 'random'. Duh.
                //
                // We could to an "ORDER BY RAND() LIMIT 1", but with the table on Iceberg being over 85 thousand records, that would
                // incur a *massive* performance penalty, since we're sorting the entire table for each query. For big maps, this
                // adds up to a very long wait. Hence, we find a random id (COUNT(*) is very fast) and pick out the word.
                $word_count = sc_query('SELECT COUNT(*) FROM '.$server['systemNameSource'], __FILE__.'*'.__LINE__);
                $random_id = rand(1, mysql_result($word_count, 0, 0));

                $select = sc_query('SELECT word FROM '.$server['systemNameSource'].' WHERE id = '.$random_id, __FILE__.'*'.__LINE__);
                $word = mysql_fetch_array($select);

                return ucfirst($word['word']);
                }
        else
                return('System');
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns an array of random names. Use this function if you need to generate a lot of them, especially if they come
# from the words table.
#

function randomNames($count)
{
	global $server;
	
	$names = array();

	if ($server['systemNameSource'])
		{
		$select = sc_query('SELECT word FROM '.$server['systemNameSource'].' ORDER BY RAND() LIMIT '.$count);
		while ($word = mysql_fetch_array($select)) $names[] = ucfirst($word['word']);
		}
	else
		for ($x = 0; $x < $count; $x++) $names[] = randomName();

	return $names;
}


#=--------------------------------------------------------------------------------------=#
# Returns the current UNIX time, with microsecond precision.

function utime()
{
        list($microseconds, $seconds) = explode(' ', microtime());

        return number_format($microseconds+(double)$seconds, 6, '', '');
}
?>
