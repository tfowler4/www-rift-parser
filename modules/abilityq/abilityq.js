var chartPie;

function pageLoad() {
	$(function () {
		var table;
		var infoArray 	= [];
		var playerArray = []
		var start	= 6;

		table = document.getElementsByTagName('table');
		
		for (var index = 0; index < table.length; index++) {
			var numOfTables			= table.length - start;
			var individualTables		= numOfTables / 2;
			
			var value			= 0;
			
			if ( index == 0 ) {
				value 			= index + start + 2;
			} else {
				value 			= (index + start) + (index + 2);
			}

			if ( value < table.length  && table[value].id != 'NaN' && table[value].id.indexOf('graph') == -1 ) {
				var tableID			= table[value].id;
				var divID			= 'chart' + tableID;
				
				playerArray.push(tableID);
				
				var tableCurrent 		= document.getElementById(tableID);
				var numOfRows 			= tableCurrent.rows.length;
				var startTime			= 0;
				var timeArray			= [];
				var dpsRealArray		= [];
				var abilityDPSArray		= [];
				var totalDPSArray		= [];
				var value			= 0;
				var type			= tableCurrent.rows[0].cells[5].textContent;
				var title			= "";
				var tooltip			= "";
				var duration			= numOfRows - 1;

				if ( type == "DPS" ) {
					title 	= "Damage Per Second (DPS)";
					tooltip = '\'s Current DPS: <b>';
				} else if ( type == "HPS" ) {
					title 	= "Heal Per Second (HPS)";
					tooltip = '\'s Current HPS: <b>';
				} else if ( type == "INCDPS" ) {
					title 	= "Incoming Damage Per Second (iDPS)";
					tooltip = '\'s Current iDPS: <b>';
				} else if ( type == "INCHPS" ) {
					title 	= "Incoming Heal Per Second (iHPS)";
					tooltip = '\'s Current iHPS: <b>';
				}

				var previousDPS = 0.00;

				for (var row = 1; row < numOfRows; row++) {
					value = row - 1
					timeArray[value] = tableCurrent.rows[row].cells[0].textContent;

					if (tableCurrent.rows[row].cells[5].textContent == '-') {
						dpsRealArray[value] = previousDPS;
					} else {						
						dpsRealArray[value] = parseFloat(tableCurrent.rows[row].cells[5].textContent);
						previousDPS = parseFloat(tableCurrent.rows[row].cells[5].textContent);
						
						//var abilityName = tableCurrent.rows[row].cells[1].textContent;
						//abilityDPSArray[abilityName] = abilityDPSArray[abilityName] + parseFloat(tableCurrent.rows[row].cells[5].textContent);
					}
				}

				var arrayLength = dpsRealArray.length;
				var numOfPlots = 15;
				var interval = parseInt(arrayLength / numOfPlots);
				var currentPlot = 0;
				var plotDPSArray = [];
				var plotTimeArray = [];
				var selectedIndex = 0;

				for (var plots = 0; plots < numOfPlots; plots++) {
					if (plots == 0) {
						selectedIndex = 0
						plotDPSArray[plots] = dpsRealArray[0];
						plotTimeArray[plots] = '0s';
					} else {
						selectedIndex = plots * interval;
						plotDPSArray[plots] = parseInt(dpsRealArray[selectedIndex]);
						plotTimeArray[plots] = String(selectedIndex + 's');
					}
				}

				plotDPSArray.push(dpsRealArray[arrayLength - 1]);
				plotTimeArray.push(String(arrayLength + 's'));

				var tempArray = [];
				tempArray[0] = plotDPSArray;
				tempArray[1] = plotTimeArray;

				infoArray.push(tempArray);
			}
		}
		
		
		var pieTables = (playerArray.length * 2) + start + 1;		
		for (var row = pieTables; row < table.length; row++) {
			var id			= table[row].id;
			var tablePie 		= document.getElementById(id);
			var percArray		= [];
			var abilitySum		= 0;
			
			setTable(id);
			
			for (var row2 = 0; row2 < tablePie.rows.length; row2++) {
				var ability2		= tablePie.rows[row2].cells[1].textContent;
				
				var temp 		= [];
				var value2 		= tablePie.rows[row2].cells[3].textContent;
				temp[0] 		= ability2;
				temp[1] 		= parseFloat(value2);
				percArray[row2] 	= temp;
				
				if ( row2 > 1 ) {
					abilitySum += parseFloat(value2);
				}
			}
			
			percArray.splice(0,1);
			percArray[0][1] = percArray[0][1] - abilitySum;
			
			if ( percArray[0][1] < 1 ) {
				percArray.splice(0,1);
			}

			var playerGraph = id.substring(5);
			createPie(playerGraph, percArray);
		}
		
		var chartLine;
		$(function () {
			chartLine = new Highcharts.Chart({
				chart: {
					renderTo: 'container',
					type: 'line',
					zoomType: 'xy'
				},
				title: {
					text: 'Ability Queue'
				},
				xAxis: {
					categories: infoArray[0][1]
				},
				yAxis: {
					title: {
						text: title,
						align: 'middle'
					}
				},
				credits: {
					enabled: false
				},
				tooltip: {
					formatter: function() {
						return this.series.name + tooltip + this.y +'</b>';
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

			for (var player = 0; player < playerArray.length; player++) {
				chartLine.addSeries({
					name: playerArray[player],
					data: infoArray[player][0]
				});
			}
		});

		var chartArea;
			$(function () {
				chartArea = new Highcharts.Chart({
					chart: {
						renderTo: 'container2',
						type: 'area'
					},
					title: {
						text: 'Ability Queue'
					},
					xAxis: {
						categories: infoArray[0][1]
					},
					yAxis: {
						text: 'DPS'
					},
					credits: {
						enabled: false
					},
					tooltip: {
						formatter: function() {
							return this.series.name + '\'s Current DPS: <b>'+ this.y +'</b>';
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

				for (var player = 0; player < playerArray.length; player++) {
					chartArea.addSeries({
						name: playerArray[player],
						data: infoArray[player][0]
					});
				}
			});
		});
}

function createPie(playerName, data) {
	var render = 'chart' + playerName;
	$(function () {
		chartPie = new Highcharts.Chart({
			chart: {
				renderTo: render,
				type: 'pie',
				backgroundColor: '',
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				margin: [0, 0, 0, 0],
				height: 200
			},
			title: {
				text: ''
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
					return '<b>' + playerName + '</b><br/>'+ this.point.name + ' %: ' + this.percentage.toFixed(2) +'%';
				}
			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					size: '55%',
					cursor: 'pointer',
					dataLabels: {
						enabled: true,
						distance: 20,
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
				data: data
			}]
		});
	});
}

function setTable(id) {
	document.getElementById(id).style.display="none";
}