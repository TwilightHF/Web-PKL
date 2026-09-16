// notifications.js
// Dipakai bersama di semua halaman (index.php, inbox.php, report.php,
// setting.php) untuk menampilkan dropdown bell notifikasi.
// Polling ke api/notifications.php tiap NOTIF_POLL_INTERVAL ms.

(function () {
    const API_URL = "api/notifications.php";
    const POLL_INTERVAL = 30000; // 30 detik
    const LAST_SEEN_KEY = "netops_notif_last_seen";

    const iconMap = {
        order_baru: { icon: "bi-box-seam", color: "text-primary" },
        inbox_baru: { icon: "bi-inbox", color: "text-warning" }
    };

    function getLastSeen() {
        return localStorage.getItem(LAST_SEEN_KEY);
    }

    function setLastSeen(iso) {
        localStorage.setItem(LAST_SEEN_KEY, iso);
    }

    function timeAgo(timestamp) {
        const then = new Date(timestamp);
        if (isNaN(then.getTime())) return "";

        const diffSec = Math.floor((Date.now() - then.getTime()) / 1000);

        if (diffSec < 60) return "Baru saja";
        if (diffSec < 3600) return Math.floor(diffSec / 60) + " menit lalu";
        if (diffSec < 86400) return Math.floor(diffSec / 3600) + " jam lalu";
        return Math.floor(diffSec / 86400) + " hari lalu";
    }

    function renderList(notifications) {
        const listEl = document.getElementById("notifList");
        if (!listEl) return;

        if (!notifications.length) {
            listEl.innerHTML = '<div class="text-center text-muted small p-4">Belum ada notifikasi.</div>';
            return;
        }

        const lastSeen = getLastSeen();

        listEl.innerHTML = notifications.map(n => {
            const meta = iconMap[n.type] || { icon: "bi-bell", color: "text-secondary" };
            const isUnread = !lastSeen || new Date(n.timestamp) > new Date(lastSeen);

            return `
                <div class="list-group-item small ${isUnread ? 'bg-light' : ''}">
                    <div class="d-flex gap-2">
                        <i class="bi ${meta.icon} ${meta.color} mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="${isUnread ? 'fw-semibold' : ''}">${escapeHtml(n.message)}</div>
                            <div class="text-muted" style="font-size:.75rem;">${timeAgo(n.timestamp)}</div>
                        </div>
                        ${isUnread ? '<span class="badge bg-primary rounded-circle p-1 align-self-center" style="width:8px;height:8px;"></span>' : ''}
                    </div>
                </div>`;
        }).join("");
    }

    function escapeHtml(str) {
        const div = document.createElement("div");
        div.textContent = str ?? "";
        return div.innerHTML;
    }

    function updateBadge(notifications) {
        const badge = document.getElementById("notifBadge");
        if (!badge) return;

        const lastSeen = getLastSeen();
        const unreadCount = lastSeen
            ? notifications.filter(n => new Date(n.timestamp) > new Date(lastSeen)).length
            : notifications.length;

        if (unreadCount > 0) {
            badge.textContent = unreadCount > 9 ? "9+" : unreadCount;
            badge.classList.remove("d-none");
        } else {
            badge.classList.add("d-none");
        }
    }

    let latestNotifications = [];

    async function fetchNotifications() {
        try {
            const res = await fetch(API_URL);
            const data = await res.json();

            if (data.success) {
                latestNotifications = data.notifications || [];
                renderList(latestNotifications);
                updateBadge(latestNotifications);
            }
        } catch (err) {
            console.error("Gagal memuat notifikasi:", err);
            const listEl = document.getElementById("notifList");
            if (listEl) listEl.innerHTML = '<div class="text-center text-danger small p-4">Gagal memuat notifikasi.</div>';
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const bellBtn = document.getElementById("notifBellBtn");
        if (!bellBtn) return; // halaman ini tidak punya bell (jaga-jaga)

        fetchNotifications();
        setInterval(fetchNotifications, POLL_INTERVAL);

        // Tandai semua sudah dibaca begitu dropdown dibuka
        bellBtn.addEventListener("shown.bs.dropdown", () => {
            setLastSeen(new Date().toISOString());
            updateBadge(latestNotifications);
            renderList(latestNotifications);
        });
    });
})();