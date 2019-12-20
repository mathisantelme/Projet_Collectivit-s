# Projet Collectivités
Projet de Master - Analyse de la présence des collectivités territoriales sur les réseaux sociaux

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
Il faut donc penser à changer les valeurs ci-dessus par celles de la base de données utilisée. (host, username, password, database_name)



