var ctx;
var pen = 0;
var x, y;
var canvas;
var lastPoints;
var canvaspos;

var easing = 0.4;

var requestsent = false;

function init(){  
    canvas = document.getElementById('layer');  
    if (canvas.getContext){  
        ctx = canvas.getContext('2d');
        ctx.fillStyle = "black";
		ctx.lineWidth = 2;
    }

	lastPoints = Array();

	canvaspos = $("#layer").offset();

    if (canvas.getContext) {
        ctx = canvas.getContext('2d');
        ctx.fillStyle = "black";
		ctx.lineWidth = 2;
        ctx.beginPath();

        canvas.onmousedown = startDraw;
        canvas.onmouseup = stopDraw;
        canvas.ontouchstart = startDraw;
        canvas.ontouchstop = stopDraw;
        canvas.ontouchmove = drawMouse;
    }



    return 0;
}

function setXY(new_x, new_y) { 
    x = new_x;
    y = new_y;

    return 0;
}

function toggleState(state) { pen = state; }

function setStyle(style) {


ctx.lineWidth = 2;

    switch(style) {
		
		
		
        case "red":
            ctx.strokeStyle = "rgb(255,0,0)";
            break;
        case "blue":
            ctx.strokeStyle = "rgb(0,0,255)";
            break;
        case "green":
            ctx.strokeStyle = "rgb(0,255,0)";
            break;
        case "erase":
				ctx.globalCompositeOperation = "copy";
				ctx.lineWidth = 10;
	            ctx.strokeStyle = "rgba(0,0,0,0)";
	   		break;
	    default:
            ctx.strokeStyle = "black";
        break;
    }

    return 0;
}

function canSketch() { 

	if(confirm("Are you sure? This will loose unsaved work."))
		window.close()
}

function canComment(originalID) { 

	if(confirm("Are you sure? This will loose unsaved work."))
		location.href = "data/"+originalID+".html";
}

function postSketch(originalID) { 

	if(requestsent){
		return false;
	}
	
	title = $('#sketch_title').val();
	if(!title){
		alert('Please enter a title for the sketch!');
		return false;
	}
	
	requestsent = true;
	
	
    var img = canvas.toDataURL("image/png");
	img = img.replace(/^data:image\/png;base64,/, "");

	$.post("parser.php", { image: img, postID: originalID, title: title },
		function(data){
			//window.opener.location.href = window.opener.location.href
			window.opener.post_form.elements["itemloop"].value = 1;
			window.opener.post_form.submit();
			
			window.close()
	   });
	  
}


function postComment(originalID) { 

	if(requestsent){
		return false;
	}
	requestsent = true;
	
    var img = canvas.toDataURL("image/png");
	img = img.replace(/^data:image\/png;base64,/, "");

	$.post("parser.php", { image: img, originalID: originalID },
	   function(data){
			location.href = "data/"+originalID+".html";
	   });

}

// Author: Richard Garside - www.nogginbox.co.uk [2010]

function startDraw(e) {
    if (e.touches) {
        // Touch event
        for (var i = 1; i <= e.touches.length; i++) {
            lastPoints[i] = getCoords(e.touches[i - 1]); // Get info for finger #1
        }
    }
    else {
        // Mouse event
        lastPoints[0] = getCoords(e);
        canvas.onmousemove = drawMouse;
    }
    
    return false;
}

// Called whenever cursor position changes after drawing has started
function stopDraw(e) {
    e.preventDefault();
    canvas.onmousemove = null;
}

function drawMouse(e) {
    if (e.touches) {
        // Touch Enabled
        for (var i = 1; i <= e.touches.length; i++) {
            var p = getCoords(e.touches[i - 1]); // Get info for finger i
            lastPoints[i] = drawLine(lastPoints[i].x, lastPoints[i].y, p.x, p.y);
        }
    }
    else {
        // Not touch enabled
        var p = getCoords(e);
        lastPoints[0] = drawLine(lastPoints[0].x, lastPoints[0].y, p.x, p.y);
    }
    ctx.stroke();
    ctx.closePath();
    ctx.beginPath();

    return false;
}

// Draw a line on the canvas from (s)tart to (e)nd
function drawLine(sX, sY, eX, eY) {
    ctx.moveTo(sX, sY);
    ctx.lineTo(eX, eY);
    return { x: eX, y: eY };
}

// Get the coordinates for a mouse or touch event
function getCoords(e) {
    if (e.offsetX) {
        return { x: e.offsetX, y: e.offsetY };
    }
    else if (e.layerX) {
        return { x: e.layerX, y: e.layerY };
    }
    else {
        //return { x: e.pageX - canvas.offsetLeft, y: e.pageY - canvas.offsetTop };
 		return { x: e.pageX - canvaspos.left, y: e.pageY - canvaspos.top };
    }
}
