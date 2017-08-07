	Index

todo list see stellar/todo.txt ...do not put todo items in this log

change log -add at bottom

		Todo list - cjp code => match aleks

Messages shows system messages untitled in list box
exit after show all messages causes rollback
created by admin shows as created by "" instead of all blank.
logout doesn't seem to remove user from online list
admin/edit series second window needs prompt to select series
   just change dropdown label from edit to select
admin/edit if on select series page and click go = re-login
admin/halt displays series not game.
edit server news = Fatal error: 
   Call to undefined function: editnews() in /var/www/sc/admin/admin.php on line 118
user/notes then messages = rollback
4 of 4 players ready but if game is not closed no update occurs => correct action.
ships/build-at dropdown missing planet name.
Admin should be able to read error log 
  and erase it.
Planet are unnamed.

		Todo list - aleks code

When have explored 1 of 2 systems and the link has been cut, 
   the scouting report shows link even though no link is there.
   Need to overlay a red or blank link marker.
No you have met message
no you were destroyed message
no sighted message.
Fleet - next BR, Max BR values are switched. - compare to ship screen
Next update for game is taken from series yet stored in games table too.
720 hours update = 1 day 6 hours?


		Wish list
Provide name sets for planets and ships.
  First colonizer sets name.
  If first explorer would provide tactical information to opponent.
  eg Elemental: Hydrogen, Helium, Argon
     Molecular: H2O, CH4, CCl4
     Greek, Asgard, Canada, Usa, Britain, 
  Private or shared sets.
  Admin-view all users - sort by name, last use, score

		Change log

utility/create_tables	
   missing quote, 
	create table => $sql='create table if not exist...' & mysql_query($sql);
server
   Added mysql error handing
debug
   create logfile and chmod 777 to allow writing.
	error not handled well.
footer
   protect against lack of memory_get_usage php-function
	was this a php version problem?
sc
   protect against lack of memory_get_usage php-function
	was this a php version problem?
   mysql version must be > 4.1
   Upgraded my mysql
   inserted version check (where error occured -> put at start)
update
   if(is_array($missive)){ //cjp line 1361
   doubled msg: 1 Colony ship was built in mary0,0 (1 Colony ship was built in mary0,0).
      my bug - typo =  $amissive .= ' ('.   dot instead of colon   $amissive .= ' (';

tables
   added 4 fields to empires
	is_admin 
	email_visible
	tos_accepted 
	validation_info
   added to series
	custom 
	creator
	cloakers_as_attacks
	max_allies
	avg_min
	avg_fuel
	avg_ag
   added to ships
	order_arguments
	player_id
   addet to fleets
	order_arguments

create_tables
   made all above changes
   inserted ship_type table structure
   loaded ship_type in create_tables

series
	alphabetized fields
	updated editseries & createseries to match
	call to sendEmpireMessage was missing empireID 
	added empireID to hidden fields
	call to sendEmpireMessage was missing empire
	

