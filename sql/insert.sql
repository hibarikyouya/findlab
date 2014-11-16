-- enregistrement d'un utilisateur
INSERT INTO user (login, lastname, firstname, password) VALUES ("jpmarielle","marielle","jean-pierre","coupdetorchon");

-- quelle a ete la recherche ?
INSERT IGNORE INTO query (value) VALUES ('synthetic[All Fields] AND ("chromosomes"[MeSH Terms] OR "chromosomes"[All Fields] OR "chromosome"[All Fields])');

-- quel utilisateur a effectue cette recherche ?
INSERT IGNORE INTO has_searched (user, query) VALUES ("jpmarielle","breast cancer");

-- a quel(s) articles a abouti la recherche ?
INSERT IGNORE INTO results_in (query, pmid) VALUES ('synthetic[All Fields] AND ("chromosomes"[MeSH Terms] OR "chromosomes"[All Fields] OR "chromosome"[All Fields])',"24674868");

-- quel(s) est (sont) cet (ces) articles ?
INSERT IGNORE INTO article (pmid, title, pubdate) VALUES ("24674868","Total synthesis of a functional designer eukaryotic chromosome.", "2014");

-- dans quel journal ?
INSERT IGNORE INTO has_published (pmid, journal) VALUES ("24674868","Science");

-- on ajoute le journal
INSERT IGNORE INTO journal (name, issn, impactfactor) VALUES ("Science","0036-8075","31.48");

-- quels sont ses mots-cles ?
INSERT IGNORE INTO has_keyword (pmid, value) VALUES ("24674868","Chromosomes, Fungal");

-- on ajoute les mots-cle
INSERT IGNORE INTO keyword (value) VALUES ("Chromosomes, Fungal");

-- on ajoute les qualificateurs
INSERT IGNORE INTO qualifier (value) VALUES ("genetics");

-- on ajoute les qualificateurs correspondant aux mots-cles
INSERT IGNORE INTO has_qualifier (value, qualifier) VALUES ("Chromosomes, Fungal","genetics");

-- qui a ecrit cet article ?
INSERT IGNORE INTO has_written (author, pmid) VALUES (id,"24674868");

-- on ajoute l'auteur
INSERT IGNORE INTO author (lastname, firstname, initials) VALUES ("Annaluru", "Narayana", "N");

-- par quel institut a-t-il ete publie ?
INSERT IGNORE INTO belongs_to (pmid, affiliation) VALUES ("24674868", "Department of Environmental Health Sciences, Johns Hopkins University (JHU) School of Public Health, Baltimore, MD 21205, USA");

-- on ajoute l'institut
INSERT IGNORE INTO affiliation (rawname, processed_department, processed_institute, processed_university, processed_hospital, processed_city, processed_country, google_name, google_city, google_country, google_address, google_lat, google_lng) VALUES ("Department of Biochemistry, Hong Kong University of Science and Technology, Clear Water Bay, Hong Kong","department of biochemistry","","hong kong university of science and technology","","","hong kong","Hong Kong University of Science and Technology","Hong Kong","Hong Kong University of Science and Technology, Clear Water Bay, Hong Kong","22.33640","114.26547");

-- a quel institut est affilie l'auteur ?
INSERT IGNORE INTO is_affiliated_to (author, affiliation) VALUES (id,"Department of Environmental Health Sciences, Johns Hopkins University (JHU) School of Public Health, Baltimore, MD 21205, USA");

