#!/usr/bin/env python3
"""P2-C2: writes the content in build.py / strings*.py / suppliers.py as database rows (docs/plan/04-database.md §6).

Run:  python3 build/export_seed.py              -> app/database/seed/seed.json (loaded by `php bin/seed.php`)
      python3 build/export_seed.py --preview    -> app/storage/build/seed-preview.json (sample suppliers, for the P2-C3 check)
Rows carry their ids so links between tables survive the import.
"""
import json
import os
import re
import sys

from PIL import Image

import build as B
import suppliers as SUPDATA
from strings import LANGS

ROOT = B.ROOT
PARTNERSHIP = {"sup_auth": "authorized", "sup_exclusive": "exclusive", "sup_distributor": "distributor"}
MIME = {".jpg": "image/jpeg", ".jpeg": "image/jpeg", ".png": "image/png", ".webp": "image/webp"}


def tr(field, k):
    """Translated columns from a strings key: name_lo, name_en, ..."""
    return {field + "_" + lang: B.T[k][i] for i, lang in enumerate(LANGS)}


def tr_values(field, values):
    return {field + "_" + lang: (values[i] if values else None) for i, lang in enumerate(LANGS)}


class Media:
    def __init__(self):
        self.rows, self.ids = [], {}

    def add(self, img_path):
        """img_path is relative to public/assets/img, as in build.py."""
        path = "assets/img/" + img_path
        if path not in self.ids:
            full = os.path.join(ROOT, "public", path)
            w, h = Image.open(full).size
            self.ids[path] = len(self.rows) + 1
            self.rows.append({"id": self.ids[path], "kind": "image", "path": path, "original_name": os.path.basename(path),
                              "mime": MIME[os.path.splitext(path)[1]], "size_bytes": os.path.getsize(full), "width": w, "height": h})
        return self.ids[path]


def main():
    preview = "--preview" in sys.argv
    suppliers, supply = (SUPDATA.SAMPLE_SUPPLIERS, SUPDATA.sample_supply(B.DIVS)) if preview else (SUPDATA.SUPPLIERS, SUPDATA.SUPPLY)
    media = Media()
    divisions, groups, products = [], [], []
    product_id = {}
    for di, d in enumerate(B.DIVS):
        divisions.append({"id": di + 1, "slug": d["id"], "icon": d["icon"], "image_id": media.add(d["img"]), "sort": di * 10,
                          **tr("name", d["k"]), **tr("summary", d["k"] + "_desc"), **tr("body", d["k"] + "_long")})
        for gk, items in d["groups"]:
            gid = len(groups) + 1
            groups.append({"id": gid, "division_id": di + 1, "code": gk, "icon": B.GICON[gk], "sort": len([g for g in groups if g["division_id"] == di + 1]) * 10,
                           **tr("name", gk), **tr("summary", gk + "_p")})
            for pi, it in enumerate(items):
                pid = len(products) + 1
                product_id[it["name"]] = pid
                slug = re.sub(r"[^a-z0-9]+", "-", it["name"].lower()).strip("-")
                assert it["slug"] in (None, "product-" + slug), it["name"]
                featured = it["img"] in B.FEATURED
                products.append({"id": pid, "group_id": gid, "slug": slug, "name": it["name"],
                                 "image_id": media.add("products/" + it["img"] + ".jpg") if it["img"] else None,
                                 "variants_note": it["desc"] or None, **tr_values("summary", B.ITEMS.get(it["img"])),
                                 "is_featured": int(featured), "featured_sort": B.FEATURED.index(it["img"]) * 10 if featured else 0, "sort": pi * 10})

    news = []
    for ni, (k, date, cat, pic, href) in enumerate(B.NEWS):
        own_page = href.startswith("news-")
        news.append({"id": ni + 1, "slug": href[5:] if own_page else re.sub(r"[^a-z0-9]+", "-", B.T[k + "_t"][1].lower()).strip("-"),
                     "category": {"n_cat_prod": "product", "n_cat_co": "company"}[cat], "cover_id": media.add(pic),
                     # build.py's only article links to the security division; other items point at a page instead of their own
                     "link_url": "security.html" if own_page else href + ".html", "published_on": date, "status": "published",
                     **tr("title", k + "_t"), **tr("summary", k + "_p"),
                     **({"body_" + lang: "<p>" + B.e(B.T[k + "_body"][i]) + "</p>" for i, lang in enumerate(LANGS)} if own_page
                        else {"body_" + lang: "" for lang in LANGS})})

    sup_rows, links = [], []
    for si, s in enumerate(suppliers):
        logo = None
        if s["logo"]:
            logo = media.add("suppliers/" + s["logo"][0])
        sup_rows.append({"id": si + 1, "slug": s["slug"], "name": s["name"], "country_code": s["country"], "website_url": s["web"],
                         "logo_id": logo, "logo_on_dark": int(s["on_dark"]), "partnership": PARTNERSHIP[s["partnership"]] if s["partnership"] else None,
                         "since_year": s["since"], **tr_values("about", s["about"]), "is_featured": int(s["featured"]),
                         "featured_sort": si * 10, "sort": si * 10})
    sup_id = {s["slug"]: si + 1 for si, s in enumerate(suppliers)}
    for name, slugs in supply.items():
        for i, slug in enumerate(slugs):
            links.append({"product_id": product_id[name], "supplier_id": sup_id[slug], "sort": i})

    settings = {
        "contact": {"tel": B.TEL, "mobile": B.MOB, "fax": B.FAX, "email": B.MAIL, "whatsapp": B.WA.rsplit("=", 1)[1]},
        "address": {lang: B.T["addr_full"][i] for i, lang in enumerate(LANGS)},
        "map": {"query": B.MAP_Q.replace("+", " ")},
        "stats": {"founded": 2016, "experience": 20},
    }
    seed = {"media": media.rows, "divisions": divisions, "product_groups": groups, "products": products, "suppliers": sup_rows,
            "product_suppliers": links, "news": news, "settings": settings}
    out = os.path.join(ROOT, "app", "storage", "build", "seed-preview.json") if preview else os.path.join(ROOT, "app", "database", "seed", "seed.json")
    os.makedirs(os.path.dirname(out), exist_ok=True)
    with open(out, "w", encoding="utf-8") as f:
        json.dump(seed, f, ensure_ascii=False, indent=1)
        f.write("\n")
    print(os.path.relpath(out, ROOT), {k: len(v) for k, v in seed.items()})


if __name__ == "__main__":
    main()
