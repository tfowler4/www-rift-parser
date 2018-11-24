function pageLoad(type) {
	$(function () {
		var tableFilter 		= document.getElementById('filterTable');
		var numCols			= 10;
		var numOfRows 			= tableFilter.rows.length;
		var abilityArray 		= [];
		var numOfAbil			= 0;
		var style			= type;
		var perSecond			= "";
		var columnUsed			= 0;

		if (style == "" || style == "DPS" || style == undefined) {
			style 		= "DPS";
			perSecond 	= "Damage Per Second (DPS)";
			columnUsed	= 4;
		} else if (style == "HPS") {
			style = "HPS";
			perSecond = "Heal Per Second (HPS)";
			columnUsed	= 4;
		} else if (style == "INCDPS") {
			style = "iDPS";
			perSecond = "Incoming Damage Per Second (iDPS)";
			columnUsed	= 2;
		} else if (style == "INCHPS") {
			style = "iHPS";
			perSecond = "Incoming Heal Per Second (iHPS)";
			columnUsed	= 2;
		}

		for (var row = 0; row < numOfRows; row++) {
			for (var cols = 0; cols < numCols; cols++) {
				if (tableFilter.rows[row].cells[cols] != null) {
					abilityArray.push(tableFilter.rows[row].cells[cols].textContent);
				}
			}
		}

		//alert('column: ' + columnUsed);

		for (var curAbil = 0; curAbil < abilityArray.length; curAbil++) {
			var chartArea			= 'chartArea' + abilityArray[curAbil];
			var tableID				= 'table' + abilityArray[curAbil];
			var tableAbility 		= document.getElementById(tableID);
			var tableTitle 			= document.getElementById(abilityArray[curAbil]);

			if (tableAbility != null) {
				var title			= tableTitle.rows[0].cells[0].textContent;
				var numOfRows 		= tableAbility.rows.length;
				var value 			= 0;
				var playerArray 	= [];
				var dpsArray 		= [];

				for (var row = 0; row < numOfRows; row++) {
					playerArray[row] = String(tableAbility.rows[row].cells[0].textContent);
					dpsArray[row] = parseFloat(tableAbility.rows[row].cells[columnUsed].textContent);
				}

				playerArray.splice(0,1);
				dpsArray.splice(0,1);



				chartBar = new Highcharts.Chart({
					chart: {
						renderTo: chartArea,
						type: 'bar'
					},
					title: {
						text: title
					},
					xAxis: {
						categories: playerArray
					},
					yAxis: {
						title: {
							text: perSecond,
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
					credits: {
						enabled: false
					},
					tooltip: {
						formatter: function() {
							return this.x + '\'s ' + style + ': ' + this.y;
						}
					},
					plotOptions: {
						bar: {
							dataLabels: {
								enabled: true
							}
						},
						series: {
							colorByPoint: true
						}
					},
					series: [{
						id: 'DPS',
						name: style,
						data: dpsArray
					}]
				});
			}
		}

	});
}

function updatePane(id) {
	var attribute_array 	= [];
	var type 				= "";
	var encid 				= "";
	var playersName_string	= "";
	var mobName_string		= "";
	var indexOfChar			= 0;

	attribute_array 	= id.split(",");
	indexOfChar			= attribute_array[0].indexOf('_');
	type				= attribute_array[0].substr(0,indexOfChar);
	encid				= attribute_array[0].substr(indexOfChar + 1);
	mobName_string		= attribute_array[1];
	playerName_string	= attribute_array[2];

	var xmlHttp = getXMLHttp();

	xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4) {
			HandleTypeResponse(xmlHttp.responseText, type);
	    	}
	}

	updateAbilityTable(encid, type, playerName_string, mobName_string);

	var url = "paneData.php?&enc=" + encid + "&players=" + playerName_string + "&mob=" + mobName_string + "&type=" + type;
	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
}

function HandleTypeResponse(response, type) {
	document.getElementById('paneData').innerHTML = response;
	pageLoad(type);
}

function updateAbilityTable(encid, type, playerName_string, mobName_string) {
	var xmlHttp = getXMLHttp();

	xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4) {
			HandleAbilityResponse(xmlHttp.responseText);
	    	}
	}

	var url = "paneTable.php?&enc=" + encid + "&players=" + playerName_string + "&mob=" + mobName_string + "&type=" + type;
	xmlHttp.open("GET", url, true);
  	xmlHttp.send(null);
}

function HandleAbilityResponse(response) {
	document.getElementById('paneFilter').innerHTML = response;
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