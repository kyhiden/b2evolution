function hex_sha1(a){return rstr2hex(rstr_sha1(str2rstr_utf8(a)))}function b64_sha1(a){return rstr2b64(rstr_sha1(str2rstr_utf8(a)))}function any_sha1(a,b){return rstr2any_sha1(rstr_sha1(str2rstr_utf8(a)),b)}function hex_hmac_sha1(a,b){return rstr2hex(rstr_hmac_sha1(str2rstr_utf8(a),str2rstr_utf8(b)))}function b64_hmac_sha1(a,b){return rstr2b64(rstr_hmac_sha1(str2rstr_utf8(a),str2rstr_utf8(b)))}function any_hmac_sha1(a,b,c){return rstr2any_sha1(rstr_hmac_sha1(str2rstr_utf8(a),str2rstr_utf8(b)),c)}function sha1_vm_test(){return"a9993e364706816aba3e25717850c26c9cd0d89d"==hex_sha1("abc")}function rstr_sha1(a){return binb2rstr(binb_sha1(rstr2binb(a),8*a.length))}function rstr_hmac_sha1(a,b){var c=rstr2binb(a);c.length>16&&(c=binb_sha1(c,8*a.length));for(var d=Array(16),e=Array(16),f=0;16>f;f++)d[f]=909522486^c[f],e[f]=1549556828^c[f];var g=binb_sha1(d.concat(rstr2binb(b)),512+8*b.length);return binb2rstr(binb_sha1(e.concat(g),672))}function rstr2hex(a){for(var b,c=hexcase?"0123456789ABCDEF":"0123456789abcdef",d="",e=0;e<a.length;e++)b=a.charCodeAt(e),d+=c.charAt(b>>>4&15)+c.charAt(15&b);return d}function rstr2b64(a){for(var b="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",c="",d=a.length,e=0;d>e;e+=3)for(var f=a.charCodeAt(e)<<16|(d>e+1?a.charCodeAt(e+1)<<8:0)|(d>e+2?a.charCodeAt(e+2):0),g=0;4>g;g++)c+=8*e+6*g>8*a.length?b64pad:b.charAt(f>>>6*(3-g)&63);return c}function rstr2any_sha1(a,b){var c,d,e,f,g=b.length,h=Array(),i=Array(Math.ceil(a.length/2));for(c=0;c<i.length;c++)i[c]=a.charCodeAt(2*c)<<8|a.charCodeAt(2*c+1);for(;i.length>0;){for(f=Array(),e=0,c=0;c<i.length;c++)e=(e<<16)+i[c],d=Math.floor(e/g),e-=d*g,(f.length>0||d>0)&&(f[f.length]=d);h[h.length]=e,i=f}var j="";for(c=h.length-1;c>=0;c--)j+=b.charAt(h[c]);var k=Math.ceil(8*a.length/(Math.log(b.length)/Math.log(2)));for(c=j.length;k>c;c++)j=b[0]+j;return j}function str2rstr_utf8(a){for(var b,c,d="",e=-1;++e<a.length;)b=a.charCodeAt(e),c=e+1<a.length?a.charCodeAt(e+1):0,b>=55296&&56319>=b&&c>=56320&&57343>=c&&(b=65536+((1023&b)<<10)+(1023&c),e++),127>=b?d+=String.fromCharCode(b):2047>=b?d+=String.fromCharCode(192|b>>>6&31,128|63&b):65535>=b?d+=String.fromCharCode(224|b>>>12&15,128|b>>>6&63,128|63&b):2097151>=b&&(d+=String.fromCharCode(240|b>>>18&7,128|b>>>12&63,128|b>>>6&63,128|63&b));return d}function str2rstr_utf16le(a){for(var b="",c=0;c<a.length;c++)b+=String.fromCharCode(255&a.charCodeAt(c),a.charCodeAt(c)>>>8&255);return b}function str2rstr_utf16be(a){for(var b="",c=0;c<a.length;c++)b+=String.fromCharCode(a.charCodeAt(c)>>>8&255,255&a.charCodeAt(c));return b}function rstr2binb(a){for(var b=Array(a.length>>2),c=0;c<b.length;c++)b[c]=0;for(var c=0;c<8*a.length;c+=8)b[c>>5]|=(255&a.charCodeAt(c/8))<<24-c%32;return b}function binb2rstr(a){for(var b="",c=0;c<32*a.length;c+=8)b+=String.fromCharCode(a[c>>5]>>>24-c%32&255);return b}function binb_sha1(a,b){a[b>>5]|=128<<24-b%32,a[(b+64>>9<<4)+15]=b;for(var c=Array(80),d=1732584193,e=-271733879,f=-1732584194,g=271733878,h=-1009589776,i=0;i<a.length;i+=16){for(var j=d,k=e,l=f,m=g,n=h,o=0;80>o;o++){c[o]=16>o?a[i+o]:bit_rol(c[o-3]^c[o-8]^c[o-14]^c[o-16],1);var p=safe_add(safe_add(bit_rol(d,5),sha1_ft(o,e,f,g)),safe_add(safe_add(h,c[o]),sha1_kt(o)));h=g,g=f,f=bit_rol(e,30),e=d,d=p}d=safe_add(d,j),e=safe_add(e,k),f=safe_add(f,l),g=safe_add(g,m),h=safe_add(h,n)}return Array(d,e,f,g,h)}function sha1_ft(a,b,c,d){return 20>a?b&c|~b&d:40>a?b^c^d:60>a?b&c|b&d|c&d:b^c^d}function sha1_kt(a){return 20>a?1518500249:40>a?1859775393:60>a?-1894007588:-899497514}function safe_add(a,b){var c=(65535&a)+(65535&b),d=(a>>16)+(b>>16)+(c>>16);return d<<16|65535&c}function bit_rol(a,b){return a<<b|a>>>32-b}function hex_md5(a){return rstr2hex(rstr_md5(str2rstr_utf8(a)))}function b64_md5(a){return rstr2b64(rstr_md5(str2rstr_utf8(a)))}function any_md5(a,b){return rstr2any_md5(rstr_md5(str2rstr_utf8(a)),b)}function hex_hmac_md5(a,b){return rstr2hex(rstr_hmac_md5(str2rstr_utf8(a),str2rstr_utf8(b)))}function b64_hmac_md5(a,b){return rstr2b64(rstr_hmac_md5(str2rstr_utf8(a),str2rstr_utf8(b)))}function any_hmac_md5(a,b,c){return rstr2any_md5(rstr_hmac_md5(str2rstr_utf8(a),str2rstr_utf8(b)),c)}function md5_vm_test(){return"900150983cd24fb0d6963f7d28e17f72"==hex_md5("abc")}function rstr_md5(a){return binl2rstr(binl_md5(rstr2binl(a),8*a.length))}function rstr_hmac_md5(a,b){var c=rstr2binl(a);c.length>16&&(c=binl_md5(c,8*a.length));for(var d=Array(16),e=Array(16),f=0;16>f;f++)d[f]=909522486^c[f],e[f]=1549556828^c[f];var g=binl_md5(d.concat(rstr2binl(b)),512+8*b.length);return binl2rstr(binl_md5(e.concat(g),640))}function rstr2any_md5(a,b){var c,d,e,f,g,h=b.length,i=Array(Math.ceil(a.length/2));for(c=0;c<i.length;c++)i[c]=a.charCodeAt(2*c)<<8|a.charCodeAt(2*c+1);var j=Math.ceil(8*a.length/(Math.log(b.length)/Math.log(2))),k=Array(j);for(d=0;j>d;d++){for(g=Array(),f=0,c=0;c<i.length;c++)f=(f<<16)+i[c],e=Math.floor(f/h),f-=e*h,(g.length>0||e>0)&&(g[g.length]=e);k[d]=f,i=g}var l="";for(c=k.length-1;c>=0;c--)l+=b.charAt(k[c]);return l}function rstr2binl(a){for(var b=Array(a.length>>2),c=0;c<b.length;c++)b[c]=0;for(var c=0;c<8*a.length;c+=8)b[c>>5]|=(255&a.charCodeAt(c/8))<<c%32;return b}function binl2rstr(a){for(var b="",c=0;c<32*a.length;c+=8)b+=String.fromCharCode(a[c>>5]>>>c%32&255);return b}function binl_md5(a,b){a[b>>5]|=128<<b%32,a[(b+64>>>9<<4)+14]=b;for(var c=1732584193,d=-271733879,e=-1732584194,f=271733878,g=0;g<a.length;g+=16){var h=c,i=d,j=e,k=f;c=md5_ff(c,d,e,f,a[g+0],7,-680876936),f=md5_ff(f,c,d,e,a[g+1],12,-389564586),e=md5_ff(e,f,c,d,a[g+2],17,606105819),d=md5_ff(d,e,f,c,a[g+3],22,-1044525330),c=md5_ff(c,d,e,f,a[g+4],7,-176418897),f=md5_ff(f,c,d,e,a[g+5],12,1200080426),e=md5_ff(e,f,c,d,a[g+6],17,-1473231341),d=md5_ff(d,e,f,c,a[g+7],22,-45705983),c=md5_ff(c,d,e,f,a[g+8],7,1770035416),f=md5_ff(f,c,d,e,a[g+9],12,-1958414417),e=md5_ff(e,f,c,d,a[g+10],17,-42063),d=md5_ff(d,e,f,c,a[g+11],22,-1990404162),c=md5_ff(c,d,e,f,a[g+12],7,1804603682),f=md5_ff(f,c,d,e,a[g+13],12,-40341101),e=md5_ff(e,f,c,d,a[g+14],17,-1502002290),d=md5_ff(d,e,f,c,a[g+15],22,1236535329),c=md5_gg(c,d,e,f,a[g+1],5,-165796510),f=md5_gg(f,c,d,e,a[g+6],9,-1069501632),e=md5_gg(e,f,c,d,a[g+11],14,643717713),d=md5_gg(d,e,f,c,a[g+0],20,-373897302),c=md5_gg(c,d,e,f,a[g+5],5,-701558691),f=md5_gg(f,c,d,e,a[g+10],9,38016083),e=md5_gg(e,f,c,d,a[g+15],14,-660478335),d=md5_gg(d,e,f,c,a[g+4],20,-405537848),c=md5_gg(c,d,e,f,a[g+9],5,568446438),f=md5_gg(f,c,d,e,a[g+14],9,-1019803690),e=md5_gg(e,f,c,d,a[g+3],14,-187363961),d=md5_gg(d,e,f,c,a[g+8],20,1163531501),c=md5_gg(c,d,e,f,a[g+13],5,-1444681467),f=md5_gg(f,c,d,e,a[g+2],9,-51403784),e=md5_gg(e,f,c,d,a[g+7],14,1735328473),d=md5_gg(d,e,f,c,a[g+12],20,-1926607734),c=md5_hh(c,d,e,f,a[g+5],4,-378558),f=md5_hh(f,c,d,e,a[g+8],11,-2022574463),e=md5_hh(e,f,c,d,a[g+11],16,1839030562),d=md5_hh(d,e,f,c,a[g+14],23,-35309556),c=md5_hh(c,d,e,f,a[g+1],4,-1530992060),f=md5_hh(f,c,d,e,a[g+4],11,1272893353),e=md5_hh(e,f,c,d,a[g+7],16,-155497632),d=md5_hh(d,e,f,c,a[g+10],23,-1094730640),c=md5_hh(c,d,e,f,a[g+13],4,681279174),f=md5_hh(f,c,d,e,a[g+0],11,-358537222),e=md5_hh(e,f,c,d,a[g+3],16,-722521979),d=md5_hh(d,e,f,c,a[g+6],23,76029189),c=md5_hh(c,d,e,f,a[g+9],4,-640364487),f=md5_hh(f,c,d,e,a[g+12],11,-421815835),e=md5_hh(e,f,c,d,a[g+15],16,530742520),d=md5_hh(d,e,f,c,a[g+2],23,-995338651),c=md5_ii(c,d,e,f,a[g+0],6,-198630844),f=md5_ii(f,c,d,e,a[g+7],10,1126891415),e=md5_ii(e,f,c,d,a[g+14],15,-1416354905),d=md5_ii(d,e,f,c,a[g+5],21,-57434055),c=md5_ii(c,d,e,f,a[g+12],6,1700485571),f=md5_ii(f,c,d,e,a[g+3],10,-1894986606),e=md5_ii(e,f,c,d,a[g+10],15,-1051523),d=md5_ii(d,e,f,c,a[g+1],21,-2054922799),c=md5_ii(c,d,e,f,a[g+8],6,1873313359),f=md5_ii(f,c,d,e,a[g+15],10,-30611744),e=md5_ii(e,f,c,d,a[g+6],15,-1560198380),d=md5_ii(d,e,f,c,a[g+13],21,1309151649),c=md5_ii(c,d,e,f,a[g+4],6,-145523070),f=md5_ii(f,c,d,e,a[g+11],10,-1120210379),e=md5_ii(e,f,c,d,a[g+2],15,718787259),d=md5_ii(d,e,f,c,a[g+9],21,-343485551),c=safe_add(c,h),d=safe_add(d,i),e=safe_add(e,j),f=safe_add(f,k)}return Array(c,d,e,f)}function md5_cmn(a,b,c,d,e,f){return safe_add(bit_rol(safe_add(safe_add(b,a),safe_add(d,f)),e),c)}function md5_ff(a,b,c,d,e,f,g){return md5_cmn(b&c|~b&d,a,b,e,f,g)}function md5_gg(a,b,c,d,e,f,g){return md5_cmn(b&d|c&~d,a,b,e,f,g)}function md5_hh(a,b,c,d,e,f,g){return md5_cmn(b^c^d,a,b,e,f,g)}function md5_ii(a,b,c,d,e,f,g){return md5_cmn(c^(b|~d),a,b,e,f,g)}var hexcase=0,b64pad="";