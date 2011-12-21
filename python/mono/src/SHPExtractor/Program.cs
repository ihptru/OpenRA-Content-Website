using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using OpenRA.FileFormats;
using OpenRA.Graphics;
using OpenRA.Traits;
using System.Drawing;
using System.Drawing.Imaging;

namespace SHPExtractor
{
    class Program
    {
        static void Main(string[] args)
        {
            var image = "test";
            string[] e = { ".shp" };
            FileSystem.Mount(".");
            using (var s = FileSystem.OpenWithExts(image, e))
            {
                var shp = new ShpReader(s);
                var palette = new Palette(FileSystem.Open("temperat.pal"), true);

				// assume that it has 32 sides
				// we want the SHP/Actor/Unit on a angle instead of facing north
				int frames = shp.ImageCount;
				while(frames > 32)
					frames -= 32;
				int frameToUse = (int)((float)frames / 100.0 * 37.5);

                var frame = shp[frameToUse];
                var bitmap = new Bitmap(shp.Width, shp.Height, PixelFormat.Format8bppIndexed);
                bitmap.Palette = palette.AsSystemPalette();
                var data = bitmap.LockBits(new Rectangle(0, 0, bitmap.Width, bitmap.Height),
                    ImageLockMode.WriteOnly, PixelFormat.Format8bppIndexed);

                unsafe
                {
                    byte* q = (byte*)data.Scan0.ToPointer();
                    var stride2 = data.Stride;

                    for (var i = 0; i < shp.Width; i++)
                        for (var j = 0; j < shp.Height; j++)
                            q[j * stride2 + i] = frame.Image[i + shp.Width * j];
                }

                bitmap.UnlockBits(data);
                bitmap.Save("preview.bmp");
            }
        }
    }
}
