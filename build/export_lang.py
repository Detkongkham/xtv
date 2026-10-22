#!/usr/bin/env python3
"""One-off (P2-C1): writes the site's UI text from strings*.py to app/lang/<lang>.php and app/lang/countries.php.

Content text (division, group, product and news copy, the address) is not written here: it moves to the
database through export_seed.py. After this runs once, app/lang/*.php is the source; edit it there.

Run:  python3 build/export_lang.py
"""
import os

from strings import T, LANGS
from strings2 import FORMAL, EXTRA
from suppliers import SUP, COUNTRIES

T.update(FORMAL)
T.update(EXTRA)
T.update(SUP)

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
LANG_DIR = os.path.join(ROOT, "app", "lang")

# keys that become database content (export_seed.py)
CONTENT_PREFIXES = ("d_", "g_", "n1_", "n3_")
CONTENT_KEYS = {"addr_full"}

# labels for database enums that strings.py has no text for yet
ADDED = {
    "n_cat_event": ("ກິດຈະກຳ", "Events", "กิจกรรม", "活动"),
    "sup_exclusive": ("ຕົວແທນຈຳໜ່າຍແຕ່ຜູ້ດຽວ", "Exclusive distributor", "ตัวแทนจำหน่ายแต่เพียงผู้เดียว", "独家经销商"),
    "sup_distributor": ("ຕົວແທນຈຳໜ່າຍ", "Distributor", "ตัวแทนจำหน่าย", "经销商"),
}


def php(s):
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def is_content(k):
    return k in CONTENT_KEYS or k.startswith(CONTENT_PREFIXES)


def main():
    keys = [k for k in T if not is_content(k)] + list(ADDED)
    for i, lang in enumerate(LANGS):
        rows = "".join("    {} => {},\n".format(php(k), php((T.get(k) or ADDED[k])[i])) for k in keys)
        with open(os.path.join(LANG_DIR, lang + ".php"), "w", encoding="utf-8") as f:
            f.write("<?php\n// UI text of the public site ({}). Placeholders: {{}} in order, {{n}} / {{name}} by name.\n"
                    "// Created from build/strings*.py by build/export_lang.py; edit this file from now on.\n"
                    "declare(strict_types=1);\n\nreturn [\n{}];\n".format(lang, rows))
    rows = "".join("    {} => [{}],\n".format(php(c), ", ".join("{} => {}".format(php(l), php(v[i])) for i, l in enumerate(LANGS)))
                   for c, v in COUNTRIES.items())
    with open(os.path.join(LANG_DIR, "countries.php"), "w", encoding="utf-8") as f:
        f.write("<?php\n// Country names for suppliers, by ISO 3166-1 alpha-2 code (docs/plan/08-suppliers.md).\n"
                "declare(strict_types=1);\n\nreturn [\n{}];\n".format(rows))
    print("keys per language:", len(keys), "| countries:", len(COUNTRIES))


if __name__ == "__main__":
    main()
