var chartPie;
var chartBar;
var chartLine;
var ability			= '';
var playerName			= '';
var numOfPlots 			= 19;
var startTime			= 0;
var value 			= 0;
var abilityArray 		= [];
var abilityDPSArray 		= [];
var dpsArray 			= [];
var allPercArracy		= [];
var dpsPercArray 		= [];
var timeArray			= [];
var dpsRealArray		= [];
var plotDPSArray 		= [];
var plotTimeArray 		= [];

function pageLoad() {
	$(function () {
		var tableInfo			= document.getElementById('infoTable');
		var tableAbility 		= document.getElementById('abilityTable');
		var tableTime 			= document.getElementById('timeTable');
		var dataType			= "";
		var numOfRows 			= tableAbility.rows.length;
		var numOfRows2 			= tableTime.rows.length;
		playerName 			= tableInfo.rows[1].cells[1].textContent;
		abilityArray 			= [];
		abilityDPSArray 		= [];
		dpsArray 			= [];
		allPercArracy			= [];
		dpsPercArray 			= [];
		timeArray			= [];
		dpsRealArray			= [];
		
		if ( numOfRows > 2 ) {
			for (var row = 0; row < numOfRows; row++) {	
				value 			= tableAbility.rows[row].cells[3].textContent;
				dpsArray[row] 		= value;

				ability 		= tableAbility.rows[row].cells[0].textContent;
				abilityDPSArray[row] 	= ability;
				abilityArray[row] 	= ability;

				var temp 		= [];
				value 			= tableAbility.rows[row].cells[4].textContent;
				temp[0] 		= ability;
				temp[1] 		= parseFloat(value);
				dpsPercArray[row] 	= temp;
			}

			abilityArray.splice((numOfRows - 1),1);
			abilityDPSArray.splice((numOfRows - 1),1);
			dpsArray.splice((numOfRows - 1),1);
			dpsPercArray.splice((numOfRows - 1),1);	

			abilityArray.splice(0,1);
			abilityDPSArray.splice(0,1);
			dpsArray.splice(0,1);
			dpsPercArray.splice(0,1);

			var dpsCount 		= 0;
			var tempDPSAbility 	= [];
			var tempDPS 		= [];
			var tempDPSPercAbility 	= [];

			for (var count = 0; count < abilityArray.length; count++) {
				if (dpsArray[count] != '-') {
					tempDPSAbility.push(abilityDPSArray[count]);
					tempDPS.push(parseFloat(dpsArray[count]));
					tempDPSPercAbility.push(dpsPercArray[count]);
				}
			}
	
			abilityDPSArray 		= tempDPSAbility;
			dpsArray 			= tempDPS;
			dpsPercArray 			= tempDPSPercAbility;

			var firstSeconds		= 0;
			var seconds			= 0;
			var lastSeconds			= 0;
			var secondArray			= [];
			var minutes			= 0;

			firstSeconds = parseInt(tableTime.rows[1].cells[0].textContent.substring(6, 8));
			lastSeconds = parseInt(tableTime.rows[1].cells[0].textContent.substring(6, 8));
			seconds = parseInt(tableTime.rows[1].cells[0].textContent.substring(6, 8));

			for (var row = 0; row < numOfRows2; row++) {
				if (row > 0) {

					seconds = parseInt(tableTime.rows[row].cells[0].textContent.substring(6, 8));

					if (seconds > lastSeconds) { // 2s > 1s so increase by one
						seconds = seconds + 1;
					} else if (seconds < lastSeconds) { // 0s < 59s
						minutes = minutes + 1;
						seconds = (60 * minutes) + seconds + 1;
					} else if (seconds == lastSeconds) {
						seconds = lastSeconds;
					}

					secondArray[row] = parseInt(seconds);
				}

				timeArray[row] = String(tableTime.rows[row].cells[0].textContent)
				dpsRealArray[row] = parseFloat(tableTime.rows[row].cells[6].textContent);
			}

			timeArray.splice(0,1);
			dpsRealArray.splice(0,1);

			var arrayLength 	= dpsRealArray.length;
			var interval 		= Math.round(arrayLength / numOfPlots);
			var currentPlot 	= 0;
			var selectedIndex 	= 0;
			plotDPSArray 	= [];
			plotTimeArray 	= [];

			for (var plots = 0; plots < numOfPlots; plots++) {
				if (plots == 0) {
					selectedIndex = 0
					plotDPSArray[plots] = dpsRealArray[0];
					plotTimeArray[plots] = '0s';
				} else {
					selectedIndex = plots * interval;
					plotDPSArray[plots] = dpsRealArray[selectedIndex];
					plotTimeArray[plots] = String(selectedIndex + 's');
				}
			}



			plotDPSArray.push(dpsRealArray[arrayLength - 1]);
			plotTimeArray.push(String(arrayLength + 's'));

			createChartBar(abilityDPSArray, dpsArray, 'DPS', 'Damage Per Second (DPS)', 'Ability DPS Breakdown');
			createChartPie(dpsPercArray, 'DPS', 'DPS Ability Usage');
			createChartArea(plotDPSArray, playerName, 'DPS', 'Damage Per Second (DPS)', '#488AC7');
		} else {
			alert('No Abilities Timeline');
			alert('No Abilities');
			chartBar.destroy();
			chartPie.destroy();
			chartLine.destroy();
		}
	});
}

function updateBarDPS() {
	chartBar.destroy();
	chartPie.destroy();
	createChartBar(abilityDPSArray, dpsArray, 'DPS', 'Damage Per Second (DPS)', 'Ability DPS Breakdown');
	createChartPie(dpsPercArray, 'DPS', 'DPS Ability Usage');
	createChartArea(plotDPSArray, playerName, 'DPS', 'Damage Per Second (DPS)', '#488AC7');
}

function updateBarHPS() {
	chartBar.destroy();
	chartPie.destroy();
	createChartBar(abilityDPSArray, dpsArray, 'HPS', 'Heal Per Second (HPS)', 'Ability HPS Breakdown');
	createChartPie(dpsPercArray, 'HPS', 'HPS Ability Usage');
	createChartArea(plotDPSArray, playerName, 'HPS', 'Heal Per Second (HPS)', '#3E8A21');
}

function updateBarIDPS() {
	chartBar.destroy();
	chartPie.destroy();
	createChartBar(abilityDPSArray, dpsArray, 'iDPS', 'Incoming Damage Per Second (iDPS)', 'Ability iDPS Breakdown');
	createChartPie(dpsPercArray, 'iDPS', 'iDPS Ability Usage');
	createChartArea(plotDPSArray, playerName, 'iDPS', 'Incoming Damage Per Second (iDPS)', '#E66C2C');
}

function updateBarIHPS() {
	chartBar.destroy();
	chartPie.destroy();
	createChartBar(abilityDPSArray, dpsArray, 'iHPS', 'Incoming Heal Per Second (iHPS)', 'Ability iHPS Breakdown');
	createChartPie(dpsPercArray, 'iHPS', 'iHPS Ability Usage');
	createChartArea(plotDPSArray, playerName, 'iHPS', 'Incoming Heal Per Second (iHPS)', '#E66C2C');
}

function createChartBar(categoryArray, dataArray, name, category, title) {
	$(function () {
		chartBar = new Highcharts.Chart({
			chart: {
				renderTo: 'container',
				type: 'bar',
				height: 500,
				spacingLeft:5,
				spacingRight:15
			},
			title: {
				text: title
			},
			credits: {
				enabled: false
			},
			xAxis: {
				categories: categoryArray
			},
			yAxis: {
				title: {
					text: category,
					align: 'middle'
				}
			},
			legend: {
				enabled: false,
				layout: 'vertical',
				floating: true,
				backgroundColor: '#FFFFFF',
				align: 'right',
				verticalAlign: 'top',
				y: 60,
				x: -60
			},
			tooltip: {
				formatter: function() {
					return '<b>'+ playerName +'</b><br/>'+ this.x + ' ' + this.series.name + ' : '+ this.y;
				}
			},
			plotOptions: {
				bar: {
					dataLabels: {
						enabled: true,
						distance: -10,
						x: 0
					}
				},
				series: {
					colorByPoint: true
				}
			},
			series: [{
				id: 'data',
				name: name,
				data: dataArray
			}]
		});
	});
}

function createChartPie(percentArray, name, title) {
	$(function () {
		chartPie = new Highcharts.Chart({
			chart: {
				renderTo: 'container2',
				type: 'pie',
				backgroundColor: '#FFFFFF',
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				margin: [0, 0, 0, 0],
				height: 300
			},
			title: {
				text: title
			},
			legend: {
				enabled: false,
				layout: 'horizontal',
				backgroundColor: '#FFFFFF',
				align: 'right',
				verticalAlign: 'top',
				floating: false,
				x: 0,
				y: 200
			},
			credits: {
				enabled: false
			},
			tooltip: {
				formatter: function() {
					return '<b>' + playerName + '</b><br/>'+ this.point.name + ' ' + this.series.name + ' %: ' + this.percentage.toFixed(2) +'%';
				}
			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					size: '55%',
					cursor: 'pointer',
					dataLabels: {
						enabled: true,
						distance: 30,
						color: '#000000',
						connectorColor: '#000000',
						formatter: function() {
							return '<center><b>'+ this.point.name +'</b></center><br><center>'+ this.percentage.toFixed(2) +'%</center>';
						}
					},
					showInLegend: true
				}
			},
			series: [{
				name: name,
				data: percentArray
			}]
		});
	});
}

function createChartArea(data, name, type, titleName, color) {
	$(function () {
		chartLine = new Highcharts.Chart({
			chart: {
				renderTo: 'container3',
				type: 'area',
				zoomType: 'xy'
			},
			title: {
				text: 'Performance Timeline'
			},
			legend: {
				    enabled: true
			},
			xAxis: {
				categories: plotTimeArray,
				title: {
					enabled: true,
					text: 'Duration (Seconds)',
					style: {
					    fontWeight: 'normal'
					}
				}				
			},
			yAxis: {
				title: {
					text: titleName,
					align: 'middle'
				}
			},
			credits: {
				enabled: false
			},
			tooltip: {
				formatter: function() {
					return this.series.name + '<b>'+ this.y +'</b>';
				}
			},
			plotOptions: {
				area: {
					pointStart: 0,
					marker: {
						enabled: true,
						symbol: 'circle',
						radius: 3,
						states: {
							hover: {
								enabled: true
							}
						}
					}
				}
			}
		});

		var title;
		title 	= name + '\'s ' + type;

		chartLine.addSeries({
			name: title,
			color: color,
			data: data
		});			

	});
	
}

function updateData(id) {
	var data	= id.split("|");
	var dataType 	= data[0];
	var encid	= data[1];
	var playerName	= data[2];
	var playerClass	= data[3];

	var xmlHttp = getXMLHttp();

	xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4) {
			HandleResponse(xmlHttp.responseText, dataType);
		}
	}

	var url = "paneTable.php?type=" + dataType + "&enc=" + encid + "&player=" + playerName + "&class=" + playerClass;
	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
}

function getXMLHttp() {
	var xmlHttp

  	try {
    		xmlHttp = new XMLHttpRequest();
  	} catch(e) {
    		try {
      			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
    		} catch(e) {
      			try {
        			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
      			} catch(e) {
        			alert("Your browser does not support AJAX!")
        			return false;
      			}
    		}
  	}

  	return xmlHttp;
}

function HandleResponse(response, dataType) {
	document.getElementById('paneTable').innerHTML = response;
	
	pageLoad();
	
	if ( dataType == "DPS" ) {
		updateBarDPS();
	} else if ( dataType == "HPS" ) {
		updateBarHPS();
	} else if ( dataType == "iDPS" ) {
		updateBarIDPS();
	} else if ( dataType == "iHPS" ) {
		updateBarIHPS();
	}
}

function ChangeEncounter(id) {
	var data	= $('#encounterList').val().split("|");
	var encid	= data[0];
	var playerName	= data[1];

	var xmlHttp = getXMLHttp();

	xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4) {
			HandleEncounterResponse(xmlHttp.responseText);
		}
	}
	
	var url = "player.php?enc=" + encid + "&player=" + playerName + "&mob=";
	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
}

function HandleEncounterResponse(response) {
	document.getElementsByTagName("html")[0].innerHTML = response;
	pageLoad();
}