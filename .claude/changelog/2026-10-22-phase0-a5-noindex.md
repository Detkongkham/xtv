
## Summary
- **What changed:** `build/build.py`: new `INDEXABLE = False` flag; every page and `public/index.html` get `<meta name="robots" content="noindex, nofollow">` while it is False (preview always); build writes `public/robots.txt` (`Disallow: /` now, `Allow: /` when INDEXABLE).
- **Why:** P0-A5, keep Google out until go-live; P4-E5 flips the flag.
- **Status:** done — rebuilt 276 pages; all 276 + index.html carry noindex; diff vs. previous build is that one added line per page plus the new robots.txt.
- **Next:** P0-A7 (video + WebP).
