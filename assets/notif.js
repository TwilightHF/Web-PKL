/**
 * assets/notif.js
 * Notifikasi otomatis: "Order Baru" (untuk semua role) dan "Inbox Baru"
 * (khusus role yang kategori-nya baru cocok dengan kolom AZ).
 *
 * CARA PASANG di tiap halaman (index.php, inbox.php, report.php, setting.php):
 * Tambahkan 2 baris ini SEBELUM tag </body>, atau di mana saja setelah
 * <body> dibuka (urutan tidak masalah, script ini nunggu DOM siap):
 *
 *   <script>
 *     window.NOTIF_USERNAME = "<?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES) ?>";
 *     window.NOTIF_ROLE     = "<?= htmlspecialchars(strtoupper($_SESSION['role'] ?? ''), ENT_QUOTES) ?>";
 *   </script>
 *   <script src="assets/notif.js"></script>
 *
 * Script ini otomatis mencari ikon lonceng (elemen dengan class "bi-bell")
 * di navbar, lalu menambahkan badge jumlah notif belum dibaca + dropdown
 * daftar notifikasi saat diklik - TANPA perlu ubah HTML apapun di halaman.
 */

(function () {
  // >>> URL Apps Script dashboard (yang punya mode ?notifications=1) <<<
  const NOTIF_GAS_URL = "https://script.google.com/macros/s/AKfycbxuXndEYpie-gQJXBet3-hbt0HvntCarFiwEGJ_03O980gUjl5LYiHil9h7Nx6Zf01wVA/exec";

  const POLL_INTERVAL_MS = 30000; // cek tiap 30 detik
  const MAX_STORED_NOTIF = 50;

  const username = window.NOTIF_USERNAME || "guest";
  const roleRaw = window.NOTIF_ROLE || "";

  const SNAPSHOT_KEY = "netops_notif_snapshot_" + username;
  const NOTIF_LIST_KEY = "netops_notif_list_" + username;

  // ------------------------------------------------------------
  // Logic kategori role - SAMA PERSIS dengan parseRole/isCategoryMatch
  // di code.gs, supaya deteksi "Inbox Baru" konsisten dengan backend.
  // ------------------------------------------------------------
  function parseRole(role) {
    const r = (role || "").toString().toUpperCase().trim();
    if (r.indexOf("MSO") === 0) return { cat: "MSO" };
    if (r.indexOf("MBB") === 0) return { cat: "MBB" };
    if (r.indexOf("SS") === 0) return { cat: "SS" };
    if (r.indexOf("ED") === 0) return { cat: "ED" };
    return { cat: "" };
  }

  function isCategoryMatch(cat, azValue) {
    if (!cat) return false; // role tak dikenal -> tidak pernah dapat notif "Inbox Baru"
    const az = (azValue || "").toString().toUpperCase().trim();
    switch (cat) {
      case "MSO": return az.indexOf("SO") !== -1 && az.indexOf("MBB") === -1;
      case "MBB": return az.indexOf("MBB/SO") !== -1;
      case "SS":  return az.indexOf("DWS") !== -1;
      case "ED":  return az.indexOf("TA/ED") !== -1 || az.indexOf("TA-ED") !== -1 || az.indexOf("DID") !== -1;
      default:    return false;
    }
  }

  const userCat = parseRole(roleRaw).cat;

  // ------------------------------------------------------------
  // Storage helper
  // ------------------------------------------------------------
  function loadSnapshot() {
    try {
      const raw = localStorage.getItem(SNAPSHOT_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (e) { return null; }
  }

  function saveSnapshot(map) {
    try { localStorage.setItem(SNAPSHOT_KEY, JSON.stringify(map)); } catch (e) {}
  }

  function loadNotifList() {
    try {
      const raw = localStorage.getItem(NOTIF_LIST_KEY);
      return raw ? JSON.parse(raw) : [];
    } catch (e) { return []; }
  }

  function saveNotifList(list) {
    try { localStorage.setItem(NOTIF_LIST_KEY, JSON.stringify(list.slice(0, MAX_STORED_NOTIF))); } catch (e) {}
  }

  // ------------------------------------------------------------
  // UI: bungkus ikon lonceng dengan badge + dropdown
  // ------------------------------------------------------------
  let dropdownEl = null;
  let badgeEl = null;

  function setupBellUI() {
    const bell = document.querySelector(".bi-bell");
    if (!bell) return null; // halaman ini tidak punya ikon lonceng

    const wrapper = document.createElement("div");
    wrapper.style.position = "relative";
    wrapper.style.display = "inline-block";
    wrapper.style.cursor = "pointer";

    bell.parentNode.insertBefore(wrapper, bell);
    wrapper.appendChild(bell);

    badgeEl = document.createElement("span");
    badgeEl.className = "badge rounded-pill bg-danger";
    badgeEl.style.cssText = "position:absolute;top:-6px;right:-8px;font-size:.6rem;display:none;";
    wrapper.appendChild(badgeEl);

    dropdownEl = document.createElement("div");
    dropdownEl.style.cssText = [
      "position:absolute", "top:130%", "right:0", "width:320px", "max-height:400px",
      "overflow-y:auto", "background:#fff", "border-radius:10px",
      "box-shadow:0 .5rem 1.5rem rgba(0,0,0,.15)", "z-index:1050", "display:none",
      "padding:8px 0"
    ].join(";");
    wrapper.appendChild(dropdownEl);

    wrapper.addEventListener("click", (e) => {
      e.stopPropagation();
      const isOpen = dropdownEl.style.display === "block";
      dropdownEl.style.display = isOpen ? "none" : "block";
      if (!isOpen) markAllRead();
    });

    document.addEventListener("click", () => {
      if (dropdownEl) dropdownEl.style.display = "none";
    });

    return wrapper;
  }

  function renderDropdown() {
    if (!dropdownEl) return;
    const list = loadNotifList();

    if (list.length === 0) {
      dropdownEl.innerHTML = `<div class="text-center text-muted small py-4">Belum ada notifikasi</div>`;
      return;
    }

    dropdownEl.innerHTML = list.map(n => {
      const iconClass = n.type === "order" ? "bi-plus-circle text-primary" : "bi-inbox-fill text-success";
      const bg = n.read ? "" : "background:#f0f7ff;";
      return `
        <div style="padding:10px 16px;border-bottom:1px solid #f1f1f1;${bg}">
          <div class="d-flex gap-2 align-items-start">
            <i class="bi ${iconClass} mt-1"></i>
            <div>
              <div class="small fw-semibold">${escapeHtml(n.title)}</div>
              <div class="small text-muted">${escapeHtml(n.desc)}</div>
              <div class="small text-muted" style="font-size:.7rem;">${escapeHtml(n.time)}</div>
            </div>
          </div>
        </div>`;
    }).join("");
  }

  function updateBadge() {
    if (!badgeEl) return;
    const unread = loadNotifList().filter(n => !n.read).length;
    if (unread > 0) {
      badgeEl.textContent = unread > 9 ? "9+" : unread;
      badgeEl.style.display = "inline-block";
    } else {
      badgeEl.style.display = "none";
    }
  }

  function markAllRead() {
    const list = loadNotifList().map(n => ({ ...n, read: true }));
    saveNotifList(list);
    updateBadge();
    renderDropdown();
  }

  function escapeHtml(v) {
    return String(v || "")
      .replace(/&/g, "&amp;").replace(/</g, "&lt;")
      .replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  function pushNotif(type, title, desc) {
    const list = loadNotifList();
    list.unshift({
      type: type,
      title: title,
      desc: desc,
      time: new Date().toLocaleString("id-ID", { day: "2-digit", month: "short", hour: "2-digit", minute: "2-digit" }),
      read: false
    });
    saveNotifList(list);
  }

  // ------------------------------------------------------------
  // Ambil data terbaru, bandingkan dengan snapshot, hasilkan notif
  // ------------------------------------------------------------
  async function checkForUpdates() {
    try {
      const res = await fetch(NOTIF_GAS_URL + "?notifications=1");
      const data = await res.json();
      if (!data.success || !Array.isArray(data.items)) return;

      const prevSnapshot = loadSnapshot(); // { [id]: responsibility } atau null (pertama kali)
      const newSnapshot = {};
      let anyNotif = false;

      data.items.forEach(item => {
        newSnapshot[item.id] = item.responsibility;

        if (!prevSnapshot) return; // baseline pertama kali - jangan generate notif

        const isNewOrder = !(item.id in prevSnapshot);

        if (isNewOrder) {
          pushNotif("order", "Order Baru", item.id + " - " + (item.siteName || ""));
          anyNotif = true;
        }

        // Inbox Baru: sekarang cocok kategori role user, sebelumnya tidak
        const matchesNow = isCategoryMatch(userCat, item.responsibility);
        const matchedBefore = isNewOrder ? false : isCategoryMatch(userCat, prevSnapshot[item.id]);

        if (matchesNow && !matchedBefore) {
          pushNotif("inbox", "Inbox Baru", item.id + " - " + (item.siteName || ""));
          anyNotif = true;
        }
      });

      saveSnapshot(newSnapshot);

      if (anyNotif) {
        updateBadge();
        if (dropdownEl && dropdownEl.style.display === "block") renderDropdown();
      }

    } catch (err) {
      console.warn("Gagal cek notifikasi:", err);
    }
  }

  // ------------------------------------------------------------
  // INIT
  // ------------------------------------------------------------
  document.addEventListener("DOMContentLoaded", () => {
    // Sidebar dimuat async (fetch + innerHTML) di banyak halaman, tapi
    // ikon lonceng ada di navbar (bukan sidebar), jadi biasanya sudah
    // ada di DOM saat DOMContentLoaded. Tetap beri sedikit jeda jaga-jaga.
    setTimeout(() => {
      const wrapper = setupBellUI();
      if (!wrapper) return;

      updateBadge();
      renderDropdown();
      checkForUpdates();
      setInterval(checkForUpdates, POLL_INTERVAL_MS);
    }, 300);
  });
})();