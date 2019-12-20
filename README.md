# Projet Collectivités
Projet de Master - Analyse de la présence des collectivités territoriales sur les réseaux sociaux

## Script "startScrapTwitter.php"

Ce script permet de faire appel au script python scrapTwitter.py pour pouvoir scrapper les informations relatives aux posts et à une page Twitter et les sctockers dans des fichiers CSV.

### Prérequis
- **Python** >= 3.6
- [Twint](https://github.com/twintproject/twint)

Installer twint en tant que sudo : 
```
sudo -i
pip3 install twint
```

Donner les droits à www-data pour exécuter le script python en sudo

    sudo visudo
Ajouter la ligne suivante :
` www-data ALL=(ALL) NOPASSWD:/usr/bin/python3` 


#### Connexion à la base de données

La connexion à la base se fait à partir de la ligne de code suivante :

*$bdd = new PDO('mysql:host=localhost;dbname=Projet;charset=utf8', 'phpmyadmin', 'root');*



## Script "script_insertion_donnees.php"

Ce script permet de stocker dans une base de données les informations relatives aux posts et à une page Twitter qui sont disponibles dans des fichiers CSV générés grâce à un script de scraping.

### Comment se servir du script ?

Les fichiers CSV des collectivités doivent être placés dans un dossier nommé "scraps" à la racine du serveur web.
Dans notre cas, la racine est */var/www/html/*, le fichier scraps doit donc être créé à l'intérieur de celle-ci, le chemin sera donc */var/www/html/scraps/*.

Le script de scraping créé des dossiers nommés par l'ID de la collectivité, ces dossiers doivent être placés dans le dossier *scraps*, voici un exemple d'arborescence :

 - var/www/html/
	- scraps/
		- 24/
			- tweets.csv
			- users.csv
		- 82/
			- tweets.csv
			- users.csv

Attention à ne pas exécuter le script une seconde fois avec les même fichiers dans le dossier scraps, cela peut créer des doublons dans la base de données.

#### Connexion à la base de données

La connexion à la base se fait à partir de la ligne de code suivante :

*$conn = mysqli_connect("localhost", "phpmyadmin", "martinez", "phpmyadmin");*

## Script "notation.php"

Le script a pour but de calculer les notes de chaque colléctivité individuellement et de stocker ces notes dans la base de données.

1 - Premierement, il est nécéssaire de modifier les données liées à la connexion à la base de données :

Ligne 4 : *$bdd = new PDO('mysql:host=host;dbname=databasename;charset=utf8', 'username', 'password');*

Il est nécéssaire de modifier les données host, databasename, username et password et de les adapter aux données de connexion lié à votre compte MySQL.
 

2- Utilisation du script :

Deux variables correspondantes aux dates de début et de fin de traitement sont à inclure lors de l'éxécution du script.
Exemple d'utilisation : notation.php?min=2018-01-01&max=2019-01-01
Cette exécution calculera les notes individuelles de chaque colléctivité sur la periode du 1 Janvier 2018 au 1 Janvier 2019.

## Script "score.php"

Ce script à pour but de calculer le score de chaque colléctivité, comparée aux autres colléctivités et sur une periode choisie.
La periode choisie doit corréspondre à une periode analysé au préalable par le script "notation.php".

1 - Premierement, il est nécéssaire de modifier les données liées à la connexion à la base de données :

Ligne 4 : *$bdd = new PDO('mysql:host=host;dbname=databasename;charset=utf8', 'username', 'password');*

Il est nécéssaire de modifier les données host, databasename, username et password et de les adapter aux données de connexion lié à votre compte MySQL.

2- On défini une nouvelle fois les deux variables de dates lors de l'appel du script.

Exemple : *score.php?min=2018-01-01&max=2019-01-01*

Cette exécution comparera les données de chaque colléctivités aux autres et calculera le score final de chaque colléctivité.

Il faut donc penser à changer les valeurs ci-dessus par celles de la base de données utilisée. (host, username, password, database_name)



