function scrollTop(id,fx,width,height,speed,stepper){
	this.objBox=document.getElementById(id);
	var timer=null,
		scrollFn=null,
		c_width=c_len=null;
	this.objBox.style.position="relative";
	this.objBox.style.overflow="hidden";
	//this.objBox.style.width=width+'px';
	this.objBox.style.width="100%";
	//this.objBox.style.height=height+'px';
	this.objBox.style.height='11rem';
	c_width=this.objBox.children[0].offsetWidth;
	c_len=this.objBox.children.length;//获取滚动子元素的个数
	this.objBox.innerHTML="<div id="+this.objBox.id+"-scrollMn><div>"+this.objBox.innerHTML+"</div><div>"+this.objBox.innerHTML+"</div></div>";
	var objScrMn=document.getElementById(this.objBox.id+"-scrollMn");
	objScrMn.style.position="absolute";
	switch(fx){
		case "vertical":
			objScrMn.style.left="0";
			scrollFn=function (){
				if(objScrMn.offsetTop<-objScrMn.offsetHeight/2){
					objScrMn.style.top=0;
				}
				if(objScrMn.offsetTop>0){
					objScrMn.style.top=-objScrMn.offsetHeight/2+"px";
				}
				objScrMn.style.top=objScrMn.offsetTop-stepper+'px';						
			}
			break;
		case "level":
			for(var i=0;i<objScrMn.children.length;i++){
				objScrMn.children[i].style.cssFloat="left";
				objScrMn.children[i].style.styleFloat="left";
				objScrMn.children[i].style.width=c_width*c_len+"px";
			}
			objScrMn.style.width=objScrMn.children[0].offsetWidth*2+"px";
			scrollFn=function (){
				if(objScrMn.offsetLeft<-objScrMn.offsetWidth/2){
					objScrMn.style.left=0;
				}
				if(objScrMn.offsetLeft>0){
					objScrMn.style.left=-objScrMn.offsetWidth/2;
				}
				objScrMn.style.left=objScrMn.offsetLeft-stepper+'px';						
			}
			break;
		default:
			alert(this.objBox.id+"对象marquee初始化错误！");
			return false;
	}
	timer=setInterval(scrollFn,speed);
	// this.objBox.onmouseover=function (){
	// 	clearInterval(timer);
	// }
	// this.objBox.onmouseout=function (){
	// 	timer=setInterval(scrollFn,speed);
	// }	
}