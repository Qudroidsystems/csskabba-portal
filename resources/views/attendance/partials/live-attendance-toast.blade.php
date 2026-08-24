{{-- resources/views/attendance/partials/live-attendance-toast.blade.php
     Include this once in your master layout (e.g. right before </body> in
     app.blade.php) so punch toasts show up on every admin page, not just
     the attendance report. --}}
<div id="latToastStack" style="position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:10px;max-width:320px;"></div>

<style>
.lat-toast {
    background:#fff; border-left:4px solid #4f5fff; border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.12); padding:12px 16px;
    display:flex; align-items:center; gap:10px; font-family:'Outfit',sans-serif;
    animation:latIn .25s ease;
}
.lat-toast.late { border-left-color:#d97706; }
.lat-toast-icon {
    width:34px; height:34px; border-radius:9px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    background:#eef2ff; color:#4f5fff; font-size:15px;
}
.lat-toast.late .lat-toast-icon { background:#fef3c7; color:#d97706; }
.lat-toast-name { font-weight:600; font-size:13px; color:#1e2a3a; }
.lat-toast-sub  { font-size:11.5px; color:#6b7280; }
@keyframes latIn { from{opacity:0;transform:translateX(24px);} to{opacity:1;transform:translateX(0);} }
</style>

<script>
(function () {
    // Persist last-seen id per browser tab session so a refresh doesn't
    // replay every punch from the day as a toast.
    let lastId = parseInt(sessionStorage.getItem('lat_last_id') || '0', 10);
    let primed = lastId > 0; // on first load, just sync the pointer — don't toast the backlog

    function showToast(item) {
        const stack = document.getElementById('latToastStack');
        if (!stack) return;

        const el = document.createElement('div');
        el.className = 'lat-toast' + (item.is_late ? ' late' : '');
        el.innerHTML = `
            <div class="lat-toast-icon"><i class="ri-${item.is_late ? 'time-line' : 'checkbox-circle-line'}"></i></div>
            <div>
                <div class="lat-toast-name">${item.name}</div>
                <div class="lat-toast-sub">${item.type === 'staff' ? 'Staff' : 'Student'} clocked in at ${item.punch_time}${item.is_late ? ' — Late' : ''}</div>
            </div>
        `;
        stack.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; }, 5000);
        setTimeout(() => el.remove(), 5400);
    }

    function poll() {
        fetch(`{{ route('attendance.live-feed') }}?since_id=${lastId}`, {
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.logs && data.logs.length) {
                if (primed) {
                    data.logs
                        .filter(l => l.type === 'staff') // change/remove this filter to also toast student punches
                        .forEach(showToast);
                }
                primed = true;
            }
            lastId = data.last_id || lastId;
            sessionStorage.setItem('lat_last_id', lastId);
        })
        .catch(() => {}) // silent — a missed poll just gets picked up next cycle
        .finally(() => setTimeout(poll, 6000));
    }

    poll();
})();
</script>