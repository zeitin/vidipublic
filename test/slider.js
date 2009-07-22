//---------------------------------+
//  CARPE  S l i d e r        1.3  |
//  2005 - 12 - 10                 |
//  By Tom Hermansson Snickars     |
//  Copyright CARPE Design         |
//  http://carpe.ambiprospect.com/ |
//---------------------------------+

// carpeGetElementByID: Cross-browser version of "document.getElementById()"
function carpeGetElementById(element)
{
	if (document.getElementById) element = document.getElementById(element);
	else if (document.all) element = document.all[element];
	else element = null;
	return element;
}
// carpeLeft: Cross-browser version of "element.style.left"
function carpeLeft(elmnt, pos)
{
	if (!(elmnt = carpeGetElementById(elmnt))) return 0;
	if (elmnt.style && (typeof(elmnt.style.left) == 'string')) {
		if (typeof(pos) == 'number') elmnt.style.left = pos + 'px';
		else {
			pos = parseInt(elmnt.style.left);
			if (isNaN(pos)) pos = 0;
		}
	}
	else if (elmnt.style && elmnt.style.pixelLeft) {
		if (typeof(pos) == 'number') elmnt.style.pixelLeft = pos;
		else pos = elmnt.style.pixelLeft;
	}
	return pos;
}
// carpeTop: Cross-browser version of "element.style.top"
function carpeTop(elmnt, pos)
{
	if (!(elmnt = carpeGetElementById(elmnt))) return 0;
	if (elmnt.style && (typeof(elmnt.style.top) == 'string')) {
		if (typeof(pos) == 'number') elmnt.style.top = pos + 'px';
		else {
			pos = parseInt(elmnt.style.top);
			if (isNaN(pos)) pos = 0;
		}
	}
	else if (elmnt.style && elmnt.style.pixelTop) {
		if (typeof(pos) == 'number') elmnt.style.pixelTop = pos;
		else pos = elmnt.style.pixelTop;
	}
	return pos;
}
// moveSlider: Handles slider and display while dragging
function moveSlider(evnt)
{
	var evnt = (!evnt) ? window.event : evnt; // The mousemove event
	if (mouseover) { // Only if slider is dragged
		x = pxLeft + evnt.screenX - xCoord // Horizontal mouse position relative to allowed slider positions
		y = pxTop + evnt.screenY - yCoord // Horizontal mouse position relative to allowed slider positions
		if (x > xMax) x = xMax // Limit horizontal movement
		if (x < 0) x = 0 // Limit horizontal movement
		if (y > yMax) y = yMax // Limit vertical movement
		if (y < 0) y = 0 // Limit vertical movement
		carpeLeft(sliderObj.id, x)  // move slider to new horizontal position
		carpeTop(sliderObj.id, y) // move slider to new vertical position
		sliderVal = x + y // pixel value of slider regardless of orientation
		sliderPos = (sliderObj.pxLen / sliderObj.valCount) * Math.round(sliderObj.valCount * sliderVal / sliderObj.pxLen)
		v = Math.round((sliderPos * sliderObj.scale + sliderObj.fromVal) * // calculate display value
			Math.pow(10, displayObj.dec)) / Math.pow(10, displayObj.dec)
		displayObj.value = v // put the new value in the slider display element
		return false
	}
	return
}
// moveSlider: Handles the start of a slider move.
function slide(evnt, orientation, length, from, to, count, decimals, display)
{
	if (!evnt) evnt = window.event;
	sliderObj = (evnt.target) ? evnt.target : evnt.srcElement; // Get the activated slider element.
	sliderObj.pxLen = length // The allowed slider movement in pixels.
	sliderObj.valCount = count ? count - 1 : length // Allowed number of values in the interval.
	displayObj = carpeGetElementById(display) // Get the associated display element.
	displayObj.dec = decimals // Number of decimals to be displayed.
	sliderObj.scale = (to - from) / length // Slider-display scale [value-change per pixel of movement].
	if (orientation == 'horizontal') { // Set limits for horizontal sliders.
		sliderObj.fromVal = from
		xMax = length
		yMax = 0
	}
	if (orientation == 'vertical') { // Set limits and scale for vertical sliders.
		sliderObj.fromVal = to
		xMax = 0
		yMax = length
		sliderObj.scale = -sliderObj.scale // Invert scale for vertical sliders. "Higher is more."
	}
	pxLeft = carpeLeft(sliderObj.id) // Sliders horizontal position at start of slide.
	pxTop  = carpeTop(sliderObj.id) // Sliders vertical position at start of slide.
	xCoord = evnt.screenX // Horizontal mouse position at start of slide.
	yCoord = evnt.screenY // Vertical mouse position at start of slide.
	mouseover = true
	document.onmousemove = moveSlider // Start the action if the mouse is dragged.
	document.onmouseup = sliderMouseUp // Stop sliding.
}
// sliderMouseup: Handles the mouseup event after moving a slider.
// Snaps the slider position to allowed/displayed value. 
function sliderMouseUp()
{
	mouseover = false // Stop the sliding.
	v = (displayObj.value) ? displayObj.value : 0 // Find last display value.
	pos = (v - sliderObj.fromVal)/(sliderObj.scale) // Calculate slider position (regardless of orientation).
	if (yMax == 0) carpeLeft(sliderObj.id, pos) // Snap horizontal slider to corresponding display position.
	if (xMax == 0) carpeTop(sliderObj.id, pos) // Snap vertical slider to corresponding display position.
	if (document.removeEventListener) { // Remove event listeners from 'document' (Moz&co).
//		document.removeEventListener('mousemove', moveSlider)
//		document.removeEventListener('mouseup', sliderMouseUp)
	}
	else if (document.detachEvent) { // Remove event listeners from 'document' (IE&co).
//		document.detachEvent('onmousemove', moveSlider)
//		document.detachEvent('onmouseup', sliderMouseUp)
	}
}
