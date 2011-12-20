import sys;
import string;
import struct;
import io;
import bmp;
import os;

# 1 byte
def Bytes2Int1(data):
    return struct.unpack('B', data[0])[0]

# 2 bytes
def Bytes2Int2(data):
    return struct.unpack('B', data[0])[0] + struct.unpack('B', data[1])[0]

# 4 bytes
def Bytes2Int4(data):
    return struct.unpack('B', data[0])[0] + struct.unpack('B', data[1])[0] + struct.unpack('B', data[2])[0] + struct.unpack('B', data[3])[0]

def init2Dlist(w,h):
    ll = []
    for i in range(w):
        l = []
        for j in range(h):
            l.append(0);
        ll.append(l)
    return ll

class ImageHeader:
	Offset = 0;
	RefOffset = 0;
	Format = 0;
	RefFormat = 0;
	RefImage = None;
	Image = [];
	SizeOnDisk = 8;
	def __init__(self, bytes):
		self.Offset = Bytes2Int4(bytes)
		self.Format = self.Offset;
		#Offset >> 24;
		self.Offset = 16777215;
		self.RefOffset = Bytes2Int4(bytes)
		self.RefFormat = Bytes2Int4(bytes)
		self.RefImage = None;

class Format80:
	@staticmethod
	def DecodeInto(src, dest):
		var ctx = new FastByteReader(src);
			int destIndex = 0;
			while( true ):
				byte i = ctx.ReadByte();
				if( ( i & 0x80 ) == 0 ):
					#case 2
					byte secondByte = ctx.ReadByte();
					int count = ( ( i & 0x70 ) >> 4 ) + 3;
					int rpos = ( ( i & 0xf ) << 8 ) + secondByte;
					ReplicatePrevious( dest, destIndex, destIndex - rpos, count );
					destIndex += count;
				elif ( ( i & 0x40 ) == 0 ):
					#case 1
					int count = i & 0x3F;
					if( count == 0 )
						return destIndex;
					ctx.CopyTo( dest, destIndex, count );
					destIndex += count;
				else:
					int count3 = i & 0x3F;
					if count3 == 0x3E:
						#case 4
						int count = ctx.ReadWord();
						byte color = ctx.ReadByte();
						for( int end = destIndex + count ; destIndex < end ; destIndex++ )
							dest[ destIndex ] = color;
					elif count3 == 0x3F:
						#case 5
						int count = ctx.ReadWord();
						int srcIndex = ctx.ReadWord();
						if srcIndex >= destIndex:
							throw new NotImplementedException( string.Format( "srcIndex >= destIndex  {0}  {1}", srcIndex, destIndex ) );
						for( int end = destIndex + count ; destIndex < end ; destIndex++ )
							dest[ destIndex ] = dest[ srcIndex++ ];
					else:
						#case 3
						int count = count3 + 3;
						int srcIndex = ctx.ReadWord();
						if srcIndex >= destIndex:
							throw new NotImplementedException( string.Format( "srcIndex >= destIndex  {0}  {1}", srcIndex, destIndex ) );
						for( int end = destIndex + count ; destIndex < end ; destIndex++ )
							dest[ destIndex ] = dest[ srcIndex++ ];

class SHPReader:
	recurseDepth = 0;
	headers = [];
	Width = 0;
	Height = 0;
	ImageCount = 0;
	def CopyImageData(self, baseImage)
		imageData = [self.Width * self.Height];
		for i = range(Width * Height)
			imageData[i] = baseImage[i];
		return imageData;
	def Decompress(self, stream, h):
		if self.recurseDepth > self.ImageCount:
			print "Format20/40 headers contain infinite loop";
		else:
			if h.Format == 32 or h.Format == 64:
				if h.RefImage != 0:
					if h.RefImage != None and h.RefImage.Image == None:
						print "rec"
						self.recurseDepth = self.recurseDepth + 1;
						self.Decompress( stream, h.RefImage );
						self.recurseDepth = self.recurseDepth - 1;
				h.Image = CopyImageData( h.RefImage.Image );
				#Format40.DecodeInto(ReadCompressedData(stream, h), h.Image);
				print "32 or 64"
			elif h.Format == 128:
				imageBytes = [ Width * Height ];
				#Format80.DecodeInto( ReadCompressedData( stream, h ), imageBytes );
				h.Image = imageBytes;
				print "128"
			else:
				print "invalid data - " + str(h.Format);
	def __init__(self, bytes):
		self.ImageCount = Bytes2Int4(bytes)
		Bytes2Int4(bytes)
		Bytes2Int4(bytes)
		self.Width = Bytes2Int4(bytes)
		self.Height = Bytes2Int4(bytes)
		Bytes2Int4(bytes)
		print "ImageCount: " + str(self.ImageCount)
		print "Width: " + str(self.Width)
		print "Height: " + str(self.Height)
		for r in range(self.ImageCount):
			self.headers.append( ImageHeader(bytes) );
		offsets = init2Dlist(self.ImageCount,2);
		for i in range(len(self.headers)):
			offsets[i][0] = self.headers[i].Offset
			offsets[i][1] = self.headers[i]
		# 20 = 32, 40 = 64, 80 = 128
		for i in range(self.ImageCount):
			h = self.headers[i];
			#32
			if h.Format == 32:
				h.RefImage = self.headers[i-1];
			#else if h.Format == 64:
			#	if !offsets.TryGetValue( h.RefOffset, out h.RefImage ):
			#		throw new InvalidDataException( string.Format( "Reference doesnt point to image data {0}->{1}", h.Offset, h.RefOffset ) );
		for i in range(len(self.headers)):
			print self.headers[i]
			self.Decompress(bytes, self.headers[i]);
			print self.headers[i]
			
bytes = io.open("test.shp","rb").read()
reader = SHPReader(bytes);
