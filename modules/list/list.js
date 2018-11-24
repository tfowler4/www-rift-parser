var chartDistPie;
var chartLine;

function pageLoad() {	
	var tableList 			= document.getElementById('listTable');
	var tableDPS			= document.getElementById('tableDataDPS');
	var tableHPS			= document.getElementById('tableDataHPS');
	var tableINC			= document.getElementById('tableDataINC');

	var numOfRows 			= tableList.rows.length;
	var numOfRowsDPS 		= tableDPS.rows.length;
	var numOfRowsHPS 		= tableHPS.rows.length;
	var numOfRowsINC 		= tableINC.rows.length;

	var dpsArray 			= [];
	var hpsArray 			= [];
	var incArray 			= [];
	var dpsOverallArray		= [];
	var hpsOverallArray 		= [];
	var incOverallArray 		= [];
	var timeArray 			= [];
	var idpsArray			= [];
	var dpsRealArray		= [];
	var hpsRealArray		= [];
	var incRealArray		= [];
	var classArray			= ['Rogue', 'Warrior', 'Cleric', 'Mage'];
	var numOfClasses		= classArray.length;

	var currentClass		= '';
	var damageCount			= 0;
	var overallDamage		= 0;
	var healCount			= 0;
	var overallHeal			= 0;
	var incDamageCount		= 0;
	var overallIncDamage		= 0;
	
	for (var theClass = 0; theClass < numOfClasses; theClass++) {
		damageCount = 0;
		healCount = 0;
		incDamageCount = 0;
		currentClass = classArray[theClass];

		for (var row = 0; row < numOfRows; row++) {
			if (row > 0) {
				var rowClass = tableList.rows[row].cells[1].textContent;

				if (rowClass == currentClass) {
					var newDamage = tableList.rows[row].cells[5].textContent.replace(/,/g,'');
					damageCount = damageCount + parseInt(newDamage);

					var newHeal = tableList.rows[row].cells[7].textContent.replace(/,/g,'');
					healCount = healCount + parseInt(newHeal);

					var newIncDamage = tableList.rows[row].cells[9].textContent.replace(/,/g,'');
					incDamageCount = incDamageCount + parseInt(newIncDamage);

				}
			}
		}

		dpsArray[theClass] = damageCount;
		overallDamage = overallDamage + damageCount;

		hpsArray[theClass] = healCount;
		overallHeal = overallHeal + healCount;

		idpsArray[theClass] = incDamageCount;
		overallIncDamage = overallIncDamage + incDamageCount;
	}

	var plotDPSArray = 	[
				(dpsArray[0]/overallDamage * 100),
				(dpsArray[1]/overallDamage * 100),
				(dpsArray[2]/overallDamage * 100),
				(dpsArray[3]/overallDamage * 100)
				];

	var plotHPSArray = 	[
				(hpsArray[0]/overallHeal * 100),
				(hpsArray[1]/overallHeal * 100),
				(hpsArray[2]/overallHeal * 100),
				(hpsArray[3]/overallHeal * 100)
				];

	var plotINCArray = 	[
				(idpsArray[0]/overallIncDamage * 100),
				(idpsArray[1]/overallIncDamage * 100),
				(idpsArray[2]/overallIncDamage * 100),
				(idpsArray[3]/overallIncDamage * 100)
				];


	var finalDPSArray = [];
	var finalHPSArray = [];
	var finalINCArray = [];
	
	var sumDPS = 0;
	var sumHPS = 0;
	var sumINC = 0;

	for (var theClass = 0; theClass < numOfClasses; theClass++) {
		var temp = [];
		temp[0] = String(classArray[theClass]);
		temp[1] = parseFloat(plotDPSArray[theClass]);
		sumDPS = sumDPS + temp[1];

		finalDPSArray[theClass] = temp;

		var temp = [];
		temp[0] = String(classArray[theClass]);
		temp[1] = parseFloat(plotHPSArray[theClass]);
		sumHPS = sumHPS + temp[1];

		finalHPSArray[theClass] = temp;

		var temp = [];
		temp[0] = String(classArray[theClass]);
		temp[1] = parseFloat(plotINCArray[theClass]);
		sumINC = sumINC + temp[1];

		finalINCArray[theClass] = temp;
	}

	createDistPie('DPS', finalDPSArray);
	createDistPie('HPS', finalHPSArray);	
	createDistPie('INC', finalINCArray);

	var firstSeconds		= 0;
	var seconds			= 0;
	var lastSeconds			= 0;
	var minutes			= 0;
	var secondArray			= [];

	firstSeconds = parseInt(tableDPS.rows[1].cells[0].textContent.substring(6,8));
	lastSeconds = parseInt(tableDPS.rows[1].cells[0].textContent.substring(6,8));
	seconds = parseInt(tableDPS.rows[1].cells[0].textContent.substring(6,8));

	for (var row = 0; row < numOfRowsDPS; row++) {
		if (row > 0) {
			seconds = parseInt(tableDPS.rows[row].cells[0].textContent.substring(6,8));

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

		timeArray[row] = String(tableDPS.rows[row].cells[0].textContent)
		dpsRealArray[row] = parseFloat(tableDPS.rows[row].cells[3].textContent);
		hpsRealArray[row] = parseFloat(tableHPS.rows[row].cells[3].textContent);
		incRealArray[row] = parseFloat(tableINC.rows[row].cells[3].textContent);

	}

	timeArray.splice(0,1);
	dpsRealArray.splice(0,1);
	hpsRealArray.splice(0,1);
	incRealArray.splice(0,1);

	var arrayLength 	= dpsRealArray.length;
	var numOfPlots 		= 19;
	var interval 		= Math.round(arrayLength / numOfPlots);
	var currentPlot 	= 0;
	var plotDPSArray 	= [];
	var plotHPSArray 	= [];
	var plotINCArray 	= [];
	var plotTimeArray 	= [];
	var selectedIndex 	= 0;

	for (var plots = 0; plots < numOfPlots; plots++) {
		if (plots == 0) {
			selectedIndex = 0
			plotDPSArray[plots] = dpsRealArray[0];
			plotHPSArray[plots] = hpsRealArray[0];
			plotINCArray[plots] = incRealArray[0];
			plotTimeArray[plots] = '0s';
		} else {
			selectedIndex = plots * interval;
			plotDPSArray[plots] = parseFloat(dpsRealArray[selectedIndex]);
			plotHPSArray[plots] = parseFloat(hpsRealArray[selectedIndex]);
			plotINCArray[plots] = parseFloat(incRealArray[selectedIndex]);
			plotTimeArray[plots] = String(selectedIndex + 's');
		}
	}

	plotDPSArray.push(dpsRealArray[arrayLength - 1]);
	plotHPSArray.push(hpsRealArray[arrayLength - 1]);
	plotINCArray.push(incRealArray[arrayLength - 1]);
	plotTimeArray.push(String(arrayLength + 's'));
	
	createTimeline(plotTimeArray,plotDPSArray,plotHPSArray,plotINCArray, numOfPlots);
	setTable();
}

function changeMob() {
	var xmlHttp = getXMLHttp();
 	document.getElementById('graphData').innerHTML = "";

  	xmlHttp.onreadystatechange = function() {
  		if(xmlHttp.readyState == 4) {
      			HandlePlayerResponse(xmlHttp.responseText);
    		}
  	}
  	
  	var chkboxes		= document.mobForm.elements.length;
  	var mobName 		= "";
  	
  	for (var count = 0; count < chkboxes; count++) {
  		if (document.mobForm.elements[count].checked) {
  			if (mobName == "") {
  				mobName = document.mobForm.elements[count].value;
  			} else {
  				mobName = mobName + "_" + document.mobForm.elements[count].value;
  			}
  		}
  	}
  	
  	//alert(mobName);
  	  	  	
	var tableDetails 	= document.getElementById('detailsTable');
	var encid 		= tableDetails.rows[1].cells[1].textContent;  	
	var url 		= "panePlayers.php?enc=" + encid + "&mob=" + mobName;
	
  	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
}

function HandlePlayerResponse(response) {
	document.getElementById('panePlayers').innerHTML = response;	
	pageLoad();
}

function comparePlayers() {
	var tablePlayers 	= document.getElementById('listTable');
	var tableDetails	= document.getElementById('detailsTable');
	var encid		= tableDetails.rows[1].cells[1].textContent;
	var playerArray		= [];
	var playerName_string	= "";
	var numOfPlayers	= tablePlayers.rows.length;
	
  	var chkboxes		= document.mobForm.elements.length;
  	var mobName 		= "";
  	
  	for (var count = 0; count < chkboxes; count++) {
  		if (document.mobForm.elements[count].checked) {
  			if (mobName == "") {
  				mobName = document.mobForm.elements[count].value;
  			} else {
  				mobName = mobName + "_" + document.mobForm.elements[count].value;
  			}
  		}
  	}
	
	for (var row = 1; row < numOfPlayers; row++) {
		var currentPlayer 	= tablePlayers.rows[row].cells[0].textContent;
		var currentElement	= document.getElementById(currentPlayer);

		if (currentElement.checked) {
			playerArray.push(currentPlayer);
			
			if (playerName_string == "") {
				playerName_string = currentPlayer;
			} else {
				playerName_string = playerName_string + "_" + currentPlayer
			}
		}
	}
	//alert(playerName_string);
	var address	= "localhost/";
	var url 	= "../p2p/p2p.php?&enc=" + encid + "&players=" + playerName_string + "&mob=" + mobName;
	
	if (playerArray.length < 2) {
		alert("Minimum of 2 players needed for this feature!");
	} else {
		window.location = url;
	}
}

function compareClass(id) {
	var tablePlayers 	= document.getElementById('listTable');
	var tableDetails	= document.getElementById('detailsTable');
	var encid		= tableDetails.rows[1].cells[1].textContent;
	var classCompared	= id;
	var playerArray		= [];
	var playerName_string	= "";
	var numOfPlayers	= tablePlayers.rows.length;
	
  	var chkboxes		= document.mobForm.elements.length;
  	var mobName 		= "";
  	
  	for (var count = 0; count < chkboxes; count++) {
  		if (document.mobForm.elements[count].checked) {
  			if (mobName == "") {
  				mobName = document.mobForm.elements[count].value;
  			} else {
  				mobName = mobName + "_" + document.mobForm.elements[count].value;
  			}
  		}
  	}
	
	for (var row = 0; row < numOfPlayers; row++) {
		var currentPlayer = tablePlayers.rows[row].cells[0].textContent;
		var currentClass = tablePlayers.rows[row].cells[1].textContent;
		
		if (currentClass == classCompared) {
			playerArray.push(currentPlayer);
			
			if (playerName_string == "") {
				playerName_string = currentPlayer;
			} else {
				playerName_string = playerName_string + "_" + currentPlayer
			}
		}
	}
	//alert(playerName_string);
	var address	= "localhost/";
	var url 	= "../p2p/p2p.php?&enc=" + encid + "&players=" + playerName_string + "&mob=" + mobName;
	window.location = url;
}

function setTable() {
	document.getElementById('tableDataDPS').style.display="none";
	document.getElementById('tableDataHPS').style.display="none";
	document.getElementById('tableDataINC').style.display="none";
}

function createDistPie(type, data) {
	var name 	= '';
	var title 	= '';
	var container	= '';
	
	if (type == 'DPS') {
		container 	= 'containerDPS';
		title 		= 'DPS Distribution';
		name 		= 'DPS Distribution via Class';
	} else if (type == 'HPS') {
		container 	= 'containerHPS';
		title 		= 'HPS Distribution';
		name 		= 'HPS Distribution via Class';
	} else if (type == 'INC') {
		type		= 'Inc DPS';
		container 	= 'containerIDPS';
		title 		= 'Inc DPS Distribution';
		name 		= 'Incoming DPS Distribution via Class';
	}
	
	var checkItems = 0;
	
	for (var item in data) {
		checkItems = checkItems + data[item][1];
	}
	
	if (checkItems > 0) {
		checkItems = "Yes";
	} else {
		checkItems = "No";
		data 		= [];
		
		var temp 	= [];
		temp[0] 	= "Unknown";
		temp[1] 	= 100;
		
		data[0] = temp;
	}
	
	chartDistPie = new Highcharts.Chart({
		chart: {
			renderTo: container,
			type: 'pie',
			backgroundColor: null,
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			margin: [0, 0, 0, 0],
			height: 255
		},
		title: {
			text: title
		},
		legend: {
			y: 15
		},
		credits: {
			enabled: false
		},
		tooltip: {
			formatter: function() {
				return this.point.name + '\'s ' + type + ': ' + this.percentage.toFixed(2) +'%';
			}
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: false,
					distance: -30,
					color: '#000000',
					connectorColor: '#000000',
					formatter: function() {
						return '<b>' + this.percentage.toFixed(2) +'%';
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
}

function createTimeline(dataTime, dataDPS, dataHPS, dataINC, numOfPlots) {
	chartLine = new Highcharts.Chart({
		chart: {
			renderTo: 'overallChart',
			type: 'area',
			zoomType: 'xy',
			width: 1020
		},
		title: {
			text: 'Performance Timeline'
		},
		legend: {
			    enabled: true
		},
		xAxis: {
			categories: dataTime,
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
				text: 'Per Second',
				align: 'middle'
			}
		},
		credits: {
			enabled: false
		},
		tooltip: {
			formatter: function() {
				return this.series.name + '<b>'+ Highcharts.numberFormat(this.y, 2, '.', ',') +'</b>';
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
		
	var dpsAVG = 0;
	var hpsAVG = 0;
	var incAVG = 0;
	var seriesArray = {};

	for (var avg = 0; avg < numOfPlots; avg++) {
		dpsAVG += dataDPS[avg];
		hpsAVG += dataHPS[avg];
		incAVG += dataINC[avg];
	}

	dpsAVG = dpsAVG / numOfPlots;
	hpsAVG = hpsAVG / numOfPlots;
	incAVG = incAVG / numOfPlots;

	seriesArray['DPS'] = dpsAVG;
	seriesArray['HPS'] = hpsAVG;
	seriesArray['INC'] = incAVG;

	var sortedKeys		= [];
	var sortedValues	= [];
	var tempHigh		= 0;
	var recent		= 0;

	for (key in seriesArray) {
		if (seriesArray[key] > tempHigh) {
			sortedKeys.unshift(key);
			sortedValues.unshift(seriesArray[key]);

			tempHigh 	= seriesArray[key];
			recent		= seriesArray[key];
		} else {
			if (sortedKeys[key] > recent) {
				sortedKeys.splice(1,0,key);
				sortedValues.splice(1,0,seriesArray[key]);

				recent = seriesArray[key];
			} else {
				sortedKeys.push(key);
				sortedValues.push(seriesArray[key]);
				recent = seriesArray[key];
			}					
		}
	}

	for (var count = 0; count < sortedKeys.length; count++) {
		var title;
		var data;
		var color;

		if (sortedKeys[count] == 'DPS') {
			title 	= 'Raid DPS';
			data	= dataDPS;
			color	='#488AC7';
		} else if (sortedKeys[count] == 'HPS') {
			title 	= 'Raid HPS';
			data	= dataHPS;
			color	= '#3E8A21';
		} else if (sortedKeys[count] == 'INC') {
			title 	= 'Raid INC DPS';
			data	= dataINC;
			color	= '#E66C2C';
		}

		chartLine.addSeries({
			name: title,
			color: color,
			data: data
		});			
	}
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