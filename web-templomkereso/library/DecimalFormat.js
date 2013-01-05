/**
 * @class DecimalFormat
 * @constructor
 * @param {String} formatStr
 * @author Oskan Savli
 */
function DecimalFormat(formatStr)
{
     /**
     * @fieldOf DecimalFormat
     * @type String
     */
    this.prefix = '';
    /**
     * @fieldOf DecimalFormat
     * @type String
     */
    this.suffix = '';
    /**
     * @description Grouping size
     * @fieldOf DecimalFormat
     * @type String
     */
    this.comma = 0;
    /**
     * @description Minimum integer digits to be displayed
     * @fieldOf DecimalFormat
     * @type Number
     */
    this.minInt = 1;
    /**
     * @description Minimum fractional digits to be displayed
     * @fieldOf DecimalFormat
     * @type String
     */
    this.minFrac = 0;
    /**
     * @description Maximum fractional digits to be displayed
     * @fieldOf DecimalFormat
     * @type String
     */
    this.maxFrac = 0;
    
    // get prefix
    for (var i=0; i<formatStr.length; i++) {
        if (formatStr.charAt(i) == '#' || formatStr.charAt(i) == '0') {
            this.prefix = formatStr.substring(0,i);
            formatStr = formatStr.substring(i);
            break;
        }
    }
    
    // get suffix
    this.suffix = formatStr.replace(/[#]|[0]|[,]|[.]/g , '');

    // get number as string
    var numberStr = formatStr.replace(/[^0#,.]/g , '');
    
    var intStr = '';
    var fracStr = '';
    var point = numberStr.indexOf('.');
    if (point != -1) {
        intStr = numberStr.substring(0,point);
        fracStr = numberStr.substring(point+1);
    }
    else {
        intStr = numberStr;
    }
    
    var commaPos = intStr.lastIndexOf(',');
    if (commaPos != -1) {
        this.comma = intStr.length - 1 - commaPos;
    }
    
    intStr = intStr.replace(/[,]/g , ''); // remove commas

    fracStr = fracStr.replace(/[,]|[.]+/g , '');

    this.maxFrac = fracStr.length;
    var tmp = intStr.replace(/[^0]/g , ''); // remove all except zero
    if (tmp.length > this.minInt)
      this.minInt = tmp.length;
    tmp = fracStr.replace(/[^0]/g , '');
    this.minFrac = tmp.length;
}

/**
 * @description Formats given value
 * @methodOf DecimalFormat
 * @param {String} numberStr
 * @return {String} Formatted number
 * @author Oskan Savli
 */
DecimalFormat.prototype.format = function(numStr) { // 1223.06 --> $1,223.06
    // remove prefix, suffix and commas
    var numberStr = this.formatBack(numStr).toLowerCase();
    
    // do not format if not a number
    if (isNaN(numberStr) || numberStr.length == 0)
      return numStr;
    
    //scientific numbers
    if (i = numberStr.indexOf("e") != -1) {
      var n = Number(numberStr);
      if (n=="Infinity" || n=="-Infinity") return numberStr;
      numberStr = n+"";
      if(numberStr.indexOf('e') != -1) return numberStr;
    }

    var negative = false;
    // remove sign
    if (numberStr.charAt(0) == '-') {
      negative = true;
      numberStr = numberStr.substring(1);
    }
    else if (numberStr.charAt(0) == '+') {
      numberStr = numberStr.substring(1);
    }

    var point = numberStr.indexOf('.'); // position of point character
    var intStr = '';
    var fracStr = '';
    if (point != -1) {
        intStr = numberStr.substring(0,point);
        fracStr = numberStr.substring(point+1);
    }
    else {
        intStr = numberStr;
    }
    fracStr = fracStr.replace(/[.]/ , ''); // remove other point characters
    
    var isPercentage = this.suffix && this.suffix.charAt(0) === '%';
    // if percentage, number will be multiplied by 100.
    var minInt = this.minInt, minFrac = this.minFrac, maxFrac = this.maxFrac;
    if (isPercentage) {
      minInt -= 2;
      minFrac += 2;
      maxFrac += 2;
    }
    
    if (fracStr.length > maxFrac) { // round
        //case 6143
        var num = new Number('0.' + fracStr);
        num = (maxFrac == 0)? Math.round(num) : num.toFixed(maxFrac);
        // toFixed method has bugs on IE (0.7 --> 0)
        fracStr = num.toString(10).substr(2);
        var c = (num>=1)? 1:0; //carry
        var x, i=intStr.length-1;
        while (c) { //increment intStr
          if (i==-1) {
            intStr = '1'+intStr;
            break;
          }
          else {
            x = intStr.charAt(i);
            if (x==9) {x='0'; c=1;}
            else {x = (++x)+''; c=0;}
            intStr = intStr.substring(0,i) + x + intStr.substring(i+1,intStr.length);
            i--;
          }
        }
    }
    for (var i=fracStr.length; i<minFrac; i++) { // if minFrac=4 then 1.12 --> 1.1200
        fracStr = fracStr + '0';
    }
    while (fracStr.length > minFrac && fracStr.charAt(fracStr.length-1) == '0') { // if minInt=4 then 00034 --> 0034)
        fracStr = fracStr.substring(0,fracStr.length-1);
    }
    
    for (var i=intStr.length; i<minInt; i++) { // if minInt=4 then 034 --> 0034
        intStr = '0' + intStr;
    }
    while (intStr.length > minInt && intStr.charAt(0) == '0') { // if minInt=4 then 00034 --> 0034)
        intStr = intStr.substring(1);
    }
    
    if (isPercentage) { // multiply by 100
      intStr += fracStr.substring(0,2);
      fracStr = fracStr.substring(2);
    }
    
    var j = 0;
    for(var i=intStr.length; i>0; i--) { // add commas
        if (j != 0 && j%this.comma == 0) {
            intStr = intStr.substring(0,i) + ',' + intStr.substring(i);
            j = 0;
        }
        j++;
    }

    var formattedValue;
    if (fracStr.length > 0)
        formattedValue = this.prefix + intStr + '.' + fracStr + this.suffix;
    else
        formattedValue = this.prefix + intStr + this.suffix;
        
    if (negative) {
        formattedValue = '-' + formattedValue;
    }
    
    return formattedValue;
}


/**
 * @description Converts formatted value back to non-formatted value
 * @methodOf DecimalFormat
 * @param {String} fNumberStr Formatted number
 * @return {String} Original number
 * @author Oskan Savli
 */
DecimalFormat.prototype.formatBack = function(fNumStr) { // $1,223.06 --> 1223.06
  fNumStr += ''; //ensure it is string
  if (!fNumStr) return ''; //do not return undefined or null
  if (!isNaN(fNumStr)) return this.getNumericString(fNumStr);
  var fNumberStr = fNumStr;
  var negative = false;
  if (fNumStr.charAt(0) == '-') {
    fNumberStr = fNumberStr.substr(1);
    negative = true;
  }
  var pIndex = fNumberStr.indexOf(this.prefix);
  var sIndex = (this.suffix == '')? fNumberStr.length : fNumberStr.indexOf(this.suffix, this.prefix.length+1);
  if (pIndex == 0 && sIndex > 0) {
    // remove suffix
    fNumberStr = fNumberStr.substr(0,sIndex);
    // remove prefix
    fNumberStr = fNumberStr.substr(this.prefix.length);
    // remove commas
    fNumberStr = fNumberStr.replace(/,/g , '');
    if (negative)
      fNumberStr = '-' + fNumberStr;
    if (!isNaN(fNumberStr))
      return this.getNumericString(fNumberStr);
  }
  return fNumStr;
}
/**
 * @description We shouldn't return strings like 1.000 in formatBack method.
 * However, using only Number(str) is not enough, because it omits . in big numbers
 * like 23423423423342234.34 => 23423423423342236 . There's a conflict in cases
 * 6143 and 6541.
 * @methodOf DecimalFormat
 * @param {String} str Numberic string
 * @return {String} Corrected numeric string
 * @author Serdar Bicer
 */
DecimalFormat.prototype.getNumericString = function(str){
    //first convert to number
    var num = new Number(str);
    //check if there is a missing dot
    var numStr = num + '';
    if (str.indexOf('.')>-1 && numStr.indexOf('.')<0){
        //check if original string has all zeros after dot or not
        for (var i=str.indexOf('.')+1;i<str.length;i++){
            //if not, this means we lost precision
            if (str.charAt(i) !== '0') return str;
        }
        return numStr;
    }
    return str;
}