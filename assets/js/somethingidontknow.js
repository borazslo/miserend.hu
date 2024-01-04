
function OpenPrintWindow(url, x, y) {
	var options = "toolbar=no,menubar=yes,scrollbars=yes,resizable=yes,width=" + x + ",height=" + y;
	msgWindow=window.open(url,"", options);
}

function OpenNewWindow(url, x, y) {
	var options = "toolbar=no,menubar=no,scrollbars=no,resizable=yes,width=" + x + ",height=" + y;
	msgWindow=window.open(url,"", options);
}

function OpenScrollWindow(url, x, y) {
	var options = "toolbar=no,menubar=no,scrollbars=yes,resizable=yes,width=" + x + ",height=" + y;
	msgWindow=window.open(url,"", options);
}

function UnCryptMailto(s) {
	var n=0;
	var r="";
	for(var i=0;i<s.length;i++) {
		n=s.charCodeAt(i);
		if (n>=8364) {n = 128;}
		r += String.fromCharCode(n-(2));
	}
	return r;
}

function EnCryptMailto(s) {
	var n=0;
	var r="";
	for(var i=0;i<s.length;i++) {
		n=s.charCodeAt(i);
		if (n>=8364) {n = 128;}
		r += String.fromCharCode(n+(2));
	}
	return r;
}

function linkTo_UnCryptMailto(s)	{
	location.href=UnCryptMailto(s);
}