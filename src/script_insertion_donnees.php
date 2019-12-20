<?php
  /*
    Ce script permet de récupérer des données scrapées depuis twitter stockées dans des fichiers CSV
    Les fichiers CSV doivent être placés dans le dossier scraps à la racine du serveur web
      Dans notre cas la racine est /var/www/html
      Donc le chemin vers les fichiers CSV pour chaque collectivité doit être /var/www/html/scraps/{id_collectivite}
    Attention à ne pas rééxecuter le script une deuxième fois pour les même données car il y aura des doublons dans la base
    Le script s'occupe déjà de supprimer les fichiers CSV traités pour un gain de place
  */

  // Affichage des erreurs PHP pour le débogage
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  // Connexion à la base de données, les valeurs à rentrer sont :
  //  host, username, password, database_name
  $conn = mysqli_connect("localhost", "phpmyadmin", "martinez", "phpmyadmin");

  // Chemin des dossiers contenant les données scrapées de Twitter
  $scrapPath = '/var/www/html/scraps/';

  // Liste des fichiers dans le répertoire
  $allFiles = scandir($scrapPath);
  // On retire les fichiers . et .. de la liste car il ne nous sont pas utiles pour le script
  $files = array_diff($allFiles, array('.', '..'));

  // On parcoure tous les dossiers dans le répertoire
  foreach($files as $file){
    $filePath = $scrapPath . $file . '/';
    // Chemin des données de la page
    $usersPath = $filePath . 'users.csv';
    // Chemin des tweets de la page
    $tweetsPath = $filePath . 'tweets.csv';

    // PROFILE PART
    if(($h = fopen($usersPath, "r")) !== FALSE){
      $data = fgetcsv($h, 1000, ",");

      // Lecture du fichier CSV
      while(($data = fgetcsv($h, 1000, ",")) !== FALSE){
        // On vérifie qu'il n'existe pas déjà la même collectivité dans la base
        $sqlSelect = "SELECT * FROM Twitter_Profile WHERE id_collectivite='" . $file . "' AND date_enregistrement=now()";
        $result = mysqli_query($conn, $sqlSelect);

        if(mysqli_num_rows($result) < 1){
          // Insertion de la page twitter dans la base
          $sqlInsert = "INSERT INTO Twitter_Profile (id_collectivite, nom_compte, date_enregistrement, is_certified) VALUES ('" . $file . "','" . $data[1] . "',now(),'" . $data[3] . "')";
          $result = mysqli_query($conn, $sqlInsert);

          // Affichage des erreurs
          if (empty($result)) {
            $type = "Error";
            $message = "Problème d'importation du CSV";
            echo "<p>" . $type . " : " . $message . "</p>";
          }
        }

        // Récupération du dernier profil twitter enregistré dans la base (profil actuel)
        $sqlSelect = "SELECT MAX(id) AS id FROM Twitter_Profile";
        $result = mysqli_query($conn, $sqlSelect);
        $id = $result->fetch_assoc()['id'];

        // Insertion des relations de la page dans la base
        $sqlInsert = "INSERT INTO Twitter_Relations (twitter_profile_id, follows, followers, date_enregistrement) VALUES ('" . $id . "','" . $data[4] . "','" . $data[5] . "', now())";
        $result = mysqli_query($conn, $sqlInsert);

        // Affichage des erreurs
        if (empty($result)) {
          $type = "Error";
          $message = "Problème d'importation du CSV";
          echo "<p>" . $type . " : " . $message . "</p>";
        }
      }

      // Fermeture du fichier CSV
      fclose($h);
    }

    // TWEETS PART
    if(($h = fopen($tweetsPath, "r")) !== FALSE){
      $data = fgetcsv($h, 1000, ",");

      while(($data = fgetcsv($h, 1000, ",")) !== FALSE){
        // Si l'id du post est le même que celui de la conversation, il ne s'agit pas d'une réponse
        $isResponse = $data[0] == $data[1];
        // Il arrive que la variable soit vide donc si c'est le cas on lui donne la valeur 0 (false)
        if(empty($isResponse)){
          $isResponse = 0;
        }
        // Conversion True/False(CSV) -> 1/0(Base)
        $isRetweet = $data[11] == "True" ? 1 : 0;

        // Récupération du dernier profil twitter enregistré dans la base (profil actuel)
        $sqlSelect = "SELECT MAX(id) AS id FROM Twitter_Profile";
        $result = mysqli_query($conn, $sqlSelect);
        $id = $result->fetch_assoc()['id'];

        // On stocke le type de tweet dans la variable tweetType
        $tweetType = "texte";

        $photos = $data[6];
        if($photos != '[]'){
          $tweetType = "photo";
        } else {
            $video = $data[7];
            if($video != 0){
              $tweetType = "video";
            }
        }

        // Insertion d'un post dans la base
        $sqlInsert = "INSERT INTO Twitter_Post (twitter_profile_id, date_enregistrement, date_creation, lien_tweet, is_response, texte_valeur, lien_image, lien_video, is_retweet, post_type) VALUES ('" . $id . "',now(),'" . $data[3] . "','" . $data[4] . "','" . $isResponse . "','" . addslashes($data[5]) . "','" . addslashes($data[6]) . "','" . $data[7] . "','" . $isRetweet . "', '" . $tweetType . "')";
        $result = mysqli_query($conn, $sqlInsert);

        // Affichage des erreurs
        if (empty($result)) {
          $type = "Error";
          $message = "Problème d'importation du CSV";
          echo "<p>" . $type . " : " . $message . "</p>";
        }

        // Récupération de l'id du dernier post enregistré
        $sqlSelect = "SELECT MAX(id) AS id FROM Twitter_Post";
        $result = mysqli_query($conn, $sqlSelect);
        $id = $result->fetch_assoc()['id'];
        $totalReactions = $data[8] + $data[9] + $data[10];

        // Ajout des réactions du post dans la base
        $sqlInsert = "INSERT INTO Twitter_Reaction (post_id, total_likes, total_retweets, total_comments, total_reactions, scrapping_date) VALUES (" . $id . ",'" . $data[8] . "','" . $data[9] . "','" . $data[10] . "','" . $totalReactions . "',now())";
        $result = mysqli_query($conn, $sqlInsert);
      }

      fclose($h);
    }
  }
 ?>
