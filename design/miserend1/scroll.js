                                   
orientation = "horizontal"        // Orientation of scroller (Horizontal or vertical)
scrollerWidth = "460"             // Width of entire scroller.
scrollerHeight = "110"             // Height of Scroller.
borderWidth = 0                   // Width of border. (use 0 for no border).
borderColour = ""          // Colour of scroller border. (Use either hexadecimal or text values. e.g. "#FF0000" or "Red") 
backColour = ""              // Colour of scroller background. (Use either hexadecimal or text values. e.g. "#FF0000" or "Red") 
staticColour = ""          // Colour of scroller items that are NOT a link. (including scrollerDivider characters)
stopScroll = 1                    // Pause the scroller on mouseOver. (use 0 for no.)
textAlignment="center"            // Alignment of each scroller item.  Only really makes a difference on vertical scroller
                                  // (center, left, right, justify)

// Scroller Links
linkFont = "arial"                // Font of scroller links;
linkWeight = "normal"             // Weight of scroller links;
linkColour = "#006600"            // Colour of scroller links
linkSize = "10"                   // Size of links (in points)
linkDecoration = "none"           // Decoration of links. (underline, overline, none)

// Scroller Links On MouseOver
slinkFont = "arial"               // Font of scroller links;
slinkWeight = "normal"            // Weight of scroller links;
slinkColour = "#AFAF9D"           // Colour of scroller links
slinkSize = "10"                  // Size of links (in points)
slinkDecoration = "none"     // Decoration of links. (underline, overline, none)

scrollerDivider = "<img src=img/space.gif width=5 height=3>" // Character to place between each scroller item. 
                                  // <img> tags can be used if an image is required. 
                                  // Use "0" for nothing.  For Vertical scrollers, it is best to use "<br>"
ns4Text = "Click Here to view our articles";  // Alternative text to display in Netscape 4.
ns4URL = "http://www.ricom.co.uk";            // URL of link in NS4. If no URL is required, enter "none"
ns4Target = "_top";                           // Frame target for link in NS4
////// DO NOT EDIT BELOW THIS LINE  ///////////////////////////////////////////////////////////////////

//Browser Sniffer
var isIE = (document.getElementById && document.all)?true:false;
var isNS4 = (document.layers)?true:false;
var isNS6 = (document.getElementById && !document.all)?true:false;
var isLoaded=false;
var pereg = 0;


// Build the scroller and place it on the page
function buildScroller()
{
  boundry='<div name="boundry" id="boundry" style="position:relative"></div>';
  document.writeln(boundry);
}

function loadScroller(){
  if(isNS4){
    scroller='<table border="0" cellpadding="0" cellspacing="0" width="'+scrollerWidth+'" bgcolor="'+borderColour+'"><tr><td>'
    scroller+='<table border="0" cellpadding="0" cellspacing="0" width="100%" height="'+scrollerHeight+'" bgcolor="'+backColour+'"><tr><td align="center" nowrap><p>';
    if(ns4URL.toLowerCase()!="none"){scroller+='<a href="'+ns4URL+'" class="rcScroller" target="'+ns4Target+'">'+ns4Text+'</a>';}
    else{scroller+=ns4Text;} 
    scroller+='</p></td></tr></table></td></tr></table>'   
  }else{
    scroller='<table border="0" cellpadding="0" cellspacing="0" style="width:'+scrollerWidth+';height:'+scrollerHeight+';border:'+borderWidth+'px solid '+borderColour+';background-color:'+backColour+'">';
    scroller+='<tr valign="middle"><td><div id="div" style="';
    if(orientation.toLowerCase()=="vertical"){scroller+='height:'+scrollerHeight+';';}
    scroller+='width:'+scrollerWidth+'; position:relative; background-color:'+backColour+'; overflow:hidden">';
    scroller+='<div id="div1" style="position:relative; left:0; z-index:1">';
    scroller+='<table border="0" name="table" id="table"';
    if(orientation.toLowerCase()=="vertical"){scroller+='style="width:'+scrollerWidth+'"';}
    scroller+='><tr>';
    y=0;
    while (y<4)
    {
      for (x=0; x<(Article.length); x++)
      {
        if(orientation.toLowerCase()=="vertical"){scroller+='';}
        scroller+='<td ';
        if(orientation.toLowerCase()=="horizontal"){scroller+='nowrap';} if(stopScroll==1){scroller+=' onMouseOver="stopScroller();" onMouseOut="setWidth()"';}
        scroller+='>';
        if(Article[x][1].toLowerCase()!="none"){scroller+='<a href="'+Article[x][1]+'"><img border="0" src="'+Article[x][0]+'" title="'+Article[x][2]+'"><\/a>';
        }else{scroller+=Article[x][0];}          
        scroller+='<\/td>';
        
        if(orientation.toLowerCase()=="vertical"){scroller+='';}
              
        if(scrollerDivider.toLowerCase() != "none"){scroller+='<td nowrap>'+scrollerDivider+'<\/td>';}
      }
      y++
    }
    scroller+='<\/tr><\/table><\/div><\/div><\/td><\/tr><\/table>';  
  }
  document.getElementById("boundry").innerHTML=scroller;
  setWidth();
}


// Ensure the width of the scroller is divisible by 2. This allows smooth flowing of the scrolled content
function setWidth()
{ 
  tableObj=(isIE)?document.all("table"):document.getElementById("table"); 
  obj=(isIE)?document.all.div1:document.getElementById("div1");   
  objWidth=(orientation.toLowerCase()=="horizontal")?getOffset(tableObj,"width"):getOffset(tableObj,"height");
  HalfWidth=Math.floor(objWidth/2);
  newWidth = (HalfWidth*2)+2;
  obj.style.width=newWidth
//  moveLayer(obj, newWidth);
  
}

// Move the layer by one pixel to the left
//function moveLayer(obj, width)

function moveLayer()
{
  tableObj=(isIE)?document.all("table"):document.getElementById("table"); 
  obj=(isIE)?document.all.div1:document.getElementById("div1");   
  objWidth=(orientation.toLowerCase()=="horizontal")?getOffset(tableObj,"width"):getOffset(tableObj,"height");
  HalfWidth=Math.floor(objWidth/2);
  width = (HalfWidth*2)+2;
  obj.style.width=width;
  pereg = 1;
  
  maxLeft = (0-(width/2)+2)/2
  if(orientation.toLowerCase()=="horizontal"){
    obj.style.left=(parseInt(obj.style.left) <= maxLeft)?0:parseInt(obj.style.left)-1
  }else{
    if(obj.style.top==""){obj.style.top=0;}
   // alert(obj.style.top)
    if (parseInt(obj.style.top)<(0-(width/2)+6)){
      obj.style.top = 0
    }else{
      obj.style.top = parseInt(obj.style.top)-2
    }
  }
  timer = setTimeout ("moveLayer(obj, "+width+");", 20); 
}

// Get width and height of layer
function getOffset(obj, dim) 
{
  if(dim=="width")
  {
    oWidth = obj.offsetWidth
    return oWidth
  }  
  else if(dim=="height")
  {
    oHeight = obj.offsetHeight
    return oHeight
  }    
}

function stopScroller()
{
  if(pereg == 1)
	clearTimeout(timer)  
}