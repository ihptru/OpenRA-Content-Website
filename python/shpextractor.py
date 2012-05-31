# shp extractor; temporary uses OpenRA.Utility to get a PNG from SHP

from PIL import Image
import getopt
import os
import subprocess
import sys
import struct

import images2gif
import config

WEBSITE_PATH = os.getcwd() + os.sep

# -s <source shp> -p <palette>

try:
    optlist,  args = getopt.getopt(sys.argv[1:], 's:p:')
except getopt.GetoptError, err:
    print err
    exit()

if optlist == []:
    print "Incorrect options"
    exit(2)

for  i in range(len(optlist)):
    if optlist[i][0] == "-s":
        source_shp = optlist[i][1]
    if optlist[i][0] == "-p":
        palette = optlist[i][1]

# get PNG from SHP using OpenRA.Utility
subprocess.Popen(["mono", config.openra_path+"OpenRA.Utility.exe", "--png", source_shp, WEBSITE_PATH+"python/palette/"+palette]).wait()
print("created png form shp")

# 2 bytes
def Bytes2Int2(data):
    _byte1 = struct.unpack('B', data[0])[0]
    _byte2 = struct.unpack('B', data[1])[0]
    _byte2 = _byte2 * 256
    return _byte1 + _byte2

# get amount of frames from SHP
bin = open(source_shp, "r")
frames = Bytes2Int2(bin.read(2))
    
# get a full path to PNG
path_to_shp = os.path.dirname(source_shp)+os.sep
img_path = path_to_shp + os.path.basename(source_shp).split('.shp')[0] + ".png"

# crop PNG into frames
im = Image.open(img_path)
size = im.size

area = []
current_pos = 0
for frame in range(frames):
    # coords: left, bottom, right, top
    box = (current_pos, 0, current_pos+size[0]/frames, size[1])
    copy_im = im.copy()
    area.append(copy_im.crop(box))
    current_pos = current_pos + size[0]/frames

images2gif.writeGif(path_to_shp+"preview.gif", area, duration=0.5, loops=0, dither=0)
