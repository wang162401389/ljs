/*
 * JS Keyboard - 随机生成的软键盘.
*/

function RandomSort(a,b){
	return Math.random() - 0.5;
}

function getRandomNum()
{
	var numArray=new Array();
	var i;
	for(i=0;i<10;i++)
	  numArray[i]=i;//生成一个数组
	numArray.sort(RandomSort);
	return numArray;
}

function getRandomChar()
{
	var charArray=new Array();
	var i,j;
	for(i=0,j=97;j<123;i++,j++)
	  charArray[i]=j;//生成字母表
	charArray.sort(RandomSort);
	//对字母进行翻译
	for(i=0;i<charArray.length;i++)
		charArray[i] = String.fromCharCode(charArray[i]);
	return charArray;
}

function showKeyboard(inputId)
{
	var kb = $('#yh_KeyBoard');
	if (kb.length!=0)
	{
		kb.remove();
		return false;
	}
	
	kb = $('<div id="yh_KeyBoard" class="kbdiv"></div>');
	var i=0;
	var keyboard = '<div class="kbtable">';
	numArray = getRandomNum();
	charArray = getRandomChar();
	for(i=0;i<10;i++)
	{
		keyboard += '<em class="kbkey">'+numArray[i]+'</em>';
	}
	keyboard += "</div><table><tr>";
//	for(i=0;i<26;i++)
//	{
//		if (i%10==0 && i>0)
//			keyboard += "</tr><tr>";
//		keyboard += '<td class="kbkey">'+charArray[i]+'</td>';
//	}
	//keyboard += '<td id="kbcaps" colspan="2" class="kbcolspan">大小写</td>';
	keyboard += '<td><em id="kbclose" class="kbcolspan">确认</em></td>';
	keyboard += '<td><em id="kbback" class="kbcolspan">退 格</em></td>';
	keyboard += '</tr></table>';
	kb.html(keyboard);
	kb.appendTo('body');

$("em",kb).mouseover(function() {
		this.className += " kbmouseover";
	}).mouseout(function() {
		this.className = this.className.replace(" kbmouseover","");
	}).click(function() {
		
		if(this.id == "kbclose") {
			kb.remove();
			return false;
		}
//		else if(this.id == "kbcaps") {
//			$.each($(".kbkey",kb),function(i,o) {
//				var num = o.innerHTML.charCodeAt(0);
//				if(num>96 && num<123)
//					o.innerHTML = o.innerHTML.toUpperCase();
//				else if(num>64 && num<91)
//					o.innerHTML = o.innerHTML.toLowerCase();
//			});
//
//			return false;
//		}
		//退格
		if(this.id == 'kbback'){
			var pw = $("#"+inputId).val();
			//alert(pp)
			$("#"+inputId).val(pw.substr(0, pw.length - 1));
			return false;
		}
		
		$("#"+inputId).attr("value",$("#"+inputId).val()+this.innerHTML);
	});
	
	var offset = $("#"+inputId).offset();
	var left = offset.left;
	var height = $("#"+inputId).height();
	var top = offset.top+height+8;
	kb.css({"left": left+"px", "top": top+"px","position":"absolute","z-index":"100"});
	return false;
}

