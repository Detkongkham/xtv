
## Summary
- **What changed:** `git mv assets public/assets`; `build/build.py` writes to `public/` (`OUT`), `--preview` links `../../public/assets`; generated `lo/ en/ th/ zh/ index.html` removed from git and disk (now built into `public/`, ignored); `.gitignore` = target list from 05 §5 + `preview/`; paths updated in `design.md` §10 and `build/suppliers.py` comment.
- **Why:** P0-A2, repo layout = server layout (docroot `public/`).
- **Status:** done — `python3 build/build.py` writes 276 pages to `public/`, output byte-identical to the pre-move build (`diff -rq`); `--preview` builds 312 pages and its CSS path resolves.
- **Next:** GitHub Pages (https://detkongkham.github.io/xtvk/, source main `/`) breaks once this is pushed — needs a decision (Actions deploy of `public/`, or accept). Then P0-A5.
