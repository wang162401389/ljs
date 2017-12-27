$(function() {
    (function() {
        //二级菜单-我的账户
        $("#ui-nav-item-link").mouseenter(function(){
            $(".ui-nav-dropdown").show()
        }).mouseleave(function(){
            $(".ui-nav-dropdown").hide()
        });

        //二级菜单-通用
        var time = 0;
        $(".navigation-item").mouseenter(function() {
            var that = $(this);
            clearTimeout(time);
            time = setTimeout(function() {
                var next = that.find(".navigation-list-two-con"),
                width = that.width(),
                eleWidth = next.width(),
                cha = (eleWidth - width) / 2;
                that.addClass("select");
                next.css("left", -cha);
                next.find(".nav-sanjiao").css("left", eleWidth / 2 - 6);
                next.stop().fadeIn(0);
            },
            0);
        }).mouseleave(function() {
            var that = $(this),
            listTwo = that.find(".navigation-list-two-con");
            clearTimeout(time);
            setTimeout(function() {
                if (that.hasClass("select")) {
                    that.removeClass("select");
                    that.find(".navigation-list-two-con").stop().fadeOut(0);
                }
            },
                0);
        });

        //模拟弹出窗
        $(".js-popclose").on("click",function(){
            $(this).parents(".pop-div").hide();
            $(".pop-bg").hide();
        })

    })();
});

//提现弹窗
function withdraw(){
    var flag    =   $("#tips_withdarw").val();
    var a=window.location.href;                     //冒泡过滤如果，弹窗里面按钮添加？号
    var s=a.indexOf("?");
    var t=a.substring(s+1);// t就是?后面的东西了
    if(flag == 1){
        //有问号就隐藏弹窗
        if( s!=-1){
            $(".pop-bg,.js-withdraw-pop").hide();
        }
        else{
            $(".pop-bg,.js-withdraw-pop").show();
        }
    }
    else{
        window.location.href="/member/withdraw#fragment-1";
    }
}