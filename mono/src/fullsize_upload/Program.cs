using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Drawing;
using System.Windows.Forms;
using System.Drawing.Imaging;
using OpenRA.FileFormats;
using OpenRA.Graphics;
using OpenRA.Traits;
using OpenRA;
using System.Drawing.Imaging;
using System.IO;
using System.Reflection;

namespace ImageMapGenerator
{
    class ActorTemplate
    {
        public Bitmap Bitmap;
        public ActorInfo Info;
        public EditorAppearanceInfo Appearance;
    }

    class ResourceTemplate
    {
        public Bitmap Bitmap;
        public ResourceTypeInfo Info;
        public int Value;
    }

    static class ActorReferenceExts
    {
        public static int2 Location(this ActorReference ar)
        {
            return ar.InitDict.Get<LocationInit>().value;
        }

        public static void DrawStringContrast(this Graphics g, Font f, string s, int x, int y, Brush fg, Brush bg)
        {
            g.DrawString(s, f, bg, x - 1, y - 1);
            g.DrawString(s, f, bg, x + 1, y - 1);
            g.DrawString(s, f, bg, x - 1, y + 1);
            g.DrawString(s, f, bg, x + 1, y + 1);

            g.DrawString(s, f, fg, x, y);
        }
    }

    class Program
    {


        static void Main(string[] args)
        {
            //string path = System.IO.Path.GetDirectoryName(System.Reflection.Assembly.GetEntryAssembly().Location);
            //FileSystem.Mount(path + System.IO.Path.DirectorySeparatorChar + "temperat");

            string filename = "";
            OptionSet o = new OptionSet()
                .Add("filename=|f=", v => filename = v);
            o.Parse(args);
            string output = System.IO.Path.GetDirectoryName(filename) + System.IO.Path.DirectorySeparatorChar + "fullPreview.bmp";

            Directory.SetCurrentDirectory(Application.ExecutablePath);

            Dictionary<int2, Bitmap> Chunks = new Dictionary<int2, Bitmap>();
            Dictionary<int, ResourceTemplate> ResourceTemplates = new Dictionary<int, ResourceTemplate>();
            Dictionary<string, ActorTemplate> ActorTemplates = new Dictionary<string, ActorTemplate>();

            if(filename != "" && System.IO.File.Exists(filename))
            {
                Map map = new Map(filename);
                string mod = map.RequiresMod;
                if (map.RequiresMod == null || map.RequiresMod == "")
                {
                    mod = "ra";
                }
                Game.modData = new ModData(mod);
                FileSystem.LoadFromManifest(Game.modData.Manifest);
                Manifest manifest = Game.modData.Manifest;

                Rules.LoadRules(manifest, map);
                TileSet tileset = Rules.TileSets[map.Tileset];
                tileset.LoadTiles();

                Palette palette = new Palette(FileSystem.Open(tileset.Palette), true);
            

                var resourceTemplates = new List<ResourceTemplate>();
                foreach (var a in Rules.Info["world"].Traits.WithInterface<ResourceTypeInfo>())
                {
                    try
                    {
                        var template = RenderResourceType(a, tileset.Extensions, palette);
                        resourceTemplates.Add(template);
                    }
                    catch { }
                }
                ResourceTemplates = resourceTemplates.ToDictionary(a => a.Info.ResourceType);

                var actorTemplates = new List<ActorTemplate>();
                foreach (var a in Rules.Info.Keys)
                {
                    try
                    {
                        var info = Rules.Info[a];
                        if (!info.Traits.Contains<RenderSimpleInfo>()) continue;
                        var template = RenderActor(info, tileset, palette);
                        actorTemplates.Add(template);
                    }
                    catch { }
                }
                ActorTemplates = actorTemplates.ToDictionary(a => a.Info.Name.ToLowerInvariant());

                int ChunkSize = 8;

                int x1 = map.Bounds.Right - map.Bounds.Left;
                int y1 = map.Bounds.Bottom - map.Bounds.Top;

                int w = x1 * tileset.TileSize;
                int h = y1 * tileset.TileSize;
                Bitmap full = new Bitmap(w,h);
                Graphics e = Graphics.FromImage(full);

                for (int x = map.Bounds.Left; x < map.Bounds.Right; x += ChunkSize)
                {
                    for (int y = map.Bounds.Top; y < map.Bounds.Bottom; y += ChunkSize)
                    {
                        var x2 = new int2((x - map.Bounds.Left) / ChunkSize, (y - map.Bounds.Top) / ChunkSize);
                        if (!Chunks.ContainsKey(x2)) Chunks[x2] = RenderChunk(x / ChunkSize, y / ChunkSize, tileset, map, ResourceTemplates, palette);

                        Bitmap bmp = Chunks[x2];
                        if (bmp == null)
                            continue;

                        float DrawX = tileset.TileSize * (float)x2.X * ChunkSize;
                        float DrawY = tileset.TileSize * (float)x2.Y * ChunkSize;
                        RectangleF sourceRect = new RectangleF(0, 0, bmp.Width, bmp.Height);
                        RectangleF destRect = new RectangleF(DrawX, DrawY, bmp.Width, bmp.Height);

                        e.DrawImage(bmp, destRect, sourceRect, GraphicsUnit.Pixel);
                    }
                }

                foreach (var ar in map.Actors.Value)
                {
                    if (ActorTemplates.ContainsKey(ar.Value.Type))
                    {
                        int2 loc = ar.Value.Location();
                        loc.X -= map.Bounds.Left;
                        loc.Y -= map.Bounds.Top;
                        DrawActor(e, loc, ActorTemplates[ar.Value.Type],
                            GetPaletteForActor(ar.Value, palette, map), tileset);
                    }
                    else
                        Console.WriteLine("Warning: Unknown or excluded actor: {0}", ar.Value.Type);
                }

                e.Dispose();
                full.Save(output);
            }
        }

        public static ColorPalette GetPaletteForPlayerInner(string name, Palette palette, Map map)
        {
            var pr = map.Players[name];
            var pcpi = Rules.Info["player"].Traits.Get<PlayerColorPaletteInfo>();
            var remap = new PlayerColorRemap(pcpi.PaletteFormat, pr.ColorRamp);
            return new Palette(palette, remap).AsSystemPalette();
        }

        public static ColorPalette GetPaletteForActor(ActorReference ar, Palette palette, Map map)
        {
            var ownerInit = ar.InitDict.GetOrDefault<OwnerInit>();
            if (ownerInit == null)
                return null;

            return GetPaletteForPlayerInner(ownerInit.PlayerName, palette, map);
        }

        public static void DrawActor(Graphics g, int2 p, ActorTemplate t, ColorPalette cp, TileSet tileset)
        {
            var centered = t.Appearance == null || !t.Appearance.RelativeToTopLeft;
            DrawImage(g, t.Bitmap, p, centered, cp, tileset);
        }

        public static float2 GetDrawPosition(int2 location, Bitmap bmp, bool centered, TileSet tileset)
        {
            float OffsetX = centered ? bmp.Width / 2 - tileset.TileSize / 2 : 0;
            float DrawX = tileset.TileSize * location.X;

            float OffsetY = centered ? bmp.Height / 2 - tileset.TileSize / 2 : 0;
            float DrawY = tileset.TileSize * location.Y;

            return new float2(DrawX, DrawY);
        }

        public static void DrawImage(Graphics g, Bitmap bmp, int2 location, bool centered, ColorPalette cp, TileSet tileset)
        {
            var drawPos = GetDrawPosition(location, bmp, centered, tileset);

            var sourceRect = new RectangleF(0, 0, bmp.Width, bmp.Height);
            var destRect = new RectangleF(drawPos.X, drawPos.Y, bmp.Width, bmp.Height);

            var restorePalette = bmp.Palette;
            if (cp != null) bmp.Palette = cp;
            g.DrawImage(bmp, destRect, sourceRect, GraphicsUnit.Pixel);
            if (cp != null) bmp.Palette = restorePalette;
        }

        public static ActorTemplate RenderActor(ActorInfo info, TileSet tileset, Palette p)
        {
            var image = RenderSimple.GetImage(info);

            using (var s = FileSystem.OpenWithExts(image, tileset.Extensions))
            {
                var shp = new ShpReader(s);
                var bitmap = RenderShp(shp, p);

                try
                {
                    using (var s2 = FileSystem.OpenWithExts(image + "2", tileset.Extensions))
                    {
                        var shp2 = new ShpReader(s2);
                        var roofBitmap = RenderShp(shp2, p);

                        using (var g = System.Drawing.Graphics.FromImage(bitmap))
                            g.DrawImage(roofBitmap, 0, 0);
                    }
                }
                catch { }

                return new ActorTemplate
                {
                    Bitmap = bitmap,
                    Info = info,
                    Appearance = info.Traits.GetOrDefault<EditorAppearanceInfo>()
                };
            }
        }

        static Bitmap RenderShp(ShpReader shp, Palette p)
        {
            var frame = shp[0];

            var bitmap = new Bitmap(shp.Width, shp.Height, PixelFormat.Format8bppIndexed);

            bitmap.Palette = p.AsSystemPalette();

            var data = bitmap.LockBits(bitmap.Bounds(),
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
            return bitmap;
        }

        public static ResourceTemplate RenderResourceType(ResourceTypeInfo info, string[] exts, Palette p)
        {
            var image = info.SpriteNames[0];
            using (var s = FileSystem.OpenWithExts(image, exts))
            {
                var shp = new ShpReader(s);
                var frame = shp[shp.ImageCount - 1];

                var bitmap = new Bitmap(shp.Width, shp.Height, PixelFormat.Format8bppIndexed);
                bitmap.Palette = p.AsSystemPalette();
                var data = bitmap.LockBits(bitmap.Bounds(),
                    ImageLockMode.WriteOnly, PixelFormat.Format8bppIndexed);

                unsafe
                {
                    byte* q = (byte*)data.Scan0.ToPointer();
                    var stride = data.Stride;

                    for (var i = 0; i < shp.Width; i++)
                        for (var j = 0; j < shp.Height; j++)
                            q[j * stride + i] = frame.Image[i + shp.Width * j];
                }

                bitmap.UnlockBits(data);
                return new ResourceTemplate { Bitmap = bitmap, Info = info, Value = shp.ImageCount - 1 };
            }
        }

        static Bitmap RenderChunk(int u, int v, TileSet tileset, Map map, Dictionary<int, ResourceTemplate> ResourceTemplates, Palette palette)
		{
            int ChunkSize = 8;
            //if (u >= 74 || v >= 74)
            //    return null;
            var bitmap = new Bitmap((int)ChunkSize * tileset.TileSize,(int) ChunkSize * tileset.TileSize);

            if (bitmap == null)
                return null;

			var data = bitmap.LockBits(bitmap.Bounds(),
				ImageLockMode.WriteOnly, PixelFormat.Format32bppArgb);

			unsafe
			{
				int* p = (int*)data.Scan0.ToPointer();
				var stride = data.Stride >> 2;

				for (var i = 0; i < ChunkSize; i++)
					for (var j = 0; j < ChunkSize; j++)
					{
                        if (v * ChunkSize + j >= 128)
                            continue;
                        if (u * ChunkSize + i >= 128)
                            continue;

                        var tr = map.MapTiles.Value[u * ChunkSize + i, v * ChunkSize + j];
                        var tile = tileset.Templates[tr.type].Data;
						var index = (tr.index < tile.TileBitmapBytes.Count) ? tr.index : (byte)0;
						var rawImage = tile.TileBitmapBytes[index];
                        for (var x = 0; x < tileset.TileSize; x++)
                            for (var y = 0; y < tileset.TileSize; y++)
                                p[(j * tileset.TileSize + y) * stride + i * tileset.TileSize + x] = palette.GetColor(rawImage[x + tileset.TileSize * y]).ToArgb();

                        if (map.MapResources.Value[u * ChunkSize + i, v * ChunkSize + j].type != 0)
						{
                            var resourceImage = ResourceTemplates[map.MapResources.Value[u * ChunkSize + i, v * ChunkSize + j].type].Bitmap;
							var srcdata = resourceImage.LockBits(resourceImage.Bounds(),
								ImageLockMode.ReadOnly, PixelFormat.Format32bppArgb);

							int* q = (int*)srcdata.Scan0.ToPointer();
							var srcstride = srcdata.Stride >> 2;

                            for (var x = 0; x < tileset.TileSize; x++)
                                for (var y = 0; y < tileset.TileSize; y++)
								{
									var c = q[y * srcstride + x];
									if ((c & 0xff000000) != 0)	/* quick & dirty, i cbf doing real alpha */
                                        p[(j * tileset.TileSize + y) * stride + i * tileset.TileSize + x] = c;
								}

							resourceImage.UnlockBits(srcdata);
						}
					}
			}

            bitmap.UnlockBits(data);

            /*if (ShowGrid)
                using (var g = SGraphics.FromImage(bitmap))
                {
                    var rect = new Rectangle(0, 0, bitmap.Width, bitmap.Height);
                    ControlPaint.DrawGrid(g, rect, new Size(2, Game.CellSize), Color.DarkRed);
                    ControlPaint.DrawGrid(g, rect, new Size(Game.CellSize, 2), Color.DarkRed);
                    ControlPaint.DrawGrid(g, rect, new Size(Game.CellSize, Game.CellSize), Color.Red);
                }*/

            return bitmap;
        }
    }
}
