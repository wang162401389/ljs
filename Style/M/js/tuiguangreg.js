var arrBox = new Array();
arrBox["dvPhone"] = "请输入手机号";
arrBox["dvsmsCode"] = "请输入手机验证码";
arrBox["dvPwd"] = "6-16个字母、数字、下划线";
arrBox["dvRepwd"] = "请再一次输入您的密码。";
arrBox["dvCode"] = "请按照图片显示内容输入验证码";

var arrWrong = new Array();
arrWrong["dvPhone0"] = "请输入手机号";
arrWrong["dvPhone"] = "手机号码有误";
arrWrong["dvPhone1"] = "该手机号码已被其他用户使用";
arrWrong["dvPhone2"] = "校验码发送失败,请重试";
arrWrong["dvsmsCode"] = "请输入手机验证码";
arrWrong["dvPwd"] = "6-16个字母,数字,下划线";
arrWrong["dvCode"] = "验证码输入不正确";

var arrOk = new Array();
arrOk["dvPhone"] = "短信已发送，请耐心等待...";
arrOk["dvsmsCode"] = "手机验证通过。";
arrOk["dvPwd"] = "密码格式正确";
arrOk["dvCode"] = "验证码正确";


function Init() {
	$('#txt_phone').click(function() { ClickBox("dvPhone"); });
    $('#txt_smsCode').click(function() { ClickBox("dvsmsCode"); });
    $('#txtPwd').click(function() { ClickBox("dvPwd") });
    $('#txtCode').click(function() { ClickBox("dvCode") });

    $('#txt_smsCode').blur(function() { Blurtxt_smsCode(); });
    $('#txtPwd').blur(function() { BlurPwd(); });
    $('#txtCode').blur(function() { BlurCode(); });

}

$(document).ready(
function() {
    Init();
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

function Blurtxt_smsCode(){
    var str = $('#txt_smsCode').val();
    if(str == ''){
        $(".smscodeverify").html(GetP("reg_wrong1", arrWrong["dvsmsCode"]));
        $(".smscodeverify").show();
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
    }
}

function Asynccode(data) {
    if (data.status == "1") {
        $(".smscodeverify").html("");
        $(".smscodeverify").hide();
    }else {
        $(".smscodeverify").html(GetP("reg_wrong1", "验证码不正确。"));
        $(".smscodeverify").show();

    }

}

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
        $(".pwverify").html("");
        $(".pwverify").hide();
    }
    else {
        $(".pwverify").html(GetP("reg_wrong1", arrWrong["dvPwd"]));
        $(".pwverify").show();
    }
}

//检验 验证码
function BlurCode() {
    var txt = "#txtCode";
    var td = "#dvCode";
    var str = $(txt).val();
    if (str) {
        //格式正确
        $.post("/member/common/ckcode/", { Action: "post", Cmd: "CheckVerCode", sVerCode: str }, AsyncVerCode);
        
    }
    else {
       // $(".codeverify").html(GetP("reg_wrong1", arrWrong["dvCode"]));
       $(".codeverify").html(GetP("reg_wrong1", "请输入验证码"));
        $(".codeverify").show();
    }
    i=0;
}

function AsyncVerCode(data) {
    if (data == "1") {
        // $("#dvCode").html(GetP("reg_ok", arrOk["dvCode"]));
        $(".codeverify").html("");
        $(".codeverify").hide();
    }
    else {
        $(".codeverify").html(GetP("reg_wrong1", "验证码填写错误"));
        $(".codeverify").show();
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
    var arrTds = new Array(".mbverify", ".pwverify",".codeverify", ".smscodeverify");
    Blurtxt_smsCode();
    BlurPwd();
    BlurCode();
    for (var i = 0; i < arrTds.length; i++) {
        if ($(arrTds[i]).html().indexOf('reg_wrong1') > -1) {
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
                "txt_phone":    $("#txt_phone").val(),
                "txtPwd":       $("#txtPwd").val(),
                "sVerCode":     $("#txtCode").val(),
            },
            cache: false,
            type: "post",
            dataType: "json",
            success: function (d, s, r) {
                if(d){
                    if(d.status==0){
                        $.jBox.tip(d.message,"fail");
                    }else{
                        window.location.href="/member/common/regsuccess/";
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
                window.location.href="/member/common/regsuccess/";
            }else {
                if (d.status == 2) {
                    alert(2);return;
                    $(".smscodeverify").html(d.message);
                    $(".smscodeverify").show();
                    setTimeout("failskip()",5000);
                }
                if (d.status == 0) {
                    alert(3);return;
                    $(".smscodeverify").html(d.message);
                    $(".smscodeverify").show();
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