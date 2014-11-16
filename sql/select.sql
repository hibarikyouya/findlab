-- les sujets favoris de l'utilisateur
SELECT @rank := @rank + 1 AS rank, results.* FROM ( SELECT user.login, has_qualifier.value, COUNT(has_qualifier.value) AS occurence FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN has_keyword INNER JOIN has_qualifier ON ( user.login = "jpmarielle" AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = has_keyword.pmid AND has_keyword.value = has_qualifier.value ) GROUP BY has_qualifier.qualifier ORDER BY COUNT(*) DESC LIMIT 10) results CROSS JOIN (SELECT @rank := 0) init;

-- les mots-cles favoris de l'utilisateur
SELECT @rank := @rank + 1 AS rank, results.* FROM ( SELECT user.login, has_keyword.value, COUNT(has_keyword.value) AS occurence FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN has_keyword ON ( user.login = "jpmarielle" AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = has_keyword.pmid ) GROUP BY has_keyword.value ORDER BY COUNT(*) DESC LIMIT 10) results CROSS JOIN (SELECT @rank := 0) init;

-- l'institut favori de l'utilisateur
SELECT belongs_to.affiliation AS affiliation FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN belongs_to ON ( user.login = "jpmarielle" AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = belongs_to.pmid ) GROUP BY belongs_to.affiliation ORDER BY COUNT(*) DESC LIMIT 3;

-- les chercheurs favoris de l'utilisateur
SELECT author.firstname, author.lastname FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN has_written INNER JOIN author ON ( user.login = "jpmarielle" AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = has_written.pmid AND has_written.author = author.id) GROUP BY author.id ORDER BY COUNT(*) DESC LIMIT 5;

-- les recherches de l'utilisateur sont-elles a la mode ?
SELECT EXTRACT(YEAR FROM article.pubdate) year, COUNT(EXTRACT(YEAR FROM article.pubdate)) AS hit FROM user INNER JOIN has_searched INNER JOIN results_in INNER JOIN article ON ( user.login = "jpmarielle" AND user.login = has_searched.user AND has_searched.query = results_in.query AND results_in.pmid = article.pmid AND EXTRACT(YEAR FROM article.pubdate) IS NOT NULL ) GROUP BY year ORDER BY year;

-- classement des instituts
SELECT affiliation.rawname, AVG(journal.impactfactor) AS score FROM affiliation INNER JOIN belongs_to INNER JOIN article INNER JOIN has_published JOIN journal ON ( affiliation.rawname = belongs_to.affiliation AND belongs_to.pmid = article.pmid AND article.pmid = has_published.pmid AND has_published.journal = journal.name) GROUP BY affiliation.rawname ORDER BY score DESC;

-- classement des auteurs
SELECT author.firstname, author.lastname, is_affiliated_to.affiliation, AVG(journal.impactfactor) AS score FROM author INNER JOIN is_affiliated_to INNER JOIN has_written INNER JOIN article INNER JOIN has_published JOIN journal ON ( author.id = is_affiliated_to.author AND author.id = has_written.author AND has_written.pmid = article.pmid AND article.pmid = has_published.pmid AND has_published.journal = journal.name) GROUP BY author.firstname, author.lastname ORDER BY score DESC;

-- representativite des pays
SELECT processed_country, ( COUNT(processed_country) * 100 / (SELECT COUNT(*) FROM affiliation) ) AS frequency FROM affiliation WHERE affiliation.processed_country != '' GROUP BY affiliation.processed_country ORDER BY frequency DESC;

-- evolution des publications au cours du temps
SELECT EXTRACT(YEAR FROM article.pubdate) year, COUNT( EXTRACT(YEAR FROM article.pubdate) ) AS hit FROM article WHERE EXTRACT(YEAR FROM article.pubdate) IS NOT NULL GROUP BY year ORDER BY year;

-- quel est l'identifiant correspondant a ce nom ? (necessaire car la cle est l'identifiant)
SELECT id FROM author WHERE lastname = "Annaluru" AND firstname = "Narayana" AND initials = "N";

-- ce login est-il disponible ? (necessaire a l'inscription)
SELECT login FROM user WHERE login = "jpmarielle";

-- quelles sont les informations correspondant a ce login ? (necessaire a l'idenfification)
SELECT * FROM user WHERE login = "jpmarielle";

