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
            int frameIndex = 0;
            string filename = "";
            int uid = 0;
            OptionSet o = new OptionSet()
                .Add ("filename=|f=", v => filename = v)
                .Add("u=|uid=", v => uid = Convert.ToInt32(v))
                .Add("frame=", v => frameIndex = Convert.ToInt32(v));
            o.Parse(args);

            string file = System.IO.Path.GetFileNameWithoutExtension(filename);
            string path = System.IO.Path.GetDirectoryName(filename);

            Console.WriteLine("Filename: " + filename);
            Console.WriteLine("UID: " + uid.ToString());
            Console.WriteLine("Frame: " + frameIndex.ToString());

            Console.WriteLine("File: " + file);
            Console.WriteLine("Path: " + path);
            Console.WriteLine("Output: " + path + System.IO.Path.DirectorySeparatorChar + "preview.bmp");

            var image = file + ".shp";
            FileSystem.Mount(path);
            FileSystem.Mount(".");
            using (var s = FileSystem.Open(image))
            {
                var shp = new ShpReader(s);
                var palette = new Palette(FileSystem.Open("temperat.pal"), true);

                if (frameIndex > shp.ImageCount)
                    frameIndex = shp.ImageCount;

                var frame = shp[frameIndex];
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
                bitmap.Save(path + System.IO.Path.DirectorySeparatorChar + "preview.bmp");
            }
        }
    }
}
