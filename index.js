function pageLoad() {
	$(function () {
		var tableEncounter 		= document.getElementById('encounterTable');
		var numOfRows 			= tableEncounter.rows.length;
		var ability			= '';
		var value 			= 0;
		var mobArray 			= [];
		var dpsArray 			= [];
		var hpsArray			= [];
		var idpsArray			= [];
		var idpsArray			= [];
		var ihpsArray			= [];

		for (var row = 0; row < numOfRows; row++) {
			mobArray[row] = String(tableEncounter.rows[row].cells[0].innerHTML + ' - ' + tableEncounter.rows[row].cells[1].innerHTML);
			dpsArray[row] = parseFloat(tableEncounter.rows[row].cells[2].textContent);
			hpsArray[row] = parseFloat(tableEncounter.rows[row].cells[3].textContent);
			idpsArray[row] = parseFloat(tableEncounter.rows[row].cells[4].textContent);
			ihpsArray[row] = parseFloat(tableEncounter.rows[row].cells[5].textContent);
		}

		mobArray.splice(0,1);
		dpsArray.splice(0,1);
		hpsArray.splice(0,1);
		idpsArray.splice(0,1);
		ihpsArray.splice(0,1);

		var mobLimit = 5;
		var cutAmount = mobArray.length - mobLimit;

		if (mobArray.length > mobLimit) {
			mobArray.splice(mobLimit,cutAmount);
			dpsArray.splice(mobLimit,cutAmount);
			hpsArray.splice(mobLimit,cutAmount);
			idpsArray.splice(mobLimit,cutAmount);
			ihpsArray.splice(mobLimit,cutAmount);
		}

		$(function () {
			chartBar = new Highcharts.Chart({
				chart: {
					renderTo: 'paneGraph',
					height: 500,
					width: 1000,
					type: 'bar'
				},
				title: {
					text: mobLimit + ' Most Recent Parses'
				},
				xAxis: {
					categories: mobArray
				},
				yAxis: {
					title: {
						text: 'Per Second',
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
								return '<b>' + Highcharts.numberFormat(this.y, 2, '.', ',') + '</b>';
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
				},{
					id: 'HPS',
					name: 'HPS',
					data: hpsArray
				},{
					id: 'iDPS',
					name: 'iDPS',
					data: idpsArray
				},{
					id: 'iHPS',
					name: 'iHPS',
					data: ihpsArray
				}]
			});

		});
	});

	setTable();
}

function RequestParses() {
	var xmlHttp = getXMLHttp();

  	xmlHttp.onreadystatechange = function() {
  		if(xmlHttp.readyState == 4) {
      			HandleParseResponse(xmlHttp.responseText);
    		}
  	}

  	var player = 	document.getElementById("playerName").innerHTML;
	var encName = 	$('#selectEncounter').val();

	if (encName != "") {
		var url = 	"paneChart.php?mob=" + encName + "&player=" + player;

  	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
  	}
}

function resetSearch() {
	var tiers 	= document.getElementById('parseTiers');
	var players 	= document.getElementById('parsePlayers');
	var dungeons 	= document.getElementById('parseDungeons');
	var encounters 	= document.getElementById('parseEncounters');
	var types 	= document.getElementById('parseTypes');

	var valTiers		= String($('#parseTiers').val());
	var valPlayers		= String($('#parsePlayers').val());
	var valDungeons		= String($('#parseDungeons').val());
	var valEncounters	= String($('#parseEncounters').val());
	var valTypes		= String($('#parseTypes').val());

	tiers.selectedIndex 		= 0;
	players.selectedIndex 		= 0;
	dungeons.selectedIndex 		= 0;
	encounters.selectedIndex 	= 0;
	types.selectedIndex 		= 2;
	valTypes 					= String($('#parseTypes').val());
	types.selectedIndex 		= 0;

	var xmlHttp = getXMLHttp();

	xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4) {
			HandleSearchResponse(xmlHttp.responseText, valTiers, valPlayers, valDungeons, valEncounters, valTypes);
	    	}
	  }

	var url = "paneSearch.php?tier=" + valTiers + "&play=" + valPlayers + "&dun=" + valDungeons + "&enc=" + valEncounters + "&type=" + valTypes;
	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
}

function MakeRequest() {
	var xmlHttp = getXMLHttp();

  	xmlHttp.onreadystatechange = function() {
  		if(xmlHttp.readyState == 4) {
      			HandleResponse(xmlHttp.responseText);
    		}
  	}

	var encid 	= 	$('#encounterList').val();
	var url 	= 	'panePreview.php?enc=' + encid;
  	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
}

function DeleteParse() {
	var xmlHttp = getXMLHttp();

  	xmlHttp.onreadystatechange = function() {
  		if(xmlHttp.readyState == 4) {
      			HandleDeleteResponse(xmlHttp.responseText);
    		}
  	}

   	var valTiers		= String($('#parseTiers').val());
   	var valPlayers		= String($('#parsePlayers').val());
   	var valDungeons		= String($('#parseDungeons').val());
   	var valEncounters	= String($('#parseEncounters').val());
  	var valTypes		= String($('#parseTypes').val());

	var search		= '&tiers=' + valTiers + '&play=' + valPlayers + '&dun=' + valDungeons + '&encounter=' + valEncounters + '&types=' + valTypes;
  	var type 		= "delete";
	var encid 		= $('#encounterList').val();
	var url 		= "paneList.php?type=" + type + "&enc=" + encid + search;

	if ( confirm("Delete Parse?") ) {
	  	xmlHttp.open("GET", url, true);
	  	xmlHttp.send(null);
	}
}

function loadLineChart() {
	var tableEncounter 		= document.getElementById('encounterTable');
	var numOfRows 			= tableEncounter.rows.length;
	var ability			= '';
	var value 			= 0;
	var mobArray 			= [];
	var dpsArray 			= [];
	var dateArray			= [];
	var plotDPSArray		= [];
	var mobName			= String(tableEncounter.rows[1].cells[0].innerHTML);

	for (var row = 0; row < numOfRows; row++) {
		mobArray[row] = String(tableEncounter.rows[row].cells[0].innerHTML + ' - ' + tableEncounter.rows[row].cells[1].innerHTML);
		dpsArray[row] = parseFloat(tableEncounter.rows[row].cells[2].innerHTML);
		dateArray[row] = String(tableEncounter.rows[row].cells[1].innerHTML);

		if (row > 0) {
			var temp = [];
			temp[0] = String(tableEncounter.rows[row].cells[0].innerHTML);
			temp[1] = parseFloat(tableEncounter.rows[row].cells[2].innerHTML);

			plotDPSArray[row] = temp;
		}
	}

	mobArray.splice(0,1);
	dpsArray.splice(0,1);
	dateArray.splice(0,1);

	var mobLimit = 20;
	var cutAmount = mobArray.length - mobLimit;

	if (mobArray.length > mobLimit) {
		mobArray.splice(19,cutAmount);
		dpsArray.splice(19,cutAmount);
	}

	var chartLine;
	$(function () {
		chartLine = new Highcharts.Chart({
			chart: {
				renderTo: 'testChart',
				type: 'area',
				zoomType: 'xy'
			},
			xAxis: {
				categories: dateArray
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
			},
			series: [{
				name: mobName,
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

function HandleChangeResponse(response) {
	document.getElementById('paneList').innerHTML = response;
}

function HandleDeleteResponse(response) {
	document.getElementById('paneList').innerHTML = response;
}

function HandleResponse(response) {
	document.getElementById('panePreview').innerHTML = response;
}

//**********************START-PERFORMING SEARCH FUNCTION**********************

function changeData() {
	var valTiers		= String($('#parseTiers').val());
	var valPlayers		= String($('#parsePlayers').val());
	var valDungeons		= String($('#parseDungeons').val());
	var valEncounters	= String($('#parseEncounters').val());
	var valTypes		= String($('#parseTypes').val());
	var startMonths 	= $('#parseStartMonthList').val();
	var startDays 		= $('#parseStartDayList').val();
	var startYears 		= $('#parseStartYearList').val();
	var endMonths 		= $('#parseEndMonthList').val();
	var endDays 		= $('#parseEndDayList').val();
	var endYears 		= $('#parseEndYearList').val();
	var startDate 		= startMonths + "/" + startDays + "/" + startYears;
	var endDate 		= endMonths + "/" + endDays + "/" + endYears;
	
	//alert(startYears + " - " + startMonths + " - " + startDays);
	
    	var date1 = new Date(startYears, startMonths, startDays);
    	var date2 = new Date(endYears, endMonths, endDays);
    	
    	//alert(date1 + " - " + date2);
    	
    	if ( date2 >= date1 ) {
		var xmlHttp = getXMLHttp();

		xmlHttp.onreadystatechange = function() {
			if(xmlHttp.readyState == 4) {
				HandleSearchResponse(xmlHttp.responseText, valTiers, valPlayers, valDungeons, valEncounters, valTypes);
			}
		}

		var url = "paneSearch.php?tier=" + valTiers + "&play=" + valPlayers + "&dun=" + valDungeons + "&enc=" + valEncounters + "&type=" + valTypes + "&start=" + startDate + "&end=" + endDate;
		xmlHttp.open("GET", url, true);
		xmlHttp.send(null);
	} else {
		alert("Invalid Date Range!");
	}
}

function HandleSearchResponse(response, tiers, players, dungeons, encounters, types) {
	document.getElementById('paneList').innerHTML = response;
	updateTable(tiers, players, dungeons, encounters, types);
}

function updateTable(tiers, players, dungeons, encounters, types) {
	var xmlHttp = getXMLHttp();

	xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4) {
			HandleTableResponse(xmlHttp.responseText, tiers, players, dungeons, encounters, types);
		}
	}

	var url = "paneChart.php?tier=" + tiers + "&play=" + players + "&dun=" + dungeons + "&enc=" + encounters + "&type=" + types;
	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
}

function HandleTableResponse(response, tiers, players, dungeons, encounters, types) {
	document.getElementById('paneChart').innerHTML = response;
	updateChart(tiers, players, dungeons, encounters, types);
}

function updateChart(tiers, players, dungeons, encounters, types) {
	var tableEncounters	= document.getElementById('encounterTable');
	var searchMob		= String($('#parseEncounters').val());
	var numOfRows 		= tableEncounters.rows.length;
	var mobArray		= [];

	var mob1		= '';
	var mob2		= '';
	for (var row = 0; row < numOfRows; row++) {
		mobArray[row] = String(tableEncounters.rows[row].cells[0].textContent);
	}

	mobArray.splice(0,1);

	reloadChart(tiers, players, dungeons, encounters, types);
	setTable();
}

function reloadChart(tiers, players, dungeons, encounters, types) {
	var title = '';

	if (encounters != '') {
		title = 'Recent ' + encounters + ' ';

		if (types != '') {
			title = title + types + ' Parses';
		} else {
			title = title + ' Parses';
		}
	} else if (encounters == '') {
		title = 'Recent Encounter Performance';
	}

	var tableEncounter 		= document.getElementById('encounterTable');
	var numOfRows 			= tableEncounter.rows.length;
	var ability			= '';
	var value 			= 0;
	var mobArray 			= [];
	var dpsArray 			= [];
	var hpsArray			= [];
	var idpsArray			= [];
	var ihpsArray			= [];

	for (var row = 0; row < numOfRows; row++) {
		mobArray[row] = String(tableEncounter.rows[row].cells[0].innerHTML + ' - ' + tableEncounter.rows[row].cells[1].innerHTML);
		dpsArray[row] = parseFloat(tableEncounter.rows[row].cells[2].textContent);
		hpsArray[row] = parseFloat(tableEncounter.rows[row].cells[3].textContent);
		idpsArray[row] = parseFloat(tableEncounter.rows[row].cells[4].textContent);
		ihpsArray[row] = parseFloat(tableEncounter.rows[row].cells[5].textContent);
	}

	mobArray.splice(0,1);
	dpsArray.splice(0,1);
	hpsArray.splice(0,1);
	idpsArray.splice(0,1);
	ihpsArray.splice(0,1);

	var mobLimit = 5;
	var cutAmount = mobArray.length - mobLimit;

	if (mobArray.length > mobLimit) {
		mobArray.splice(mobLimit,cutAmount);
		dpsArray.splice(mobLimit,cutAmount);
		hpsArray.splice(mobLimit,cutAmount);
		idpsArray.splice(mobLimit,cutAmount);
		ihpsArray.splice(mobLimit,cutAmount);
	}

	$(function () {
		chartBar = new Highcharts.Chart({
			chart: {
				renderTo: 'paneGraph',
				height: 500,
				width: 1000,
				type: 'bar'
			},
			title: {
				text: title
			},
			xAxis: {
				categories: mobArray
			},
			yAxis: {
				title: {
					text: 'Per Second',
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
				backgroundColor: '#FFFFFF',
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
							return '<b>' + Highcharts.numberFormat(this.y, 2, '.', ',') + '</b>';
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
			}, {
				id: 'HPS',
				name: 'HPS',
				data: hpsArray
			}, {
				id: 'iDPS',
				name: 'iDPS',
				data: idpsArray
			}, {
				id: 'iHPS',
				name: 'iHPS',
				data: ihpsArray
			}]
		});

	});
}

function setTable() {
	//document.getElementById('encounterTable').style.display="none";
}

//**********************END-PERFORMING SEARCH FUNCTION**********************

//**********************START-ADJUSTING PARSE FUNCTION**********************

//**********************END-ADJUSTING PARSE FUNCTION**********************

function MakeChanges() {
	var xmlHttp = getXMLHttp();

  	xmlHttp.onreadystatechange = function() {
  		if(xmlHttp.readyState == 4) {
      			HandleChangeResponse(xmlHttp.responseText);
    		}
  	}

 	var valTiers		= String($('#parseTiers').val());
 	var valPlayers		= String($('#parsePlayers').val());
 	var valDungeons		= String($('#parseDungeons').val());
 	var valEncounters	= String($('#parseEncounters').val());
	var valTypes		= String($('#parseTypes').val());

	var search		= '&tiers=' + valTiers + '&play=' + valPlayers + '&dun=' + valDungeons + '&encounter=' + valEncounters + '&types=' + valTypes;
  	var type 		= "update";
	var encid 		= $('#encounterList').val();
	var encName		= document.getElementById('nameValue').value;
	var months 		= $('#monthList').val();
	var days 		= $('#dayList').val();
	var years 		= $('#yearList').val();
	var date 		= months + "/" + days + "/" + years;
	var uploader 		= document.getElementById('uploadby').value;
	var notes 		= document.getElementById('notes').value;
	var parseType 		= $('#parseList').val();
	var url 		= "paneList.php?type=" + type + "&enc=" + encid + "&name=" + encName + "&date=" + date + "&notes=" + notes + "&uploader=" + uploader + "&parse=" + parseType + search;

	if ( confirm("Submit Changes?") ) {
  		xmlHttp.open("GET", url, true);
  		xmlHttp.send(null);
	}
}