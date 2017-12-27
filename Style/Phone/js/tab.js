/* 
* @Author: 韦念富
* @Date:   2015-11-03 10:16:54
* @Last Modified by:   Administrator
* @Last Modified time: 2015-11-03 11:07:26
*/
function SetTab(selectorTabMenu, selectorTabItem, options) { 
    var settings = { e: 'click' }; 
    if (options) { $.extend(settings, options) }; 
    var objTabMenu = $(selectorTabMenu); 
    var objTabItem = $(selectorTabItem); 
    var items = objTabMenu.length; 
    objTabItem.hide().eq(0).show(); 
    objTabMenu.each(function (index) { 
        $(this).bind(settings.e, function () { 
            //            $(this).addClass('tabon').removeClass('tabout'); 
            //            $(this).siblings().removeClass('tabon').addClass('tabout'); 
            $(this).addClass('tabon').removeClass('tabout').siblings().removeClass('tabon').addClass('tabout'); 
            objTabItem.hide(); 
            objTabItem.eq(index).show(); 
            var lazyObj = objTabItem.eq(index).find('img[data-original]'); 
            if (lazyObj.length > 0) { 
                lazyObj.lazyload(); 
            } 
        }) 
    }); 
}; 

/*SetTab在商品详情页面与ShowFunc.js的SetTab冲突*/ 
function SetMenuTab(selectorTabMenu, selectorTabItem, options) { 
    var settings = { e: 'click' }; 
    if (options) { $.extend(settings, options) }; 
    var objTabMenu = $(selectorTabMenu); 
    var objTabItem = $(selectorTabItem); 
    var items = objTabMenu.length; 
    objTabItem.hide().eq(0).show(); 
    objTabMenu.each(function (index) { 
        $(this).bind(settings.e, function () { 
            //            $(this).addClass('tabon').removeClass('tabout'); 
            //            $(this).siblings().removeClass('tabon').addClass('tabout'); 
            $(this).addClass('tabon').removeClass('tabout').siblings().removeClass('tabon').addClass('tabout'); 
            objTabItem.hide(); 
            objTabItem.eq(index).show(); 
            var lazyObj = objTabItem.eq(index).find('img[data-original]'); 
            if (lazyObj.length > 0) { 
                lazyObj.lazyload(); 
            } 
        }) 
    }); 
};