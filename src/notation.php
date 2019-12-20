<?php

try
{
	$bdd = new PDO('mysql:host=localhost;dbname=projet;charset=utf8', 'root', '');
}
catch (Exception $e)
{
        die('Erreur : ' . $e->getMessage());
}

$request_ids = $bdd->query("SELECT id_collectivite FROM Twitter_Profile");  //On récupere l'ensemble des colléctivités dont on possède les données

//VARIABLES RATIOS
//Fréquence de publication
$min_frequence_pub = 5;
$max_frequence_pub = 7;

//Ratio d'abonnement
$min_ratio_abonnement = 1;
$max_ratio_abonnement = 3;

//Ratio de retweets
$ideal_ratio_rt = 30;

//VARIABLES SCRIPT
$date_min = $_GET['min']; //Date limite inferieur de l'interval traité
$date_min_php = new DateTime($_GET['min']); //Date au format PHP
$date_max = $_GET['max']; //Date limite superieur de l'interval traité
$date_max_php = new DateTime($_GET['max']); //Date au format PHP


foreach($request_ids as $col) {
    //VARIABLES POUR NOTES COLLECTIVITE
    $note_engagement = 0;
    $note_portee = 0;
    $note_frequence = 0;
    $note_ratio_abo = 0;
    $note_ratio_rt = 0;
    $ecart_type = 0;
    $note_certif = 0;
    $diversite_tweet = 0;

    //RECUPERATION DES DONNEES GLOBALES
    $id_collectivite = $col['id_collectivite']; //Récuperation de l'id de la colléctivité
    $profil_request = $bdd->query("SELECT id, is_certified FROM Twitter_Profile WHERE id_collectivite = '$id_collectivite'"); //Récuperation de l'id du profil de la colléctivité et de sa certification
    $pop_request = $bdd->query("SELECT nb_habitants, nom FROM collectivites WHERE id = '$id_collectivite'"); //Récupération du nombre d'habitants et du nom de la colléctivité
    $profil_request = $profil_request->fetch(PDO::FETCH_ASSOC);
    $pop_request = $pop_request->fetch(PDO::FETCH_ASSOC);
    $id_profile = $profil_request['id']; //ID du profil associé
    $population = $pop_request['nb_habitants']; //Population de la colléctivité
    $nom = $pop_request['nom']; //Nom de la colléctivité
    $certified = $profil_request['is_certified']; //Indicateur de certifiation

    $followers_request = $bdd->query("SELECT AVG(followers) as followers, AVG(follows) as follows FROM Twitter_Relations WHERE twitter_profile_id = '$id_profile' AND date_enregistrement > '$date_min';"); //Récupération du nombre de followers et follow moyen sur la periode de la colléctivité
    $followers_request = $followers_request->fetch(PDO::FETCH_ASSOC);
    $followers = $followers_request['followers']; //Récupération du nombre de followers
    $follows = $followers_request['follows']; //Récupération du nombre de follows

    echo('<br/><br/>COLLECTIVITE : ' . $nom . '<br/>'); //Affichage du nom de la colléctivité

    //Calcul du taux d'engagement
    $post_ids = $bdd->query("SELECT id FROM Twitter_Post WHERE twitter_profile_id = '$id_profile' AND date_creation BETWEEN '$date_min' AND '$date_max';"); //Récupération des id de toutes les posts de la colléctivité

    $post_count = $bdd->query("SELECT COUNT(id) as total_posts FROM Twitter_Post WHERE twitter_profile_id = '$id_profile' AND date_creation BETWEEN '$date_min' AND '$date_max';"); //Récupération du nombre de posts de la colléctivité
    $post_count = $post_count->fetch(PDO::FETCH_ASSOC);

    $total_post = $post_count['total_posts']; //Total de tweets sur la periode
    $sum_reactions = 0; //Somme des réactions sur la periode

    foreach ($post_ids as $post) {
        $post_id = $post['id']; //On récupere l'id du post à traiter
        $post_reactions = $bdd->query("SELECT total_reactions FROM Twitter_Reaction WHERE post_id = '$post_id' ORDER BY scrapping_date DESC LIMIT 1"); //Récupération du total de reactions au post traité
        $post_reactions = $post_reactions->fetch(PDO::FETCH_ASSOC);
        $sum_reactions += $post_reactions['total_reactions']; //Ajout du nombre de réaction a la somme globale pour tout les posts
    }

    if($total_post != 0) { //On vérifie que le nombre de posts est superieur à 0
        $note_engagement = ($sum_reactions / $followers) / $total_post; // Calcul d'une note temporaire d'engagement pour la colléctivité
    } else {
        $note_engagement = 0;
    }

    //Affichage des données liées au taux d'engagement
    echo('<br/>Total reactions :' . $sum_reactions);
    echo('<br/>Followers :' . $followers);
    echo('<br/>Nombre total de posts : ' . $total_post);
    echo('<br/>Engagement : ' . $note_engagement);

    //Calcul de la portée
    $note_portee = $followers / $population; //Calcul d'une note temporaire de portée pour la colléctivité
    echo('<br/><br/>Portee : ' . $note_portee);

    //Calcul de l'indice de fréquence de publication
    $note_frequence = $total_post / (($date_min_php->diff($date_max_php))->days); //Calcul de la note temporaire de fréquence de publication de la colléctivité
    echo('<br/><br/>Nb jours : ' . ($date_min_php->diff($date_max_php))->days);
    echo('<br/>Fréquence posts : ' . $note_frequence);

    //Calcul du ratio d'abonnement
    $ratio_abo = $followers / $follows;
    echo('<br/><br/>Ratio abonnement : ' . $ratio_abo);

    if ($ratio_abo < $min_ratio_abonnement) { //Si ratio inferieur au ratio minimum défini
        $note_ratio_abo = $ratio_abo * (100 / $min_ratio_abonnement);
    } elseif ($ratio_abo > $max_ratio_abonnement) { //Si ratio superieur au ratio minimum défini
        $note_ratio_abo = $max_ratio_abonnement * (100 / $ratio_abo);
    } else { //Si ratio compris dans l'onterval défini
        $note_ratio_abo = 100;
    }
    echo('<br/>Note ratio abonnement : ' . $note_ratio_abo);

    //Calcul du ratio de RT
    $request_tweets = $bdd->query("SELECT COUNT(id) as nb_tweets FROM Twitter_Post WHERE twitter_profile_id = '$id_profile' AND date_creation BETWEEN '$date_min' AND '$date_max' AND is_retweet = 0"); //Récupération du nombre de tweets
    $request_tweets = $request_tweets->fetch(PDO::FETCH_ASSOC);

    $request_retweets = $bdd->query("SELECT COUNT(id) as nb_rt FROM Twitter_Post WHERE twitter_profile_id = '$id_profile' AND date_creation BETWEEN '$date_min' AND '$date_max' AND is_retweet = 1"); //Récupération du nombre de Retweets
    $request_retweets = $request_retweets->fetch(PDO::FETCH_ASSOC);

    if($request_retweets['nb_rt'] != 0) { //Si le nombre de RT est superieur à 0
        $ratio_rt = $request_tweets['nb_tweets'] / $request_retweets['nb_rt'];
    } else {
        $ratio_rt = 0;
    }

    echo('<br/><br/>Ratio retweets : ' . $ratio_rt);
    if ($ratio_rt < $ideal_ratio_rt) { //Si ratio inferieur au ratio minimal défini
        $note_ratio_rt = $ratio_rt * (100 / $ideal_ratio_rt);
    } elseif ($ratio_rt > $ideal_ratio_rt) { //Si ratio superieur au ratio minimum défini
        $note_ratio_rt = $ideal_ratio_rt * (100 / $ratio_rt);
    } else { //Si ratio dans l'interval défini
        $note_ratio_rt = 100;
    }
    echo('<br/>Note ratio retweets : ' . $note_ratio_rt);

    //Calcul ecart-type
    if($total_post != 0) { //Vérification que le nombre de post est superieur à 0
        $requests_dates = $bdd->query("SELECT date_creation FROM Twitter_Post WHERE twitter_profile_id = '$id_profile' AND date_creation BETWEEN '$date_min' AND '$date_max' ORDER BY date_creation;"); //Récupération de la date de creation de chaque post
        $dates = array(); //Creation de l'array permettant le stockage des dates de chaque post
        $ecarts = array(); //Creation de l'array permettant le stockage des écarts entre chaque post

        foreach ($requests_dates as $date) { //Pour chaque date,  à l'array en format Datetime pour le traitement PHP
            array_push($dates, new DateTime($date['date_creation']));
        }

        for ($i = 0; $i < sizeof($dates) - 1; $i++) { //Pour chaque date de l'array, on calcul l'ecart entre la date observée et la date suivante et on l'ajoute à l'array
            array_push($ecarts, ($dates[$i]->diff($dates[$i + 1])->days));
        }

        $moyenne_ecarts = array_sum($ecarts) / count($ecarts); //Calcul de la moyenne des écarts observés
        echo('<br/><br/>Moyenne des écarts de publication : ' . $moyenne_ecarts);

        $sum_ecarts = 0;
        for ($i = 0; $i < sizeof($ecarts); $i++) {
            $sum_ecarts += pow($ecarts[$i] - $moyenne_ecarts, 2);
        }
        echo('<br/>Somme des écarts de publication : ' . $sum_ecarts);

        $ecart_type = sqrt(($sum_ecarts) / count($ecarts)); //Calcul de l'acart-type temporaire pour la colléctivité
        echo('<br/>Ecart-type des publications : ' . $ecart_type);
    } else {
        $ecart_type = 0;
    }

    //Calcul certification
    if ($certified == 0) { //Si la colléctivité n'est pas certifiée
        $note_certif = 0;
    } else { //Si la colléctivité est certifiée
        $note_certif = 100;
    }
    echo('<br/><br/>Note certification : ' . $note_certif);

    //Calcul indicateur de diversité
    if($total_post != 0) { //Vérification que le nombre de posts est superieur à 0
        $total_differences = 0;
        $request_type = $bdd->query("SELECT DISTINCT(post_type) as type FROM Twitter_Post;"); //On récupere l'ensemble des types de posts
        $types = array(); //Initialisation de l'array contenant chaque type de post
        $nb_types = array(); //Initialisation de l'array contenant le nombre de posts de chaque type

        foreach ($request_type as $type) { //Pour chaque type de posts
            array_push($types, $type['type']); //Ajout du type à l'array
            $temp = $type['type'];
            $request_nb_type = $bdd->query("SELECT COUNT(id) as nb FROM Twitter_Post WHERE twitter_profile_id = '$id_profile' AND post_type = '$temp' AND date_creation BETWEEN '$date_min' AND '$date_max';"); //Récupération du nombre de tweets du type
            $request_nb_type = $request_nb_type->fetch(PDO::FETCH_ASSOC);
            array_push($nb_types, $request_nb_type['nb']); //Ajout à l'array
        }
        $taux_ideal = 1 / count($types); //Calcul du taux idéal à avoir pour chaque type de tweet
        echo('<br/><br/>Taux ideal par type de tweet :' . $taux_ideal);

        foreach ($nb_types as $nb) { //Pour chaque type de tweet, on ajoute la différence avec le taux idél à la variable "$total_differences"
            $total_differences += abs(($nb / $total_post) - $taux_ideal);
        }

        $diversite_tweet = (1 - $total_differences) * 100; //Calcul de l'indicateur de diversité de types de tweets

    } else {
        $diversite_tweet = 0;
    }

    if($diversite_tweet < 0) {
        $diversite_tweet = 0;
    }
    echo('<br/>Indicateur de diversité de tweets :' . $diversite_tweet);

    //Ajout des données dans la base
    $insert_datas = $bdd->prepare("INSERT INTO Twitter_Notes(collectivites_id, date_min, date_max, engagement, portee, frequence_pub, ratio_abo, ratio_rt, ecart_type, certif, diversite_tweet, total_post, avg_followers) VALUES(:id, :min, :max, :engagement, :portee, :frequence, :abo, :rt, :et, :certif, :divers, :posts, :followers)");
    $insert_datas->execute(array('id' => $id_collectivite, 'min' => $date_min, 'max' => $date_max, 'engagement' => $note_engagement, 'portee' => $note_portee, 'frequence' => $note_frequence, 'abo' => $note_ratio_abo, 'rt' => $note_ratio_rt, 'et' => $ecart_type, 'certif' => $note_certif, 'divers' => $diversite_tweet, 'posts' => $total_post, 'followers' => $followers));
}
?>