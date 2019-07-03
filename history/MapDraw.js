universe = new Array();
var maxX = 0, minX = 50000, maxY = 0, minY = 50000;
var imagePath;
var movieUpdate = 0;

// Function to create a coordiante ohject
function coordinate( x, y )
{
	this.x = x;
	this.y = y;
}

// Function to create a new planet
//
function planet( x, y, name, icon, min, fuel, ag, pop, fships, eships, x0, y0, x1, y1, x2, y2, x3, y3)
{
	this.loc = new coordinate( x, y );
	this.links = new Array(4);
	this.name = name;
	this.icon = icon;
	this.min = min;
	this.fuel = fuel;
	this.ag = ag;
	this.pop = pop;
	
	// following two lines can be replaced by a calculation later....
	this.fships = fships;
	this.eships = eships;
	
	this.links[0] = new coordinate( x0, y0 );
	this.links[1] = new coordinate( x1, y1 );
	this.links[2] = new coordinate( x2, y2 );
	this.links[3] = new coordinate( x3, y3 );
	
	if ( x > maxX )
		maxX = x;
	if ( x < minX )
		minX = x;
	if ( y > maxY )
		maxY = y;
	if ( y < minY )
		minY = y;
}

function linkExists ( index, linkX, linkY )
{
	var i;
	
	for ( i=0; i<=3; i++ )
		if ( (universe[index].links[i].x == linkX )
			&& (universe[index].links[i].y == linkY )
			)
			return true;
	return false;
}

function planetExists ( x, y )
{
	var i;
	
	for ( i=0; i<nPlanets; i++ )
		if ( (universe[i].loc.x == x) && (universe[i].loc.y == y) )
			return i;
	return null;
}

function drawMap( )
{
	var i, j, index;
	
	document.write("<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0>");
	for ( j=maxY; j>=minY; j-- )
	{
		document.write("<TR>");
		for ( i=minX; i<=maxX; i++ )
		{
			document.write("<TD colspan=3 ALIGN='CENTER'>");
			if ( (index=planetExists(i,j)) != null )
				if ( linkExists( index, i, (j+1) ) )
					document.write("<IMG SRC='",imagePath,"vert.gif'>");
			document.write("</TD>");
		}
		document.write("</TR><TR>");
		for ( i=minX; i<=maxX; i++ )
		{
			document.write("<TD valign=middle>");
			if ( (index=planetExists(i,j)) != null )
			{
				if ( linkExists( index, (i-1), j) )
					document.write("<IMG SRC='",imagePath,"horz.gif'>");
				document.write("</TD><TD align=center><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0>");
				document.write("<TR><TD align=center><font size=1>",universe[index].min,"</font></TD>");
				document.write("<TD align=center rowspan=3><img border=0 name='system:",index,"' src='",imagePath,universe[index].icon,"'></TD>");
				document.write("<TD align=center><font size=1>",universe[index].fuel,"</font></TD></TR>");
				document.write("<TR><TD align=center><font size=1>",universe[index].ag,"</font></TD>");
				document.write("<TD align=center><font size=1>",universe[index].pop,"</font></TD></TR>");
				document.write("<TR><TD align=center><font size=1>(",universe[index].fships,")</font></TD>");
				document.write("<TD align=center><font size=1>(",universe[index].eships,")</font></TD></TR>");
				document.write("<TR><TD align=center colspan=3><font size=1>",universe[index].name," (",universe[index].loc.x);
				document.write(",",universe[index].loc.y,")</font></TD></TR></TABLE></TD><TD valign=middle>");
				if ( linkExists( index, (i+1), j) )
					document.write("<IMG SRC='",imagePath,"horz.gif'>");
				document.write("</TD>");
			}
			else
				document.write("</TD><TD></TD><TD></TD>");
		}
		document.write("</TR><TR>");
		for ( i=minX; i<=maxX; i++ )
		{
			document.write("<TD colspan=3 ALIGN='CENTER'>");
			if ( (index=planetExists(i,j)) != null )
				if ( linkExists( index, i, (j-1) ) )
					document.write("<IMG SRC='",imagePath,"vert.gif'>");
			document.write("</TD>");
		}
		document.write("</tr>");
	}
	document.write("</TABLE>");
}

function drawMovieControl()
{
//	document.write('<p><form name="moviecontrols">');
	document.write('<p><input type=submit value="Show Action" onclick="startMovie();">');
//	document.write('<p><input type=text size=3 value="',lastUpdate,'" name="updatenumber">');
//	document.write('</form>');
}

function showMovieFrame()
{
	for ( i=0; i<nPlanets; i++ )
		document.images[ ('system:'+i) ].src = imagePath + icons[ owner_history[i][movieUpdate] ];
	movieUpdate++;
	showUpdateNumber( movieUpdate );
	if ( movieUpdate < lastUpdate )
		setTimeout("showMovieFrame()", 1000);
}

function startMovie()
{
	movieUpdate = firstUpdate;
	showMovieFrame();
}

function showUpdateNumber( n )
{
	var use0 = false;
	var digit;
	
	digit = Math.floor ( n/100 );
	if ( digit > 0 )
	{
		use0 = true;
		document.images[ 'hundreds' ].src = 'd'+digit+'.gif';
	}
	else
	{
		document.images[ 'hundreds' ].src = 'blank.gif';
	}
		
	n = n - 100*digit;
	digit = Math.floor ( n/10 );
	if ( digit > 0 )
	{
		use0 = true;
		document.images[ 'tens' ].src = 'd'+digit+'.gif';
	}
	else if ( use0 )
		document.images[ 'tens' ].src = 'd0.gif';
	else
		document.images[ 'tens' ].src = 'blank.gif';
	
	n = n - 10*digit;
	document.images[ 'ones' ].src = 'd'+n+'.gif';
}

function drawUpdateNumber()
{
	document.write('<img src="upno.gif"><img name="hundreds" src="blank.gif" width=16 height=24>');
	document.write('<img name="tens" src="blank.gif" width=16 height=24><img name="ones" src="blank.gif" width=16 height=24><br>');
	showUpdateNumber( lastUpdate );
}
