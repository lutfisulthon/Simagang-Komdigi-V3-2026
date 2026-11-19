"""Membuat juknis.html -> Petunjuk-Teknis-SIMAGANG.pdf (A5) dan memeriksa teks yang meluap.
Pakai: python render.py [folder_pratinjau_png]"""
import os, subprocess, sys, json
from playwright.sync_api import sync_playwright

HERE = os.path.dirname(os.path.abspath(__file__))
subprocess.check_call([sys.executable, os.path.join(HERE, "build.py")])
URL = "file:///" + os.path.join(HERE, "juknis.html").replace("\\", "/")
PDF = os.path.join(HERE, "Petunjuk-Teknis-SIMAGANG.pdf")
PREV = sys.argv[1] if len(sys.argv) > 1 else None

with sync_playwright() as p:
    b = p.chromium.launch(channel="chrome", headless=True)
    pg = b.new_page(viewport={"width": 900, "height": 1000})
    pg.goto(URL)
    pg.wait_for_load_state("networkidle")
    pg.evaluate("document.fonts.ready")
    res = pg.evaluate("""() => [...document.querySelectorAll('.pg')].map((s, i) => {
        const bd = s.querySelector('.body');
        if (!bd) return {i: i + 1, over: 0, used: 0, h: 0};
        const kids = [...bd.children]; const last = kids[kids.length - 1];
        const used = last ? last.getBoundingClientRect().bottom - bd.getBoundingClientRect().top : 0;
        return {i: i + 1, over: bd.scrollHeight - bd.clientHeight, used: Math.round(used), h: Math.round(bd.clientHeight)};
    })""")
    bad = [r for r in res if r["over"] > 1]
    for r in res:
        print(f'hal {r["i"]:>2}: terpakai {r["used"]:>4}/{r["h"]} px', "  <-- MELUAP %d px" % r["over"] if r["over"] > 1 else "")
    pg.pdf(path=PDF, width="148mm", height="210mm", print_background=True, prefer_css_page_size=True)
    if PREV:
        os.makedirs(PREV, exist_ok=True)
        for i, el in enumerate(pg.query_selector_all(".pg")):
            el.screenshot(path=os.path.join(PREV, f"p{i + 1:02d}.png"))
    b.close()
print("PDF:", PDF, "| meluap:", len(bad))
