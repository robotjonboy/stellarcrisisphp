function ensureHiddenField(form, name, value)
{
	var field = form[name];
	if (!field)
		{
		field = document.createElement('input');
		field.type = 'hidden';
		field.name = name;
		form.appendChild(field);
		}
	field.value = value;
	return field;
}

//-------------------------------------------------------------------------------------------//

function openInstantMessage(recipient)
{
	var form = document.forms[0];
	if (!form)
		return;

	ensureHiddenField(form, 'recipient', recipient);
	if (form.page)
		form.page.value = 'instantMessage';
	else
		ensureHiddenField(form, 'page', 'instantMessage');

	if (form.section)
		form.section.value = 'main';
	else
		ensureHiddenField(form, 'section', 'main');

	form.submit();
}

//-------------------------------------------------------------------------------------------//

function blab()
{
	var form = document.forms[0];
	if (!form || !form.onlinePlayers)
		return;

	if (form.onlinePlayers.selectedIndex > 0)
		{
		openInstantMessage(form.onlinePlayers.value);
		form.onlinePlayers.selectedIndex = 0;
		}
	else
		alert('Select a player first.');
}

//-------------------------------------------------------------------------------------------//

function blabReply(recipient)
{
	openInstantMessage(recipient);
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
//-------------------------------------------------------------------------------------------//
// Player listing tooltips: tap/click to toggle (hover still works via CSS).

function closePlayerListingTooltips()
{
	var openLinks = document.querySelectorAll('.playerListing a.tooltipOpen');
	for (var i = 0; i < openLinks.length; i++)
		openLinks[i].className = openLinks[i].className.replace(/\btooltipOpen\b/g, '').replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
}

function initPlayerListingTooltips()
{
	if (!document.addEventListener || !document.querySelectorAll)
		return;

	document.addEventListener('click', function(event)
		{
		var target = event.target;
		while (target && target !== document)
			{
			if (target.tagName && target.tagName.toLowerCase() === 'a' &&
				target.parentNode && (' ' + target.parentNode.className + ' ').indexOf(' playerListing ') !== -1)
				break;
			target = target.parentNode;
			}

		if (target && target !== document && target.tagName && target.tagName.toLowerCase() === 'a')
			{
			event.preventDefault();
			var alreadyOpen = (' ' + target.className + ' ').indexOf(' tooltipOpen ') !== -1;
			closePlayerListingTooltips();
			if (!alreadyOpen)
				target.className = (target.className ? target.className + ' ' : '') + 'tooltipOpen';
			return;
			}

		closePlayerListingTooltips();
		});
}

if (document.addEventListener)
	document.addEventListener('DOMContentLoaded', initPlayerListingTooltips);
