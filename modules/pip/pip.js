function pageLoad() {
	$(function () {
		var playerName 			= document.getElementById('playerName').innerHTML;
		var tableEncounter 		= document.getElementById('encounterTable');
		var numOfRows 			= tableEncounter.rows.length;
		var ability			= '';
		var value 			= 0;
		var mobArray 			= [];
		var dpsArray 			= [];
		var hpsArray 			= [];
		var dmgArray 			= [];

		for (var row = 0; row < numOfRows; row++) {
			mobArray[row] = String(tableEncounter.rows[row].cells[0].innerHTML + ' - ' + tableEncounter.rows[row].cells[1].innerHTML);
			dpsArray[row] = parseFloat(tableEncounter.rows[row].cells[2].innerHTML);
			hpsArray[row] = parseFloat(tableEncounter.rows[row].cells[3].innerHTML);
			dmgArray[row] = parseInt(tableEncounter.rows[row].cells[4].innerHTML);
		}

		mobArray.splice(0,1);
		dpsArray.splice(0,1);
		hpsArray.splice(0,1);
		dmgArray.splice(0,1);


		$(function () {
			chartBar = new Highcharts.Chart({
				chart: {
					renderTo: 'chartArea',
					height: 500,
					width: 824,
					type: 'bar'
				},
				title: {
					text: 'Encounter Performance'
				},
				xAxis: {
					categories: mobArray
				},
				yAxis: {
					title: {
						text: 'Damage/Heal Per Second (DPS/HPS)',
						align: 'high'
					}
				},
				credits: {
					enabled: false
				},
				legend: {
					enabled: true,
					layout: 'horizontal',
					floating: false,
					align: 'center',
					verticalAlign: 'bottom'
				},
				tooltip: {
					formatter: function() {
						return this.x +': '+ this.y +' ' + this.series.name;
					}
				},
				plotOptions: {
					bar: {
						dataLabels: {
							x: 0,
							enabled: true,
							formatter: function() {
										return '<b>' + this.y + '</b>';
							}
						}
					},
					series : {
						pointWidth: 10
					}
				},
				series: [{
					id: 'DPS',
					name: 'DPS',
					data: dpsArray
				}]
			});
		});
	})
}

function reloadChart() {
	var playerName 			= document.getElementById('playerName').innerHTML;
	var tableEncounter 		= document.getElementById('encounterTable');
	var numOfRows 			= tableEncounter.rows.length;
	var ability			= '';
	var value 			= 0;
	var mobArray 			= [];
	var dpsArray 			= [];
	var hpsArray 			= [];
	var dmgArray 			= [];

	for (var row = 0; row < numOfRows; row++) {
		mobArray[row] = String(tableEncounter.rows[row].cells[0].innerHTML);
		dpsArray[row] = parseFloat(tableEncounter.rows[row].cells[2].innerHTML);
		hpsArray[row] = parseFloat(tableEncounter.rows[row].cells[3].innerHTML);
		dmgArray[row] = parseInt(tableEncounter.rows[row].cells[4].innerHTML);
	}

	mobArray.splice(0,1);
	dpsArray.splice(0,1);
	hpsArray.splice(0,1);
	dmgArray.splice(0,1);

	$(function () {
		chartBar = new Highcharts.Chart({
			chart: {
				renderTo: 'chartArea',
				height: 500,
				width: 1024,
				type: 'bar'
			},
			title: {
				text: 'Encounter Performance'
			},
			xAxis: {
				categories: mobArray
			},
			yAxis: {
				text: 'Damage/Heal Per Second (DPS/HPS)'
			},
			credits: {
				enabled: false
			},
			legend: {
				enabled: true,
				layout: 'horizontal',
				floating: false,
				align: 'center',
				verticalAlign: 'bottom'
				},
			tooltip: {
				formatter: function() {
					return '<b>'+ playerName +'</b><br/>'+ this.x + 's DPS : '+ this.y;
				}
			},
			plotOptions: {
				bar: {
					dataLabels: {
						x: 0,
						enabled: true,
						formatter: function() {
									return '<b>' + this.y + '</b>';
						}
					}
				},
				series : {
					pointWidth: 10
				}
			},
			series: [{
				id: 'DPS',
				name: 'DPS',
				data: dpsArray
			}]
		});

	});
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

function RequestParses() {
	var xmlHttp = getXMLHttp();
 
  	xmlHttp.onreadystatechange = function() {
  		if(xmlHttp.readyState == 4) {
      			HandleParseResponse(xmlHttp.responseText);
    		}
  	}
  	
  	var player 	= document.getElementById("playerName").innerHTML;
	var encName 	= $('#selectEncounter').val();
	var parseType 	= $('#parseTypes').val();
	
	if (encName != "") {
		var url = 	"getParses.php?enc=" + encName + "&player=" + player + "&type=" + parseType;
	
  	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
  	}
}

function HandleParseResponse(response) {	
	document.getElementById('parseData').innerHTML = response;
	reloadChart();
}