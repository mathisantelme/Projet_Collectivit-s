import twint

List = ["paris", "bordeaux", "PointeNoire116"]

for l in List :
    c = twint.Config()
    c.Store_csv = True
    c.Count = True
    c.User_full = False
    c.Hide_output = True
    #c.Profile_full = True
    #c.Limit = 2
    c.Username = l
    c.Output = "/tmp/Output/" + l
    c.Retweets = True
    c.Custom["user"] = ["id", "username", "join_date", "verified", "following", "followers"]
    c.Custom["tweet"] = ["id", "conversation_id", "username", "date", "link", "tweet", "photos", "video", "likes_count", "retweets_count", "replies_count", "retweet", "retweet_date"]
    
    
    twint.run.Lookup(c)
    
    twint.run.Profile(c)

    #twint.run.Search(c)