<?php
require("utils.php");
Page::open();
Page::setSession();
Page::displayNavbar();
?>

<div class="container">
<?php
// only if the user is registered
if ( isset($_SESSION['user_login']) ) {

	echo "<div class='row'>";

	/*** display an affiliation ranking based on the user queries ***/
	echo "<div class='col-xs-6'>";
	echo "<h1>Labs that suit you best ?</h1>";
	// sql query
	$dbh = Database::connect();
	$query = "SELECT belongs_to.affiliation AS affiliation FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN belongs_to ON ( user.login = '" .
		$_SESSION['user_login'] .
		"' AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = belongs_to.pmid ) GROUP BY belongs_to.affiliation ORDER BY COUNT(*) DESC LIMIT 3";
	$sth = $dbh->prepare($query);
	if (!$sth->execute()) {
		$dbh = null;
		exit(0);
	}
	// display the list
	echo "<p>Your first three choices for a fellowship :";
	echo "<ol>";
	while ($aff = $sth->fetch()) {
		echo "<li>$aff[0]</li>";
	}
	echo "</ol></p>";
	echo "</div>";

	/*** display an author ranking based on the user queries ***/
	echo "<div class='col-xs-6'>";
	echo "<h1>People you should work with</h1>";
	// sql query
	$dbh = Database::connect();
	$query = "SELECT author.firstname, author.lastname FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN has_written INNER JOIN author ON ( user.login = '" .
		$_SESSION['user_login'] .
		"' AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = has_written.pmid AND has_written.author = author.id) GROUP BY author.id ORDER BY COUNT(*) DESC LIMIT 5";
	$sth = $dbh->prepare($query);
	if (!$sth->execute()) {
		$dbh = null;
		exit(0);
	}
	// display the list
	echo "<p>If you wanted to organize a scientific retreat, you should contact :";
	echo "<ol>";
	while ($auth = $sth->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
		echo "<li>$auth[0] $auth[1]</li>";
	}
	echo "</ol></p>";
	echo "</div>";

	echo "</div>";

	/*** display a the number on articles through the time ***/
	echo "<div class='row'>";
	echo "<h1 align='center'>Are you up-to-date ?</h1>";
	// sql query
	$query = "SELECT EXTRACT(YEAR FROM article.pubdate) year, COUNT(EXTRACT(YEAR FROM article.pubdate)) AS hit FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN article ON ( user.login = '" .
		$_SESSION['user_login'] .
		"' AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = article.pmid AND EXTRACT(YEAR FROM article.pubdate) IS NOT NULL ) GROUP BY year ORDER BY year";
	$sth = $dbh->prepare($query);
	if (!$sth->execute()) {
		$dbh = null;
		exit(0);
	}
	// display the list
	echo "<div id=timeline>";
	$years = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
	// have to query again...
	if (!$sth->execute()) {
		$dbh = null;
	}
	$scores = $sth->fetchAll(PDO::FETCH_COLUMN, 1);
	echo "</div>";
	echo "</div>";

	/*** display a the top 10 of qualifier ***/
	echo "<div class='row'>";
	echo "<div class='col-xs-6'>";
	echo "<h1>Your favorite subjects</h1>";
	// sql query
	$query = "SELECT @rank := @rank + 1 AS rank, results.* FROM ( SELECT user.login, has_qualifier.value, COUNT(has_qualifier.value) AS occurence FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN has_keyword INNER JOIN has_qualifier ON ( user.login = '" .
		$_SESSION['user_login'] .
		"' AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = has_keyword.pmid AND has_keyword.value = has_qualifier.value ) GROUP BY has_qualifier.qualifier ORDER BY COUNT(*) DESC LIMIT 10) results CROSS JOIN (SELECT @rank := 0) init";
	$sth = $dbh->prepare($query);
	if (!$sth->execute()) {
		$dbh = null;
		exit(0);
	}
	// display the list
	echo "<p>Your field of expertise could be :";
	echo "<ol>";
	while ($qualifier = $sth->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
		echo "<li>$qualifier[2]</li>";
	}
	echo "</ol></p>";
	echo "</div>";

	/*** display a the top 10 of keyword ***/
	echo "<div class='col-xs-6'>";
	echo "<h1>Your favorite keywords</h1>";
	// sql query
	$query = "SELECT @rank := @rank + 1 AS rank, results.* FROM ( SELECT user.login, has_keyword.value, COUNT(has_keyword.value) AS occurence FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN has_keyword ON ( user.login = '" .
		$_SESSION['user_login'] .
		"' AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = has_keyword.pmid) GROUP BY has_keyword.value ORDER BY COUNT(*) DESC LIMIT 10) results CROSS JOIN (SELECT @rank := 0) init";
	$sth = $dbh->prepare($query);
	if (!$sth->execute()) {
		$dbh = null;
		exit(0);
	}
	// display the list
	echo "<p>You seem to be very insterested in :";
	echo "<ol>";
	while ($keyword = $sth->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
		echo "<li>$keyword[2]</li>";
	}
	echo "</ol></p>";
	echo "</div>";

	echo "</div>";

}

// redirect to the registration page if the user has no account
else {
	echo "You have no account. <a href='registration.php'>Register !</a>";
}
?>

</div>

<script type="text/javascript">
$(function () {
	$(document).ready(function () {
		$('#timeline').highcharts({
		chart: { type: 'spline' },
			title: {
			text: "Your queries over time",
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
});
});
</script>

<?php
Page::close();
?>

