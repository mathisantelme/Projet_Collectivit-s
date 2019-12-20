CREATE TABLE IF NOT EXISTS `collectivites` (
	`id` int(11) NOT NULL,
	`idbis` varchar(255) NOT NULL DEFAULT '',
	`nom` varchar(255) NOT NULL,
	`nb_habitants` bigint(20) NOT NULL,
	`type_collectivite_id` int(11) NOT NULL,
	`code_commune` varchar(50) NOT NULL DEFAULT '',
	`cp` varchar(20) NOT NULL DEFAULT '',
	`latitude` varchar(191) DEFAULT NULL,
	`longitude` varchar(191) DEFAULT NULL,
	`surface` varchar(191) DEFAULT NULL,
	`altitude` double NOT NULL,
	`code_region` varchar(50) NOT NULL DEFAULT '',
	`code_departement` varchar(50) NOT NULL DEFAULT '',
	`code_epci` varchar(50) NOT NULL DEFAULT '',
	`typo_epci` varchar(20) NOT NULL DEFAULT '',
	`site` text NOT NULL,
	`compte_instagram` varchar(255) NOT NULL,
	`compte_facebook` varchar(255) NOT NULL,
	`compte_twitter` varchar(255) NOT NULL,
	`compte_youtube` varchar(255) NOT NULL,
	`compte_linkedin` varchar(255) NOT NULL,
	`compte_snapchat` varchar(255) NOT NULL,
	`compte_instagram_existe` int(11) NOT NULL DEFAULT '1',
	`compte_facebook_existe` int(11) NOT NULL DEFAULT '1',
	`compte_twitter_existe` int(11) NOT NULL DEFAULT '1',
	`compte_youtube_existe` int(11) NOT NULL DEFAULT '1',
	`compte_linkedin_existe` int(11) NOT NULL DEFAULT '1',
	`compte_snapchat_existe` int(11) NOT NULL DEFAULT '1',
	PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS `Twitter_Profile`(
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`id_collectivite` int(11) NOT NULL,
	`nom_compte` varchar(255) NOT NULL,
	`date_enregistrement` date NOT NULL,
	`is_certified` boolean NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY(id_collectivite) REFERENCES collectivites(id)
);

CREATE TABLE IF NOT EXISTS `Twitter_Relations`(
	`id` int NOT NULL AUTO_INCREMENT,
	`twitter_profile_id` int(11) NOT NULL,
	`follows` bigint(20) NOT NULL DEFAULT '0',
	`followers` bigint(20) NOT NULL DEFAULT '0',
	`date_enregistrement` date NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY(twitter_profile_id) REFERENCES Twitter_Profile(id)		
);

CREATE TABLE IF NOT EXISTS `Twitter_Post`(
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`twitter_profile_id` int(11) NOT NULL,
	`date_enregistrement` date NOT NULL,
	`date_creation` date NOT NULL,
	`lien_tweet` varchar(255) NOT NULL,
	`is_response` boolean NOT NULL,
	`id_parent` int(25),
	`texte_valeur` text NOT NULL,
	`lien_image` varchar(255) NOT NULL,
	`lien_video` varchar(255) NOT NULL,
	`is_retweet` boolean NOT NULL,
	`post_type` VARCHAR(255) NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY(twitter_profile_id) REFERENCES Twitter_Profile(id)	
);

CREATE TABLE IF NOT EXISTS `Twitter_Reaction`(
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`post_id` int(11) NOT NULL,
	`total_likes` bigint(20) NOT NULL,
	`total_retweets` bigint(20) NOT NULL,
	`total_comments` bigint(20) NOT NULL,
	`total_reactions` bigint(20) NOT NULL,
	`scrapping_date` date NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY(post_id) REFERENCES Twitter_Post(id)
);

CREATE TABLE IF NOT EXISTS `Twitter_Notes`(
	`id` int NOT NULL AUTO_INCREMENT,
	`collectivites_id` int(11) NOT NULL,
	`date_min` date NOT NULL,
	`date_max` date NOT NULL,
	`engagement` double NOT NULL,
	`portee` double NOT NULL,
`frequence_pub` double NOT NULL,
`ratio_abo` double NOT NULL,
`ratio_rt` double NOT NULL,
`ecart_type` double NOT NULL,
`certif` double NOT NULL,
`diversite_tweet` double NOT NULL,
`total_post` int(11) NOT NULL,
`avg_followers` double NOT NULL,
	PRIMARY KEY(id),
	FOREIGN KEY(collectivites_id) REFERENCES collectivites(id)
);
