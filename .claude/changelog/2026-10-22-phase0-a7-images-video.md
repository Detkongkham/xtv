# 2026-10-22-phase0-a7-images-video

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:04:27 | Write | build/optimize_media.py |

## Summary
- **What changed:** new `build/optimize_media.py` (Pillow WebP q80 next to every jpg/png, only when the source is newer; `--video <ffmpeg>` encodes the hero to 960px/25fps silent H.264 + VP9 and asserts ≤ 2 MB). 67 `.webp` files in `public/assets/img/` (3.2 MB of jpg/png → 1.6 MB webp). Hero source moved to `build/media/hero-lab-source.mp4` (out of the docroot); `public/assets/video/hero-lab.mp4` 725 KB + `hero-lab.webm` 548 KB (was 5.3 MB). `build/build.py`: `img()` adds real width/height (read with Pillow) and wraps in `<picture>` with the WebP source; hero `<video>` has webm + mp4 sources and a WebP poster; marquee uses `img()`; `versioned()` adds `?v=<md5 8>` to every asset URL. `style.css`: `img { height: auto }`, `picture { display: contents }`, `picture > source { display: none }`, `.phead--img` selectors also match `picture > img`. `design.md` §10 lists the script.
- **Why:** P0-A7, page weight and layout shift; cache busting so a 1-year asset cache is safe.
- **Status:** done — build OK (276 pages, preview 312). Headless Chrome with reduced motion: 10 pages × 1440/390 px pixel-identical to the pre-change build (first pass found a 20 px extra gap in the marquee caused by `<source>` as a flex item; fixed with `display: none`). All 9,544 asset refs exist and carry `?v=`. Video quality checked only by size, not by eye. ffmpeg came from `imageio-ffmpeg` in a scratch venv (not a project dependency).
- **Next:** P0-A6 `.htaccess`.
