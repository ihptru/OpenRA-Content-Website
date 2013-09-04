#!/usr/bin/env python
import sys
import os
import hashlib
from map import *
import bmp
import getopt
import shutil
import MySQLdb
import config

WEBSITE_PATH = os.getcwd() + os.sep

conn = MySQLdb.connect(config.host, config.user, config.password, config.database)
cur = conn.cursor()

# -s <source file> -u <user_name> -i uid -t <target file> -p <previous_version_uid>

def mapToBMP(pmap):
    img = bmp.BitMap(pmap.Right,pmap.Bottom,bmp.Color(0,0,0));
    for x in range(pmap.Left,pmap.Right+pmap.Left):
        for y in range(pmap.Top,pmap.Bottom+pmap.Top):
            color = bmp.Color(0,0,0);
            d1 = 0
            if pmap.tilesTile[x][y] == 510:
                pmap.tilesTile[x][y] = 255 #Change 510 to clear (should probably never happen hint: byte size 255 function in .net line:130)
            for i in range(len(pmap.templates)):
                if int(pmap.templates[i].id) == pmap.tilesTile[x][y]:
                    index = pmap.tilesIndex[x][y]
                    c = ""
                    for j in range(len(pmap.templates[i].list)):
                        if pmap.templates[i].list[j].id == index:
                            c = pmap.templates[i].list[j].type
                            break;
                    if c == "":
                        c = c = pmap.templates[i].list[0].type
                        # accually error but we save it for now
                    for j in range(len(pmap.terrTypes)):
                        if pmap.terrTypes[j].type == c:
                            color = bmp.Color(pmap.terrTypes[j].r,pmap.terrTypes[j].g,pmap.terrTypes[j].b)
                            d1 = 1
                            break;
                    if d1 == 1:
                        break;
            d2 = 0
            for i in range(len(pmap.resTypes)):
                if pmap.resTypes[i].type == pmap.resTile[x][y]:
                    for j in range(len(pmap.terrTypes)):
                        if pmap.terrTypes[j].type == pmap.resTypes[i].terrType:
                            color = bmp.Color(pmap.terrTypes[j].r,pmap.terrTypes[j].g,pmap.terrTypes[j].b)
                            d2 = 1
                            break;
                if d2 == 1:
                    break
            d = 0
            if(d1 == 0 and d2 == 0):
                #nothing was found at all use 255 = clear to fill gap
                for i in range(len(pmap.templates)):
                    if int(pmap.templates[i].id) == 255:
                        index = pmap.tilesIndex[x][y]
                        c = ""
                        for j in range(len(pmap.templates[i].list)):
                            if pmap.templates[i].list[j].id == index:
                                c = pmap.templates[i].list[j].type
                                break;
                        if c == "":
                            c = c = pmap.templates[i].list[0].type
                            # accually error but we save it for now
                        for j in range(len(pmap.terrTypes)):
                            if pmap.terrTypes[j].type == c:
                                color = bmp.Color(pmap.terrTypes[j].r,pmap.terrTypes[j].g,pmap.terrTypes[j].b)
                                d = 1
                                break;
                        if d == 1:
                            break;
            img.setPenColor(color);
            img.plotPoint(x-pmap.Left,y-pmap.Top);
    return img

try:
    optlist,  args = getopt.getopt(sys.argv[1:], 's:i:u:t:p:')
except getopt.GetoptError, err:
    print err
    exit()

if optlist == []:
    print "Incorrect options"
    exit(2)

for  i in range(len(optlist)):
    if optlist[i][0] == "-s":
        source = optlist[i][1]
    if optlist[i][0] == "-i":
        uid = optlist[i][1]
    if optlist[i][0] == "-u":
        username = optlist[i][1]
    if optlist[i][0] == "-t":
        mapfile = optlist[i][1]
    if optlist[i][0] == "-p":
        pre_version = optlist[i][1]

map1 = map(source)

if map1.UseAsShellmap == 1:
    exit(9) # shellmap is not allowed

# getting hash
concat_bytes = map1.raw_yamlData + map1.bin
h = hashlib.sha1()
h.update(concat_bytes)
hash = h.hexdigest()

#Check if map with the same hash already exists for this user

sql = """SELECT * FROM maps
        WHERE user_id = %(uid)s AND maphash = '%(hash)s'
""" % vars()
cur.execute(sql)
records = cur.fetchall()
conn.commit()
if len(records) != 0:
    print "user already has such a map"
    exit(8)
tag = "r1"
if pre_version != '0':
    sql = """SELECT uid,tag FROM maps
                WHERE uid = %(pre_version)s
    """ % vars()
    cur.execute(sql)
    records = cur.fetchall()
    conn.commit()
    tag = records[0][1][0] + str(int(records[0][1][1:]) + 1)

# Move source file to correct place on disk
mapfile_full_path = WEBSITE_PATH + "users/" + username + "/" + "maps/" + map1.MapMod + "-" + tag + "-" + ".".join(mapfile.split('.')[0:-1]) + "/" + mapfile
path = os.path.dirname(mapfile_full_path) + os.sep
db_path = path.split(WEBSITE_PATH)[1]

try:
    os.mkdir(path)
except OSError as e:
    if e.args[0]==17: #Directory already exists = map already exists
        print "Directory exists... exit"
        exit(5)

shutil.move(source, mapfile_full_path)    #File was uploaded into tmp dir and must be moved into right place

print "Path: " + mapfile_full_path
if not os.path.isfile(mapfile_full_path):
    print "Error: File is not moved"
    exit(6)

#Generate info file
print "Creating info file..."
text_file = open(path + "info.txt", "w")
lines = map1.getInfo();
text_file.writelines(lines)
text_file.close()

#Push map.yaml on disk
yaml_f = open(path + "map.yaml", "w")
yaml_f.write(map1.raw_yamlData)
yaml_f.close()

#Put record into database
MapTitle = map1.MapTitle
MapDesc = map1.MapDesc
MapAuthor = map1.MapAuthor
MapType = map1.MapType
MapPlayers = map1.MapPlayers
MapMod = map1.MapMod
width = map1.width
height = map1.height
MapTileset = map1.MapTileset

try:
    print "Putting record into database..."
    sql = """INSERT INTO maps
            (title, description, author, type, players, g_mod, maphash, width, height, tileset, path, user_id, tag, p_ver)
            VALUES
            (
            '%(MapTitle)s',
            '%(MapDesc)s',
            '%(MapAuthor)s',
            '%(MapType)s',
            %(MapPlayers)s,
            '%(MapMod)s',
            '%(hash)s',
            %(width)s,
            %(height)s,
            '%(MapTileset)s',
            '%(db_path)s',
            %(uid)s,
            '%(tag)s',
            %(pre_version)s
            )
    """ % vars()
    cur.execute(sql)
    conn.commit()
    sql = """SELECT uid FROM maps
            WHERE user_id = %(uid)s AND maphash = '%(hash)s'
    """ % vars()
    cur.execute(sql)
    records = cur.fetchall()
    conn.commit()
    new_uid = records[0][0]
    sql = """UPDATE maps
            SET n_ver = %(new_uid)s
            WHERE uid = %(pre_version)s
    """ % vars()
    cur.execute(sql)
    conn.commit()
except:
    exit(7)
cur.close()

print "Generating full size preview..."
if MapMod == "ra":
    os.system('mono '+WEBSITE_PATH+'mono/ImageMapGenerator/bin/Debug/ImageMapGenerator.exe -filename="'+mapfile_full_path+'"')

print("Map's hash: "+hash)

mapToBMP(map1).saveFile(path + "minimap.bmp");
print("minimap is saved: "+path+"minimap.bmp")

if MapPlayers == 0:
    exit(10) # everything is ok but inform user that his map has zero playable slots

exit(0)

