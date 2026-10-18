#!/usr/bin/env python3
"""Generates the static XTK demo site (lo/, en/, th/, zh/) from the templates below.

Run:  python3 build/build.py
Output is plain HTML that uses assets/css/style.css and assets/js/main.js. No backend.
"""
import os
import re
import html

from strings import T, LANGS, LANG_NAMES, LANG_SHORT, HTML_LANG
from strings2 import FORMAL, EXTRA, ITEMS

T.update(FORMAL)
T.update(EXTRA)

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
A = "../assets"
LI = 0  # index of the language being rendered

TEL, TEL_HREF = "(856-21) 563121", "+85621563121"
MOB, MOB_HREF = "020 5589 2929", "+8562055892929"
FAX = "(856-21) 562949"
MAIL = "keokhounma@xtklaos.com"
WA = "https://api.whatsapp.com/send?phone=8562055892929"
MAP_Q = "Dondeng+Village,+Chanthabouly,+Vientiane,+Laos"
MAP_EMBED = "https://www.google.com/maps?q=" + MAP_Q + "&output=embed"
MAP_LINK = "https://www.google.com/maps/search/?api=1&query=" + MAP_Q


def t(k):
    return T[k][LI]


def e(s):
    return html.escape(s, quote=True)


ICONS = {
    "arrow": "M5 12h14M13 6l6 6-6 6",
    "chev": "M6 9l6 6 6-6",
    "menu": "M4 7h16M4 12h16M4 17h16",
    "x": "M6 6l12 12M18 6L6 18",
    "globe": "M12 3a9 9 0 100 18 9 9 0 000-18zM3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9s1.3-6.3 3.8-9z",
    "phone": "M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z",
    "mail": "M4 6h16v12H4zM4 7l8 6 8-6",
    "pin": "M12 21s7-6.1 7-11a7 7 0 10-14 0c0 4.9 7 11 7 11zM12 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z",
    "chat": "M4 20l1.3-4.2A8 8 0 1112 20a8 8 0 01-3.8-1L4 20z",
    "up": "M12 19V5M6 11l6-6 6 6",
    "download": "M12 4v11M7 11l5 5 5-5M5 20h14",
    "print": "M7 8V4h10v4M7 17H5a1 1 0 01-1-1V9a1 1 0 011-1h14a1 1 0 011 1v7a1 1 0 01-1 1h-2M7 14h10v6H7z",
    "tool": "M15 5a4 4 0 00-4.9 5.3L4 16.4V20h3.6l6.1-6.1A4 4 0 0019 9l-2.5 2.5-2-2L17 7a4 4 0 00-2-2z",
    "shield": "M12 3l8 3v6c0 4.5-3.3 7.9-8 9-4.7-1.1-8-4.5-8-9V6l8-3z",
    "users": "M9 11a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM2.5 20a6.5 6.5 0 0113 0M16 4.2a3.5 3.5 0 010 6.6M18.5 14.5a6.5 6.5 0 013 5.5",
    "box": "M4 8l8-4 8 4v8l-8 4-8-4V8zM4 8l8 4 8-4M12 12v8",
    "cap": "M3 9l9-4 9 4-9 4-9-4zM7 11v4c1.5 1.3 3.2 2 5 2s3.5-.7 5-2v-4M21 9v5",
    "award": "M12 15a6 6 0 100-12 6 6 0 000 12zM8.5 14L7 21l5-3 5 3-1.5-7",
    "pill": "M10.5 3.5a5 5 0 017 7l-7 7a5 5 0 01-7-7l7-7zM7 7l7 7",
    "file": "M7 3h7l5 5v13H7zM14 3v5h5",
    "fb": "M14 8h3V4h-3a4 4 0 00-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z",
    "clock": "M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v5l3 2",
    "scan": "M4 8V5a1 1 0 011-1h3M16 4h3a1 1 0 011 1v3M20 16v3a1 1 0 01-1 1h-3M8 20H5a1 1 0 01-1-1v-3M12 8a4 4 0 100 8 4 4 0 000-8z",
    "pulse": "M3 12h4l2-5 4 10 2-5h6",
    "scissors": "M6 9a3 3 0 100-6 3 3 0 000 6zM6 21a3 3 0 100-6 3 3 0 000 6zM8.1 8.1L20 20M8.1 15.9L20 4",
    "monitor": "M4 5h16v11H4zM9 20h6M12 16v4M7 12l2.5-3 2 2 2.5-4 3 5",
    "flask": "M9 3h6M10 3v6l-5 9a2 2 0 001.8 3h10.4a2 2 0 001.8-3l-5-9V3M7.5 15h9",
    "syringe": "M17 3l4 4M19 5l-4 4M10 6l8 8M12 8l-8 8v4h4l8-8M4 20l-1.5 1.5",
    "bag": "M5 8h14a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V9a1 1 0 011-1zM9 8V5a1 1 0 011-1h4a1 1 0 011 1v3M4 13h16",
    "gate": "M6 21V4h12v17M3 21h18M12 8a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM10 21v-6l2-2 2 2v6",
    "wand": "M14 4a4 4 0 110 8 4 4 0 010-8zM11.2 10.8L4 18l2 2 7.2-7.2",
    "search": "M11 4a7 7 0 100 14 7 7 0 000-14zM16 16l4 4M8.5 11h5M11 8.5v5",
}
GICON = {"g_classes": "pill", "g_imaging": "scan", "g_diagnostic": "pulse", "g_surgical": "scissors", "g_analyzers": "monitor", "g_reagents": "flask",
         "g_disposable": "syringe", "g_paper": "file", "g_xray": "bag", "g_walk": "gate", "g_hand": "wand", "g_trace": "search"}


def ic(name):
    return '<svg class="i" viewBox="0 0 24 24" aria-hidden="true"><path pathLength="1" d="' + ICONS[name] + '"/></svg>'


# ---------------------------------------------------------------- product data
# (name, image or None, short description). Names and lists come from the previous xtklaos.com site.
def P(name, img=None, desc=""):
    slug = re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")
    return {"name": name, "img": img, "desc": desc, "slug": "product-" + slug if img else None}


DIVS = [
    {"id": "pharmaceuticals", "k": "d_pharma", "img": "products/pharma.jpg", "icon": "pill", "groups": [
        ("g_classes", [
            P("Antibacterial drugs"), P("Antihistamines"), P("Analgesics, Antipyretic and NSAIDs"),
            P("Respiratory drugs"), P("Gastrointestinal drugs"),
        ]),
    ]},
    {"id": "healthcare", "k": "d_health", "img": "products/h01.jpg", "icon": "award", "groups": [
        ("g_imaging", [
            P("CT Scan", "h00"), P("Digital X-Ray Imaging Machine", "h01"), P("Cart-based Ultrasound", "h02"),
            P("Fetal & Maternal Monitor", "h03"), P("ECG Machine", "h04"), P("Centrifuge Machine", "h05"),
            P("Surgical Lamp", "h06"), P("C-Arm X-ray"), P("Anesthesia Machine"), P("Ventilator"), P("Patient Monitor"),
        ]),
        ("g_diagnostic", [
            P("Blood Pressure Monitor", "h07"), P("Blood Glucose Monitoring", "h08"), P("Fingertip Pulse Oximeter", "h09"),
            P("Fetal Doppler", "h10"), P("Stethoscope", "h11"), P("Handheld Pulse Oximeter", "h12"),
            P("Oxygen Flow Meter", "h13"), P("Medical Infrared Forehead Thermometer", "h14"),
        ]),
        ("g_surgical", [
            P("Surgical Instruments", "h15"), P("Absorbable Surgical Suture"), P("Nonabsorbable Surgical Suture"),
        ]),
    ]},
    {"id": "laboratory", "k": "d_lab", "img": "products/l00.jpg", "icon": "box", "groups": [
        ("g_analyzers", [
            P("Hematology Analyzer (CBC)", "l00"), P("Auto & Semi-auto Chemistry Analyzer", "l02"),
            P("Thyroid & HbA1c Analyzer", "l03", "T3 · T4 · TSH · HbA1c"), P("Urine Analyzer", "l05"),
        ]),
        ("g_reagents", [
            P("Analyzer Reagents", "l01", "Diluent · Lyse · Cholesterol · Glucose · Urea/BUN · Uric acid · Triglyceride · Creatinine · AST/GOT · ALT/GPT"),
            P("Immunoassay Test Kits", "l04", "T3 · T4 · TSH · HbA1c"),
            P("Urine Test Strips", "l06"),
            P("Febrile Antigen", "l07", "Salmonella Typhi H · Salmonella Typhi O · Proteus OXK · OX2 · OX19"),
            P("Latex Reagents", "l08", "ASLO · CRP · RPR · S.L.E · RF"),
            P("Blood Grouping Antisera", "l09", "Anti A · Anti B · Anti AB · Anti D"),
            P("Rapid Tests", "l10", "HBsAg · HIV I&II · HCV · Dengue IgG/IgM/NS1 · Malaria Pf/Pv · H. pylori"),
        ]),
    ]},
    {"id": "consumables", "k": "d_cons", "img": "products/c00.jpg", "icon": "box", "groups": [
        ("g_disposable", [
            P("Blood Collection Tube", "c00"), P("Operator's Protection", "c01"), P("Medical Tube", "c02"),
            P("Anesthesia Breathing", "c03"), P("Syringe & Needles", "c04"), P("Pipette Tips", "c05"),
        ]),
        ("g_paper", [
            P("Ultrasound Paper", "c06", "SONY Type V UPP-110HD (110mm × 18m) · Type II UPP-110HD (110mm × 20m)"),
            P("Thermal Paper", "c07", "CBC 79 × 40mm · Chemistry / Immunoassay 57 × 34mm · Urine 55 × 46mm"),
            P("ECG Paper", "c08", "110mm × 20m · 215mm × 30m"),
            P("Fetal & Maternal Monitor Paper", "c09", "152 × 90mm"),
        ]),
    ]},
    {"id": "security", "k": "d_sec", "img": "products/s01.jpg", "icon": "shield", "groups": [
        ("g_xray", [
            P("180150DVS X-ray Inspection System", "s00"), P("100100TDVS-DV X-ray Inspection System", "s01"),
            P("7555DVS X-ray Inspection System", "s02"), P("5333DVS X-ray Inspection System", "s03"),
        ]),
        ("g_walk", [P("SMD600 Plus - MI2 Walk-Through Metal Detector", "s04")]),
        ("g_hand", [P("PD240CB Hand Held Metal Detector", "s05"), P("PD140N Hand Held Metal Detector", "s06"), P("PD240 Hand Held Metal Detector", "s07")]),
        ("g_trace", [P("E5000 Series Trace Detector", "s08"), P("N2300 Trace Detector", "s09"), P("CLX Compact Trace Detector", "s10"), P("LRS-900", "s11")]),
    ]},
]
N_ITEMS = sum(len(items) for d in DIVS for _, items in d["groups"])
FEATURED = ["h00", "h01", "h02", "l00", "l02", "s01", "c00", "h03"]
SOLS = [
    ("hospital", "photo/4779.jpg", ["g_imaging", "g_surgical", "g_disposable"], "healthcare"),
    ("clinic", "photo/35963.jpg", ["g_diagnostic", "g_imaging"], "healthcare"),
    ("lab", "photo/4719.jpg", ["g_analyzers", "g_reagents"], "laboratory"),
    ("pharmacy", "products/pharma.jpg", ["g_classes", "g_diagnostic"], "pharmaceuticals"),
    ("security", "products/s01.jpg", ["g_xray", "g_walk", "g_hand", "g_trace"], "security"),
]
SERVICES = [("srv1", "tool"), ("srv2", "shield"), ("srv3", "cap"), ("srv4", "box")]
NEWS = [
    ("n1", "2024-11-27", "n_cat_prod", "products/s01.jpg", "news-security-division"),
    ("n2", "2021-06-07", "n_cat_job", "photo/4766.jpg", "careers"),
    ("n3", "2021-06-06", "n_cat_prod", "photo/47584.jpg", "laboratory"),
]


def all_items():
    for d in DIVS:
        for gk, items in d["groups"]:
            for it in items:
                yield d, gk, it


def item_text(gk, it):
    """One formal sentence about the product; falls back to the group description."""
    return ITEMS[it["img"]][LI] if it["img"] in ITEMS else t(gk + "_p")


def steps(keys, with_p=True):
    return "".join('<div class="step rv" style="--i:{}"><h3 class="h4">{}</h3>{}</div>'.format(
        i, t(k), "<p>" + t(k + "_p") + "</p>" if with_p else "") for i, k in enumerate(keys))


def why_cards():
    return "".join('<div class="card card--text rv" style="--i:{}"><div class="card__body"><span class="ico">{}</span><h3 class="h4">{}</h3><p>{}</p></div></div>'.format(
        i, ic(icon), t(k), t(k + "_p")) for i, (k, icon) in enumerate([("why1", "award"), ("why2", "users"), ("why3", "tool"), ("why4", "box")]))


def fdate(iso):
    y, m, d = iso.split("-")
    if LANGS[LI] == "zh":
        return "{} 年 {} 月 {} 日".format(y, int(m), int(d))
    return "{}/{}/{}".format(d, m, y)


# ---------------------------------------------------------------- shared pieces
def img(src, alt="", cls="", lazy=True):
    return '<img src="{}/img/{}" alt="{}"{}{}>'.format(A, src, e(alt), ' class="' + cls + '"' if cls else "", ' loading="lazy"' if lazy else "")


def arrow_link(label, href):
    return '<a class="arrow-link" href="{}">{}<span class="circ">{}</span></a>'.format(href, label, ic("arrow"))


def sec_head(h, p="", left=False):
    return '<div class="sec-head{} rv"><h2 class="h2">{}</h2>{}</div>'.format(" sec-head--left" if left else "", h, "<p>" + p + "</p>" if p else "")


def crumb(trail, last):
    parts = ['<a href="index.html">' + t("home") + "</a>"]
    for label, href in trail:
        parts.append('<a href="' + href + '">' + label + "</a>")
    parts.append('<span aria-current="page">' + last + "</span>")
    return '<nav class="crumb" aria-label="Breadcrumb">' + "<span>&gt;</span>".join(parts) + "</nav>"


def phead(title, lead="", trail=(), image=None):
    cls = "phead phead--img" if image else "phead"
    pic = img(image, "", "plx", lazy=False) if image else ""
    return '<section class="{}">{}<div class="wrap">{}<h1>{}</h1>{}</div></section>'.format(
        cls, pic, crumb(trail, title), title, "<p>" + lead + "</p>" if lead else "")


def product_card(div, it, i=0, gk=None):
    gk = gk or next(g for g, items in div["groups"] if it in items)
    if it["img"]:
        return ('<a class="card rv" style="--i:{}" href="{}.html"><span class="card__img">{}</span>'
                '<span class="card__body"><span class="small">{}</span><b class="h4">{}</b>{}'
                '<span class="card__foot">{}<span class="circ">{}</span></span></span></a>').format(
            i % 4, it["slug"], img("products/" + it["img"] + ".jpg", it["name"]), t(div["k"]), e(it["name"]),
            '<p class="clamp2">' + item_text(gk, it) + "</p>", t("view_detail"), ic("arrow"))
    return ('<a class="card card--text rv" style="--i:{}" href="quote.html"><span class="card__body">'
            '<span class="ico">{}</span><b class="h4">{}</b><span class="small">{}</span>'
            '<span class="card__foot">{}<span class="circ">{}</span></span></span></a>').format(
        i % 4, ic(div["icon"]), e(it["name"]), t(div["k"]), t("ask_price"), ic("arrow"))


def division_cards(cols=5):
    out = ['<div class="grid g-{}">'.format(cols)]
    for i, d in enumerate(DIVS):
        n = sum(len(x) for _, x in d["groups"])
        out.append(('<a class="card rv" style="--i:{}" href="{}.html"><span class="card__img">{}</span>'
                    '<span class="card__body"><b class="h4">{}</b><p>{}</p>'
                    '<span class="card__foot">{} {}<span class="circ">{}</span></span></span></a>').format(
            i, d["id"], img(d["img"], t(d["k"])), t(d["k"]), t(d["k"] + "_desc"), n, t("items"), ic("arrow")))
    out.append("</div>")
    return "".join(out)


def service_cards():
    out = ['<div class="grid g-4">']
    for i, (k, icon) in enumerate(SERVICES):
        out.append('<div class="card card--text rv" style="--i:{}"><div class="card__body"><span class="ico">{}</span><h3 class="h4">{}</h3><p>{}</p></div></div>'.format(
            i, ic(icon), t(k), t(k + "_p")))
    out.append("</div>")
    return "".join(out)


def news_cards():
    out = ['<div class="grid g-3">']
    for i, (k, date, cat, pic, href) in enumerate(NEWS):
        out.append(('<a class="card card--news rv" style="--i:{}" href="{}.html"><span class="card__img">{}</span>'
                    '<span class="card__body"><span><span class="tag">{}</span></span><time datetime="{}">{}</time>'
                    '<b class="h4">{}</b><p class="clamp2">{}</p>'
                    '<span class="card__foot">{}<span class="circ">{}</span></span></span></a>').format(
            i, href, img(pic, ""), t(cat), date, fdate(date), t(k + "_t"), t(k + "_p"), t("learn_more"), ic("arrow")))
    out.append("</div>")
    return "".join(out)


def contact_dl():
    return ('<dl><div>{}<dd>{}</dd></div><div>{}<dd>{}: <a href="tel:{}">{}</a><br>{}: <a href="tel:{}">{}</a><br>{}: {}</dd></div>'
            '<div>{}<dd><a href="mailto:{}">{}</a></dd></div></dl>').format(
        ic("pin"), t("addr_full"), ic("phone"), t("tel"), TEL_HREF, TEL, t("mobile"), MOB_HREF, MOB, t("fax"), FAX, ic("mail"), MAIL, MAIL)


def location_block():
    return ('<div class="loc rv"><div class="loc__list on-dark"><div><span class="tag">{}</span><h3 class="h4">{}</h3>{}</div>'
            '<div class="btns"><a class="btn btn--white btn--sm" href="{}" target="_blank" rel="noopener">{}</a>'
            '<a class="btn btn--light btn--sm" href="{}" target="_blank" rel="noopener">{}{}</a></div></div>'
            '<div class="loc__map"><iframe title="{}" src="{}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div></div>').format(
        t("head_office"), t("co_name"), contact_dl(), MAP_LINK, t("open_map"), WA, ic("chat"), "WhatsApp", t("head_office"), MAP_EMBED)


def cta():
    return ('<section class="cta on-dark"><div class="wrap rv"><h2 class="h2">{}</h2><p>{}</p><div class="btns">'
            '<a class="btn btn--white" href="quote.html">{}</a><a class="btn btn--light" href="contact.html">{}</a></div></div></section>').format(
        t("cta_h"), t("cta_p"), t("quote"), t("contact"))


def field(label, name, kind="text", req=False, full=False, options=None, hint=""):
    star = " <em>*</em>" if req else ""
    r = " required" if req else ""
    fid = "f-" + name
    if kind == "textarea":
        ctl = '<textarea id="{}" name="{}"{}></textarea>'.format(fid, name, r)
    elif kind == "select":
        opts = '<option value="">' + t("f_select") + "</option>" + "".join("<option>" + o + "</option>" for o in options)
        ctl = '<select id="{}" name="{}"{}>{}</select>'.format(fid, name, r, opts)
    else:
        ac = {"name": "name", "tel": "tel", "email": "email", "org": "organization"}.get(name, "off")
        ctl = '<input id="{}" name="{}" type="{}" autocomplete="{}"{}{}>'.format(fid, name, kind, ac, " multiple" if kind == "file" else "", r)
    return '<div class="field{}"><label for="{}">{}{}</label>{}{}</div>'.format(
        " field--full" if full else "", fid, label, star, ctl, '<span class="hint">' + hint + "</span>" if hint else "")


def form(fields, submit):
    return ('<form class="form" data-demo novalidate-off action="#" method="post">{}'
            '<div class="field field--full"><label class="check"><input type="checkbox" name="consent" required><span>{} (<a href="privacy.html">{}</a>)</span></label></div>'
            '<p class="form__ok" role="status" tabindex="-1" hidden>{}</p>'
            '<div class="field field--full"><div class="btns"><button class="btn" type="submit">{}</button></div><span class="hint">{}</span></div></form>').format(
        "".join(fields), t("f_consent"), t("privacy"), t("f_ok"), submit, t("f_req")).replace(" novalidate-off", "")


def contact_aside():
    return ('<div class="aside-box"><h3 class="h4">{}</h3><ul class="contact-list">'
            '<li>{}<span>{}</span></li><li>{}<span>{}: <a href="tel:{}">{}</a><br>{}: <a href="tel:{}">{}</a></span></li>'
            '<li>{}<span>{}: {}</span></li><li>{}<a href="mailto:{}">{}</a></li></ul></div>'
            '<div class="aside-box"><h3 class="h4">{}</h3><p>{}: <a href="tel:{}"><b>{}</b></a></p>'
            '<div class="btns" style="margin-top:16px"><a class="btn btn--sm" href="{}" target="_blank" rel="noopener">{}{}</a></div></div>').format(
        t("head_office"), ic("pin"), t("addr_full"), ic("phone"), t("tel"), TEL_HREF, TEL, t("mobile"), MOB_HREF, MOB,
        ic("print"), t("fax"), FAX, ic("mail"), MAIL, MAIL, t("hotline"), t("mobile"), MOB_HREF, MOB, WA, ic("chat"), t("whatsapp"))


# ---------------------------------------------------------------- layout
NAV_ABOUT = [("about", "about_profile", "photo/4767.jpg"), ("vision", "vision", "photo/4733.jpg"), ("careers", "careers", "photo/4766.jpg")]


def header(slug, overlay, active):
    cur = lambda k: ' aria-current="page"' if active == k else ""
    lang = LANGS[LI]
    about_cards = "".join('<a class="mega__card" href="{}.html"><span class="ph">{}</span>{}</a>'.format(p, img(pic, ""), t(k)) for p, k, pic in NAV_ABOUT)
    prod_cards = "".join('<a class="mega__card" href="{}.html"><span class="ph">{}</span>{}</a>'.format(d["id"], img(d["img"], ""), t(d["k"])) for d in DIVS)
    langs = "".join('<a href="../{}/{}.html" lang="{}" hreflang="{}"{}>{}</a>'.format(
        l, slug, HTML_LANG[l], HTML_LANG[l], ' aria-current="true"' if l == lang else "", LANG_NAMES[l]) for l in LANGS)
    nav = (
        '<li class="has-mega"><a class="nav__link" href="about.html"{}>{}{}</a><div class="mega"><div class="wrap mega__in"><div><p class="mega__title">{}</p><p>{}</p>{}</div><div class="mega__cards mega__cards--3">{}</div></div></div></li>'
        '<li class="has-mega"><a class="nav__link" href="products.html"{}>{}{}</a><div class="mega"><div class="wrap mega__in"><div><p class="mega__title">{}</p><p>{}</p>{}</div><div class="mega__cards">{}</div></div></div></li>'
        '<li><a class="nav__link" href="solutions.html"{}>{}</a></li>'
        '<li><a class="nav__link" href="services.html"{}>{}</a></li>'
        '<li><a class="nav__link" href="news.html"{}>{}</a></li>'
        '<li><a class="nav__link" href="contact.html"{}>{}</a></li>'
    ).format(
        cur("about"), t("about"), ic("chev"), t("about"), t("co_tag"), arrow_link(t("about_profile"), "about.html"), about_cards,
        cur("products"), t("products"), ic("chev"), t("products"), t("home_prod_p"), arrow_link(t("all_products"), "products.html"), prod_cards,
        cur("solutions"), t("solutions"), cur("services"), t("services"), cur("news"), t("news"), cur("contact"), t("contact"))
    head = ('<a class="skip" href="#main">{}</a><header class="hdr{}"><div class="wrap wrap--wide hdr__in">'
            '<a class="logo" href="index.html" aria-label="XTK — {}"><img src="{}/img/logo.svg" alt="XTK" width="1663" height="1069"></a>'
            '<nav class="nav" aria-label="{}"><ul>{}</ul></nav><div class="hdr__tools">'
            '<details class="lang"><summary aria-label="{}">{}{}{}</summary><div class="lang__menu">{}</div></details>'
            '<a class="btn btn--sm hdr__cta" href="quote.html">{}</a>'
            '<button class="burger" type="button" aria-label="{}" aria-expanded="false" aria-controls="drawer">{}</button></div></div></header>').format(
        t("skip"), " hdr--overlay" if overlay else "", t("home"), A, t("menu"), nav, t("language"), ic("globe"), LANG_SHORT[lang], ic("chev"), langs,
        t("quote"), t("menu"), ic("menu"))
    d_about = "".join('<a href="{}.html">{}</a>'.format(p, t(k)) for p, k, _ in NAV_ABOUT)
    d_prod = '<a href="products.html">{}</a>'.format(t("all_products")) + "".join('<a href="{}.html">{}</a>'.format(d["id"], t(d["k"])) for d in DIVS)
    d_langs = "".join('<a href="../{}/{}.html"{}>{}</a>'.format(l, slug, ' aria-current="true"' if l == lang else "", LANG_NAMES[l]) for l in LANGS)
    drawer = ('<div class="drawer" id="drawer" aria-label="{}"><div class="drawer__top"><img src="{}/img/logo.svg" alt="XTK">'
              '<button class="burger drawer__close" type="button" aria-label="{}" style="display:grid">{}</button></div>'
              '<details><summary>{}{}</summary><div>{}</div></details><details><summary>{}{}</summary><div>{}</div></details>'
              '<a class="drawer__link" href="solutions.html">{}</a><a class="drawer__link" href="services.html">{}</a>'
              '<a class="drawer__link" href="news.html">{}</a><a class="drawer__link" href="contact.html">{}</a>'
              '<div class="drawer__langs">{}</div><a class="btn" href="quote.html">{}</a></div>').format(
        t("menu"), A, t("close"), ic("x"), t("about"), ic("chev"), d_about, t("products"), ic("chev"), d_prod,
        t("solutions"), t("services"), t("news"), t("contact"), d_langs, t("quote"))
    return head + drawer


def footer():
    col = lambda title, links: '<div class="ftr__col"><h3>{}</h3><ul>{}</ul></div>'.format(
        title, "".join('<li><a href="{}.html">{}</a></li>'.format(p, lab) for p, lab in links))
    return ('<footer class="ftr"><div class="wrap"><div class="ftr__top">'
            '<div class="ftr__brand"><img src="{}/img/logo-ring.svg" alt="XTK" width="1751" height="1157" loading="lazy"><p>{}</p></div>{}{}{}'
            '<div class="ftr__contact"><h3>{}</h3><ul><li>{}<span>{}</span></li><li>{}<span><a href="tel:{}">{}</a><br><a href="tel:{}">{}</a></span></li>'
            '<li>{}<a href="mailto:{}">{}</a></li></ul><div class="social"><a href="{}" target="_blank" rel="noopener" aria-label="WhatsApp">{}</a>'
            '<a href="tel:{}" aria-label="{}">{}</a><a href="mailto:{}" aria-label="{}">{}</a></div></div></div>'
            '<div class="ftr__bot"><p>&copy; 2026 {}. {}.</p><ul><li><a href="privacy.html">{}</a></li><li><a href="terms.html">{}</a></li><li><a href="sitemap.html">{}</a></li></ul></div>'
            '</div></footer>'
            '<div class="float"><a class="wa" href="{}" target="_blank" rel="noopener" aria-label="{}">{}</a>'
            '<a href="tel:{}" aria-label="{}">{}</a><button class="top" type="button" aria-label="{}">{}</button></div>').format(
        A, t("co_tag"),
        col(t("company"), [("about", t("about_profile")), ("vision", t("vision")), ("news", t("news")), ("careers", t("careers"))]),
        col(t("products"), [(d["id"], t(d["k"])) for d in DIVS]),
        col(t("support"), [("services", t("services")), ("solutions", t("solutions")), ("quote", t("quote")), ("downloads", t("downloads")), ("faq", t("faq"))]),
        t("contact"), ic("pin"), t("addr_full"), ic("phone"), TEL_HREF, TEL, MOB_HREF, MOB, ic("mail"), MAIL, MAIL,
        WA, ic("chat"), MOB_HREF, t("call_us"), ic("phone"), MAIL, t("email"), ic("mail"),
        t("co_name"), t("rights"), t("privacy"), t("terms"), t("sitemap"),
        WA, t("whatsapp"), ic("chat"), MOB_HREF, t("call_us"), ic("phone"), t("back_top"), ic("up"))


def page(slug, title, body, desc="", overlay=False, active=None):
    lang = LANGS[LI]
    # Latin, Lao and Thai fonts are self-hosted so the demo works offline; only Chinese is fetched from Google Fonts.
    fonts = '<link rel="stylesheet" href="{}/fonts/fonts.css">'.format(A)
    if lang == "zh":
        fonts += ('\n<link rel="preconnect" href="https://fonts.googleapis.com">\n<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\n'
                  '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap">')
    alts = "".join('<link rel="alternate" hreflang="{}" href="../{}/{}.html">'.format(HTML_LANG[l], l, slug) for l in LANGS)
    full = title + " | XTK" if slug != "index" else "XTK — " + t("co_name")
    doc = ('<!doctype html>\n<html lang="{}">\n<head>\n<meta charset="utf-8">\n<meta name="viewport" content="width=device-width, initial-scale=1">\n'
           '<title>{}</title>\n<meta name="description" content="{}">\n<meta name="theme-color" content="#2f368e">\n'
           '<meta property="og:title" content="{}">\n<meta property="og:description" content="{}">\n<meta property="og:type" content="website">\n{}\n'
           '<link rel="icon" href="{}/img/logo.svg" type="image/svg+xml">\n<link rel="alternate icon" href="{}/img/favicon.png">\n'
           '{}\n<link rel="stylesheet" href="{}/css/style.css">\n</head>\n<body>\n'
           '{}\n<main id="main">\n{}\n</main>\n{}\n<script src="{}/js/main.js" defer></script>\n</body>\n</html>\n').format(
        HTML_LANG[lang], e(full), e(desc or t("co_tag")), e(full), e(desc or t("co_tag")), alts, A, A, fonts, A,
        header(slug, overlay, active), body, footer(), A)
    out = os.path.join(ROOT, lang, slug + ".html")
    with open(out, "w", encoding="utf-8") as f:
        f.write(doc)


# ---------------------------------------------------------------- pages
def p_home():
    def slide(n, media, btn_href, btn_label):
        return ('<div class="hero__slide{}">{}<div class="hero__body"><div class="wrap"><div class="hero__text">'
                '<span class="hero__eyebrow">{}</span><{} class="hero__title">{}</{}><p>{}</p>'
                '<div class="btns"><a class="btn btn--white" href="{}">{}</a><a class="btn btn--light" href="quote.html">{}</a></div></div></div></div></div>').format(
            " is-active" if n == 1 else "", media, t("hero%d_eye" % n), "h1" if n == 1 else "p", t("hero%d_t" % n), "h1" if n == 1 else "p",
            t("hero%d_p" % n), btn_href, btn_label, t("quote"))
    video = '<video class="hero__media" src="{0}/video/hero-lab.mp4" poster="{0}/img/photo/4773.jpg" autoplay muted loop playsinline preload="metadata"></video>'.format(A)
    hero = ('<section class="hero" aria-roledescription="carousel">{}{}{}<div class="hero__ui"><div class="wrap">'
            '<span class="hero__scroll">{}</span><div class="hero__nav"><span class="hero__count" aria-live="polite">01 / 03</span><span class="hero__bar"><i></i></span>'
            '<button class="hero__btn hero__btn--prev" type="button" aria-label="{}">{}</button><button class="hero__btn hero__btn--next" type="button" aria-label="{}">{}</button></div></div></div></section>').format(
        slide(1, video, "products.html", t("view_products")),
        slide(2, img("photo/46364.jpg", "", "hero__media", lazy=False), "healthcare.html", t("d_health")),
        slide(3, img("photo/4765.jpg", "", "hero__media", lazy=False), "services.html", t("services")),
        t("scroll"), t("prev"), ic("arrow"), t("next"), ic("arrow"))

    loader = '<div class="loader" aria-hidden="true"><img src="{}/img/logo.svg" alt=""></div><noscript><style>.loader{{display:none}}</style></noscript>'.format(A)
    strip = "".join('<img src="{}/img/photo/{}.jpg" alt="" loading="lazy">'.format(A, n) for n in ("4773", "46364", "4719", "35963", "4767", "47584", "4765", "4733"))
    marquee = '<div class="marquee" aria-hidden="true"><div class="marquee__track">{0}</div><div class="marquee__track">{0}</div></div>'.format(strip)
    tiles = "".join('<a class="gtile rv" style="--i:{}" href="{}.html#{}"><span class="gtile__ico">{}</span><b>{}</b><span>{}</span></a>'.format(
        i % 6, d["id"], gk, ic(GICON[gk]), t(gk), t(d["k"])) for i, (d, gk) in enumerate((d, gk) for d in DIVS for gk, _ in d["groups"]))
    groups = '<section class="sec"><div class="wrap">{}<div class="gtiles">{}</div></div></section>'.format(sec_head(t("home_groups"), t("home_groups_p")), tiles)
    statement = '<section class="sec sec--statement"><div class="wrap" style="max-width:1000px;text-align:center"><p class="lead rv">{}</p></div></section>'.format(t("statement"))

    feat = {it["img"]: (d, it) for d, _, it in all_items() if it["img"]}
    rail = "".join(product_card(*feat[k], i=i) for i, k in enumerate(FEATURED))
    divlist = "".join('<a href="{}.html"><span class="divlist__l">{}{}</span>{}</a>'.format(d["id"], ic(GICON[d["groups"][0][0]]), t(d["k"]), ic("arrow")) for d in DIVS)
    products = ('<section class="sec sec--surface"><div class="wrap"><div class="split split--38"><div>{}<nav class="divlist rv" aria-label="{}">{}</nav>'
                '<p style="margin-top:32px" class="rv">{}</p></div><div class="rail" tabindex="0" aria-label="{}">{}</div></div></div></section>').format(
        sec_head(t("products"), t("home_prod_p"), left=True), t("products"), divlist, arrow_link(t("all_products"), "products.html"), t("products"), rail)

    btns, panels = [], []
    for i, (k, pic, chips, link) in enumerate(SOLS):
        btns.append('<button class="tabs__btn" type="button" role="tab" id="tab-{0}" aria-controls="panel-{0}" aria-selected="{1}"{2}>{3}</button>'.format(
            k, "true" if i == 0 else "false", "" if i == 0 else ' tabindex="-1"', t("sol_" + k)))
        panels.append('<div class="tabs__panel" role="tabpanel" id="panel-{0}" aria-labelledby="tab-{0}"{1}>{2}<h3 class="h3">{3}</h3><p>{4}</p><div class="chips">{5}</div><div class="btns">{6}</div></div>'.format(
            k, "" if i == 0 else " hidden", img(pic, ""), t("sol_" + k), t("sol_" + k + "_p"),
            "".join('<span class="tag">' + t(c) + "</span>" for c in chips), arrow_link(t("learn_more"), "solutions.html#" + k)))
    solutions = '<section class="sec"><div class="wrap">{}<div class="tabs rv"><div class="tabs__list" role="tablist" aria-label="{}">{}</div><div>{}</div></div></div></section>'.format(
        sec_head(t("solutions"), t("home_sol_p")), t("solutions"), "".join(btns), "".join(panels))

    stats = "".join('<div class="stat rv" style="--i:{}"><b data-count>{}</b><span>{}</span></div>'.format(i, v, t(k)) for i, (v, k) in enumerate(
        [("2016", "stat1"), ("20<sup>+</sup>", "stat2"), ("5", "stat3"), (str(N_ITEMS), "stat4")]))
    about = ('<section class="sec sec--tint"><div class="wrap"><div class="split"><div class="rv">{}</div><div class="split__img rv">{}</div></div>'
             '<div class="stats" style="margin-top:80px">{}</div></div></section>').format(
        '<div class="sec-head sec-head--left" style="margin-bottom:24px"><h2 class="h2">{}</h2></div><p>{}</p><p style="margin-top:1.2em">{}</p><p style="margin-top:32px">{}</p>'.format(
            t("home_about_h"), t("about_p1"), t("about_p2"), arrow_link(t("about_profile"), "about.html")),
        img("photo/4767.jpg", ""), stats)

    services = '<section class="sec"><div class="wrap">{}{}<p style="text-align:center;margin-top:48px" class="rv">{}</p></div></section>'.format(
        sec_head(t("services"), t("home_srv_p")), service_cards(), arrow_link(t("learn_more"), "services.html"))
    news = '<section class="sec"><div class="wrap">{}{}<p style="text-align:center;margin-top:48px" class="rv">{}</p></div></section>'.format(
        sec_head(t("news")), news_cards(), arrow_link(t("view_all"), "news.html"))
    contact = '<section class="sec sec--surface"><div class="wrap">{}{}</div></section>'.format(sec_head(t("home_contact_h")), location_block())
    why = '<section class="sec"><div class="wrap">{}<div class="grid g-4">{}</div></div></section>'.format(sec_head(t("why"), t("home_why_p")), why_cards())
    commit = '<section class="sec sec--surface"><div class="wrap">{}<div class="grid g-3 steps">{}</div></div></section>'.format(
        sec_head(t("commit_h"), t("commit_p")), steps(("commit1", "commit2", "commit3")))
    process = '<section class="sec sec--tint"><div class="wrap">{}<div class="grid g-4 steps">{}</div></div></section>'.format(
        sec_head(t("srv_process"), t("home_process_p")), steps(("step1", "step2", "step3", "step4")))
    page("index", t("home"), loader + hero + statement + marquee + products + groups + solutions + about + why + commit + services + process + news + contact + cta(), overlay=True)


def p_about():
    facts = ('<table class="spec"><tbody><tr><th scope="row">{}</th><td>{}</td></tr><tr><th scope="row">{}</th><td>{}</td></tr>'
             '<tr><th scope="row">{}</th><td>{}</td></tr><tr><th scope="row">{}</th><td>{}</td></tr>'
             '<tr><th scope="row">{}</th><td>{} {} · {} {}</td></tr><tr><th scope="row">{}</th><td>{}</td></tr></tbody></table>').format(
        t("f_name"), t("co_name") + " (XTK)", t("f_reg"), t("f_reg_v"), t("f_biz"), " · ".join(t(d["k"]) for d in DIVS),
        t("head_office"), t("addr_full"), t("contact"), t("tel"), TEL, t("fax"), FAX, t("email"), MAIL)
    why = why_cards()
    body = (phead(t("about_profile"), t("about_lead"), [(t("about"), "about.html")], "photo/4767.jpg")
            + '<section class="sec"><div class="wrap"><div class="split"><div class="rv"><div class="sec-head sec-head--left" style="margin-bottom:24px"><h2 class="h2">{}</h2></div><div class="prose"><p>{}</p><p>{}</p></div><div class="btns" style="margin-top:32px"><a class="btn" href="downloads.html">{}{}</a><a class="btn btn--ghost" href="vision.html">{}</a></div></div><div class="split__img rv">{}</div></div></div></section>'.format(
                t("co_name"), t("about_p1"), t("about_p2"), ic("download"), t("profile_dl"), t("vision"), img("photo/4733.jpg", ""))
            + '<section class="sec sec--surface"><div class="wrap">{}<div class="rv" style="max-width:960px;margin:0 auto;background:#fff">{}</div></div></section>'.format(sec_head(t("facts")), facts)
            + '<section class="sec" id="why"><div class="wrap">{}<div class="grid g-4">{}</div></div></section>'.format(sec_head(t("why")), why)
            + '<section class="sec sec--tint"><div class="wrap">{}{}</div></section>'.format(sec_head(t("our_divisions")), division_cards())
            + cta())
    page("about", t("about_profile"), body, t("about_lead"), active="about")


def p_vision():
    vals = "".join('<div class="step rv" style="--i:{}"><h3 class="h4">{}</h3><p>{}</p></div>'.format(i, t("val%d" % n), t("val%d_p" % n)) for i, n in enumerate((1, 2, 3, 4)))
    body = (phead(t("vision"), "", [(t("about"), "about.html")])
            + '<section class="sec"><div class="wrap" style="max-width:1000px;text-align:center"><span class="tag rv">{}</span><p class="lead rv" style="font-size:28px;line-height:1.7;margin-top:20px">{}</p></div></section>'.format(t("vision_h"), t("vision_p"))
            + '<section class="sec sec--surface"><div class="wrap"><div class="split"><div class="split__img rv">{}</div><div class="rv">{}<ul class="checks"><li>{}</li><li>{}</li><li>{}</li></ul></div></div></div></section>'.format(
                img("photo/4719.jpg", ""), '<div class="sec-head sec-head--left" style="margin-bottom:24px"><h2 class="h2">' + t("mission_h") + "</h2></div>", t("mission1"), t("mission2"), t("mission3"))
            + '<section class="sec"><div class="wrap">{}<div class="grid g-4 steps">{}</div></div></section>'.format(sec_head(t("values_h")), vals)
            + cta())
    page("vision", t("vision"), body, t("vision_p"), active="about")


def p_products():
    body = (phead(t("products"), t("prod_lead"))
            + '<section class="sec"><div class="wrap">{}</div></section>'.format(division_cards(3))
            + "".join('<section class="sec sec--tight{}"><div class="wrap"><div class="split">{}</div></div></section>'.format(
                " sec--surface" if i % 2 == 0 else "", (lambda a, b: a + b if i % 2 == 0 else b + a)(
                    '<div class="split__img rv" style="border:1px solid var(--line);background:#fff">' + img(d["img"], t(d["k"])) + "</div>",
                    '<div class="rv"><span class="tag">0{}</span><h2 class="h2" style="margin:16px 0">{}</h2><p>{}</p><div class="chips">{}</div><div class="btns" style="margin-top:28px"><a class="btn" href="{}.html">{}</a><a class="btn btn--ghost" href="quote.html">{}</a></div></div>'.format(
                        i + 1, t(d["k"]), t(d["k"] + "_long"), "".join('<span class="tag">' + t(g) + "</span>" for g, _ in d["groups"]), d["id"], t("view_products"), t("quote"))))
                for i, d in enumerate(DIVS))
            + '<section class="sec"><div class="wrap">{}<div class="grid g-3 steps">{}</div></div></section>'.format(sec_head(t("commit_h"), t("commit_p")), steps(("commit1", "commit2", "commit3")))
            + cta())
    page("products", t("products"), body, t("prod_lead"), active="products")


def subnav(cur):
    return '<nav class="subnav" aria-label="{}"><div class="wrap"><ul><li><a href="products.html">{}</a></li>{}</ul></div></nav>'.format(
        t("products"), t("all"), "".join('<li><a href="{}.html"{}>{}</a></li>'.format(d["id"], ' aria-current="page"' if d["id"] == cur else "", t(d["k"])) for d in DIVS))


def p_division(d):
    pills = '<button class="pill" type="button" data-g="all" aria-pressed="true">{}</button>'.format(t("all")) + "".join(
        '<button class="pill" type="button" data-g="{}" aria-pressed="false">{}</button>'.format(gk, t(gk)) for gk, _ in d["groups"])
    groups = "".join('<section class="pgroup" id="{}"><h2 class="h3 rv">{}</h2><p class="rv" style="margin-bottom:8px">{}</p><p class="small rv">{} {}</p><div class="grid g-4 keep-2">{}</div></section>'.format(
        gk, t(gk), t(gk + "_p"), len(items), t("items"), "".join(product_card(d, it, i, gk) for i, it in enumerate(items))) for gk, items in d["groups"])
    intro = ('<section class="sec sec--tight"><div class="wrap"><div class="split"><div class="rv"><div class="sec-head sec-head--left" style="margin-bottom:24px"><h2 class="h2">{}</h2></div>'
             '<p>{}</p><ul class="checks"><li>{}</li><li>{}</li><li>{}</li></ul></div><div class="split__img rv" style="border:1px solid var(--line)">{}</div></div></div></section>').format(
        t("div_about"), t(d["k"] + "_long"), t("commit1_p"), t("commit2_p"), t("commit3_p"), img(d["img"], t(d["k"])))
    filt = '<div class="filters" data-filter>{}</div>'.format(pills) if len(d["groups"]) > 1 else ""
    body = (phead(t(d["k"]), t(d["k"] + "_desc"), [(t("products"), "products.html")]) + subnav(d["id"])
            + intro + '<section class="sec sec--tight sec--surface"><div class="wrap">{}{}</div></section>'.format(filt, groups) + cta())
    page(d["id"], t(d["k"]), body, t(d["k"] + "_long"), active="products")


def p_product(d, gk, it):
    model = e(it["name"]) if d["id"] == "security" else t("sp_by_mfr")
    spec = "".join('<tr><th scope="row">{}</th><td>{}</td></tr>'.format(a, b) for a, b in [
        (t("sp_cat"), t(d["k"])), (t("sp_group"), t(gk)), (t("sp_model"), model), (t("sp_std"), t("sp_by_mfr")),
        (t("sp_warranty"), t("sp_on_request")), (t("sp_install"), t("sp_by_xtk"))])
    sibs = [x for _, items in d["groups"] for x in items if x["img"] and x is not it]
    same = [x for x in dict(d["groups"])[gk] if x["img"] and x is not it]
    rel = (same + [x for x in sibs if x not in same])[:4]
    body = ('<section class="phead" style="padding-bottom:32px"><div class="wrap">{}</div></section>'
            '<section class="sec sec--tight" style="padding-top:40px"><div class="wrap"><div class="pd"><div class="pd__img rv">{}</div><div class="rv">'
            '<span class="tag">{}</span><h1>{}</h1>{}<p>{}</p><div class="btns"><a class="btn" href="quote.html">{}</a>'
            '<a class="btn btn--ghost" href="{}" target="_blank" rel="noopener">{}{}</a></div>'
            '<h2 class="h4" style="margin-bottom:12px">{}</h2><table class="spec"><tbody>{}</tbody></table><p class="small" style="margin-top:12px">{}</p></div></div></div></section>'
            '<section class="sec sec--tight sec--tint"><div class="wrap"><div class="split" style="align-items:start"><div class="rv"><h2 class="h3">{}</h2><p style="margin-top:12px">{}</p><p style="margin-top:1.2em">{}</p></div>'
            '<div class="rv"><h2 class="h3">{}</h2><ul class="checks">{}</ul></div></div></div></section>'
            '<section class="sec sec--tight"><div class="wrap">{}<div class="grid g-3 steps">{}</div></div></section>'
            '<section class="sec sec--surface sec--tight"><div class="wrap">{}<div class="grid g-4 keep-2">{}</div></div></section>').format(
        crumb([(t("products"), "products.html"), (t(d["k"]), d["id"] + ".html")], e(it["name"])),
        img("products/" + it["img"] + ".jpg", it["name"], lazy=False), t(gk), e(it["name"]),
        '<p class="lead" style="font-size:18px;line-height:1.8;margin-bottom:12px">' + item_text(gk, it) + "</p>" + ('<p class="small" style="margin-bottom:12px">' + e(it["desc"]) + "</p>" if it["desc"] else ""),
        t("pd_p"), t("quote"), WA, ic("chat"), t("whatsapp"), t("pd_spec"), spec, t("pd_note"),
        t("pd_use"), t(gk + "_p"), t(d["k"] + "_long"),
        t("pd_srv"), "".join("<li><b>{}</b> — {}</li>".format(t(k), t(k + "_p")) for k, _ in SERVICES),
        sec_head(t("pd_order"), left=True), steps(("qh1", "qh2", "qh3"), with_p=False),
        sec_head(t("related"), left=True), "".join(product_card(d, x, i) for i, x in enumerate(rel))) + cta()
    page(it["slug"], it["name"], body, it["name"] + " — " + t(d["k"]), active="products")


def p_solutions():
    secs = []
    for i, (k, pic, chips, link) in enumerate(SOLS):
        text = ('<div class="rv"><span class="tag">{}</span><h2 class="h2" style="margin:16px 0">{}</h2><p>{}</p><h3 class="h4" style="margin-top:28px">{}</h3>'
                '<div class="chips">{}</div><div class="btns" style="margin-top:28px"><a class="btn" href="{}.html">{}</a><a class="btn btn--ghost" href="quote.html">{}</a></div></div>').format(
            "0%d" % (i + 1), t("sol_" + k), t("sol_" + k + "_p"), t("sol_rec"), "".join('<span class="tag">' + t(c) + "</span>" for c in chips), link, t("view_products"), t("quote"))
        pic_html = '<div class="split__img rv">' + img(pic, "") + "</div>"
        secs.append('<section class="sec{}" id="{}"><div class="wrap"><div class="split">{}</div></div></section>'.format(
            " sec--surface" if i % 2 else "", k, (pic_html + text) if i % 2 else (text + pic_html)))
    body = phead(t("solutions"), t("sol_lead"), image="photo/4779.jpg") + "".join(secs) + cta()
    page("solutions", t("solutions"), body, t("sol_lead"), active="solutions")


def p_services():
    steps = "".join('<div class="step rv" style="--i:{}"><h3 class="h4">{}</h3><p>{}</p></div>'.format(i, t("step%d" % n), t("step%d_p" % n)) for i, n in enumerate((1, 2, 3, 4)))
    fields = ('<div class="grid g-2"><div class="card rv"><div class="card__img" style="aspect-ratio:1/1">{}</div><div class="card__body"><h3 class="h4">{}</h3><p>{}</p></div></div>'
              '<div class="card rv" style="--i:1"><div class="card__img" style="aspect-ratio:1/1">{}</div><div class="card__body"><h3 class="h4">{}</h3><p>{}</p></div></div></div>').format(
        img("photo/service-medical.jpg", t("srv_med")), t("srv_med"), t("srv_med_p"), img("photo/service-security.jpg", t("d_sec")), t("d_sec"), t("srv_sec_p"))
    hot = ('<section class="cta on-dark"><div class="wrap rv"><h2 class="h2">{}</h2><p>{}: <a href="tel:{}" style="color:#fff;font-weight:600;font-size:28px">{}</a></p>'
           '<div class="btns"><a class="btn btn--white" href="{}" target="_blank" rel="noopener">{}{}</a><a class="btn btn--light" href="contact.html">{}</a></div></div></section>').format(
        t("srv_hot_h"), t("hotline"), MOB_HREF, MOB, WA, ic("chat"), t("whatsapp"), t("contact"))
    body = (phead(t("services"), t("srv_lead"), image="photo/4765.jpg")
            + '<section class="sec"><div class="wrap">{}{}</div></section>'.format(sec_head(t("services"), t("home_srv_p")), service_cards())
            + '<section class="sec sec--surface"><div class="wrap">{}<div class="grid g-4 steps">{}</div></div></section>'.format(sec_head(t("srv_process")), steps)
            + '<section class="sec"><div class="wrap">{}{}</div></section>'.format(sec_head(t("srv_fields")), fields) + hot)
    page("services", t("services"), body, t("srv_lead"), active="services")


def p_news():
    body = phead(t("news"), t("news_lead")) + '<section class="sec"><div class="wrap">{}</div></section>'.format(news_cards()) + cta()
    page("news", t("news"), body, t("news_lead"), active="news")
    k, date, cat, pic, slug = NEWS[0]
    art = ('<section class="phead"><div class="wrap">{}<p style="margin-top:20px"><span class="tag">{}</span> <time class="small" datetime="{}" style="margin-left:8px">{}</time></p><h1 style="max-width:900px">{}</h1></div></section>'
           '<section class="sec sec--tight"><div class="wrap"><div class="prose" style="margin:0 auto"><p class="lead" style="font-size:18px">{}</p><p>{}</p><figure style="margin:32px 0;border:1px solid var(--line)">{}</figure>'
           '<div class="btns"><a class="btn" href="security.html">{}</a><a class="btn btn--ghost" href="news.html">{}</a></div></div></div></section>').format(
        crumb([(t("news"), "news.html")], t(k + "_t")), t(cat), date, fdate(date), t(k + "_t"), t(k + "_p"), t(k + "_body"), img(pic, ""), t("view_products"), t("back_news"))
    page(slug, t(k + "_t"), art + cta(), t(k + "_p"), active="news")


def p_careers():
    jobs = "".join('<li class="rv"><div><h3 class="h4">{}</h3><p class="meta"><span>{}</span><span>{}</span><span>{}</span></p></div><a class="btn btn--ghost btn--sm" href="#apply">{}</a></li>'.format(
        t(k), t("vientiane"), t("full_time") if k == "job1" else t("job2"), t(k + "_m"), t("apply")) for k in ("job1", "job2"))
    f = form([field(t("f_fullname"), "name", req=True), field(t("f_phone"), "tel", "tel", req=True), field(t("email"), "email", "email"),
              field(t("f_position"), "position", "select", req=True, options=[t("job1"), t("job2")]),
              field(t("f_files"), "files", "file", full=True, hint=t("apply_p")), field(t("f_message"), "message", "textarea", full=True)], t("f_send_apply"))
    body = (phead(t("careers"), t("car_lead"), [(t("about"), "about.html")], "photo/4766.jpg")
            + '<section class="sec"><div class="wrap" style="max-width:1040px">{}<ul class="rows">{}</ul></div></section>'.format(sec_head(t("open_pos"), left=True), jobs)
            + '<section class="sec sec--surface" id="apply"><div class="wrap"><div class="split split--38"><div class="rv">{}<div class="aside-box" style="background:#fff"><h3 class="h4">{}</h3><p>{}: <a href="tel:{}"><b>{}</b></a></p></div></div><div class="panel rv">{}</div></div></div></section>'.format(
                sec_head(t("apply_h"), t("apply_p"), left=True), t("car_ask"), t("tel"), TEL_HREF, TEL, f))
    page("careers", t("careers"), body, t("car_lead"), active="about")


def p_contact():
    tiles = "".join('<a class="tile" href="{}">{}<b>{}</b><span>{}</span></a>'.format(h, ic(icon), t(k), v) for k, icon, h, v in [
        ("q_general", "phone", "tel:" + TEL_HREF, TEL), ("q_sales", "file", "quote.html", t("quote")),
        ("q_service", "tool", "tel:" + MOB_HREF, MOB), ("q_career", "users", "careers.html", t("careers"))])
    f = form([field(t("f_fullname"), "name", req=True), field(t("f_org"), "org"), field(t("f_phone"), "tel", "tel", req=True), field(t("email"), "email", "email", req=True),
              field(t("f_subject"), "subject", "select", full=True, options=[t("q_general"), t("q_sales"), t("q_service"), t("q_career"), t("f_other")]),
              field(t("f_message"), "message", "textarea", req=True, full=True)], t("f_send"))
    body = (phead(t("contact"), t("contact_lead"))
            + '<section class="sec sec--tight"><div class="wrap">{}<div class="tiles rv">{}</div></div></section>'.format(sec_head(t("quick"), left=True), tiles)
            + '<section class="sec sec--surface"><div class="wrap"><div class="split split--38"><div class="rv">{}{}</div><div class="panel rv">{}</div></div></div></section>'.format(
                sec_head(t("contact_form_h"), left=True), contact_aside().replace('class="aside-box"', 'class="aside-box" style="background:#fff"'), f)
            + '<section class="sec"><div class="wrap">{}{}</div></section>'.format(sec_head(t("home_contact_h")), location_block()))
    page("contact", t("contact"), body, t("contact_lead"), active="contact")


def p_quote():
    f = form([field(t("f_fullname"), "name", req=True), field(t("f_org"), "org", req=True), field(t("f_phone"), "tel", "tel", req=True), field(t("email"), "email", "email", req=True),
              field(t("f_division"), "division", "select", req=True, options=[t(d["k"]) for d in DIVS]),
              field(t("f_facility"), "facility", "select", options=[t("sol_" + k) for k, *_ in SOLS] + [t("f_other")]),
              field(t("f_product"), "product", req=True, full=True), field(t("f_qty"), "qty", "number"),
              field(t("f_message"), "message", "textarea", full=True)], t("f_send_quote"))
    how = "".join('<div class="step" style="margin-top:24px"><h3 class="h4" style="font-size:16px;line-height:26px">{}</h3></div>'.format(t(k)) for k in ("qh1", "qh2", "qh3"))
    body = (phead(t("quote"), t("quote_lead"))
            + '<section class="sec sec--tight"><div class="wrap"><div class="split split--38"><div class="rv"><h2 class="h3">{}</h2><div class="steps">{}</div><div style="margin-top:40px">{}</div></div><div class="panel rv">{}</div></div></div></section>'.format(
                t("quote_how"), how, contact_aside(), f))
    page("quote", t("quote"), body, t("quote_lead"))


def p_downloads():
    rows = ['<li class="rv"><div><h3 class="h4">{}</h3><p class="meta"><span>PDF</span><span>XTK</span></p></div><a class="btn btn--ghost btn--sm" href="#">{}{}</a></li>'.format(t("dl_profile"), ic("download"), t("download"))]
    for d in DIVS:
        rows.append('<li class="rv"><div><h3 class="h4">{} — {}</h3><p class="meta"><span>PDF</span><span>{}</span></p></div><a class="btn btn--ghost btn--sm" href="#">{}{}</a></li>'.format(
            t("dl_catalog"), t(d["k"]), t(d["k"] + "_desc"), ic("download"), t("download")))
    body = phead(t("downloads"), t("dl_lead")) + '<section class="sec sec--tight"><div class="wrap" style="max-width:1040px"><ul class="rows">{}</ul></div></section>'.format("".join(rows)) + cta()
    page("downloads", t("downloads"), body, t("dl_lead"))


def p_faq():
    qa = [(t("fq1"), t("fa1")), (t("fq2"), t("fa2")), (t("fq3"), t("fa3")), (t("fq4"), t("fa4")), (t("fq5"), t("addr_full")), (t("fq6"), t("fa6"))]
    items = "".join('<details{}><summary>{}{}</summary><p>{}</p></details>'.format(" open" if i == 0 else "", q, ic("chev"), a) for i, (q, a) in enumerate(qa))
    body = (phead(t("faq"), t("faq_lead"))
            + '<section class="sec sec--tight"><div class="wrap" style="max-width:1040px"><div class="faq rv">{}</div><div class="aside-box rv" style="margin-top:56px;text-align:center"><h2 class="h4">{}</h2><div class="btns" style="justify-content:center;margin-top:16px"><a class="btn" href="contact.html">{}</a><a class="btn btn--ghost" href="{}" target="_blank" rel="noopener">{}</a></div></div></div></section>'.format(
                items, t("faq_more"), t("contact"), WA, t("whatsapp")))
    page("faq", t("faq"), body, t("faq_lead"))


def p_legal(slug, title_k, keys):
    secs = "".join("<h2>{}</h2><p>{}</p>".format(t(k + "_h"), t(k + "_p")) for k in keys)
    body = phead(t(title_k)) + '<section class="sec sec--tight"><div class="wrap"><div class="prose" style="margin:0 auto"><p class="small">{}</p>{}<h2>{}</h2><p>{}<br><a href="mailto:{}">{}</a> · {}</p></div></div></section>'.format(
        t("legal_updated"), secs, t("contact"), t("co_name"), MAIL, MAIL, TEL)
    page(slug, t(title_k), body)


def p_sitemap():
    def box(title, links):
        return '<div><h2 class="h4">{}</h2>{}</div>'.format(title, "".join('<a href="{}.html">{}</a>'.format(p, lab) for p, lab in links))
    boxes = [box(t("company"), [("index", t("home")), ("about", t("about_profile")), ("vision", t("vision")), ("news", t("news")), ("careers", t("careers"))]),
             box(t("support"), [("solutions", t("solutions")), ("services", t("services")), ("quote", t("quote")), ("downloads", t("downloads")), ("faq", t("faq")), ("contact", t("contact")), ("privacy", t("privacy")), ("terms", t("terms"))])]
    for d in DIVS:
        boxes.append(box(t(d["k"]), [(d["id"], t("all") + " — " + t(d["k"]))] + [(it["slug"], e(it["name"])) for _, items in d["groups"] for it in items if it["slug"]]))
    body = phead(t("sitemap")) + '<section class="sec sec--tight"><div class="wrap"><div class="sitemap">{}</div></div></section>'.format("".join(boxes))
    page("sitemap", t("sitemap"), body)


def p_404():
    body = '<section class="nf"><div class="wrap"><b>404</b><h1 class="h2" style="margin-top:8px">{}</h1><p style="margin:12px 0 28px">{}</p><div class="btns" style="justify-content:center"><a class="btn" href="index.html">{}</a><a class="btn btn--ghost" href="sitemap.html">{}</a></div></div></section>'.format(
        t("nf_h"), t("nf_p"), t("nf_b"), t("sitemap"))
    page("404", t("nf_h"), body)


def main():
    global LI
    missing = [k for k, v in T.items() if len(v) != 4 or not all(v)]
    assert not missing, "incomplete strings: %s" % missing
    n = 0
    for LI, lang in enumerate(LANGS):
        os.makedirs(os.path.join(ROOT, lang), exist_ok=True)
        before = len(os.listdir(os.path.join(ROOT, lang)))
        p_home(); p_about(); p_vision(); p_products()
        for d in DIVS:
            p_division(d)
        for d, gk, it in all_items():
            if it["slug"]:
                p_product(d, gk, it)
        p_solutions(); p_services(); p_news(); p_careers(); p_contact(); p_quote(); p_downloads(); p_faq()
        p_legal("privacy", "privacy", ["pv1", "pv2", "pv3"]); p_legal("terms", "terms", ["tm1", "tm2", "tm3"])
        p_sitemap(); p_404()
        n += len(os.listdir(os.path.join(ROOT, lang)))
    with open(os.path.join(ROOT, "index.html"), "w", encoding="utf-8") as f:
        f.write('<!doctype html>\n<html lang="lo">\n<head>\n<meta charset="utf-8">\n<title>XTK</title>\n<meta http-equiv="refresh" content="0; url=lo/index.html">\n'
                '<link rel="canonical" href="lo/index.html">\n</head>\n<body>\n<p><a href="lo/index.html">ລາວ</a> · <a href="en/index.html">English</a> · <a href="th/index.html">ไทย</a> · <a href="zh/index.html">中文</a></p>\n</body>\n</html>\n')
    print("pages written:", n, "| product items:", N_ITEMS)


if __name__ == "__main__":
    main()
