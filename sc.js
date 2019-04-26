function blabBox(sender, recipient, password)
{
	msgWin = window.open("", "instantMessage", "scrollbars=yes,status=no,toolbar=no,width=600,height=400");

	msgWin.document.open();
	msgWin.document.writeln('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">');
	msgWin.document.writeln('<html>');
	msgWin.document.writeln('<head>');
	msgWin.document.writeln('<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">');
	msgWin.document.writeln('<title>SC ', scServerVersion, ' @ ', scServerName, ': Send Message</title>');
	msgWin.document.writeln('<link REL=StyleSheet HREF="styles.css" TYPE="text/css"> ');
	msgWin.document.writeln('</head>');
	msgWin.document.writeln('<body>');
	msgWin.document.writeln('<form method=post action="sc.php">');
	msgWin.document.writeln('<input type=hidden name=section value="main">');
	msgWin.document.writeln('<input type=hidden name=page value="instantMessage">');
	msgWin.document.writeln('<input type=hidden name=recipient value="', recipient, '">');
	msgWin.document.writeln('<input type=hidden name=name value="', sender, '">');
	msgWin.document.writeln('<input type=hidden name=pass value="', password, '">');
	msgWin.document.writeln('<div class=pageTitle>', sender, ': Send Message</div>');
	msgWin.document.writeln('<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">');
	msgWin.document.writeln('<div class=center>You will send the following message to <b>', recipient, '</b>:</div>');
	msgWin.document.writeln('<div class=center style="margin-top: 10pt;"><textarea name=message rows=8 cols=60></textarea></div>');
	msgWin.document.writeln('<div class=center style="margin-top: 10pt;"><input type=submit name=sendInstantMessage value="Send"></div>');
	msgWin.document.writeln('</form></body></html>');
	msgWin.document.close();
}

//-------------------------------------------------------------------------------------------//

function blab()
{
	if (document.forms[0].onlinePlayers.value != 0)
		{
		blabBox( document.forms[0].name.value, document.forms[0].onlinePlayers.value, document.forms[0].pass.value);
		document.forms[0].onlinePlayers.selectedIndex = 0;
		}
	else
		alert('Select a player first.');
}

//-------------------------------------------------------------------------------------------//

function blabReply(recipient)
{
	blabBox(document.forms[0].name.value, recipient, document.forms[0].pass.value);
}

//-------------------------------------------------------------------------------------------//

function checkAll()
{
	for ( x = 0; x < document.forms[0].elements.length; x++ )
		{
		if (document.forms[0].elements[x].type == 'checkbox')
			document.forms[0].elements[x].checked = (document.forms[0].checkOrUncheck.value == 'check');
		}
	
	if (document.forms[0].checkOrUncheck.value == 'check')
		document.forms[0].checkOrUncheck.value = 'uncheck';
	else
		document.forms[0].checkOrUncheck.value = 'check';
}

//-------------------------------------------------------------------------------------------//

function getAnchorPosition(anchorname)
{
	var useWindow = false;
	var coordinates = new Object();
	var x = 0, y = 0;
	
	var use_gebi = false, use_css = false, use_layers = false;
	
	if (document.getElementById) use_gebi = true;
	else if (document.all) use_css = true;
	else if (document.layers) use_layers = true;
	
	if (use_gebi && document.all)
		{
		x = AnchorPosition_getPageOffsetLeft(document.all[anchorname]);
		y = AnchorPosition_getPageOffsetTop(document.all[anchorname]);
		}
	else if (use_gebi)
		{
		var o = document.getElementById(anchorname);
		
		x = o.offsetLeft;
		y = o.offsetTop;
		}
	else if (use_css)
		{
		x = AnchorPosition_getPageOffsetLeft(document.all[anchorname]);
		y = AnchorPosition_getPageOffsetTop(document.all[anchorname]);
		}
	else if (use_layers)
		{
		var found = 0;
		
		for (var i = 0; i < document.links.length; i++)
			{
			alert(document.layers[i].name);
			if (document.links[i].name == anchorname)
				{
				found = 1;
				break;
				}
			}
		
		if (found==0)
			{
			coordinates.x = 0;
			coordinates.y = 0;
			return coordinates;
			}
			
		alert(document.anchors[i].x);
			
		x = document.anchors[i].x;
		y = document.anchors[i].y;
		}
	else
		{
		coordinates.x = 0;
		coordinates.y = 0;
		return coordinates;
		}
		
	coordinates.x = x;
	coordinates.y = y;
	return coordinates;
}

function getAnchorWindowPosition(anchorname)
{
	var coordinates = getAnchorPosition(anchorname);
	coordinates.x = 0;
	coordinates.y = 0;
	
	var x = 0;
	var y = 0;
	
	if (document.getElementById)
		{
		if (isNaN(window.screenX))
			{
			x = coordinates.x-document.body.scrollLeft + window.screenLeft;
			y = coordinates.y-document.body.scrollTop + window.screenTop;
			}
		else
			{
			x = coordinates.x + window.screenX + (window.outerWidth-window.innerWidth) - window.pageXOffset;
			y = coordinates.y + window.screenY + (window.outerHeight-24-window.innerHeight) - window.pageYOffset;
			}
		}
	else if (document.all)
		{
		x = coordinates.x - document.body.scrollLeft + window.screenLeft;
		y = coordinates.y - document.body.scrollTop + window.screenTop;
		}
	else if (document.layers)
		{
		x = coordinates.x + window.screenX + (window.outerWidth-window.innerWidth) - window.pageXOffset;
		y = coordinates.y + window.screenY + (window.outerHeight-24-window.innerHeight) - window.pageYOffset;
		}
		
	coordinates.x = x;
	coordinates.y = y;
	
	return coordinates;
}

//-------------------------------------------------------------------------------------------//

function showHidePlayers(element)
{
	if (document.getElementById)
		{
		if ( document.getElementById(element).style.visibility == "visible" )
			{
			document.getElementById(element).style.visibility = "hidden";
			document.getElementById(element).style.display = "none";
			}
		else
			{
			document.getElementById(element).style.visibility = "visible";
			document.getElementById(element).style.display = "block";
			}
		}
	else if (document.layers)
		{
		if ( document.layers[element].visibility == "show" )
			{
			document.layers[element].visibility = "hidden";
			document.layers[element].style.display = "none";
			}
		else
			{
			document.layers[element].visibility = "show";
			document.layers[element].style.display = "block";
			}
		}
	else if (document.all)
		{
		if (document.all[element].style.visibility == "visible")
			{
			document.all[element].style.visibility = "hidden";
			document.all[element].style.display = "none";
			}
		else
			{document.all[element].style.visibility = "visible";
			document.all[element].style.display = "block";
			}

		}
}