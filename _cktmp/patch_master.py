#!/usr/bin/env python3
"""Wire the portal theme engine into resources/views/layouts/master.blade.php.
Usage: patch_master.py <portal-root>   (each replacement must match exactly once)"""
import os, sys, shutil

root = sys.argv[1]
rel = 'resources/views/layouts/master.blade.php'
path = os.path.join(root, rel)
s = open(path, encoding='utf-8').read()

def rep(old, new, count=1):
    global s
    n = s.count(old)
    if n != count:
        sys.exit(f'ABORT: expected {count}, found {n}: {old[:120]!r}')
    s = s.replace(old, new)

if 'portal-theme.js' in s:
    sys.exit('already patched')

# 1. Theme engine right after the template's layout.js, before first paint
rep("""<script src="{{ asset('theme/layouts/assets/js/layout.js') }}"></script>""",
"""<script src="{{ asset('theme/layouts/assets/js/layout.js') }}"></script>
    {{-- Portal themes: appearance (light/dark/auto) + style (default/apple). Must stay in <head>. --}}
    <script src="{{ asset('js/portal-theme.js') }}?v={{ @filemtime(public_path('js/portal-theme.js')) }}"></script>""")

# 2. Theme stylesheet loaded last
i = s.index('</head>')
s = s[:i] + """    <link href="{{ asset('css/portal-theme.css') }}?v={{ @filemtime(public_path('css/portal-theme.css')) }}" rel="stylesheet">
""" + s[i:]

# 3. Top-bar menu: appearance + style
old_btn_icon = """<i id="theme-icon" class="bi bi-sun align-middle fs-3xl"></i>"""
rep(old_btn_icon, """<i id="theme-icon" class="ri-sun-line align-middle fs-3xl"></i>""")
rep("""<button type="button" id="theme-toggle-btn" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" style="width:38px;height:38px;">""",
"""<button type="button" id="theme-toggle-btn" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" style="width:38px;height:38px;" title="Theme" aria-label="Theme" aria-haspopup="menu">""")
rep("""min-width:170px;background:var(--vz-dropdown-bg,#fff);border:1px solid var(--vz-border-color,#e9ebec);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:9999;overflow:hidden;padding:6px;">""",
"""min-width:210px;background:var(--tb-secondary-bg,#fff);border:1px solid var(--tb-border-color,#e9ebec);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:9999;overflow:hidden;padding:6px;" role="menu">""")
ITEM = 'class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none"'
rep(f"""<a href="javascript:void(0)" {ITEM} data-mode="light"><i class="bi bi-sun"></i> Light</a>
                            <a href="javascript:void(0)" {ITEM} data-mode="dark"><i class="bi bi-moon"></i> Dark</a>
                            <a href="javascript:void(0)" {ITEM} data-mode="auto"><i class="bi bi-moon-stars"></i> Auto</a>""",
f"""<div class="pt-menu-label">Appearance</div>
                            <a href="javascript:void(0)" {ITEM} data-mode="light" role="menuitemradio"><i class="ri-sun-line"></i> Light<i class="ri-check-line pt-check"></i></a>
                            <a href="javascript:void(0)" {ITEM} data-mode="dark" role="menuitemradio"><i class="ri-moon-line"></i> Dark<i class="ri-check-line pt-check"></i></a>
                            <a href="javascript:void(0)" {ITEM} data-mode="auto" role="menuitemradio"><i class="ri-contrast-2-line"></i> Auto <small>(system)</small><i class="ri-check-line pt-check"></i></a>
                            <div class="pt-menu-sep"></div>
                            <div class="pt-menu-label">Style</div>
                            <a href="javascript:void(0)" {ITEM} data-ui="default" role="menuitemradio"><i class="ri-layout-grid-line"></i> Default<i class="ri-check-line pt-check"></i></a>
                            <a href="javascript:void(0)" {ITEM} data-ui="apple" role="menuitemradio"><i class="ri-apple-line"></i> Apple<i class="ri-check-line pt-check"></i></a>""")

# 4. initTheme → delegate to PortalTheme
a = s.index('    function initTheme() {')
b = s.index('\n    }\n', a) + len('\n    }\n')
s = s[:a] + """    function initTheme() {
        // Appearance + style are applied by js/portal-theme.js (loaded in <head>);
        // this only wires up the top-bar menu and keeps it in sync.
        var PT     = window.PortalTheme;
        var iconEl = document.getElementById('theme-icon');
        var ICON   = { light:'ri-sun-line align-middle fs-3xl', dark:'ri-moon-line align-middle fs-3xl', auto:'ri-contrast-2-line align-middle fs-3xl' };

        function setMode(m) {
            if (PT) return PT.setMode(m);
            var dark = m === 'dark' || (m === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
            try { localStorage.setItem('app-theme', m); } catch (e) {}
            sync();
        }
        function sync() {
            var mode = PT ? PT.mode : (localStorage.getItem('app-theme') || 'light');
            var ui   = PT ? PT.ui   : 'default';
            var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            if (iconEl) iconEl.className = ICON[mode] || ICON.light;
            var r = document.getElementById(dark ? 'layout-mode-dark' : 'layout-mode-light');
            if (r) r.checked = true;
            qsa('.theme-mode-item').forEach(function(a){
                var on = a.hasAttribute('data-ui') ? a.getAttribute('data-ui') === ui : a.getAttribute('data-mode') === mode;
                a.classList.toggle('is-active', on);
                a.setAttribute('aria-checked', on ? 'true' : 'false');
            });
        }
        qsa('.theme-mode-item').forEach(function(a){
            a.addEventListener('click', function(e){
                e.preventDefault();
                if (a.hasAttribute('data-ui')) { if (PT) PT.setUi(a.getAttribute('data-ui')); }
                else setMode(a.getAttribute('data-mode'));
                var dd = document.getElementById('theme-dropdown');
                if (dd) dd.style.display = 'none';
            });
        });
        qsa('[name="data-bs-theme"]').forEach(function(r){ r.addEventListener('change', function(){ setMode(r.value); }); });
        document.addEventListener('portal-theme-change', sync);
        sync();
    }
""" + s[b:]

# 5. Customizer "reset" also clears the style choice
rep("""sessionStorage.clear(); localStorage.removeItem('app-theme'); location.reload();""",
"""sessionStorage.clear(); localStorage.removeItem('app-theme'); localStorage.removeItem('app-ui'); location.reload();""")

shutil.copy2(path, path + '.bak_theme')
open(path, 'w', encoding='utf-8').write(s)
print('patched', path)
