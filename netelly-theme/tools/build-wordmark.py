"""Outline "NETELLY" in Archivo (wdth 125, wght 600, letter-spacing .02em) to an SVG path.

The site logo (client decision, replaces the supplied italic wordmark). Re-generate with:
  pip install fonttools brotli uharfbuzz
  python3 tools/build-wordmark.py assets/fonts/archivo-400_700-latin.woff2 assets/images/netelly-wordmark.min.svg
  cp assets/images/netelly-wordmark.min.svg assets/netelly-wordmark.svg
The viewBox is the cap height (no overshoot), so CSS height = letter height.
"""
import sys, io
from fontTools.ttLib import TTFont
from fontTools.varLib.instancer import instantiateVariableFont
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.pens.boundsPen import BoundsPen
import uharfbuzz as hb
src, out, text = sys.argv[1], sys.argv[2], 'NETELLY'
f = TTFont(src); f.flavor = None
buf = io.BytesIO(); f.save(buf); data = buf.getvalue()
# Shape with HarfBuzz at the same axis values the browser uses (kerning included).
face = hb.Face(data); font = hb.Font(face); font.set_variations({'wdth': 125, 'wght': 600})
b = hb.Buffer(); b.add_str(text); b.guess_segment_properties(); hb.shape(font, b, {'kern': True})
inst = instantiateVariableFont(TTFont(io.BytesIO(data)), {'wdth': 125, 'wght': 600})
gs = inst.getGlyphSet(); upem = inst['head'].unitsPerEm; order = inst.getGlyphOrder()
ls = 0.02 * upem
pen = SVGPathPen(gs); bp = BoundsPen(gs); x = 0
for info, pos in zip(b.glyph_infos, b.glyph_positions):
    g = order[info.codepoint]
    t = (1, 0, 0, -1, x + pos.x_offset, 0)   # flip Y: font units up -> SVG down
    gs[g].draw(TransformPen(pen, t)); gs[g].draw(TransformPen(bp, t))
    x += pos.x_advance + ls
x0, y0, x1, y1 = bp.bounds
d = pen.getCommands()
svg = f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{x0:.0f} {y0:.0f} {x1-x0:.0f} {y1-y0:.0f}" fill="currentColor"><path d="{d}"/></svg>'
open(out, 'w').write(svg)
print('bounds', bp.bounds, 'ratio', round((x1-x0)/(y1-y0), 3), 'bytes', len(svg))
