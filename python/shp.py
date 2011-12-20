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
		#>> 24;
		self.Offset &= 0xFFFFFF;
		self.RefOffset = Bytes2Int4(bytes)
		self.RefFormat = Bytes2Int4(bytes)
		self.RefImage = None;

class Format40:
	@staticmethod
	def DecodeInto(src, dest):
			ctx = src;
			destIndex = 0;
			while( 1 == 1 ):
				print "Format40 - len: " + str(len(ctx));
				#print "Format40 - content: " + str(ctx)
				print "before"
				i = Bytes2Int1(ctx);
				print "after"
				if( ( i & 0x80 ) == 0 ):
					count = i & 0x7F;
					if( count == 0 ):
						#case 6
						count = Bytes2Int1(ctx);
						value = Bytes2Int1(ctx);
						end = destIndex + count
						start = end - destIndex
						for x in range(start,end):
							dest[ destIndex ] ^= value;
							destIndex = destIndex + 1;
					else:
						#case 5
						end = destIndex + count
						start = end - destIndex
						for x in range(start,end):
							dest[destIndex] ^= Bytes2Int1(ctx);
				else:
					count = i & 0x7F;
					if( count == 0 ):
						count = Bytes2Int4(ctx);
						if( count == 0 ):
							return destIndex;
						if( ( count & 0x8000 ) == 0 ):
							# case 2
							destIndex += ( count & 0x7FFF );
						elif( ( count & 0x4000 ) == 0 ):
							# case 3
							end = destIndex + (count & 0x3FFF );
							start = end - destIndex;
							for x in range(start,end):
								dest[destIndex] ^= Bytes2Int1(ctx);
								destIndex = destIndex + 1;
						else:
							# case 4
							value = Bytes2Int1(ctx);
							end = destIndex + (count & 0x3FFF );
							start = end - destIndex;
							for x in range(start,end):
								dest[ destIndex ] ^= value;
								destIndex = destIndex + 1;
					else:
						# case 1
						destIndex += count;

class Format80:
    @staticmethod
    def ReplicatePrevious(dest, destIndex, srcIndex, count ):
        if srcIndex > destIndex :
            print "srcIndex > destIndex " + str(srcIndex) + " " + str(destIndex );
            sys.exit();
        if destIndex - srcIndex == 1:
            for i in range(count):
                dest[ destIndex + i ] = dest[ destIndex - 1 ];
        else:
            for i in range(count):
                dest[ destIndex + i ] = dest[ srcIndex + i ];
    
	@staticmethod
	def DecodeInto(src, dest):
		ctx = src;
        destIndex = 0;
        while( 1 == 1 ):
            i = Bytes2Int1(ctx);
            if( ( i & 0x80 ) == 0 ):
                #case 2
                secondByte = Bytes2Int1(ctx);
                count = ( ( i & 0x70 ) >> 4 ) + 3;
                rpos = ( ( i & 0xf ) << 8 ) + secondByte;
                ReplicatePrevious( dest, destIndex, destIndex - rpos, count );
                destIndex += count;
            elif ( ( i & 0x40 ) == 0 ):
                #case 1
                count = i & 0x3F;
                if count == 0:
                    return destIndex;
                #ctx.CopyTo( dest, destIndex, count );
                destIndex += count;
            else:
                count3 = i & 0x3F;
                if count3 == 0x3E:
                    #case 4
                    count = Bytes2Int4(ctx);
                    color = Bytes2Int1(ctx);
                    end = destIndex + count
                    start = end - destIndex
                    for x in range(start,end):
                        dest[ destIndex ] = color;
                        destIndex = destIndex + 1;
                elif count3 == 0x3F:
                    #case 5
                    count = Bytes2Int4(ctx);
                    srcIndex = Bytes2Int4(ctx);
                    if srcIndex >= destIndex:
                        print "srcIndex >= destIndex }" + str(srcIndex) + " " + str(destIndex);
                        sys.exit();
                    end = destIndex + count;
                    start = end - destIndex;
                    for x in range(start,end):
                        dest[ destIndex ] = dest[ srcIndex ];
                        srcIndex = srcIndex + 1;
                        destIndex = destIndex + 1;
                else:
                    #case 3
                    count = count3 + 3;
                    srcIndex = Bytes2Int4(ctx);
                    if srcIndex >= destIndex:
                        print "srcIndex >= destIndex }" + str(srcIndex) + " " + str(destIndex);
                        sys.exit();
                    end = destIndex + count;
                    start = end - destIndex;
                    for x in range(start,end):
                        dest[ destIndex ] = dest[ srcIndex ];
                        srcIndex = srcIndex + 1;
                        destIndex = destIndex + 1;

class SHPReader:
	recurseDepth = 0;
	headers = [];
	Width = 0;
	Height = 0;
	ImageCount = 0;
	def CopyImageData(self, baseImage):
		if baseImage != None:
			imageData = [self.Width * self.Height];
			for i in range(self.Width * self.Height):
				imageData[i] = baseImage[i];
			return imageData;
		return None;
	def ReadCompressedData(self, stream, h):
		pos = h.Offset;
		compressedLength = len(stream) - pos;
		compressedBytes = [ compressedLength ];
		print "ReadCompressedData - compressedLength: " + str(compressedLength)
		print "ReadCompressedData - compressedBytes: " + str(len(compressedBytes))
		print "ReadCompressedData - pos: " + str(pos)
		compressedBytes.extend(stream[:compressedLength]);
		stream = stream[compressedLength:];
		return compressedBytes;
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
				if h.RefImage == None:
					h.Image = self.CopyImageData( None );
				else:
					h.Image = self.CopyImageData( h.RefImage.Image );
				Format40.DecodeInto(self.ReadCompressedData(stream, h), h.Image);
				print "32 or 64"
			elif h.Format == 128:
				imageBytes = [ Width * Height ];
				Format80.DecodeInto( self.ReadCompressedData( stream, h ), imageBytes );
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
