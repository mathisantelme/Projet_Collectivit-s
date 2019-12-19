import twint
import argparse

parser = argparse.ArgumentParser()
parser.add_argument("id")
parser.add_argument("nom")
args = parser.parse_args()

l = args.nom

c = twint.Config()
c.Store_csv = True
c.Count = True
c.User_full = False
#c.Profile_full = True
c.Hide_output = True
#c.Limit = 1
c.Username = l
c.Output = "Output/" + str(args.id)
c.Retweets = True
c.Custom["user"] = ["id", "username", "join_date", "verified", "following", "followers"]
c.Custom["tweet"] = ["id", "conversation_id", "username", "date", "link", "tweet", "photos", "video", "likes_count", "retweets_count", "replies_count", "retweet", "retweet_date"]


twint.run.Lookup(c)

twint.run.Profile(c)

#twint.run.Search(c)