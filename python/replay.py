import os
import shutil
import re
import getopt
import config
import MySQLdb
import sys
import hashlib

WEBSITE_PATH = os.getcwd() + os.sep

conn = MySQLdb.connect(config.host, config.user, config.password, config.database)
cur = conn.cursor()

# -s <source /tmp file> -u <user_name> -i <uid> -t <target file name>
try:
    optlist,  args = getopt.getopt(sys.argv[1:], 's:i:u:t:')
except getopt.GetoptError, err:
    print err
    exit(1)

if optlist == []:
    print "Incorrect options"
    exit(2)

for  i in range(len(optlist)):
    if optlist[i][0] == "-s":
        source = optlist[i][1]        # /tmp/RaNdOm
    if optlist[i][0] == "-i":
        uid = optlist[i][1]             # user's uid   
    if optlist[i][0] == "-u":
        username = optlist[i][1]   # username
    if optlist[i][0] == "-t":
        replay_file = optlist[i][1]   # name of the target replay file

replay_path = WEBSITE_PATH + "users/" + username + "/replays/" + replay_file

shutil.move(source, replay_path)    #File was uploaded into tmp dir and must be moved into right place
print("uploaded file")

Data = open(replay_path).read(1000000)
print("opened replay file")

# getting hash
h = hashlib.sha1()
h.update(Data)
hash = h.hexdigest()

sql = """SELECT r_hash FROM replays WHERE user_id = %s
""" % uid
cur.execute(sql)
records = cur.fetchall()
conn.commit()

for i in range(len(records)):
    if records[i][0] == hash:
        exit(4) # user already has an identical replay

if len(re.findall('StartGame', Data)) == 0:
    exit(3) # could not detect the moment of the game starting

Data = Data.split('StartGame')[0]
print("found startgame point")

# server's info
global_s = Data.split('GlobalSettings:')[-1]

server_name = re.findall('ServerName: (.*)', global_s)[0].replace("'", "\\'")
maphash = re.findall('Map: (.*)', global_s)[0]
mods = re.findall('Mods: (.*)', global_s)[0]
version = re.findall('\tMods: (.*)', Data.split('Handshake:')[1].split('Response')[0])[0].split('@')[1]
print("fetched server data")

###
title = replay_file.split('.rep')[0]
path = "users/" + username + "/replays/" + replay_file

sql = """INSERT INTO replays
        (title,path,user_id,r_hash,date_time,duration,version,server_name,maphash,mods)
        VALUES
        (
        '%(title)s',
        '%(path)s',
        %(uid)s,
        '%(hash)s',
        '0000-00-00 00:00:00',
        '0',
        '%(version)s',
        '%(server_name)s',
        '%(maphash)s',
        '%(mods)s'
        );
""" % vars()
cur.execute(sql)
conn.commit()
print("inserted into db")

sql = """SELECT uid FROM replays WHERE user_id = %(uid)s ORDER BY posted DESC LIMIT 1
""" % vars()
cur.execute(sql)
records = cur.fetchall()
conn.commit()

id_replay_players = records[0][0]
###

# last clients
clients = Data.split('GlobalSettings')[-2].split('Client@', 1)[1]

client_names = re.findall('\tName: (.*)', clients)
client_colorramps = re.findall('\tColorRamp: (.*)', clients)
client_countries = re.findall('\tCountry: (.*)', clients)
client_teams = re.findall('\tTeam: (.*)', clients)
print("got client data")

for i in range(len(client_names)):
    sql = """INSERT INTO replay_players
            (id_replays,name,colorramp,country,team)
            VALUES
            (
            %s,
            '%s',
            '%s',
            '%s',
            %s
            );
    """ % (id_replay_players, client_names[i].replace("'", "\\'"), client_colorramps[i], client_countries[i], client_teams[i])
    cur.execute(sql)
    conn.commit()

print("Done")
exit(0)
