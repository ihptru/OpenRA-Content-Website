import sys;
import zipfile;
import string;
import struct;
import io;
import bmp;
import os;
import getopt;
import MySQLdb;
import hashlib;
import shutil;

WEBSITE_PATH = os.getcwd() + os.sep

# Check if path exist
# -f <source file> -u <user_id> -t <destination file>

try:
    optlist,  args = getopt.getopt(sys.argv[1:], 's:i:u:t:')
except getopt.GetoptError, err:
    print err
    exit()

if optlist == []:
    print "Incorrect options"
    exit()

for  i in range(len(optlist)):
    if optlist[i][0] == "-s":
        source = optlist[i][1]
    if optlist[i][0] == "-i":
        uid = optlist[i][1]
    if optlist[i][0] == "-u":
        username = optlist[i][1]
    if optlist[i][0] == "-t":
        mapfile = optlist[i][1]

yamlData = "";
bin = "";

z = zipfile.ZipFile(source, mode='a')

for filename in z.namelist():
    #print filename
    bytes = z.read(filename)
    #print len(bytes)
    if filename == "map.yaml":
        raw_yamlData = bytes
        yamlData = bytes.decode("utf-8")
    if filename == "map.bin":
        bin = bytes

# getting hash
concat_bytes = raw_yamlData + bin
h = hashlib.sha1()
h.update(concat_bytes)
hash = h.hexdigest()

yamlTemp = yamlData;

MapMod = "";
MapTitle = "";
MapAuthor = "";
MapTileset = "";
MapType = "";
MapBounds = "";
MapDesc = "";
MapPlayers = 0;
MapDefaultRace = [];

# 1 byte
def Bytes2Int1(data):
    return struct.unpack('B', data[0])[0]

# 2 bytes
def Bytes2Int2(data):
    return struct.unpack('B', data[0])[0] + struct.unpack('B', data[1])[0]

# 4 bytes
def Bytes2Int4(data):
    return struct.unpack('B', data[0])[0] + struct.unpack('B', data[1])[0] + struct.unpack('B', data[2])[0] + struct.unpack('B', data[3])[0]

# Removes all blankspaces in the begning
def strFixer(s):
    f = 0;
    st = "";
    for c in s:
        if not c == " " or f == 1:
            f = 1;
            st = st + c;
    return st;

def tabFixer(s):
    f = 0;
    st = "";
    for c in s:
        if not c == '\t' or f == 1:
            f = 1;
            st = st + c;
    return st;

def init2Dlist(w,h):
    ll = []
    for i in range(w):
        l = []
        for j in range(h):
            l.append(0);
        ll.append(l)
    return ll

for line in string.split(yamlData, '\n'):
    if line[0:5] == "Title":
        MapTitle = line[6:].strip().replace("'", "''");
    if line[0:11] == "RequiresMod":
        MapMod = line[12:].strip().lower();
    if line[0:6] == "Author":
        MapAuthor = line[7:].strip().replace("'", "''");
    if line[0:7] == "Tileset":
        MapTileset = line[8:].strip().lower();
    if line[0:4] == "Type":
        MapType = line[5:].strip();
    if line[0:11] == "Description":
        MapDesc = line[12:].strip().replace("'", "''");
    if line[0:6] == "Bounds":
        MapBounds = line[7:].strip();
    if line.strip()[0:8] == "Playable":
        state = line.split(':')[1]
        if state.strip().lower() in ['true', 'on', 'yes', 'y']:
            MapPlayers += 1
    if line.strip()[0:5] == "Race:":
        MapDefaultRace.append(line.strip()[6:].lower())

#Take map bounds
MapBounds = strFixer(MapBounds)
Left = int(MapBounds[0:MapBounds.find(",")]);
MapBounds = MapBounds[MapBounds.find(",")+1:];
Top = int(MapBounds[0:MapBounds.find(",")]);
MapBounds = MapBounds[MapBounds.find(",")+1:];
Right = int(MapBounds[0:MapBounds.find(",")]);
MapBounds = MapBounds[MapBounds.find(",")+1:];
Bottom = int(MapBounds);

print "Left: " + str(Left)
print "Top: " + str(Top)
print "Right: " + str(Right)
print "Bottom: " + str(Bottom)

#Check so everything is ok before generating a minimap
b = io.BytesIO(bin);
if not Bytes2Int1(b.read(1)) == 1:
    print "Error: Unknown map format"
    exit()

formatOK = 0
if MapMod == "ra":
    if MapTileset == "temperat":
        formatOK = 1
    if MapTileset == "snow":
        formatOK = 1
    if MapTileset == "interior":
        formatOK = 1
if MapMod == "cnc":
    if MapTileset == "desert":
        formatOK = 1
    if MapTileset == "temperat":
        formatOK = 1
    if MapTileset == "winter":
        formatOK = 1
if MapMod == "":
    if MapTileset == "snow":
        formatOK = 1
        MapMod = "ra"
    if MapTileset == "interior":
        formatOK = 1
        MapMod = "ra"
    if MapTileset == "desert":
        formatOK = 1
        MapMod = "cnc"
    if MapTileset == "winter":
        formatOK = 1
        MapMod = "cnc"
    if MapTileset == "temperat":
        if "gdi" not in MapDefaultRace and "nod" not in MapDefaultRace:
            MapMod = "ra"
        else:
            MapMod = "cnc"
        formatOK = 1

if formatOK == 0:
    print "Error: Unknown mod"
    exit()

width = Bytes2Int2(b.read(2));
height = Bytes2Int2(b.read(2));

# Move source file to correct place on disk
mapfile_full_path = WEBSITE_PATH + "users/" + username + "/" + "maps/" + MapMod + "-" + mapfile.split('.')[0] + "/" + mapfile
path = os.path.dirname(mapfile_full_path) + os.sep
db_path = path.split(WEBSITE_PATH)[1]

try:
    os.mkdir(path)
except OSError as e:
    if e.args[0]==17: #Directory already exists = map already exists
        print "Directory exists... exit"
        exit()

shutil.move(source, mapfile_full_path)    #File was uploaded into tmp dir and must be moved into right place

print "Path: " + mapfile_full_path
if not os.path.isfile(mapfile_full_path):
    print "Error: File is not moved"
    exit()

#Generate info file
print "Creating info file..."
text_file = open(path + "info.txt", "w")
lines = [MapTitle+"\n",MapMod+"\n",MapAuthor+"\n",MapTileset+"\n",MapType+"\n",MapDesc+"\n",str(MapPlayers)+"\n"]
text_file.writelines(lines)
text_file.close()

#Put record into database
print "Putting record into database..."
conn = MySQLdb.connect("localhost", "oramod", "iequeiR6", "oramod")
cur = conn.cursor()
sql = """INSERT INTO maps
        (title, description, author, type, players, g_mod, maphash, width, height, tileset, path, user_id, screenshot_group_id)
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
        0
        )
""" % vars()
cur.execute(sql)
conn.commit()
cur.close()

print "Generating minimap..."

print "Width: " + str(width);
print "Height: " + str(height);

tilesTile = init2Dlist(width,height)
tilesIndex = init2Dlist(width,height)

resTile = init2Dlist(width,height)
resIndex = init2Dlist(width,height)

for i in range(width):
    l = []
    for j in range(height):
        l.append(0);
    tilesIndex.append(l)

t = []

# get tile data from map
for i in range(width):
    for j in range(height):
        tile = Bytes2Int2(b.read(2))
        index = Bytes2Int1(b.read(1))
        if index == 255:
            index = (i % 4 + ( j % 4 ) * 4)
        tilesTile[i][j] = tile
        tilesIndex[i][j] = index
        # get all different types
        f = 0
        for z in range(len(t)):
            if t[z] == tile:
                f = 1
        if f == 0:
            t.append(tile)

# get res data from map
for i in range(width):
    for j in range(height):
        tile = Bytes2Int1(b.read(1))
        index = Bytes2Int1(b.read(1))
        if index == 255:
            index = (i % 4 + ( j % 4 ) * 4)
        resTile[i][j] = tile
        resIndex[i][j] = index
        # get all different types
        f = 0
        for z in range(len(t)):
            if t[z] == tile:
                f = 1
        if f == 0:
            t.append(tile)

# storing terrain types
class terrainType:
    type = ""
    r = 0
    g = 0
    b = 0
    def __init__(self,type,r,g,b):
        self.type = type
        self.r = r
        self.g = g
        self.b = b

# storing templates
class template:
    id = -1
    list = []
    def __init__(self,id,list):
        self.id = id
        self.list = list

class templateItem:
    id = -1
    type = ""
    def __init__(self,id,type):
        self.id = id
        self.type = type

#Setup
terrTypes = []
templates = []

tempType = ""
tempR = -1
tempG = -1
tempB = -1

tempID = -1
tempList = []

# load template file and the data

file = open(os.path.realpath(os.path.dirname(sys.argv[0]))+os.sep+MapMod+os.sep+MapTileset+".yaml")
while 1:
    line = file.readline()
    if not line:
        break
    line = tabFixer(line)
    line = strFixer(line)
    line = tabFixer(line)
    line = strFixer(line)
    if line[0:11] == "TerrainType":
        tempType = line[12:(len(line)-2)]
    if line[0:5] == "Color":
        s = strFixer(line[6:]);
        strR = s[0:s.find(",")];
        tempR = int(strR)
        s = strFixer(s[s.find(",")+1:])
        strG = s[0:s.find(",")]
        tempG = int(strG)
        s = strFixer(s[s.find(",")+1:])
        strB = s;
        tempB = int(s)
        terrTypes.append( terrainType(tempType,tempR,tempG,tempB) )
    if line[0:8] == "Template":
        if len(tempList) > 0:
            templates.append(template(tempID,tempList))
            tempList = []
        tempID = line[9:(len(line)-2)]
    if ((line[0:1] == "0") or (line[0:1] == "1") or (line[0:1] == "2") or (line[0:1] == "3") or (line[0:1] == "4") or (line[0:1] == "5") or (line[0:1] == "6") or (line[0:1] == "7") or (line[0:1] == "8") or (line[0:1] == "9")):
        tempList.append( templateItem(int(line[0:line.find(":")]) , strFixer(line[line.find(":")+1:len(line)-1])) )

# still one to fix
if len(tempList) > 0:
    templates.append(template(tempID,tempList))

class resourceType:
    type = 0
    terrType = ""
    def __init__(self,type,terrType):
        self.terrType = terrType
        self.type = type

resTypes = []

tempType = ""
tempTerrType = ""
file = open(os.path.realpath(os.path.dirname(sys.argv[0]))+os.sep+MapMod+os.sep+"system.yaml")
while 1:
    line = file.readline()
    if not line:
        break
    line = tabFixer(line)
    line = strFixer(line)
    line = tabFixer(line)
    line = strFixer(line)
    if line[0:12] == "ResourceType":
        if not line[0:13] == "ResourceType@":
            if not tempType == "":
                if tempTerrType == "":
                    tempTerrType = "Ore"
                resTypes.append( resourceType(int(tempType),tempTerrType) )
                print "resType: " + tempType + " terrType: " + tempTerrType
                tempTerrType = ""
            tempType = strFixer(line[line.find(":")+1:])
    if line[0:11] == "TerrainType":
        if strFixer(line[12:]).find(" ") < 0:
            tempTerrType = line[12:].strip()
#One left to fix probably
if not tempType == "":
    if tempTerrType == "":
        tempTerrType = "Ore"
    resTypes.append( resourceType(int(tempType),tempTerrType ) )
    print "resType: " + tempType + " terrType: " + tempTerrType

def isdivideable(n):
    r = n / 2.0
    print "n: " + str(n)
    print type(n)
    print "r: " + str(r)
    print type(r)
    if(r%1 == 0):
        return True
    return False

while not isdivideable(Right):
    Right = Right + 1;

while not isdivideable(Bottom):
    Bottom = Bottom + 1;

#Draw map
img = bmp.BitMap(Right,Bottom,bmp.Color(0,0,0));
for x in range(Left,Right+Left):
    for y in range(Top,Bottom+Top):
        color = bmp.Color(0,0,0);
        d1 = 0
        if tilesTile[x][y] == 510:
            tilesTile[x][y] = 255 #Change 510 to clear (should probably never happen hint: byte size 255 function in .net line:130)
        for i in range(len(templates)):
            if int(templates[i].id) == tilesTile[x][y]:
                index = tilesIndex[x][y]
                c = ""
                for j in range(len(templates[i].list)):
                    if templates[i].list[j].id == index:
                        c = templates[i].list[j].type
                        break;
                if c == "":
                    c = c = templates[i].list[0].type
                    # accually error but we save it for now
                for j in range(len(terrTypes)):
                    if terrTypes[j].type == c:
                        color = bmp.Color(terrTypes[j].r,terrTypes[j].g,terrTypes[j].b)
                        d1 = 1
                        break;
                if d1 == 1:
                    break;
        d2 = 0
        for i in range(len(resTypes)):
            if resTypes[i].type == resTile[x][y]:
                for j in range(len(terrTypes)):
                    if terrTypes[j].type == resTypes[i].terrType:
                        color = bmp.Color(terrTypes[j].r,terrTypes[j].g,terrTypes[j].b)
                        d2 = 1
                        break;
            if d2 == 1:
                break
        d = 0
        if(d1 == 0 and d2 == 0):
            #nothing was found at all use 255 = clear to fill gap
            for i in range(len(templates)):
                if int(templates[i].id) == 255:
                    index = tilesIndex[x][y]
                    c = ""
                    for j in range(len(templates[i].list)):
                        if templates[i].list[j].id == index:
                            c = templates[i].list[j].type
                            break;
                    if c == "":
                        c = c = templates[i].list[0].type
                        # accually error but we save it for now
                    for j in range(len(terrTypes)):
                        if terrTypes[j].type == c:
                            color = bmp.Color(terrTypes[j].r,terrTypes[j].g,terrTypes[j].b)
                            d = 1
                            break;
                    if d == 1:
                        break;
        img.setPenColor(color);
        img.plotPoint(x-Left,y-Top);

img.saveFile(path + "minimap.bmp");
