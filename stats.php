<?php
require("utils.php");
Page::open();
Page::setSession();
Page::displayNavbar();
?>

<div class="container">
<?php

/***************************** institutes ************************************/
echo "<div class='row'>";
echo "<h1>Institute Ranking</h1>";

// query
$dbh = Database::connect();
$query = "SELECT affiliation.rawname, AVG(journal.impactfactor) AS score FROM affiliation INNER JOIN belongs_to INNER JOIN article INNER JOIN has_published JOIN journal ON ( affiliation.rawname = belongs_to.affiliation AND belongs_to.pmid = article.pmid AND article.pmid = has_published.pmid AND has_published.journal = journal.name) GROUP BY affiliation.rawname ORDER BY score DESC";
$sth = $dbh->prepare($query);
if (!$sth->execute()) {
	$dbh = null;
	exit(0);
}

// table
echo "<div id='institutes_ranking'>";
$head = "<table id='institutes' class='table table-striped " .
	"table-bordered' cellspacing='0' width='100%'>\n" .
	"<thead>\n" .
	"<tr>\n" .
	"<th>#</th> <th>Journal</th> <th>Score</th>\n" .
	"</tr>\n" .
	"</thead>\n" .
	"<tfoot>\n" .
	"<tr>\n" .
	"<th>#</th> <th>Journal</th> <th>Score</th>\n" .
	"</tr>\n" .
	"</tfoot>\n" .
	"</tbody>\n";
echo $head;

$i = 1;
while ($aff = $sth->fetch()) {
	echo "<tr>";
	echo "<td>$i</td>";
	echo "<td>$aff[0]</td>";
	echo "<td>$aff[1]</td>";
	echo "</tr>";
	$i++;
}
echo "</tbody></table>";

echo "</div>";
echo "</div>";

/***************************** authors ***************************************/
echo "<div class='row'>";
echo "<h1>Author Ranking</h1>";

// query
$dbh = Database::connect();
$query = "SELECT author.firstname, author.lastname, is_affiliated_to.affiliation, AVG(journal.impactfactor) AS score FROM author INNER JOIN is_affiliated_to INNER JOIN has_written INNER JOIN article INNER JOIN has_published JOIN journal ON ( author.id = is_affiliated_to.author AND author.id = has_written.author AND has_written.pmid = article.pmid AND article.pmid = has_published.pmid AND has_published.journal = journal.name) GROUP BY author.firstname, author.lastname ORDER BY score DESC";
$sth = $dbh->prepare($query);
if (!$sth->execute()) {
	$dbh = null;
	exit(0);
}

// table
echo "<div id='authors_ranking'>";
$head = "<table id='authors' class='table table-striped " .
	"table-bordered' cellspacing='0' width='100%'>\n" .
	"<thead>\n" .
	"<tr>\n" .
	"<th>#</th> <th>Author</th> <th>Affiliation</th> <th>Score</th>\n" .
	"</tr>\n" .
	"</thead>\n" .
	"<tfoot>\n" .
	"<tr>\n" .
	"<th>#</th> <th>Author</th> <th>Affiliation</th> <th>Score</th>\n" .
	"</tr>\n" .
	"</tfoot>\n" .
	"</tbody>\n";
echo $head;

$i = 1;
while ($auth = $sth->fetch()) {
	echo "<tr>";
	echo "<td>$i</td>";
	echo "<td>$auth[0] $auth[1]</td>";
	echo "<td>$auth[2]</td>";
	echo "<td>$auth[3]</td>";
	echo "</tr>";
	$i++;
}
echo "</tbody></table>";
echo "</div>";
echo "</div>";

/*************************** coutries ****************************************/
echo "<div class='row'>";
echo "<h1>France deserves better</h1>";
// query
$dbh = Database::connect();
$query = "SELECT processed_country, ( COUNT(processed_country) * 100 / (SELECT COUNT(*) FROM affiliation) ) AS frequency FROM affiliation WHERE affiliation.processed_country != '' GROUP BY affiliation.processed_country ORDER BY frequency DESC";
$sth = $dbh->prepare($query);
if (!$sth->execute()) {
	$dbh = null;
	exit(0);
}
$countries = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
if (!$sth->execute()) {
	$dbh = null;
	exit(0);
}
$freq = $sth->fetchAll(PDO::FETCH_COLUMN, 1);
// piechart
$pie = [];
for ($c=0; $c<count($freq); $c++) {
	array_push($pie, "['$countries[$c]', $freq[$c]]");
}
echo "<div id='countries_ranking'></div>";
echo "</div>";

/***************************** time ******************************************/
echo "<div class='row'>";
echo "<h1>Effects of financial crisis on research ?</h1>";
// query
$dbh = Database::connect();
$query = "SELECT EXTRACT(YEAR FROM article.pubdate) year, COUNT( EXTRACT(YEAR FROM article.pubdate) ) AS hit FROM article WHERE EXTRACT(YEAR FROM article.pubdate) IS NOT NULL GROUP BY year ORDER BY year";
$sth = $dbh->prepare($query);
if (!$sth->execute()) {
	$dbh = null;
	exit(0);
}
$years = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
// have to query again...
if (!$sth->execute()) {
	$dbh = null;
}
$scores = $sth->fetchAll(PDO::FETCH_COLUMN, 1);
echo "<div id='years_ranking'></div>";
echo "</div>";
?>

<script type="text/javascript">
//*
$(function () {
	$(document).ready(function () {

	// tables
	$("#institutes").dataTable();
	$("#authors").dataTable();

	// timeline
	$('#years_ranking').highcharts({
	chart: { type: 'spline' },
		title: {
		text: "Articles over time",
			x: -20
	},
	subtitle: {
	text: '',
		x: -20
	},
xAxis: { categories: [<?php echo "'" . join($years, "','") . "'"; ?>] },
yAxis: {
title: { text: "Total number of articles" },
	plotLines: [{
	value: 0,
		width: 1,
		color: '#808080'
	}]
	},
	legend: { enabled: false },
series: [{ data: [<?php echo join($scores, ","); ?>] }]
	});

	// piechart
	$('#countries_ranking').highcharts({
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
	text: '',
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
			name: 'Pourcentage des articles',
			data: [<?php echo join($pie,","); ?>]
	}]
	});
});
});
//*/
</script>

<?php
Page::close();
?>

