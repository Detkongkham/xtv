#!/usr/bin/env python3
"""P2-C3: compares the pages build.py wrote with the pages PHP rendered.

Run:  python3 build/compare_html.py public app/storage/build/c3-db

Every page counts once: "identical" (same bytes), "same after normalising" (whitespace collapsed and HTML entities
decoded on both sides, e.g. & vs &amp;, which browsers show the same) or "different" (first difference shown).
Exit code 0 only when no page is different and both sides have the same files.
"""
import html
import os
import re
import sys

LANGS = ["lo", "en", "th", "zh"]


def files(root):
    out = {"index.html", "robots.txt"} & set(os.listdir(root))
    for lang in LANGS:
        d = os.path.join(root, lang)
        if os.path.isdir(d):
            out |= {lang + "/" + f for f in os.listdir(d) if f.endswith(".html")}
    return out


def norm(s):
    s = html.unescape(s)
    s = re.sub(r"\s+", " ", s)
    return re.sub(r">\s+<", "><", s).strip()


def first_diff(a, b, width=160):
    i = next((i for i, (x, y) in enumerate(zip(a, b)) if x != y), min(len(a), len(b)))
    lo = max(0, i - 60)
    return "    python: …{}…\n    php:    …{}…".format(a[lo:lo + width], b[lo:lo + width])


def main(a_root, b_root):
    fa, fb = files(a_root), files(b_root)
    same, normalised, different = [], [], []
    for f in sorted(fa & fb):
        with open(os.path.join(a_root, f), encoding="utf-8") as x, open(os.path.join(b_root, f), encoding="utf-8") as y:
            a, b = x.read(), y.read()
        if a == b:
            same.append(f)
        elif norm(a) == norm(b):
            normalised.append(f)
        else:
            different.append((f, first_diff(norm(a), norm(b))))
    print("pages: {} | identical: {} | same after normalising: {} | different: {}".format(len(fa | fb), len(same), len(normalised), len(different)))
    for f in sorted(fa - fb):
        print("  only in", a_root + ":", f)
    for f in sorted(fb - fa):
        print("  only in", b_root + ":", f)
    for f, d in different[:15]:
        print("  DIFF", f)
        print(d)
    if len(different) > 15:
        print("  … and", len(different) - 15, "more")
    return 0 if not different and fa == fb else 1


if __name__ == "__main__":
    sys.exit(main(sys.argv[1], sys.argv[2]))
