<?php

// Connection à la BDD
$bdd = new PDO('mysql:host=localhost;dbname=Projet;charset=utf8', 'phpmyadmin', 'root');
$reponse = $bdd->query('SELECT id, compte_twitter FROM collectivites');
$donnees = $reponse->fetchAll();


foreach($donnees as $donnee){
	$id = $donnee['id'];
	$compte_twitter = $donnee['compte_twitter'];
	$nom = explode("/", $compte_twitter)[3];
	
	$output = shell_exec('sudo python3 scrapTwitter.py '. $id .' '. $nom); // Fait appel au script python pour scrape le compte
	echo($output);
}

?>
