var arrBox = new Array();
arrBox["dvPhone"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce1.gif'/>&nbsp;请输入手机号";
arrBox["dvsmsCode"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce1.gif'/>&nbsp;请输入手机验证码";
// arrBox["dvEmail"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce1.gif'/>&nbsp;请填写真实的电子邮件地址";
// arrBox["dvUser"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce1.gif'/>&nbsp;4-20个字母、数字、汉字、下划线";
arrBox["dvPwd"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce1.gif'/>&nbsp;6-16个字母、数字、下划线";
arrBox["dvRepwd"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce1.gif'/>&nbsp;请再一次输入您的密码。";
//arrBox["dvRec"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce1.gif'/>&nbsp;请输入推荐人的用户名，可不填";
arrBox["dvCode"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce1.gif'/>&nbsp;请按照图片显示内容输入验证码";

var arrWrong = new Array();
arrWrong["dvPhone0"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;请输入手机号";
arrWrong["dvPhone"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;手机号码有误";
arrWrong["dvPhone1"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;该手机号码已被其他用户使用";
arrWrong["dvPhone2"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;校验码发送失败,请重试";
arrWrong["dvsmsCode"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;请输入手机验证码";
// arrWrong["dvEmail"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;请输入真实的电子邮件。";
// arrWrong["dvUser"] = "<img style='margin:3px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;4-20个字母、数字、汉字、下划线";
arrWrong["dvPwd"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;6-16个字母,数字,下划线";
arrWrong["dvRepwd"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;未输入或两次输入密码不同";
//arrWrong["dvRec"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;请输入推荐人的用户名";
arrWrong["dvCode"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;验证码输入不正确";

var arrOk = new Array();
arrOk["dvPhone"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce3.gif'/>&nbsp;短信已发送，请耐心等待...";
arrOk["dvsmsCode"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce3.gif'/>&nbsp;手机验证通过。";
arrOk["dvEmail"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce3.gif'/>&nbsp;电子邮件地址可用。";
// arrOk["dvUser"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce3.gif'/>&nbsp;用户名可用";
arrOk["dvPwd"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce3.gif'/>&nbsp;密码格式正确";
arrOk["dvRepwd"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce3.gif'/>&nbsp;密码格式正确";
arrOk["dvRec"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce3.gif'/>&nbsp;推荐人正确";
arrOk["dvCode"] = "<img style='margin:2px;' src='"+imgpath+"images/zhuce3.gif'/>&nbsp;验证码正确";


function Init() {
	$('#txt_phone').click(function() { ClickBox("dvPhone"); });
    $('#txt_smsCode').click(function() { ClickBox("dvsmsCode"); });
    // $('#txtEmail').click(function() { ClickBox("dvEmail"); });
    // $('#txtUser').click(function() { ClickBox("dvUser") });
    $('#txtPwd').click(function() { ClickBox("dvPwd") });
    $('#txtRepwd').click(function() { ClickBox("dvRepwd") });
	//$('#txtRec').click(function() { ClickBox("dvRec") });
    $('#txtCode').click(function() { ClickBox("dvCode") });

    $('#txt_smsCode').blur(function() { Blurtxt_smsCode(); });
    // $('#txtEmail').blur(function() { BlurEmail(); });
    // $('#txtUser').blur(function() { BlurUName(); });
    $('#txtPwd').blur(function() { BlurPwd(); });
    $('#txtRepwd').blur(function() { BlurRepwd(); });
	$('#txtRec').blur(function() { BlurRec(); });
    $('#txtCode').blur(function() { BlurCode(); });

}

$(document).ready(
function() {
	//$('#dvRec').html('<font>填写推荐人用户名，没有推荐人可不填。</font>');
    Init();
    $("#txtEmail").focus();
    //$("#Img1").click(function() { RegSubmit(this); });
    $("#txtCode").keypress(
    function(e) {
        if (e.keyCode == "13")
            $("#Img1").click();
    });
});

function strLength(as_str){
		return as_str.replace(/[^\x00-\xff]/g, 'xx').length;
}
function isLegal(str){
	if(/[!,#,$,%,^,&,*,?,~,\s+]/gi.test(str)) return false;
	return true;
}
function Bluruser_regtype() {
    var str = $('#user_regtype').val();
    if(str == ''){
        $("#dvuser_regtype").html(GetP("reg_wrong", arrWrong["dvuser_regtype"]));
    }else{
        $("#dvuser_regtype").html(GetP("arrOk", arrOk["dvuser_regtype"]));
    }
}
function Blurtxt_smsCode(){
    var str = $('#txt_smsCode').val();
    if(str == ''){
        $("#dvsmsCode").html(GetP("reg_wrong", arrWrong["dvsmsCode"]));
    }else{
        $.ajax({
            type: "post",
            async: false,
            url: "/member/common/validatephone/",
            dataType: "json",
            data: {"code":str},
            timeout: 3000,
            success: Asynccode
        });
        //$("#dvsmsCode").html(GetP("arrOk", arrOk["dvsmsCode"]));
    }
}
// function BlurUName() {
//     var txt = "#txtUser";
//     var td = "#dvUser";
//     var pat = new RegExp("^[\\d|\\.a-z_A-Z|\\u4e00-\\u9fa5|\\x00-\\xff]$", "g");
//     var str = $(txt).val();
//     var strlen = strLength(str);
//     if (isLegal(str) && strlen>=4 && strlen<=20) {
//         $(td).html(GetP("reg_info", "<img style='margin:2px;' src='"+imgpath+"images/zhuce0.gif'/>&nbsp;正在检测用户名……"));
//         $.ajax({
//             type: "post",
//             async: false,
//             url: "/member/common/ckuser/",
// 			dataType: "json",
//             data: {"UserName":str},
//             timeout: 3000,
//             success: AsyncUname
//         });
//     }
//     else {
//         $(td).html(GetP("reg_wrong", arrWrong["dvUser"]));
//     }
// }
function BlurRec() {
    var txt = "#txtRec";
    var td = "#dvRec";
    var pat = new RegExp("^[a-zA-Z0-9_]*$", "g");
    var str = $(txt).val();
	
    var strlen = strLength(str);
	if (isLegal(str) && strlen>=1 && strlen<=20) {
		$(td).html(GetP("reg_info", "<img style='margin:2px;' src='"+imgpath+"images/zhuce0.gif'/>&nbsp;正在检测推荐人……"));
		$.ajax({
			type: "post",
			async: false,
			url: "/member/common/ckInviteUser/",
			dataType: "json",
			data: {"InviteUserName":str},
			timeout: 3000,
			success: AsyncInviteUname
		}
		);
	}else if(str==''){
		$(td).empty();
    }
    else {
        $(td).html(GetP("reg_wrong", arrWrong["dvRec"]));
    }
}

// function AsyncUname(data) {
//     if (data.status == "1") {
//         $("#dvUser").html(GetP("reg_ok", arrOk["dvUser"]));
//     }
//     else {
//         $("#dvUser").html(GetP("reg_wrong", "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;此用户名已被注册。"));

//     }

// }
function Asynccode(data) {
    if (data.status == "1") {
        $("#dvsmsCode").html(GetP("reg_ok", arrOk["dvsmsCode"]));
    }else {
        $("#dvsmsCode").html(GetP("reg_wrong", "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;验证码不正确。"));

    }

}
function AsyncInviteUname(data) {
    if (data.status == "1") {
        $("#dvRec").html(GetP("reg_ok", arrOk["dvRec"]));
    }
    else {
        $("#dvRec").html(GetP("reg_wrong", "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;此推荐人不存在。"));

    }

}
// function BlurEmail() {
//     var txt = "#txtEmail";
//     var td = "#dvEmail";
//     var pat = new RegExp("^[\\w-]+(\\.[\\w-]+)*@[\\w-]+(\\.[\\w-]+)+$", "i");
//     var str = $(txt).val();
//     if (pat.test(str)) {
//         $(td).html(GetP("reg_info", "<img style='margin:2px;' src='"+imgpath+"images/zhuce0.gif'/>&nbsp;正在检测电子邮件地址……"));
//         $.ajax({
//             type: "post",
//             async: false,
// 			dataType: "json",
//             url: "/member/common/ckemail/",
//             data: {"Email":str},
//             timeout: 3000,
//             success: AsyncEmail
//         });
//     }
//     else { $(td).html(GetP("reg_wrong", arrWrong["dvEmail"])); }
// }

// function AsyncEmail(data) {
//     if (data.status == "1") {
//         $("#dvEmail").html(GetP("reg_ok", arrOk["dvEmail"]));
//     }
//      else {
//        // $("#dvEmail").html(GetP("reg_wrong", "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;邮箱已经在本站注册<a href='javascript:;' onlick='getPassWord();'>取回密码？</a>"));
// 		$("#dvEmail").html(GetP("reg_wrong", "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;邮箱已经在本站注册<a href='javascript:getPassWord();'>取回密码？</a>"));
//     }
// }

function getPassWord() {
	window.location.href = "/member/common/getpassword/";
}

function BlurPwd() {
    var txt = "#txtPwd";
    var td = "#dvPwd";
    var pat = new RegExp("^.{6,15}$", "i");
    var str = $(txt).val();
    if (pat.test(str)) {
        //格式正确
        $(td).html(GetP("reg_ok", arrOk["dvPwd"]));
    }
    else {
        $(td).html(GetP("reg_wrong", arrWrong["dvPwd"]));
    }
}

function BlurRepwd() {
    var txt = "#txtRepwd";
    var td = "#dvRepwd";
    var str = $(txt).val();
    if (str == $("#txtPwd").val() && str.length > 5) {
        //格式正确
        $(td).html(GetP("reg_ok", arrOk["dvRepwd"]));
    }
    else {
        $(td).html(GetP("reg_wrong", arrWrong["dvRepwd"]));
    }
}
//检验 验证码
function BlurCode() {
    var txt = "#txtCode";
    var td = "#dvCode";
   // var pat = new RegExp("^[\\da-z]{4}$", "i");
    var str = $(txt).val();
    if (str != null) {
        //格式正确
        $.post("/member/common/ckcode/", { Action: "post", Cmd: "CheckVerCode", sVerCode: str }, AsyncVerCode);
    }
    else {
        $(td).html(GetP("reg_wrong", arrWrong["dvCode"]));
    }
    i=0;
}

function AsyncVerCode(data) {
    if (data == "1") {
        $("#dvCode").html(GetP("reg_ok", arrOk["dvCode"]));
    }
    else {
        $("#dvCode").html(GetP("reg_wrong", "<img style='margin:2px;' src='"+imgpath+"images/zhuce2.gif'/>&nbsp;验证码填写错误！"));
		//$("#dvCode").html(GetP("reg_wrong", arrBox["dvCode"]));
        item=document.getElementById("imVcode");
        src="/Member/common/verify2?t="+Math.floor(Math.random()*10);
        item.src=src;
    }
}

function ClickBox(id) {
    var ele = '#' + id;
    $(ele).html(GetP("reg_info", arrBox[id]));
}

function GetP(clsName, c) { return "<div class='" + clsName + "'>" + c + "</div>"; }

function RegSubmit(ctrl) {


    $(ctrl).unbind("click");
    var arrTds = new Array("#dvsmsCode", "#dvPwd","#dvRepwd", "#dvCode","#dvRec");
    Blurtxt_smsCode();
    Bluruser_regtype();
    // BlurEmail();
    // BlurUName();
    BlurPwd();
	BlurRec();
    BlurCode();
    for (var i = 0; i < arrTds.length; i++) {
        if ($(arrTds[i]).html().indexOf('reg_wrong') > -1) {
            $(ctrl).click(function() { RegSubmit(this); });
            return false;
        }
    }
	
	var check = $("input[type='checkbox']").attr("checked");

	if(!check){
		$.jBox.tip("请确认服务协议");  
		return false;
  	}
    $(".pop-bg,.reg-xieyi").show();

}

$(document).ready(function(){
    //弹出窗 - 服务协议
    $(".reg-check").on("click",function(){
        if($(this).hasClass("reg-check-tick")){
            $(this).removeClass("reg-check-tick");
            $(".reg-button-sure").removeClass("js-pass");
        }
        else{
            $(this).addClass("reg-check-tick");
            $(".reg-button-sure").addClass("js-pass");
        }
    });

    //同意协议通过 - 确认提交
    $(".reg-confbutton").on("click",".js-pass",function(){
        //注册页面register保存session
        $.jBox.tip("资料提交中......","loading");
        $.ajax({
            url: curpath+"/regtemp/",
            data: {
                // "txtEmail":     $("#txtEmail").val(),
                // "txtUser":      $("#txtUser").val(),
                "txt_phone":    $("#txt_phone").val(),
                "txtPwd":       $("#txtPwd").val(),
                "sVerCode":     $("#txtCode").val(),
                "txtRec":       $("#txtRec").val(),
                //"user_regtype": $("input[name='user_regtype']:checked").val()
            },
            // timeout: 2000,
            cache: false,
            type: "post",
            dataType: "json",
            success: function (d, s, r) {
                if(d){
                    if(d.status==0){
                        $.jBox.tip(d.message,"fail");
                        //$(ctrl).click(function() { RegSubmit(this); });
                    }else{
                        window.location.href="/member/common/regsuccess/";
                        //window.location.href="/member/";//临时修改
                    }
                }
            }
        });
    })
})

function setMobile() {
    var code = $('#txt_smsCode').val();
    $.ajax({
        url: "__URL__/validatephone",
        type: "post",
        dataType: "json",
        data: {"code":code},
        success: function(d) {
            if (d.status==1) {
                alert(1);return;
                //$.jBox.tip("验证成功");
                window.location.href="/member/common/regsuccess/";
            }else {
                if (d.status == 2) {
                    alert(2);return;
                    $(".spTip").html(d.message);
                    setTimeout("failskip()",5000);
                }
                if (d.status == 0) {
                    alert(3);return;
                    $(".spTip").html(d.message);
                    setTimeout("failskip()",5000);
                }
            }
        }
    });
}

function myrefresh()
{
	   window.location.href="/member/";
}
function AsyncReg(data) {
    Close_Dialog_AutoClose();
    if (data == "True") {
        suc();
    }
    else { }
}

function AsyncReg_Back() { window.location.href = "/member/"; }