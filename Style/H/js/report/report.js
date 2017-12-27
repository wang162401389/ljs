//历史投资人次
function hisinvdate(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
		myChart = echarts.init(document.getElementById('hisinvdate'));
	option = {
	    title: {
	        text: ''
	    },
	    tooltip : {
	        trigger: 'axis'
	    },
	    grid: {
	    	left: '0%',
	        containLabel: true
	    },
	    legend: {
	        data:"历史投资人次",
	        bottom:'7%'
	    },
	    calculable : true,
	    xAxis : [
	        {
	            type : 'category',
	            boundaryGap : false,
	            data : title
	        }
	    ],
	    yAxis : [
	        {
	            type : 'value'
	        }
	    ],
	    series : [
	        {
	            name:name,
	            type:'line',
	            stack: '人次',
	            smooth:true,
	            label: {
	                normal: {
	                    show: true,
	                    position: [-20,-30],
	                    textStyle: {
	                    	fontSize:16,
	                    	color:'#33cc99',
	                    }
	                }
	            },
	            areaStyle: {normal: {
	            	color:"rgba(51, 204, 153, 0.3)",
	            }},
	            itemStyle : {  
                    normal : {  
                        color:'#33cc99', 
                            lineStyle:{  
                                color:'#33cc99'  
                            }  
                        }  
                },
	            data:data
	        }
	    ]
	};
	myChart.setOption(option);
}   

//历史成交金额
function turnoverdate(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
		myChart = echarts.init(document.getElementById('turnoverdate'));
	option = {
	    title: {
	        text: ''
	    },
	    tooltip : {
	        trigger: 'axis'
	    },
	    grid: {
	    	left: '0%',
	        containLabel: true
	    },
	    legend: {
	        data:"历史成交金额",
	        bottom:'7%'
	    },
	    calculable : true,
	    xAxis : [
	        {
	            type : 'category',
	            boundaryGap : false,
	            data : title
	        }
	    ],
	    yAxis : [
	        {
	            type : 'value'
	        }
	    ],
	    series : [
	        {
	            name:name,
	            type:'line',
	            stack: '人次',
	            smooth:true,
	            label: {
	                normal: {
	                    show: true,
	                    position: [-20,-30],
	                    textStyle: {
	                    	fontSize:16,
	                    	color:'#33cc99',
	                    }
	                }
	            },
	            areaStyle: {normal: {
	            	color:"rgba(51, 204, 153, 0.3)",
	            }},
	            itemStyle : {  
                    normal : {  
                        color:'#33cc99', 
                            lineStyle:{  
                                color:'#33cc99'  
                            }  
                        }  
                },
	            data:data
	        }
	    ]
	};
	myChart.setOption(option);
}    

//历史人均投资
function perinvdate(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
		myChart = echarts.init(document.getElementById('perinvdate'));
	option = {
	    title: {
	        text: ''
	    },
	    tooltip : {
	        trigger: 'axis'
	    },
	    grid: {
	    	left: '0%',
	        containLabel: true
	    },
	    legend: {
	        data:"历史人均投资",
	        bottom:'7%'
	    },
	    calculable : true,
	    xAxis : [
	        {
	            type : 'category',
	            boundaryGap : false,
	            data : title
	        }
	    ],
	    yAxis : [
	        {
	            type : 'value'
	        }
	    ],
	    series : [
	        {
	            name:name,
	            type:'line',
	            stack: '人次',
	            smooth:true,
	            label: {
	                normal: {
	                    show: true,
	                    position: [-20,-30],
	                    textStyle: {
	                    	fontSize:16,
	                    	color:'#33cc99',
	                    }
	                }
	            },
	            areaStyle: {normal: {
	            	color:"rgba(51, 204, 153, 0.3)",
	            }},
	            itemStyle : {  
                    normal : {  
                        color:'#33cc99', 
                            lineStyle:{  
                                color:'#33cc99'  
                            }  
                        }  
                },
	            data:data
	        }
	    ]
	};
	myChart.setOption(option);
}

//单月成交金额
function monthdate(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
		myChart = echarts.init(document.getElementById('monthdate'));
	option = {
		color: ['#008cba'],
		tooltip : {
			trigger: 'axis',
			axisPointer : {            // 坐标轴指示器，坐标轴触发有效
				type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
			}
		},
		calculable : true,
		grid: {
			left: '3%',
			right: '4%',
			bottom: '3%',
			containLabel: true
		},
		xAxis : [
			{
				type : 'category',
				data: title,
				axisTick: {
					alignWithLabel: true
				}
			}
		],
		yAxis : [
			{
				type : 'value'
			}
		],
		series : [
			{
				name:name,
				type:'bar',
				barWidth: '60%',
				itemStyle : { normal: {label : {show: true,position: 'top'}}},
				data: data,
				animation:false,
			}
		]
	};
	myChart.setOption(option);
}

//属性划分
function subattr(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
    	myChart = echarts.init(document.getElementById('subattr'));
    option = {
	    //color: ['#008cba'],
	    tooltip : {
	        trigger: 'axis',
	        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
	            type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
	        }
	    },
	    calculable : true,
	    grid: {
	        left: '0%',
	        containLabel: true
	    },
	    xAxis : [
	        {
	            type : 'category',
	            data: title,
	            axisTick: {
	                alignWithLabel: true
	            },
	            axisLabel: {
	            	interval: 0,
	            	//rotate: 50,
	            }
	        }
	    ],
	    yAxis : [
	        {
	        	show:false, 
	            type : 'value'
	        }
	    ],
	    series : [
	        {
	            name:name,
	            type:'bar',
	            barWidth: '50%',
	            //itemStyle : { normal: {label : {show: true,position: 'top'}}},
	            itemStyle: {
                    normal: {
                        color: function(params) {
                            // build a color map as your need.
                            var colorList = [
                              '#008fbf','#ef608f','#8bba00','#e8ba00','#2560e7','#f37a12'
                            ];
                            return colorList[params.dataIndex]
                        },
　　　　　　　　　　　　　　//以下为是否显示，显示位置和显示格式的设置了
                        label: {
                            show: true,
                            position: 'top',
							textStyle:{
								fontSize: 16,
							},
                            //formatter: '{b}\n{c}'
                        }
                    }
                },
	            data: data
	        }
	    ]
	};
    myChart.setOption(option);
}

//标期划分
function subtime(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
    	myChart = echarts.init(document.getElementById('subtime'));
    option = {
	    //color: ['#008cba'],
	    tooltip : {
	        trigger: 'axis',
	        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
	            type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
	        }
	    },
	    calculable : true,
	    grid: {
	        left: '0%',
	        containLabel: true
	    },
	    xAxis : [
	        {
	            type : 'category',
	            data: title,
	            axisTick: {
	                alignWithLabel: true
	            },
	            axisLabel: {
	            	interval: 0,
	            	//rotate: 50,
	            }
	        }
	    ],
	    yAxis : [
	        {
	        	show:false, 
	            type : 'value'
	        }
	    ],
	    series : [
	        {
	            name:name,
	            type:'bar',
	            barWidth: '50%',
	            //itemStyle : { normal: {label : {show: true,position: 'top'}}},
	            itemStyle: {
                    normal: {
                        color: function(params) {
                            // build a color map as your need.
                            var colorList = [
                              '#008fbf','#ef608f','#8bba00','#e8ba00','#2560e7'
                            ];
                            return colorList[params.dataIndex]
                        },
　　　　　　　　　　　　　　//以下为是否显示，显示位置和显示格式的设置了
                        label: {
                            show: true,
                            position: 'top',
							textStyle:{
								fontSize: 16,
							},
                            //formatter: '{b}\n{c}'
                        }
                    }
                },
	            data: data
	        }
	    ]
	};
    myChart.setOption(option);
}

//年化收益划分
function subincome(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
    	myChart = echarts.init(document.getElementById('subincome'));
    option = {
	    //color: ['#008cba'],
	    tooltip : {
	        trigger: 'axis',
	        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
	            type : 'line'        // 默认为直线，可选为：'line' | 'shadow'
	        }
	    },
	    calculable : true,
	    grid: {
	        left: '0%',
	        containLabel: true
	    },
	    xAxis : [
	        {
	            type : 'category',
	            data: title,
	            axisTick: {
	                alignWithLabel: true
	            },
	            axisLabel: {
	            	interval: 0,
	            	//rotate: 50,
	            }
	        }
	    ],
	    yAxis : [
	        {
	        	show:false, 
	            type : 'value'
	        }
	    ],
	    series : [
	        {
	            name:name,
	            type:'bar',
	            barWidth: '50%',
	            //itemStyle : { normal: {label : {show: true,position: 'top'}}},
	            itemStyle: {
                    normal: {
　　　　　　　　　　　　　　//好，这里就是重头戏了，定义一个list，然后根据所以取得不同的值，这样就实现了，
                        color: function(params) {
                            // build a color map as your need.
                            var colorList = [
                              '#ed5f8e','#8cbb00','#efbf00'
                            ];
                            return colorList[params.dataIndex]
                        },
　　　　　　　　　　　　　　//以下为是否显示，显示位置和显示格式的设置了
                        label: {
                            show: true,
                            position: 'top',
							textStyle:{
								fontSize: 16,
							},
                            //formatter: '{b}\n{c}'
                        }
                    }
                },
	            data: data
	        }
	    ]
	};
    myChart.setOption(option);
}

//平台年龄划分
function userdata(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
    	myChart = echarts.init(document.getElementById('userdata'));
    option = {
	    tooltip: {
	        trigger: 'item',
	        formatter: name+"<br>{b}:{d}%"
	    },
	    legend: {
	    	left:'7%',
	        orient: 'top',
	        x: 'right',
	        data:title
	    },
	    grid: {
	        left: '10%',
	        containLabel: true
	    },
	    color:['#008dbc', '#d55843','#e9ba00','#91c100',],
	    series: [
	        {
	            name:'',
	            type:'pie',
	            radius: ['50%', '70%'],
	            avoidLabelOverlap: false,
	            label: {
	                normal: {
	                    show: true,
	                    position: 'inside',
	                    textStyle:{
	                    	fontSize: 16,
	                    }
	                },
	                emphasis: {
	                    show: true,
	                    textStyle: {
	                        fontSize: '18',
	                        //fontWeight: 'bold'
	                    }
	                }
	            },
	            emphasis:{
	            	show: true,
	            },
	            labelLine: {
	                normal: {
	                    show: false
	                }
	            },
	            data:[
	                {value:data[0], name:title[0]},
	                {value:data[1], name:title[1]},
	                {value:data[2], name:title[2]},
	                {value:data[3], name:title[3]}
	            ], 
	            itemStyle: {
		           normal:{
			             label:{
				             show:true,
				             formatter: '{d}%'
			             },
			             labelLine:{
			             	show:true
			             }
		             }
		           }
	        }
	    ]
	};
    myChart.setOption(option);
}

//地图map
function chinamap(name,title,data){
	var name = name,
		title = title.split(","),
		data = data.split(","),
		myChart = echarts.init(document.getElementById('chinamap'));
	option = {
		title: {
			text: '',
			subtext: '',
			left: 'center'
		},
		/*tooltip: {
		 trigger: 'item'
		 },*/
		visualMap: {
			show:true,
			min: 0,
			max: 100,
			left: 'left',
			top: 'bottom',
			text: ['高','低'],   // 文本，默认为数值文本
			/*inRange: {
			 color: ['#e0ffff', '#006edd']
			 },*/
			calculable : true
		},
		series: [
			{
				name: '用户地域分布',
				type: 'map',
				mapType: 'china',
				roam: false,
				showLegendSymbol:false,
				label: {
					normal: {
						show: true
					},
					emphasis: {
						show: true
					}
				},
				itemStyle:{
					normal:{
						areaColor:'#ffffff'
					},
				},
				data:[
					{name: title[0],value: data[0] },
					{name: title[1],value: data[1] },
					{name: title[2],value: data[2] },
					{name: title[3],value: data[3] },
					{name: title[4],value: data[4] },
					{name: title[5],value: data[5] },
					{name: title[6],value: data[6] },
					{name: title[7],value: data[7] },
					{name: title[8],value: data[8] },
					{name: title[9],value: data[9] },
					{name: title[10],value: data[10] },
					{name: title[11],value: data[11] },
					{name: title[12],value: data[12] },
					{name: title[13],value: data[13] },
					{name: title[14],value: data[14] },
					{name: title[15],value: data[15] },
					{name: title[16],value: data[16] },
					{name: title[17],value: data[17] },
					{name: title[18],value: data[18] },
					{name: title[19],value: data[19] },
					{name: title[20],value: data[20] },
					{name: title[21],value: data[21] },
					{name: title[22],value: data[22] },
					{name: title[23],value: data[23] },
					{name: title[24],value: data[24] },
					{name: title[25],value: data[25] },
					{name: title[26],value: data[26] },
					{name: title[27],value: data[27] },
					{name: title[28],value: data[28] },
					{name: title[29],value: data[29] },
					{name: title[30],value: data[30] },
				]
			}
		]
	};
	myChart.setOption(option);
}


//冒泡排序
/*function sort(elements){
	for (var i = 0; i<elements.length -1; i++){
		for (var j = 0; j < elements.length-i-1; j++){
			if(elements[j]> elements[j+1]){
				var swap = elements[j];
				elements[j] = elements[j+1];
				elements[j+1] = swap;
			}
		}
	}
}
var elements = [3,1,5,7,2,4,9,6,10,8];
console.log('排序前'+ elements);
sort(elements);
console.log('排序后'+ elements);*/
