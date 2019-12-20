import twint
import argparse

parser = argparse.ArgumentParser()
parser.add_argument("id")
parser.add_argument("nom")
args = parser.parse_args()

l = args.nom

c = twint.Config()

c.Store_csv = True # Permet de stocker les données dans un csv
c.Profile_full = True # Permet de scraper depuis la timeline du compte
c.Retweets = True
c.Hide_output = True
c.Username = l # défini le compte à scraper
c.Output = "Output/" + str(args.id) # défini le dossier où sont stocket les CSV
c.Custom["user"] = ["id", "username", "join_date", "verified", "following", "followers"]
c.Custom["tweet"] = ["id", "conversation_id", "username", "date", "link", "tweet", "photos", "video", "likes_count", "retweets_count", "replies_count", "retweet", "retweet_date"]


twint.run.Lookup(c) # permet de scraper les informations du profile

twint.run.Profile(c) # permet de scraper les tweets et les retweets

#twint.run.Search(c) # permet de scraper les tweets