<?php

class Page {

	// open a page
	public static function open() {
		readfile("html/head.html");
	}

	// close a page
	public static function close() {
		echo "</body>";
		echo "</html>";
	}

	// WARNING !
	// using "include" is very ugly, but it's the only way we found to
	// execute php code inside an imported html file
	public static function displayNavbar() {
		$navbar = include("html/navbar.html");
		$navbar = preg_replace('/1$/','',$navbar);
		echo $navbar;
	}

	// setSession() code from the prwb course
	public static function setSession() {
		session_start();
		if (isset($_SESSION['count']))
			$_SESSION['count']++;
		else
			$_SESSION['count'] = 0;
		if ( isset($GET_['todo']) && isset($_GET['todo'])=='reset' )
			$_SESSION['count'] = 0;
		
		$sess = $_SESSION['count'];
	}
}

/*****************************************************************************/
class User {

	// check if a login is available
	function loginIsAvailable ($login) {

		$request = "SELECT login FROM user WHERE login = '$login'";
		$dbh = Database::connect();
		$sth = $dbh->prepare($request);
		if (!$sth->execute()) exit(0);
		$result = $sth->fetchAll();
		if (count($result) == 0) {
			return "yes";
		} else { return "no"; }

	}

	// creates a new user
	function newUser() {

		if ( isset($_POST['regist_login']) && isset($_POST['regist_lastname'])
			&& isset($_POST['regist_firstname'])
			&& isset($_POST['regist_password'])
		)
		{
			$user_login = $_POST['regist_login'];
			$user_lastname = $_POST['regist_lastname'];
			$user_firstname = $_POST['regist_firstname'];
			$user_password = $_POST['regist_password'];
			
			if (User::loginIsAvailable($user_login) == "yes") {
				$request="INSERT INTO user (login, lastname, firstname, password)";
				$values = "('$user_login','$user_lastname','$user_firstname'," .
					"'$user_password')";
				Database::insert($request,$values);
				echo "Félicitations ! Vous êtes maintenant enregistrés.";
			}
			else {
				echo "Vous êtes arrivé trop tard : ce login est déjà utilisé.";
			}
		}
	}

	// connect an user to his account
	function connection() {

		if ( isset($_POST['login']) && isset($_POST['password']) ) {

			$login = $_POST['login'];
			$password = $_POST['password'];

			$request = "SELECT * FROM user WHERE login = '$login'";
			$dbh = Database::connect();
			$sth = $dbh->prepare($request);
			if (!$sth->execute()) exit(0);

			if ($sth->rowCount() > 0) {

				$result = $sth->fetch(PDO::FETCH_OBJ);

				if ($result->password == $password) {
					$_SESSION['user_login'] = $login;
					echo "You're now logged in. " .
					  	'<a href="index.php">Search !</a>';
				}
				else {
					echo "Your password doesn't match. ";
				}
			}
			else {
				echo "You have no account. " .
					'<a href="registration.php">Register !</a>';
			}
		}
	}
}


/*****************************************************************************/
class Database {

	// connexion to the database
	public static function connect() {
		$db = 'prwb';
		$host = '127.0.0.1';
		$user = 'pma';
		$password = 'pmapass';
		$dsn = "mysql:dbname=$db;host=$host";
		$dbh = null;

		try {
			$dbh = new PDO($dsn, $user, $password,
				array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e) {
			echo 'Connexion failed: ' . $e->getMessage();
			exit(0);
		}
		return $dbh;
	}
	
	// disconnexion to the database
	public static function disconnect() {
		try {
			$dbh = null;
		}
		catch (PDOException $e) {
			echo 'Disconnection failed: ' . $e->getMessage();
			exit(-1);
		}
		return $dbh;
	}

	// insert data into the database
	public static function insert($request,$values) {
		$dbh = Database::connect();
		$query = $request . " VALUES " . $values;
		$sth = $dbh->prepare($query);
		if (!$sth->execute()) {
			$dbh = null;
			exit(0);
		}
	}
}

/*****************************************************************************/
class Result {

	// diplay the query field in the navbar
	public static function displayQueryField() {
		echo "<div class='form-group'>\n";
		echo "<input type='text' class='form-control' id='query' name='query' " .
			//"value='11146294 20473728 23404817 24922075' " .
			"value='' " .
			"placeholder='Your query'/>\n";
		echo "</div>\n";
		echo "<input type='submit' class='btn btn-default' id='launchquery' " .
			"value='Search'/>\n";
	}

	// takes a json and insert it into the database
	public static function insertResults($results) {

		$articles = json_decode($results,true);

		foreach ($articles as $article) {

			/*** for the query table ***/
			$query_translation = $article['query'];
			// query
			$request = "INSERT IGNORE INTO query (value)";
			$values = "('$query_translation')";
			Database::insert($request,$values);

			/*** the query has been made by an user ***/
			if (isset($_SESSION['user_login'])) {
				$user = $_SESSION['user_login'];
				$request = "INSERT IGNORE INTO has_searched (user, query)";
				$values = "('$user','$query_translation')";
				Database::insert($request,$values);
			}

			/*** for the journal table ***/
			$journal_name = $article['journal']['name'];
			$issn = $article['journal']['issn'];
			$impact_factor = $article['journal']['impactfactor'];
			// query
			$request = "INSERT IGNORE INTO journal (name, issn, impactfactor)";
			$values = "('$journal_name','$issn','$impact_factor')";
			Database::insert($request,$values);

			/*** for the article table ***/
			$pmid = $article['pmid'];
			$title = $article['title'];
			$year = $article['date']['year'];
			$day = $article['date']['day'];
			$month = $article['date']['month'];
			$date = "$year-$month-$day";
			// query
			$request = "INSERT IGNORE INTO article (pmid, title, pubdate)";
			$values = "('$pmid','$title', STR_TO_DATE( '$date', '%Y-%m-%d' ))";
			Database::insert($request,$values);
			/*** the article has been published in a journal ***/
			$request = "INSERT IGNORE INTO has_published (pmid, journal)";
			$values = "('$pmid','$journal_name')";
			Database::insert($request,$values);
			/*** the article has been obtained through a query ***/
			$request = "INSERT IGNORE INTO results_in (query, pmid)";
			$values = "('$query_translation','$pmid')";
			Database::insert($request,$values);
			
			/*** for the keyword and qualifier tables ***/
			$keywords = $article['keywords'];
			foreach ($keywords as $keyword) {
				/** keyword **/
				$descriptor = $keyword['descriptor'];
				// query
				$request = "INSERT IGNORE INTO keyword (value)";
				$values = "('$descriptor')";
				Database::insert($request,$values);
				/*** the article has keyword(s) ***/
				$request = "INSERT IGNORE INTO has_keyword (pmid, value)";
				$values = "('$pmid','$descriptor')";
				Database::insert($request,$values);
				/** qualifier **/
				$qualifiers = $keyword['qualifiers'];
				foreach ($qualifiers as $qualifier) {
					$request = "INSERT IGNORE INTO qualifier (value)";
					$values = "('$qualifier')";
					Database::insert($request,$values);
					/*** the keyword has qualifier(s) ***/
					$request = "INSERT IGNORE INTO has_qualifier (value, qualifier)";
					$values = "('$descriptor','$qualifier')";
					Database::insert($request,$values);
				}
			}

			/*** for the affiliation table ***/
			$affiliations = $article['affiliations'];
			foreach ($affiliations as $affiliation) {
				$raw = $affiliation['raw'];
				$p_department = $affiliation['processed']['department'];
				$p_institute = $affiliation['processed']['institute'];
				$p_hospital = $affiliation['processed']['hospital'];
				$p_university = $affiliation['processed']['university'];
				$p_country = $affiliation['processed']['country'];
				$p_city = $affiliation['processed']['city'];
				$g_name = $affiliation['google']['name'];
				$g_city = $affiliation['google']['city'];
				$g_country = $affiliation['google']['country'];
				$g_lat = $affiliation['google']['lat'];
				$g_lng = $affiliation['google']['lng'];
				$g_address = $affiliation['google']['address'];
				// query
				$request = "INSERT IGNORE INTO affiliation (rawname, processed_department, processed_institute, processed_university, processed_hospital, processed_city, processed_country, google_name, google_city, google_country, google_address, google_lat, google_lng)";
				$values = "('$raw','$p_department','$p_institute','$p_university','$p_hospital','$p_city','$p_country','$g_name','$g_city','$g_country','$g_address','$g_lat','$g_lng')";
				Database::insert($request,$values);
				/*** the article belongs to (an) affiliation(s) ***/
				$request = "INSERT IGNORE INTO belongs_to (pmid, affiliation)";
				$values = "('$pmid','$raw')";
				Database::insert($request,$values);
			}

			/*** for the author table ***/
			$authors = $article['authors'];
			foreach ($authors as $author) {
				$lastname = $author['lastname'];
				$firstname = $author['firstname'];
				$initials = $author['initials'];
				$author_affiliation = $author['affiliation'];
				// query
				$request = "INSERT IGNORE INTO author (lastname, firstname, initials)";
				$values = "('$lastname','$firstname','$initials')";
				Database::insert($request,$values);
				// get the author's id
				$query = "SELECT id FROM author WHERE lastname = '$lastname' AND firstname = '$firstname' AND initials = '$initials'";
				$dbh = Database::connect();
				$sth = $dbh->prepare($query);
				if (!$sth->execute()) exit(0);
				$author_id = $sth->fetchColumn();
				$dbh = null;
				/*** the author is affiliated to an affiliation ***/
				if ($author_affiliation != "") {
					$request = "INSERT IGNORE INTO is_affiliated_to (author, affiliation)";
					$values = "('$author_id','$author_affiliation')";
					Database::insert($request,$values);
				}
				/*** the author(s) has (have) written this article ***/
				$request = "INSERT IGNORE INTO has_written (author, pmid)";
				$values = "('$author_id','$pmid')";
				Database::insert($request,$values);
			}

		// end of foreach article
		}

	// end of the insertResults() function
	}

// end of the Result class
}

?>

