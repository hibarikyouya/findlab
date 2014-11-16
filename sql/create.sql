-- creation de la base de donnees
CREATE DATABASE IF NOT EXISTS prwb;
USE prwb;

-- creation de la table des utilisateurs
CREATE TABLE IF NOT EXISTS user ( login CHAR(30) NOT NULL, firstname CHAR(30) NOT NULL, lastname CHAR(30) NOT NULL, password CHAR(30) NOT NULL, PRIMARY KEY (login) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table  des articles
CREATE TABLE IF NOT EXISTS article ( pmid INT(10) NOT NULL, title CHAR(200), pubdate DATE, PRIMARY KEY (pmid) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table des mots-cles
CREATE TABLE IF NOT EXISTS keyword ( value CHAR(100) NOT NULL, PRIMARY KEY (value) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table des qualificateurs
CREATE TABLE IF NOT EXISTS qualifier ( value CHAR(40) NOT NULL, PRIMARY KEY (value) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table des auteurs
CREATE TABLE IF NOT EXISTS author ( id INT(5) NOT NULL AUTO_INCREMENT, lastname CHAR(40) NOT NULL, firstname CHAR(40), initials CHAR(10), PRIMARY KEY (id), UNIQUE KEY id_author (lastname, firstname, initials) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table requetes
CREATE TABLE IF NOT EXISTS query ( value CHAR(200) NOT NULL, PRIMARY KEY (value) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table des journaux
CREATE TABLE IF NOT EXISTS journal ( name CHAR(30) NOT NULL, issn CHAR(11), impactfactor DECIMAL(5,2), PRIMARY KEY (name) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table des affiliations
CREATE TABLE IF NOT EXISTS affiliation ( rawname CHAR(250) NOT NULL, processed_department CHAR(100), processed_institute CHAR(100), processed_university CHAR(100), processed_hospital CHAR(100), processed_city CHAR(100), processed_country CHAR(100), google_name CHAR(100), google_city CHAR(100), google_country CHAR(100), google_address CHAR(200), google_lat DECIMAL(7,5), google_lng DECIMAL(8,5), PRIMARY KEY (rawname) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table relationnelle : un article a pour mot(s)-cle(s)
CREATE TABLE has_keyword ( pmid INT(10), value CHAR(100), FOREIGN KEY (pmid) REFERENCES article(pmid), FOREIGN KEY (value) REFERENCES keyword(value), PRIMARY KEY (pmid, value) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table relationnelle : un mot-cle a pour qualificateur(s)
CREATE TABLE has_qualifier ( value CHAR(100), qualifier CHAR(40), FOREIGN KEY (value) REFERENCES keyword(value), FOREIGN KEY (qualifier) REFERENCES qualifier(value), PRIMARY KEY (value, qualifier) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table relationnelle : un auteur a ecrit un (des) article(s)
CREATE TABLE has_written ( author INT(4), pmid INT(10), FOREIGN KEY (author) REFERENCES author(id), FOREIGN KEY (pmid) REFERENCES article(pmid), PRIMARY KEY (author, pmid) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table relationnelle : un journal a edite un( des) article(s)
CREATE TABLE has_published ( pmid INT(10), journal CHAR(30), FOREIGN KEY (journal) REFERENCES journal(name), FOREIGN KEY (pmid) REFERENCES article(pmid), PRIMARY KEY (pmid, journal) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table relationnelle : un auteur a une (des) affiliation(s)
CREATE TABLE is_affiliated_to ( author INT(4), affiliation CHAR(250), FOREIGN KEY (author) REFERENCES author(id), FOREIGN KEY (affiliation) REFERENCES affiliation(rawname), PRIMARY KEY (author, affiliation) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table relationnelle : un article provient de un (des) affiliation(s)
CREATE TABLE belongs_to ( pmid INT(10), affiliation CHAR(250), FOREIGN KEY (pmid) REFERENCES article(pmid), FOREIGN KEY (affiliation) REFERENCES affiliation(rawname), PRIMARY KEY (pmid, affiliation) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table relationnelle : une requete donne zero, un ou des article(s)
CREATE TABLE results_in ( query CHAR(200), pmid INT(10), FOREIGN KEY (query) REFERENCES query(value), FOREIGN KEY (pmid) REFERENCES article(pmid), PRIMARY KEY (query, pmid) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- creation de la table relationnelle : un utilisateur a effectue une (des) requete(s)
CREATE TABLE has_searched ( user CHAR(30), query CHAR(200), FOREIGN KEY (user) REFERENCES user(login), FOREIGN KEY (query) REFERENCES query(value), PRIMARY KEY (user, query) ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

