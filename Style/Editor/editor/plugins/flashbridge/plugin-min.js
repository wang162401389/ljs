KISSY.Editor.add("flashbridge",function(){function f(a){this._init(a)}function m(a){var b=c.isString(a)?a.match(/(\d)+/g):a;a=a;if(c.isArray(b))a=parseFloat(b[0]+"."+n(b[1],3)+n(b[2],5));return a||0}function n(a,b){for(var d=(a+"").length;d++<b;)a="0"+a;return a}var c=KISSY,g=c.Editor;if(g.FlashBridge)c.log("KE.FlashBridge attach more","warn");else{var h={};c.augment(f,c.EventTarget,{_init:function(a){var b=c.guid("flashbridge-");a.flashVars=a.flashVars||{};a.attrs=a.attrs||{};a.params=a.params||
{};var d=a.flashVars,e=a.params;c.mix(a.attrs,{id:b,width:"100%",height:"100%"},false);c.mix(e,{allowScriptAccess:"always",allowNetworking:"all",scale:"noScale"},false);c.mix(d,{shareData:false,useCompression:false},false);e={YUISwfId:b,YUIBridgeCallback:"KISSY.Editor.FlashBridge.EventHandler"};if(a.ajbridge)e={swfID:b,jsEntry:"KISSY.Editor.FlashBridge.EventHandler"};c.mix(d,e);h[b]=this;this.id=b;this.swf=g.Utils.flash.createSWFRuntime(a.movie,a);this._expose(a.methods)},_expose:function(a){for(var b=
this,d=0;d<a.length;d++)(function(e){b[e]=function(){return b._callSWF(e,c.makeArray(arguments))}})(a[d])},_callSWF:function(a,b){b=b||[];try{if(this.swf[a])return this.swf[a].apply(this.swf,b)}catch(d){var e="";if(b.length!==0)e="'"+b.join("', '")+"'";return(new Function("self","return self.swf."+a+"("+e+");"))(this)}},_eventHandler:function(a){var b=a.type;if(b==="log")c.log(a.message);else b&&this.fire(b,a)},_destroy:function(){delete h[this.id]}});f.EventHandler=function(a,b){var d=h[a];d&&setTimeout(function(){d._eventHandler.call(d,
b)},100)};g.FlashBridge=f;var i=c.UA,j,k,l=true;i.fpv=function(a){if(a||l){l=false;var b;if(navigator.plugins&&navigator.mimeTypes.length)b=(navigator.plugins["Shockwave Flash"]||{}).description;else if(window.ActiveXObject)try{b=(new ActiveXObject("ShockwaveFlash.ShockwaveFlash")).GetVariable("$version")}catch(d){}j=b?b.match(/(\d)+/g):void 0;k=m(j)}return j};i.fpvGEQ=function(a,b){l&&i.fpv(b);return!!k&&k>=m(a)}}},{attach:false});
