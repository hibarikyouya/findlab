$(document).ready(function () {

	// to display fancy tables
	$("#results_table").dataTable();

	// map init
	$("#map").gmap3({
		map:{
			options:{
				center:[0,0], zoom:2, minzoom:2
			}
		}
	});

	// the user query field
	$("#launchquery").on('click', function(evnt) {
		evnt.preventDefault();
		getAffiliations($("#query").val());
	});

});

/*****************************************************************************/
/*
 * the main function
 * 
 *
 *
 */
function getAffiliations(userquery) {

	// variables
	var pmids = [];
	var requests = [];
	var articles = [];
	var gmaprequests = [];
	var gmapresults = [];
	var querytranslation = "";

	// urls
	var entrez = "http://www.ncbi.nlm.nih.gov/entrez/eutils/";
	var entrezajax = "http://entrezajax.appspot.com/";
	var googlemap = "http://maps.googleapis.com/maps/api/geocode/json?";

	// parameters for entrez
	var esearchparams = {
		"db"				:"pubmed",
		// well... maybe not ideal, but nothing else could be done tmk
		"retmax"			:"1000000",
		"usehistory"	:"y",
		"term"			:userquery
	};

	// first query to get the number of articles, pmids list
	// and query translation
	var esearch = entrez+"esearch.fcgi?"+jQuery.param(esearchparams);
	$.get(esearch, function(xml) {
		$count = $(xml).find("Count:first").text();
		$(xml).find("Id").each(function(){ pmids.push($(this).text()); });
		$(xml).find("QueryTranslation").each(function(){
			querytranslation = $(this).text();
		});

		// it's required to divide in round(n/150)+1 small parts
		while(pmids.length > 0) {
			var query = pmids.splice(0,150).join(" ");
			// parameters for entrezajax
			var efetchparams = {
				// "apikey":"2a4a19e8ae2b375daa2eb8ab44a61e9c", // previous key
				"apikey"	:"f76901c525cdad0a2eaf727f4b9763c1",
				"start"	:"0",
				// limited to 250, but do not go further than 177 in test... :/
				"max"		:"150",
				// also necessary, but i dunno why...
				"retmax"	:"150",
				"db"		:"pubmed",
				"term"	:query
				};

			// query with pmids lists
			var efetch = entrezajax+"esearch+efetch?callback=?";
			requests.push( $.getJSON(efetch, efetchparams,
						function(data) {
							$("#rawresults").prepend(
									'<p>'+JSON.stringify(data,undefined,4)+'</p>'
									);
							extractArticleData(data,articles,querytranslation);
						})
					);
		}

		// when all articles informations have been retrieved
		$.when.apply($, requests).then(function() {

			// affiliations list from articles
			affiliations = getAffiliationsList(articles);

			// googlemap queries and results processing
			for(var i=0; i<affiliations.length; i++) {

				// removes commas and dots
				var query = affiliations[i].replace(/,/g,"").replace(/\./g,"");

				// creates requests list
				var params = { address : query };
				gmaprequests.push( $.getJSON(googlemap, params,
							// googlemap results processing
							function(data) {
								for (var j=0; j<data.results.length; j++) {
									// redefines json components
									var res = data.results[j];
									var address = res.address_components;
									var names =
										getArrayOfPropertyValues(gmapresults,"name");

									// select only hospitals and universities
									if (res.types.indexOf('hospital') != -1 ||
											res.types.indexOf('university') != -1) {
										var longname = address[0].long_name;
										if (names.indexOf(longname.toLowerCase()) == -1){
											var entry = new Object();
											entry.name = longname;
											for (var k=0; k<address.length; k++) {
												if(address[k].types.indexOf('locality')
														!= -1)
												{
													entry.city = address[k].long_name;
												}
												if(address[k].types.indexOf('country')
														!= -1)
												{
													entry.country = address[k].long_name;
												}
											}

											// get coordinates and address
											entry.lat = res.geometry.location.lat;
											entry.lng = res.geometry.location.lng;
											entry.address = res.formatted_address;

											// and finally
											gmapresults.push(entry);
										}
									}
								}
							})
						);
			}

			// when all googlemap queries have been done
			$.when.apply($, gmaprequests).then(function() {

				// adds gmapresults to articles
				addGmapResults(articles,gmapresults);

				// display results table
				displayResults(articles);

				// post results on the server
				$.post('/index.php',
						{articles:JSON.stringify(articles)}
						);

				// display on the map
				for (var i=0; i<gmapresults.length; i++) {
					$("#rawresults").prepend(
							'<p>'+JSON.stringify(articles,undefined,4)+'</p>'
							);
					$("#map").gmap3({
						marker:{
							values:[
							{
								latLng:[gmapresults[i].lat,gmapresults[i].lng],
								data: gmapresults[i].name
							}],
							options:{},
							// we took the events code from this url :
							// http://gmap3.net/en/catalog/18-data-types/event-65
							events:{
								mouseover: function(marker, event, context) {
									var map = $(this).gmap3("get"),
									infowindow = $(this).gmap3({
										get:{name:"infowindow"}});
									if (infowindow){
										infowindow.open(map, marker);
										infowindow.setContent(context.data);
									} else {
										$(this).gmap3({
											infowindow:{
												anchor:marker,
												options:{content: context.data}
											}
										});
									}
								},
								mouseout:function () {
									var infowindow = $(this).gmap3({
										get:{name:"infowindow"}});
									if (infowindow){
										infowindow.close();
									}
								},
							}
						}
					});
				}

				// timeline
				var time_stats = timeline(articles);
				$('#timeline').highcharts({
					chart: { type: 'spline' },
					title: {
						text: "The query's timeline",
						x: -20
					},
					subtitle: {
						text: '"'+userquery+'"',
						x: -20
					},
					xAxis: { categories: time_stats.years },
					yAxis: {
						title: { text: "% total number of articles" },
						plotLines: [{
							value: 0,
							width: 1,
							color: '#808080'
						}]
					},
					legend: { enabled: false },
					series: [{
						data: time_stats.scores
					}]
				});

				// piechart
				var countries_stats = piechart(articles);
				$('#piechart').highcharts({
					chart: {
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false
					},
					title: {
						text: "Frequencies",
						x: -20
					},
					subtitle: {
						text: '"'+userquery+'"',
						x: -20
					},
					tooltip: {
						pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
					},
					plotOptions: {
						pie: {
							allowPointSelect: true,
							cursor: 'pointer',
							dataLabels: {
								enabled: true,
								format:'<b>{point.name}</b>: {point.percentage:.1f} %',
								style: {
									color: (Highcharts.theme
											  && Highcharts.theme.contrastTextColor)
										|| 'black'
								}
							}
						}
					},
					series: [{
						type: 'pie',
						name: '% articles',
						data: countries_stats
					}]
				});







			});
		});
	});
}

/*****************************************************************************/
/*
 * this function parses the json resulting from entrezajax
 *
 * for each article it extracts informations : date, pmid, title, authors and
 * affiliations
 * 
 * takes an json and returns an array of article objects
 *
 */
function extractArticleData(json,articles,query) {
	for(var i=0; i<json.result.length; i++) {

		// variables
		var article = new Object();
		article.query = query;
		var authors = [];
		var affiliations = [];
		var keywords = [];

		// redefines current entry
		var entry = json.result[i].MedlineCitation;

		// date
		var history = json.result[i].PubmedData.History;
		var date = {
			day:history[0].Day,
			month:history[0].Month,
			year:history[0].Year
		};
		article.date = date;

		// pmid and title
		article.pmid = entry.PMID;
		article.title = clean(entry.Article.ArticleTitle);

		// journal and impact factor
		var journal = {
			name:entry.Article.Journal.Title,
			issn:entry.Article.Journal.ISSN
		};
		if(entry.Article.Journal.ISSN) {
			for (var n=0; n<issn.length; n++) {
				if (journal.issn == issn[n]) {
					journal.impactfactor = impactfactors[n];
					break;
				} else { journal.impactfactor = 0; }
			}
		}
		else {
			journal.issn = "XXXX-XXXX";
			journal.impactfactor = 0;
		}
		article.journal = journal;

		// keywords
		var meshterms = entry.MeshHeadingList;
		if (meshterms) {
			for (var j=0; j<meshterms.length; j++) {
				var keyword = new Object();
				keyword.descriptor = meshterms[j].DescriptorName;
				keyword.qualifiers = [];
				if (meshterms[j].QualifierName.length > 0) {
					for (var k=0; k<meshterms[j].QualifierName.length; k++) {
						keyword.qualifiers.push(
								meshterms[j].QualifierName[k]
								);
					}
				}
				keywords.push(keyword);
			}
		}
		article.keywords = keywords;

		// authors and affiliations
		var authorsList = entry.Article.AuthorList;
		for(var l=0; l<authorsList.length; l++) {
			// for authors
			var person = authorsList[l];
			var author = {
				firstname:person.ForeName,
				lastname:person.LastName,
				initials:person.Initials
			};
			authors.push(author);

			// for affiliations
			var aff = person.Affiliation;
			if (aff) {
				// remove email addresses and useless informations after dots
				aff = cleanAffiliation(aff);
				author.affiliation = aff;
				// keep raw affiliation and processed affiliation
				affiliation = new Object();
				affiliation.raw = aff;
				affiliation.processed = extractAffiliation(aff);
				// doesn't work...
				if (!containsObject(affiliations,affiliation)) {
					affiliations.push(affiliation);
				}
				// ...so we take everything and apply jQuery.unique below...
				affiliations.push(affiliation);

			}
		}
		// ...but jQuery.unique doesn't work neither :/
		article.affiliations = jQuery.unique(affiliations);
		article.authors = authors;
		// end of authors and affiliations

		// and finally...
		articles.push(article);
	}
}

/*****************************************************************************/
/*
 * this function parses the affiliation field of pubmed
 *
 * the affiliation field is splitted in an array, and the parsing is based on
 * keywords lists defined in :
 *		js/affiliations.js
 *		js/cities.js
 *		js/countries.js
 *
 * it returns an object name affiliation that will be the value of the property
 * "processed" an article's affiliation
 *
 */
function extractAffiliation(str) {

	str = removeAccents(str);
	var affiliation = new Object();
	var array = str.split(",");

	// department
	if (searchFor(array,departments) != undefined) {
		affiliation.department = searchFor(array,departments);
	} else { affiliation.department = ""; }

	// institute
	if (searchFor(array,institutes) != undefined) {
		affiliation.institute = searchFor(array,institutes);
	} else { affiliation.institute = ""; }

	// universitie
	if (searchFor(array,universities) != undefined) {
		affiliation.university = searchFor(array,universities);
	} else { affiliation.university = ""; }

	// hospital
	if (searchFor(array,hospitals) != undefined) {
		affiliation.hospital = searchFor(array,hospitals);
	} else { affiliation.hospital = ""; }

	// country
	if (searchFor(array,countries) != undefined) {
		affiliation.country = searchFor(array,countries,true);
		// if usa
		affiliation.country = affiliation.country.replace("united states","usa");
	} else { affiliation.country = ""; }

	// cites
	// but more than 95000 cities in the world, so too long to iterate :/
	// moreover, when a city is called "as" or "edi" it matches a lot of
	// things... :-(
	/*
	if (searchFor(array,cities) != undefined) {
		affiliation.city = searchFor(array,cities,true);
	} else { affiliation.city = ""; }
	//*/
	affiliation.city = "";

	return affiliation;
}

/*****************************************************************************/
/*
 * applies the extractField() function on each element of an array
 *
 */
function searchFor(array,keywords,cityOrCountry) {

	for (var i=0; i<array.length; i++) {

		if (extractField(array[i],keywords,cityOrCountry) != undefined) {
			return extractField(array[i],keywords,cityOrCountry);
			break;
		}
	}
}

/*****************************************************************************/
/*
 * tries to extract affiliation informations from a string
 *
 * it is based on keywords (affiliations.js, cities.js and countries.js), and
 * returns the string if the keyword is matching, or a the keyword if
 * the cityOrCountry variable is true
 *
 */
function extractField(str,keywords,cityOrCountry) {

	for (var i=0; i<keywords.length; i++) {

		var regex = new RegExp(keywords[i],"gi");

		if (regex.test(str)) {

			if (cityOrCountry) {
				return keywords[i];
				// stops if the word is matched
				break;
			}

			else {
				return str.toLowerCase().replace(/(^ )|( $)/,"");
				// stops if the word is matched
				break;
			}
		}
	}
}

/*****************************************************************************/
/*
 * this function takes an array of object and return an array that contains
 * the values of the property named "name"
 *
 */
function getArrayOfPropertyValues(array, name) {

	var values = [];
	for (var i=0; i<array.length; i++) {
		values.push(array[i].name.toLowerCase());
	}
	return values;
}

/*****************************************************************************/
/*
 * removes all accents from a string
 *
 */
function removeAccents(str) {

	// we wanted to use the encoding approach first, like :
	// str.replace(/[^\x00-\x7F]/g, "")
	// but this approach is more precise
	str = str.replace(/[ÀÁÂÃÄÅÆ]/g,"A");
	str = str.replace(/Ç/g,"C");
	str = str.replace(/[ÈÉÊË]/g,"E");
	str = str.replace(/[ÌÍÎÏ]/g,"I");
	str = str.replace(/Ñ/g,"N");
	str = str.replace(/[ÒÓÔÕÖØ]/g,"O");
	str = str.replace(/Ý/g,"Y");
	str = str.replace(/[ÙÚÛÜ]/g,"U");
	str = str.replace(/[àáâãäåæ]/g,"a");
	str = str.replace(/[èéêë]/g,"e");
	str = str.replace(/[ìíîï]/g,"i");
	str = str.replace(/ñ/g,"n");
	str = str.replace(/[òóôõöø]/g,"o");
	str = str.replace(/[ùúûü]/g,"u");
	str = str.replace(/[ýÿ]/g,"u");
	return str;
}


/*****************************************************************************/
/*
 * checks if an array contains an object... but doesn't work :/
 *
 */
function containsObject(array,object) {
	for (var i=0; i<array.length; i++) {
		if (array[i] === object) {
			return true;
		}
	}
	return false;
}

/*****************************************************************************/
/*
 * this functions takes an array of article objects and returns a new array of
 * strings containing processed affiliations
 *
 */
function getAffiliationsList(articles) {
	var array = [];
	for (var i=0; i<articles.length; i++) {
		var aff = articles[i].affiliations;
		for(var j=0; j<aff.length; j++) {

			// for the raw format
			if (array.indexOf(aff[j].raw) == -1) { array.push(aff[j].raw); }

			// for the processed format
			var infos = aff[j].processed;
			var str = "";
			for(var property in infos) {
				if (infos[property] != "") {
					str += " "+infos[property];
				}
			}
			str = str.replace(/^ /,"");
			if (array.indexOf(str) == -1) { array.push(str); }
		}
	}
	return array;
}

/*****************************************************************************/
/*
 * this function takes the result array processed from entrezajax and add the
 * google map results to it
 *
 * the articles array is directly modified
 *
 * WARNING : it would have been important to replace this function with a
 * better one... but no time
 *
 */
function addGmapResults(articles,results) {

	for (var i=0; i<articles.length; i++) {
		var aff = articles[i].affiliations;

		for (var j=0; j<aff.length; j++) {
			for (var k=0; k<results.length; k++) {

				var regex = new RegExp(removeAccents(results[k].name),"gi");

				// we don't know to which query the goole map result comes from,
				// so we try to match the long_name field with the raw name of the
				// affiliation
				// this is a very big problem, and we don't know how to fix it
				// :/ :/
				if(regex.test(removeAccents(aff[j].raw))) {
					aff[j].google = results[k];
				}

				// add an empty object there is no match
				else { aff[j].google = {}; }
			}
		}
	}
}

/*****************************************************************************/
/*
 * this function is used to clean the affiliation field
 * it takes a string and removes all characters that can interfere with the
 * INSERT query
 *
 * returns an string
 *
 */
function cleanAffiliation(str) {
	str = str.replace(/\S+@\S+\.\S+/,"");
	str = str.replace(/\..*$/,"");
	str = str.replace(/'/g,"");
	str = str.replace(/"/g,"");
	
	return str;
}

/*****************************************************************************/
/*
 * the sames as the cleanAffiliation() function but more general
 *
 */
function clean(str) {
	str = str.replace(/'/g,"");
	str = str.replace(/"/g,"");
	
	return str;
}


/*****************************************************************************/
/*
 * this function creates a timeline data serie for the articles list resulting
 * from the user query
 *
 * it is coupled to highcharts, so the function returns an object of two
 * arrays
 *
 */
function timeline(articles) {

	// results array AND years array because we will iterate one to the other,
	// and there are only pointers in javascript...
	var results = [];
	var years = [];
	var scores = [];

	// we takes the publication year of all articles
	for (var i=0; i<articles.length; i++) {
		results.push(articles[i].date.year);
		years.push(articles[i].date.year);
	}

	// unique and inc order
	jQuery.unique(years);
	years.reverse();

	// counts the occurence for each year
	for (var i=0; i<years.length; i++) {
		var num = countOccur(results,years[i]);
		var score = num / results.length * 100;
		scores.push(score);
	}

	var obj = {
		years:years,
		scores:scores
	};

	return obj;
}


/*****************************************************************************/
/*
 * this function takes an array and returns the number of occurences in this
 * array for a value
 *
 */
function countOccur(array,value) {
	var num = 0
	for (var i=0; i<array.length; i++) {
		if (array[i] == value) {
			num++;
		}
	}
	return num;
}

/*****************************************************************************/
/*
 * this function creates a piechart data serie for the articles list
 * resulting from the user query
 *
 * it is coupled to highcharts, so the function returns an array of tuples
 *
 */
function piechart(articles) {

	// results array AND affiliation array
	// and there are only pointers in javascript...
	var results = [];
	var affiliations = [];
	var scores = [];
	var data_series = [];

	// a country array with unique items
	for (var i=0; i<articles.length; i++) {
		var aff = articles[i].affiliations;
		for (var j=0; j<aff.length; j++) {
			if (aff[j].processed.country) {
				affiliations.push(aff[j].processed.country);
				results.push(aff[j].processed.country);
			}
		}
	}
	jQuery.unique(affiliations);

	// calculate frequency for each country
	for (var i=0; i<affiliations.length; i++) {
		var num = countOccur(results,affiliations[i]);
		var score = Math.round(num / results.length * 100);
		scores.push(score);
	}

	// creates an array of tuples
	for (var i=0; i<affiliations.length; i++) {
		var array = [];
		array.push(affiliations[i]);
		array.push(scores[i]);
		data_series.push(array);
	}

	return data_series;
}

/*****************************************************************************/
/*
 *  this function creates a DataTable for the query results
 *
 */
function displayResults(articles) {

	// header of the table
	var res_table = "<table id='results_table' class='table table-striped " +
		"table-bordered' cellspacing='0' width='100%'>\n" +
	  	"<thead>\n" +
	  	"<tr>\n" +
	  	"<th>Title</th> <th>Journal</th> <th>Year</th> <th>Institute</th>\n" +
	  	"</tr>\n" +
	  	"</thead>\n" +
	  	"<tfoot>\n" +
	  	"<tr>\n" +
	  	"<th>Title</th> <th>Journal</th> <th>Year</th> <th>Institute</th>\n" +
	  	"</tr>\n" +
	  	"</tfoot>\n" +
	  	"</tbody>\n";


	// diplay all rows
	for (var i=0; i<articles.length; i++) {
		var aff = articles[i].affiliations;

		for (var j=0; j<aff.length; j++) {

			var row = "<tr>" +
				"<td>" +
				"<a href='http://www.ncbi.nlm.nih.gov/pubmed/" +
				articles[i].pmid +
				"' >" + articles[i].title + "</a>" +
				"</td>" +
				"<td>" + articles[i].journal.name + "</td>" +
				"<td>" + articles[i].date.year + "</td>" +
				"<td>" + aff[j].raw + "</td>" +
				"</tr>\n";

			res_table += row;

			}
		}

	// closes the table
	res_table += "</tbody>\n</table>\n";

	// push header in the page
	$("#results").html(res_table);
	$("#results_table").dataTable( { 'iDisplayLength':5 } );

}

