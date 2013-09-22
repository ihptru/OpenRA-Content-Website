import zipfile
import string
import struct
import io
import os

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

class resourceType:
    type = 0
    terrType = ""
    def __init__(self,type,terrType):
        self.terrType = terrType
        self.type = type

class map:
    def __init__(self, filename):
        self.MapMod = ""
        self.MapTitle = ""
        self.MapAuthor = ""
        self.MapTileset = ""
        self.MapType = ""
        self.MapBounds = ""
        self.MapDesc = ""
        self.MapPlayers = 0
        self.MapDefaultRace = []
        self.UseAsShellmap = 0
        self.MapValidFormat = 1
        self.Left = 0
        self.Right = 0
        self.Bottom = 0
        self.Top = 0
        self.width = 0
        self.height = 0
        
        self.terrTypes = []
        self.templates = []
        self.tilesTile = None
        self.tilesIndex = None
        self.resTile = None
        self.resIndex = None
        
        self.raw_yamlData = []
        self.bin = []
        
        self.load(filename)
    def Bytes2Int1(self,data):
        return struct.unpack('B', data[0])[0]
    def Bytes2Int2(self,data):
        _byte1 = struct.unpack('B', data[0])[0]
        _byte2 = struct.unpack('B', data[1])[0]
        _byte2 = _byte2 * 256
        return _byte1 + _byte2
    def strFixer(self,s):
        f = 0;
        st = ""
        for c in s:
            if not c == " " or f == 1:
                f = 1
                st = st + c
        return st
    def tabFixer(self,s):
        f = 0
        st = ""
        for c in s:
            if not c == '\t' or f == 1:
                f = 1
                st = st + c
        return st
    def init2Dlist(self,w,h):
        ll = []
        for i in range(w):
            l = []
            for j in range(h):
                l.append(0)
            ll.append(l)
        return ll
    def isdivideable(self,n):
            r = n / 2.0
            if(r%1 == 0):
                return True
            return False
    def getInfo(self):
        return [self.MapTitle+"\n",self.MapMod+"\n",self.MapAuthor+"\n",self.MapTileset+"\n",self.MapType+"\n",self.MapDesc+"\n",str(self.MapPlayers)+"\n"]
    def load(self,filename):
        f2 = filename
        _PATH = os.path.dirname(os.path.realpath(__file__)) + os.sep
        z = zipfile.ZipFile(filename, mode='a')
        yamlData = ""
        bin = ""
        for filename in z.namelist():
            bytes = z.read(filename)
            if filename == "map.yaml":
                self.raw_yamlData = bytes
                yamlData = bytes.decode("utf-8")
            if filename == "map.bin":
                self.bin = bytes
        filename = f2
        #Load basic map info
        for line in string.split(yamlData, '\n'):
            if line[0:5] == "Title":
                self.MapTitle = line[6:].strip().replace("'", "''")
            if line[0:11] == "RequiresMod":
                self.MapMod = line[12:].strip().lower()
            if line[0:6] == "Author":
                self.MapAuthor = line[7:].strip().replace("'", "''")
            if line[0:7] == "Tileset":
                self.MapTileset = line[8:].strip().lower()
            if line[0:4] == "Type":
                self.MapType = line[5:].strip()
            if line[0:11] == "Description":
                self.MapDesc = line[12:].strip().replace("'", "''")
            if line[0:6] == "Bounds":
                self.MapBounds = line[7:].strip()
            if line.strip()[0:8] == "Playable":
                state = line.split(':')[1]
                if state.strip().lower() in ['true', 'on', 'yes', 'y']:
                    self.MapPlayers += 1
            if line.strip()[0:5] == "Race:":
                self.MapDefaultRace.append(line.strip()[6:].lower())
            if line.strip()[0:13] == "UseAsShellmap":
                state = line.split(':')[1]
                if state.strip().lower() in ['true', 'on', 'yes', 'y']:
                    self.UseAsShellmap = 1
        
        #Take map bounds
        self.MapBounds = self.strFixer(self.MapBounds)
        self.Left = int(self.MapBounds[0:self.MapBounds.find(",")])
        self.MapBounds = self.MapBounds[self.MapBounds.find(",")+1:]
        self.Top = int(self.MapBounds[0:self.MapBounds.find(",")])
        self.MapBounds = self.MapBounds[self.MapBounds.find(",")+1:]
        self.Right = int(self.MapBounds[0:self.MapBounds.find(",")])
        self.MapBounds = self.MapBounds[self.MapBounds.find(",")+1:]
        self.Bottom = int(self.MapBounds)
        
        #Check so everything is ok before generating a minimap
        b = io.BytesIO(self.bin)
        if not self.Bytes2Int1(b.read(1)) == 1:
            print "Error: Unknown map format"
            self.MapValidFormat = 0
            return;
    
        #Check if tileset and mod is good
        formatOK = 0
        if self.MapMod == "ra":
            if self.MapTileset == "temperat":
                formatOK = 1
            if self.MapTileset == "snow":
                formatOK = 1
            if self.MapTileset == "interior":
                formatOK = 1
        if self.MapMod == "cnc":
            if self.MapTileset == "desert":
                formatOK = 1
            if self.MapTileset == "temperat":
                formatOK = 1
            if self.MapTileset == "winter":
                formatOK = 1
            if self.MapTileset == "snow":
                formatOK = 1
        if self.MapMod == "d2k":
            if self.MapTileset == "arrakis":
                formatOK = 1
        if self.MapMod == "":
            if self.MapTileset == "interior":
                formatOK = 1
                self.MapMod = "ra"
            if self.MapTileset == "desert":
                if "gdi" not in self.MapDefaultRace and "nod" not in self.MapDefaultRace:
                    self.MapMod = "ra"
                else:
                    self.MapMod = "cnc"
                formatOK = 1
            if self.MapTileset == "winter":
                formatOK = 1
                self.MapMod = "cnc"
            if self.MapTileset == "temperat":
                if "gdi" not in self.MapDefaultRace and "nod" not in self.MapDefaultRace:
                    self.MapMod = "ra"
                else:
                    self.MapMod = "cnc"
                formatOK = 1
            if self.MapTileset == "snow":
                if "gdi" not in self.MapDefaultRace and "nod" not in self.MapDefaultRace:
                    self.MapMod = "ra"
                else:
                    self.MapMod = "cnc"
                formatOK = 1
            if self.MapTileset == "arrakis":
                formatOK = 1
                self.MapMod = "d2k"
        if formatOK == 0:
            print "Error: Unknown mod"
            self.MapValidFormat = 0
            return;
        width = self.Bytes2Int2(b.read(2))
        height = self.Bytes2Int2(b.read(2))
        self.width = width
        self.height = height
        print "Path: " + filename
        if not os.path.isfile(filename):
            print "Error: file does not exist"
            self.MapValidFormat = 0
            return;
        
        self.tilesTile = self.init2Dlist(width,height)
        self.tilesIndex = self.init2Dlist(width,height)
        self.resTile = self.init2Dlist(width,height)
        self.resIndex = self.init2Dlist(width,height)
        
        for i in range(width):
            l = []
            for j in range(height):
                l.append(0);
            self.tilesIndex.append(l)
        t = []
        
        # get tile data from map
        for i in range(width):
            for j in range(height):
                tile = self.Bytes2Int2(b.read(2))
                index = self.Bytes2Int1(b.read(1))
                if index == 255:
                    index = (i % 4 + ( j % 4 ) * 4)
                self.tilesTile[i][j] = tile
                self.tilesIndex[i][j] = index
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
                tile = self.Bytes2Int1(b.read(1))
                index = self.Bytes2Int1(b.read(1))
                if index == 255:
                    index = (i % 4 + ( j % 4 ) * 4)
                self.resTile[i][j] = tile
                self.resIndex[i][j] = index
                # get all different types
                f = 0
                for z in range(len(t)):
                    if t[z] == tile:
                        f = 1
                if f == 0:
                    t.append(tile)
        
        #Setup
        tempType = ""
        tempR = -1
        tempG = -1
        tempB = -1
        
        tempID = -1
        tempList = []
    
        # load template file and the data
        file = open(_PATH+self.MapMod+os.sep+self.MapTileset+".yaml")
        while 1:
            line = file.readline()
            if not line:
                break
            line = self.tabFixer(line)
            line = self.strFixer(line)
            line = self.tabFixer(line)
            line = self.strFixer(line)
            if line[0:11] == "TerrainType":
                tempType = line[12:(len(line)-2)]
            if line[0:5] == "Color":
                s = self.strFixer(line[6:]);
                strR = s[0:s.find(",")];
                tempR = int(strR)
                s = self.strFixer(s[s.find(",")+1:])
                strG = s[0:s.find(",")]
                tempG = int(strG)
                s = self.strFixer(s[s.find(",")+1:])
                strB = s;
                if s.find(",") == -1:
                    tempB = int(s)
                else:
                    tempB = self.strFixer(s[s.find(",")+1:])
                self.terrTypes.append( terrainType(tempType,tempR,tempG,tempB) )
            if line[0:8] == "Template":
                if len(tempList) > 0:
                    self.templates.append(template(tempID,tempList))
                    tempList = []
                tempID = line[9:(len(line)-2)]
            if ((line[0:1] == "0") or (line[0:1] == "1") or (line[0:1] == "2") or (line[0:1] == "3") or (line[0:1] == "4") or (line[0:1] == "5") or (line[0:1] == "6") or (line[0:1] == "7") or (line[0:1] == "8") or (line[0:1] == "9")):
                tempList.append( templateItem(int(line[0:line.find(":")]) , self.strFixer(line[line.find(":")+1:len(line)-1])) )
        
        # still one to fix
        if len(tempList) > 0:
            self.templates.append(template(tempID,tempList))
        
        self.resTypes = []
        
        tempType = ""
        tempTerrType = ""
        isResourceTypeBlock = False
        file = open(_PATH+self.MapMod+os.sep+"system.yaml")
        while 1:
            line = file.readline()
            if not line:
                break
            line = self.tabFixer(line)
            line = self.strFixer(line)
            line = self.tabFixer(line)
            line = self.strFixer(line)
            if line[0:13] == "ResourceType@":
                isResourceTypeBlock = True
            if line[0:12] == "ResourceType":
                if not line[0:13] == "ResourceType@":
                    if not isResourceTypeBlock:
                        continue
                    if not tempType == "":
                        if tempTerrType == "":
                            tempTerrType = "Ore"
                        self.resTypes.append( resourceType(int(tempType),tempTerrType) )
                        print "resType: " + tempType + " terrType: " + tempTerrType
                        tempTerrType = ""
                    tempType = self.strFixer(line[line.find(":")+1:])
                    isResourceTypeBlock = False
            if line[0:11] == "TerrainType":
                if self.strFixer(line[12:]).find(" ") < 0:
                    tempTerrType = line[12:].strip()
        if not tempType == "":
            if tempTerrType == "":
                tempTerrType = "Ore"
            self.resTypes.append( resourceType(int(tempType),tempTerrType ) )
            print "resType: " + tempType + " terrType: " + tempTerrType
        while not self.isdivideable(self.Right):
            self.Right = self.Right + 1;
        while not self.isdivideable(self.Bottom):
            self.Bottom = self.Bottom + 1;
