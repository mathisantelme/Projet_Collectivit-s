<?php
try
{
    $bdd = new PDO('mysql:host=localhost;dbname=projet;charset=utf8', 'root', '');
}
catch (Exception $e)
{
    die('Erreur : ' . $e->getMessage());
}

//VARIABLES SCRIPT
$date_min = $_GET['min']; //Date limite inferieur de l'interval traité
$date_min_php = new DateTime($_GET['min']); //Date au format PHP
$date_max = $_GET['max']; //Date limite superieur de l'interval traité
$date_max_php = new DateTime($_GET['max']); //Date au format PHP

echo('CALCUL DU SCORE SUR LA PERIODE DU ' . $date_min . ' au ' . $date_max);
//CALCUL DU SCORE
$request_ids = $bdd->query("SELECT * FROM Twitter_Notes WHERE date_min = '$date_min' AND date_max = '$date_max'"); //Récupération des notes de chaque colléctivité pour le calcul des maximum d'engagement, portee, ecart-type et frequence de publication
$request_ids2 = $bdd->query("SELECT * FROM Twitter_Notes WHERE date_min = '$date_min' AND date_max = '$date_max'"); //Récupération des notes pour traitement

$engagement = array(); //Array de tous les taux d'engagement des colléctivités
$portee = array(); //Array de toutes les portées des colléctivités
$freq = array(); //Array de toutes les fréquences de publication  des colléctivités
$et = array(); //Array de tous les ecart-type de publication des colléctivités

foreach($request_ids as $col) { //Pour chaque colléctivités
$col_id = $col['collectivites_id'];
$request_criteres = $bdd->query("SELECT engagement, portee, frequence_pub, ecart_type FROM Twitter_Notes WHERE collectivites_id = '$col_id'"); //Récupération des critères pour le calcul des maximums

foreach($request_criteres as $notes) { //Pour chaque note
    array_push($engagement, $notes['engagement']);
    array_push($portee, $notes['portee']);
    array_push($freq, $notes['frequence_pub']);
    array_push($et, $notes['ecart_type']);
    }
}

//Calcul des maximum pour comparaison
$max_engagement = max($engagement);
$max_portee = max($portee);
$max_freq = max($freq);
$max_et = max($et);

foreach($request_ids2 as $col) { //Pour chaque colléctivité, on calcul les critères nécéssaire ainsi que le score final
    $id_col = $col['collectivites_id'];
    $request_name = $bdd->query("SELECT nom FROM collectivites WHERE id = '$id_col'");
    $request_name = $request_name->fetch(PDO::FETCH_ASSOC);
    $col_name = $request_name['nom'];

    $eng = ($col['engagement']/$max_engagement) * 100;
    $por = ($col['portee']/$max_portee) * 100;
    $frequence = ($col['frequence_pub']/$max_freq) * 100;
    $eca = ($col['ecart_type']/$max_et) * 100;

    if($col['total_post'] != 0 && $col['avg_followers'] > 100 && $frequence > 5) { //Si nombre de post > 0, nombre de followers > 100 et fréquence de publication > 5
        $score = ($eng * 10 + $por * 8 + $frequence * 4 + $col['ratio_abo'] * 4 + $col['ratio_rt'] * 2 + $eca * 3 + $col['certif'] + $col['diversite_tweet'])/33;
    } elseif($col['total_post'] != 0 && $col['avg_followers'] > 100 && $frequence <= 5) { //Si nombre de post > 0, nombre de followers > 100 et fréquence de publication < 5
        $score = (($eng * 10 + $por * 8 + $frequence * 4 + $col['ratio_abo'] * 4 + $col['ratio_rt'] * 2 + $eca * 3 + $col['certif'] + $col['diversite_tweet'])/33)/1.15;
    } elseif($col['total_post'] != 0 && $col['avg_followers'] <= 100 && $frequence > 5) { //Si nombre de post > 0, nombre de followers < 100 et fréquence de publication > 5
        $score = ($eng * 5 + $por * 8 + $frequence * 4 + $col['ratio_abo'] * 4 + $col['ratio_rt'] * 2 + $eca * 3 + $col['certif'] + $col['diversite_tweet'])/28;
    } elseif($col['total_post'] != 0 && $col['avg_followers'] <= 100 && $frequence <= 5) { //Si nombre de post > 0, nombre de followers < 100 et fréquence de publication < 5
        $score = (($eng * 5 + $por * 8 + $frequence * 4 + $col['ratio_abo'] * 4 + $col['ratio_rt'] * 2 + $eca * 3 + $col['certif'] + $col['diversite_tweet'])/28)/1.15;
    } else { //Si nombre de post = 0
        $score = 0;
    }

    //AFFICHAGE DES DONNEES
    echo('<br/><br/><br/>Collectivite : '.$col_name);
    echo('<br/> Taux d\'engagement :' .$eng);
    echo('<br/> Portée :' .$por);
    echo('<br/> Indice de fréquence de publication :' .$frequence);
    echo('<br/> Ratio d\'abonnement :' .$col['ratio_abo']);
    echo('<br/> Ratio de Retweets :' .$col['ratio_rt']);
    echo('<br/> Ecart-type des Tweets :' .$eca);
    echo('<br/> Indice de certification :' .$col['certif']);
    echo('<br/> Indicateur de diversité de Tweets :' .$col['diversite_tweet']);

    echo('<br/><br/>SCORE FINAL : '.$score);
}
?>