# Supplier data and copy (P0-A10, spec: docs/plan/08-suppliers.md).
#
# SUPPLIERS / SUPPLY hold the real data from the client (§7 checklist). While they are empty the live
# build renders no supplier UI at all. `build.py --preview` swaps in SAMPLE_* below: clearly fictional
# names, no logos, written to preview/ only. Never put guessed real company names or logos here.
#
# Supplier fields: slug, name, country (ISO code in COUNTRIES), web, logo (file in public/assets/img/suppliers/,
# width, height) or None, on_dark, featured, about (lo, en, th, zh) or None, partnership (key in SUP) or None, since.
# SUPPLY: product name exactly as in build.py DIVS -> supplier slugs, the first one is the primary supplier.

SUPPLIERS = []
SUPPLY = {}


def _s(letter, country, featured=False, about=False, partnership=None, since=None):
    text = ("ຂໍ້ຄວາມຕົວຢ່າງ. ຂໍ້ມູນແນະນຳຜູ້ສະໜອງຕົວຈິງຈະຖືກເພີ່ມເມື່ອໄດ້ຮັບຈາກລູກຄ້າ.",
            "Sample text. The supplier's real profile will be added once the client provides it.",
            "ข้อความตัวอย่าง ข้อมูลแนะนำซัพพลายเออร์จริงจะเพิ่มเมื่อได้รับจากลูกค้า",
            "示例文本。供应商的真实简介将在客户提供后添加。")
    return {"slug": "sample-supplier-" + letter.lower(), "name": "Sample Supplier " + letter, "country": country, "web": "https://example.com/",
            "logo": None, "on_dark": False, "featured": featured, "about": text if about else None, "partnership": partnership, "since": since}


SAMPLE_SUPPLIERS = [
    _s("A", "CN", featured=True, about=True, partnership="sup_auth", since=2018),
    _s("B", "JP", featured=True, about=True),
    _s("C", "KR", featured=True),
    _s("D", "DE", featured=True, about=True),
    _s("E", "IN", featured=True), _s("F", "TH", featured=True), _s("G", "US"), _s("H", "GB"),
]

# Sample links by product group, so every product has at least one supplier and some have several.
_BY_GROUP = {
    "g_classes": ["sample-supplier-e", "sample-supplier-f"], "g_imaging": ["sample-supplier-a"], "g_diagnostic": ["sample-supplier-b", "sample-supplier-c"],
    "g_surgical": ["sample-supplier-d"], "g_analyzers": ["sample-supplier-a"], "g_reagents": ["sample-supplier-d", "sample-supplier-a", "sample-supplier-g"],
    "g_disposable": ["sample-supplier-c", "sample-supplier-f"], "g_paper": ["sample-supplier-b"], "g_xray": ["sample-supplier-g"],
    "g_walk": ["sample-supplier-h"], "g_hand": ["sample-supplier-h"], "g_trace": ["sample-supplier-g", "sample-supplier-h"],
}


def sample_supply(divs):
    out = {}
    for d in divs:
        for gk, items in d["groups"]:
            for i, it in enumerate(items):
                slugs = _BY_GROUP[gk]
                out[it["name"]] = slugs if i % 3 == 0 else slugs[i % len(slugs):][:1]
    return out


COUNTRIES = {
    "CN": ("ຈີນ", "China", "จีน", "中国"),
    "JP": ("ຍີ່ປຸ່ນ", "Japan", "ญี่ปุ่น", "日本"),
    "KR": ("ເກົາຫຼີໃຕ້", "South Korea", "เกาหลีใต้", "韩国"),
    "DE": ("ເຢຍລະມັນ", "Germany", "เยอรมนี", "德国"),
    "IN": ("ອິນເດຍ", "India", "อินเดีย", "印度"),
    "TH": ("ໄທ", "Thailand", "ไทย", "泰国"),
    "US": ("ສະຫະລັດອາເມລິກາ", "United States", "สหรัฐอเมริกา", "美国"),
    "GB": ("ສະຫະລາຊະອານາຈັກ", "United Kingdom", "สหราชอาณาจักร", "英国"),
}

# Merged into T. "{}" / "{n}" / "{name}" are filled in by build.py.
SUP = {
    "sup": ("ຜູ້ສະໜອງ", "Suppliers", "ซัพพลายเออร์", "供应商"),
    "sup_h": ("ຜູ້ສະໜອງຂອງພວກເຮົາ", "Our suppliers", "ซัพพลายเออร์ของเรา", "我们的供应商"),
    "sup_lead": ("XTK ຈຳໜ່າຍຜະລິດຕະພັນຈາກຜູ້ສະໜອງເຫຼົ່ານີ້.", "XTK distributes products from the following suppliers.",
                 "XTK จัดจำหน่ายผลิตภัณฑ์จากซัพพลายเออร์เหล่านี้", "XTK 分销以下供应商的产品。"),
    "sup_mega_p": ("ຜະລິດຕະພັນທີ່ XTK ຈຳໜ່າຍ ມາຈາກຜູ້ສະໜອງທີ່ໄດ້ມາດຕະຖານ.", "The products XTK distributes come from suppliers that meet recognised standards.",
                   "ผลิตภัณฑ์ที่ XTK จัดจำหน่ายมาจากซัพพลายเออร์ที่ได้มาตรฐาน", "XTK 分销的产品均来自符合标准的供应商。"),
    "sup_meta": ("{} ຜູ້ສະໜອງ · {} ປະເທດ", "{} suppliers · {} countries", "{} ซัพพลายเออร์ · {} ประเทศ", "{} 家供应商 · {} 个国家"),
    "sup_all": ("ເບິ່ງຜູ້ສະໜອງທັງໝົດ", "View all suppliers", "ดูซัพพลายเออร์ทั้งหมด", "查看全部供应商"),
    "sup_stat1": ("ຜູ້ສະໜອງ", "Suppliers", "ซัพพลายเออร์", "供应商"),
    "sup_stat2": ("ປະເທດ", "Countries", "ประเทศ", "国家"),
    "sup_stat3": ("ຜະລິດຕະພັນຈາກຜູ້ສະໜອງເຫຼົ່ານີ້", "Products from these suppliers", "ผลิตภัณฑ์จากซัพพลายเออร์เหล่านี้", "来自这些供应商的产品"),
    "sup_featured": ("ຜູ້ສະໜອງຫຼັກ", "Key suppliers", "ซัพพลายเออร์หลัก", "主要供应商"),
    "sup_more": ("ແລະ ອີກ {} ສິນຄ້າ", "and {} more products", "และอีก {} รายการ", "及其他 {} 项产品"),
    "sup_view": ("ເບິ່ງສິນຄ້າທັງໝົດຈາກ {}", "View all products from {}", "ดูสินค้าทั้งหมดจาก {}", "查看 {} 的全部产品"),
    "sup_grid_h": ("ຜູ້ສະໜອງທັງໝົດ", "All suppliers", "ซัพพลายเออร์ทั้งหมด", "全部供应商"),
    "sup_result": ("ສະແດງ {} ຜູ້ສະໜອງ", "Showing {} suppliers", "แสดง {} ซัพพลายเออร์", "显示 {} 家供应商"),
    "sup_cta_h": ("ຕ້ອງການສິນຄ້າຈາກຜູ້ສະໜອງໃດໜຶ່ງ?", "Looking for products from a particular supplier?",
                  "ต้องการสินค้าจากซัพพลายเออร์รายใด?", "需要某一供应商的产品？"),
    "sup_sells": ("XTK ຈຳໜ່າຍ {n} ຜະລິດຕະພັນຈາກ {name} ໃນ ສປປ ລາວ.", "XTK distributes {n} products from {name} in the Lao PDR.",
                  "XTK จัดจำหน่ายผลิตภัณฑ์ {n} รายการจาก {name} ใน สปป.ลาว", "XTK 在老挝分销 {name} 的 {n} 项产品。"),
    "sup_web": ("ເວັບໄຊຂອງຜູ້ສະໜອງ", "Supplier website", "เว็บไซต์ซัพพลายเออร์", "供应商网站"),
    "sup_prod_h": ("ຜະລິດຕະພັນຈາກ {} ທີ່ XTK ຈຳໜ່າຍ", "Products from {} distributed by XTK", "ผลิตภัณฑ์จาก {} ที่ XTK จัดจำหน่าย", "XTK 分销的 {} 产品"),
    "sup_about": ("ກ່ຽວກັບ {}", "About {}", "เกี่ยวกับ {}", "关于 {}"),
    "sup_others": ("ຜູ້ສະໜອງອື່ນໃນຂະແໜງດຽວກັນ", "Other suppliers in the same divisions", "ซัพพลายเออร์อื่นในกลุ่มเดียวกัน", "同领域的其他供应商"),
    "sup_since": ("ຮ່ວມງານຕັ້ງແຕ່ປີ {}", "Partner since {}", "ร่วมงานตั้งแต่ปี {}", "自 {} 年起合作"),
    "sup_auth": ("ຕົວແທນຈຳໜ່າຍທີ່ໄດ້ຮັບອະນຸຍາດ", "Authorised distributor", "ตัวแทนจำหน่ายที่ได้รับอนุญาต", "授权经销商"),
    "sup_title": ("ຜະລິດຕະພັນ {} ໃນ ສປປ ລາວ", "{} products in Laos", "ผลิตภัณฑ์ {} ในลาว", "{} 产品 · 老挝"),
    "sp_sup": ("ຜູ້ສະໜອງ", "Supplier", "ซัพพลายเออร์", "供应商"),
    "supplied_by": ("ສະໜອງໂດຍ", "Supplied by", "จัดหาโดย", "供应商"),
    "sup_other_from": ("ເບິ່ງສິນຄ້າອື່ນຈາກ {}", "See other products from {}", "ดูสินค้าอื่นจาก {}", "查看 {} 的其他产品"),
    "sup_by": ("ໂດຍ {}", "By {}", "โดย {}", "供应商：{}"),
    "preview_note": ("ໜ້າຕົວຢ່າງ (preview): ຊື່ ແລະ ຂໍ້ມູນຜູ້ສະໜອງທັງໝົດເປັນຂໍ້ມູນສົມມຸດ ລໍຖ້າຂໍ້ມູນຈິງຈາກລູກຄ້າ.",
                     "Preview: all supplier names and details are fictional placeholders until the client sends the real data.",
                     "หน้าตัวอย่าง: ชื่อและข้อมูลซัพพลายเออร์ทั้งหมดเป็นข้อมูลสมมติ รอข้อมูลจริงจากลูกค้า",
                     "预览：所有供应商名称和信息均为虚构示例，待客户提供真实数据。"),
}
