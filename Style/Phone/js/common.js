 $(function() { 
	$(".mine_btn").bind("mousedown",function(e){
                var url=$(this).attr("data-toggle");
                window.location.href=url;
            })
});

/*移动端成功 toast消息
  type          类型，            0：成功； 1 ：失败。默认是成功;  2 :加载款 loading...
  title         提示文字          默认提示成功
  time          弹窗延长时间      默认 1400毫秒
  revclasstime  去除所有类型样式  默认比time延长1400毫秒
  autor： yaozhixing  time：2016.11.22 18:07：50  email：772364562@qq.com
*/
function supertoast(type,title,time){
    var type = type || 0 ;
    var title = title || "提交成功" ;
    var time = time || 1400;
    var revclasstime = time + 1000 || 2800;
    if( type == 0){
        $(".js-toasttit").text(title);
    }
    else if ( type == 1){
        $(".js-tickico").addClass("tick-error");
        $(".js-toasttit").text(title);
    }
    else{
        $(".js-tickico").addClass("tick-loading");
        $(".js-toasttit").text(title);
    }
    $(".zr-success").fadeIn().delay(time).fadeOut();
    setInterval ("$('.js-tickico').removeClass('tick-error tick-loading')", revclasstime);
}