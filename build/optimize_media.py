"""
Make web-ready copies of the site media (P0-A7). Run after adding or replacing a file:

    python3 build/optimize_media.py                # WebP next to every .jpg/.png in public/assets/img/
    python3 build/optimize_media.py --video FFMPEG # also re-encode the hero video (needs an ffmpeg binary)
    python3 build/optimize_media.py --zh-font TTF  # rebuild the Chinese font subset (needs fonttools + brotli)

Needs Pillow with WebP support. A WebP is only rewritten when its source is newer.
The video source is build/media/hero-lab-source.mp4 (kept out of public/); output is
public/assets/video/hero-lab.mp4 (H.264) and hero-lab.webm (VP9), both silent and at most 2 MB.

The Chinese font is Noto Sans SC (SIL OFL), variable TTF from
https://github.com/google/fonts/raw/main/ofl/notosanssc/NotoSansSC%5Bwght%5D.ttf
It is cut to weights 400-600 and to the characters on the built public/zh/ pages, so run
build.py first, then this, then build.py again. Characters added later fall back to the system font.
"""
import glob
import os
import re
import subprocess
import sys

from PIL import Image

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
IMG = os.path.join(ROOT, "public", "assets", "img")
VIDEO_SRC = os.path.join(ROOT, "build", "media", "hero-lab-source.mp4")
VIDEO_OUT = os.path.join(ROOT, "public", "assets", "video", "hero-lab")
VIDEO_MAX = 2 * 1024 * 1024


def webp():
    made = 0
    for src in sorted(glob.glob(os.path.join(IMG, "**", "*.*"), recursive=True)):
        if os.path.splitext(src)[1].lower() not in (".jpg", ".jpeg", ".png"):
            continue
        out = os.path.splitext(src)[0] + ".webp"
        if os.path.exists(out) and os.path.getmtime(out) >= os.path.getmtime(src):
            continue
        im = Image.open(src)
        if im.mode not in ("RGB", "RGBA"):
            im = im.convert("RGBA" if "transparency" in im.info else "RGB")
        im.save(out, "WEBP", quality=80, method=6)
        made += 1
        print("{}  {} KB -> {} KB".format(os.path.relpath(out, ROOT), os.path.getsize(src) // 1024, os.path.getsize(out) // 1024))
    print("webp written:", made)


def video(ffmpeg):
    # 960x540 is enough behind the dark hero overlay; crf values keep 18 s under 2 MB.
    scale = ["-vf", "scale=960:-2,fps=25", "-an", "-y"]
    jobs = [
        (VIDEO_OUT + ".mp4", ["-c:v", "libx264", "-preset", "slow", "-crf", "30", "-profile:v", "high", "-pix_fmt", "yuv420p", "-movflags", "+faststart"]),
        (VIDEO_OUT + ".webm", ["-c:v", "libvpx-vp9", "-crf", "46", "-b:v", "0", "-row-mt", "1", "-deadline", "good", "-cpu-used", "2"]),
    ]
    for out, codec in jobs:
        subprocess.run([ffmpeg, "-hide_banner", "-loglevel", "error", "-i", VIDEO_SRC] + scale + codec + [out], check=True)
        size = os.path.getsize(out)
        print("{}  {} KB".format(os.path.relpath(out, ROOT), size // 1024))
        assert size <= VIDEO_MAX, "video over 2 MB, raise crf: " + out


def zh_font(ttf):
    from fontTools import subset
    from fontTools.ttLib import TTFont
    from fontTools.varLib import instancer

    text = ""
    for page in glob.glob(os.path.join(ROOT, "public", "zh", "*.html")):
        with open(page, encoding="utf-8") as f:
            text += f.read()
    # CJK punctuation, ideographs and full-width forms; Latin, Lao and Thai come from fonts.css
    chars = sorted(set(re.findall("[　-〿㐀-䶿一-鿿＀-￯]", text)))
    assert chars, "no Chinese text found: run build.py first"
    font = instancer.instantiateVariableFont(TTFont(ttf), {"wght": (400, 600)})
    opts = subset.Options()
    opts.flavor = "woff2"
    opts.layout_features = ["*"]
    sub = subset.Subsetter(opts)
    sub.populate(text="".join(chars))
    sub.subset(font)
    out = os.path.join(ROOT, "public", "assets", "fonts", "noto-sans-sc-sub.woff2")
    subset.save_font(font, out, opts)
    print("{}  {} chars, {} KB".format(os.path.relpath(out, ROOT), len(chars), os.path.getsize(out) // 1024))


if __name__ == "__main__":
    webp()
    if "--video" in sys.argv:
        video(sys.argv[sys.argv.index("--video") + 1])
    if "--zh-font" in sys.argv:
        zh_font(sys.argv[sys.argv.index("--zh-font") + 1])
