/* 
* @Author: 韦念富
* @Date:   2015-11-03 10:16:54
* @Last Modified by:   Administrator
* @Last Modified time: 2015-11-03 10:26:40
*/

function ganbenglunbo(shijian){
		var mytulis = document.getElementById("tuul").getElementsByTagName("li"); 
		var mydianlis = document.getElementById("dianul").getElementsByTagName("li");
		var myrightbut = document.getElementById("right_but");
		var myleftbut = document.getElementById("left_but");
		var nowimg = 0;	
		var mytimer = 0;
		myrightbut.onclick = youanniushijian;
	function youanniushijian(){
		if(nowimg < mytulis.length - 1){
			nowimg ++;
		}else{
			nowimg = 0;
		}
		huantu();
		shezhixiaoyuandian();
	}
	function huantu(){
		for(var i = 0 ; i <= mytulis.length - 1; i++){
			mytulis[i].className = "";
		}
		mytulis[nowimg].className = "cur";
	}
	function shezhixiaoyuandian(){
		for(var i = 0 ; i <= mydianlis.length - 1; i ++){
			mydianlis[i].className = "";
		}
		mydianlis[nowimg].className = "current";
	}
	myleftbut.onclick = function(){
		if(nowimg > 0){
			nowimg --;
		}else{
			nowimg = mytulis.length - 1;
		}
		huantu();
		shezhixiaoyuandian();
	}
}