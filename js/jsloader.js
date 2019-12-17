function glpiURLRoot() {
   var scriptName = '/plugins/formvalidation/js/jsloader.js';
   var glpiURL = $('script[src*="' + scriptName + '"]')[0].src;
   var pos = glpiURL.search(scriptName);

   return glpiURL.substr(0, pos);
}

if ( typeof String.prototype.hashCode === 'undefined' ) {
   String.prototype.hashCode = function() {
      var hash = 0, i, chr, len;
      if (this.length === 0) {
         return hash;
      }
      for (i = 0, len = this.length; i < len; i++) {
         chr   = this.charCodeAt(i);
         hash  = ((hash << 5) - hash) + chr;
         hash |= 0; // Convert to 32bit integer
      }
      return hash;
   };
}


var d = document, g = d.createElement('script'), sl = d.getElementsByTagName('script'); g.type = 'text/javascript';
g.src = glpiURLRoot() + '/plugins/formvalidation/js/helpers_function.js.php?_=' + document.location.href.hashCode();
sl[0].parentNode.appendChild( g );
