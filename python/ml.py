import sys;
import zipfile;
import string;
import struct;
import io;
import bmp;

# Check if path exist
path = ""
file = ""
for arg in sys.argv:
	if not arg == "ml.py":
		if not path == "":
			file = arg
		if path == "":
			path = arg
if path == "":
	print "Error: Need path to map"
        exit()
if file == "":
	print "Error: Need name of map"
	exit()
if not os.path.isfile(path + file):
        print "Error: File dose not exist"
        exit()
yamlData = "";
bin = "";

print path + file

z = zipfile.ZipFile(path + file, mode='a')

for filename in z.namelist():
	#print filename
	bytes = z.read(filename)
	#print len(bytes)
	if filename == "map.yaml":
		yamlData = bytes.decode("utf-8")
	if filename == "map.bin":
		bin = bytes;

yamlTemp = yamlData;

MapMod = "";
MapTitle = "";
MapAuthor = "";
MapTileset = "";
MapType = "";

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
def strFixer(str):
	f = 0;
	st = "";
	for c in str:
		if not c == " " or f == 1:
			f = 1;
			st = st + c;
	return st;
	
def tabFixer(str):
	f = 0;
	st = "";
	for c in str:
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
		MapTitle = strFixer(line[6:]);
	if line[0:11] == "RequiresMod":
		MapMod = strFixer(line[12:]);
	if line[0:6] == "Author":
		MapAuthor = strFixer(line[7:]);
	if line[0:7] == "Tileset":
		MapTileset = strFixer(line[8:]);
	if line[0:4] == "Type":
		MapType = strFixer(line[5:]);

#Generate info file
print "Creating info file..."
text_file = open(path + "info.txt", "w")
lines = [MapTitle+"\n",MapMod+"\n",MapAuthor+"\n",MapTileset+"\n",MapType+"\n"]
text_file.writelines(lines)
text_file.close()

#Check so everything is ok before generating a minimap
b = io.BytesIO(bin);
if not Bytes2Int1(b.read(1)) == 1:
	print "Error: Unknown map format"
	exit()

formatOK = 0
if MapMod.lower() == "ra":
	if MapTileset.lower() == "temperat":
		formatOK = 1
	if MapTileset.lower() == "snow":
		formatOK = 1
	if MapTileset.lower() == "interior":
		formatOK = 1
if MapMod.lower() == "cnc":
	if MapTileset.lower() == "desert":
		formatOK = 1
	if MapTileset.lower() == "temperat":
		formatOK = 1
	if MapTileset.lower() == "winter":
		formatOK = 1
if formatOK == 0:
	print "Error: Unknown mod"
	exit()

print "Generating minimap..."

width = Bytes2Int2(b.read(2));
height = Bytes2Int2(b.read(2));

tilesTile = init2Dlist(width,height)
tilesIndex = init2Dlist(width,height)

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
		#print "tile: " + str(tile) #Shows that it loads tile correctly
		#print "index: " + str(index) #Shows that it loads indexs correctly
		# get all different types
		f = 0
		for z in range(len(t)):
			if t[z] == tile:
				f = 1
		if f == 0:
			t.append(tile)
	
# storing terrain types
class terrType:
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
file = open(MapMod+"/"+MapTileset+".yaml")
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
		str = strFixer(line[6:]);
		strR = str[0:str.find(",")];
		tempR = int(strR)
		str = strFixer(str[str.find(",")+1:])
		strG = str[0:str.find(",")]
		tempG = int(strG)
		str = strFixer(str[str.find(",")+1:])
		strB = str;
		tempB = int(str)
		terrTypes.append( terrType(tempType,tempR,tempG,tempB) )
	if line[0:8] == "Template":
		if len(tempList) > 0:
			templates.append(template(tempID,tempList))
			tempList = []
		tempID = line[9:(len(line)-2)]
	if ((line[0:1] == "0") or (line[0:1] == "1") or (line[0:1] == "2") or (line[0:1] == "3") or (line[0:1] == "4") or (line[0:1] == "5") or (line[0:1] == "6") or (line[0:1] == "7") or (line[0:1] == "8") or (line[0:1] == "9")):
		tempList.append(strFixer(line[line.find(":")+1:len(line)-1]))

# still one to fix
if len(tempList) > 0:
	templates.append(template(tempID,tempList))

#Draw map
img = bmp.BitMap(width,height);
for x in range(width):
	for y in range(height):
		color = bmp.Color(0,0,0);
		d = 0
		if tilesTile[x][y] > 255:
			tilesTile[x][y] = 255 #Change 510 to clear (should probably never happen hint: byte size 255 function in .net line:130)
		for i in range(len(templates)):
			if int(templates[i].id) == tilesTile[x][y]:
				index = tilesIndex[x][y]
				if index > len(templates[i].list)-1:
					index = len(templates[i].list)-1
					#Something is wrong out of bounds (we save it though for now)
				c = templates[i].list[index];
				for j in range(len(terrTypes)):
					if terrTypes[j].type == c:
						color = bmp.Color(terrTypes[j].r,terrTypes[j].g,terrTypes[j].b)
						d = 1
						break;
				if d == 1:
					break;
		img.setPenColor(color);
		img.plotPoint(x,y);

img.saveFile(path + "minimap.bmp");
