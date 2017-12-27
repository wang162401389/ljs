$(function(){
	ajax_show(1);
})
function ajax_show(p)
{
   $.get("__URL__/investRecord?borrow_id={$borrow_id}&p="+p, function(data){
	  $("#investrecord").html(data);
   });

   $(".pages a").removeClass('current');
   $(".pages a").eq(p).addClass("current");
}