// ============================================================
// KONFIGURASI
// ============================================================
const SPREADSHEET_ID = "18wfs1LaRBI2XoqXpTty0XqifJ6pVFmdJmsYWkrGdVyM";
const SHEET_ORDER   = "All Order";
const SHEET_USER    = "User";
const START_ROW     = 3;

// Index kolom (0-based, dihitung dari kolom A sheet "All Order")
const IDX = {
  SITE_ID       : 3,   // D
  SITE_NAME     : 5,   // F
  NIM_ORDER     : 6,   // G
  PROGRAM       : 7,   // H  (dipakai di tabel Priority Order)
  PROGRAM_CHART : 8,   // I  (dipakai khusus untuk chart "Task Open by Program")
  OA_DATE_TIF   : 19,  // T
  REGION        : 25,  // Z
  OA_DATE_MSO   : 46,  // AU (sumber UTAMA tanggal New Site On Air)
  STATUS_FINAL  : 47,  // AV (satu-satunya sumber status - kolom U TIDAK dipakai lagi)
  RESPONSIBILITY: 51,  // AZ (dipakai untuk filter kategori role)
  TTD_DAYS      : 60   // BI (jumlah hari, dipakai untuk filter Priority Order > 20 hari)
};

// Alias wilayah dari kode role ke isi kolom Z (Region).
const REGION_ALIASES = {
  "JABAT"  : "JAKARTA BANTEN",
  "JABAR"  : "JABAR",
  "EASTERN": "EASTERN JABOTABEK",
  "AREA2"  : "AREA 2"
};

// Kategori yang TIDAK difilter berdasarkan wilayah (scope nasional/semua wilayah).
const CATEGORY_SKIP_REGION_FILTER = ["MSO"];

// ============================================================
// LOGIN
// ============================================================
function doLogin(username, password) {
  try {
    const sheet = SpreadsheetApp.openById(SPREADSHEET_ID).getSheetByName(SHEET_USER);
    const data = sheet.getDataRange().getDisplayValues();

    for (let i = 1; i < data.length; i++) {
      const user = (data[i][0] || "").toString().trim();
      const pass = (data[i][1] || "").toString().trim();

      if (user === username && pass === password) {
        return {
          success: true,
          username: user,
          nama: (data[i][3] || "User").toString().trim(),
          role: (data[i][5] || "").toString().trim().toUpperCase(),
          email: (data[i][2] || "").toString().trim(),
          loker: (data[i][4] || "").toString().trim()
        };
      }
    }
  } catch (e) {
    console.log("Error doLogin:", e);
  }
  return { success: false };
}

// ============================================================
// PARSING ROLE -> { cat, region }
// Contoh: "MBBJABAR" -> { cat: "MBB", region: "JABAR" }
// ============================================================
function parseRole(role) {
  const r = (role || "").toString().toUpperCase().trim();

  if (r.indexOf("MSO") === 0) return { cat: "MSO", region: r.substring(3) };
  if (r.indexOf("MBB") === 0) return { cat: "MBB", region: r.substring(3) };
  if (r.indexOf("SS")  === 0) return { cat: "SS",  region: r.substring(2) };
  if (r.indexOf("ED")  === 0) return { cat: "ED",  region: r.substring(2) };

  return { cat: "", region: r };
}

// Cocokkan kategori role dengan isi kolom AZ (Responsibility)
function isCategoryMatch(cat, azValue) {
  if (!cat) return true; // role kosong/tidak dikenali -> tidak filter kategori
  const az = (azValue || "").toString().toUpperCase().trim();

  switch (cat) {
    case "MSO": return az.indexOf("SO") !== -1 && az.indexOf("MBB") === -1;
    case "MBB": return az.indexOf("MBB/SO") !== -1;
    case "SS":  return az.indexOf("DWS") !== -1;
    case "ED":  return az.indexOf("TA/ED") !== -1 || az.indexOf("TA-ED") !== -1 || az.indexOf("DID") !== -1;
    default:    return false;
  }
}

// Cocokkan wilayah role dengan isi kolom Z (Region)
function isRegionMatch(regionCode, regionValue) {
  const rc = (regionCode || "").toString().toUpperCase().trim();
  const rv = (regionValue || "").toString().toUpperCase().trim();

  if (!rc) return true; // role kosong -> tidak filter wilayah
  if (!rv) return false;

  const alias = REGION_ALIASES[rc] || rc;
  return rv.indexOf(alias) !== -1 || alias.indexOf(rv) !== -1 || rv === rc;
}

// ============================================================
// DO GET
// ============================================================
function doGet(e) {
  if (e.parameter.login == "1") {
    return jsonOutput(doLogin(e.parameter.username || "", e.parameter.password || ""));
  }

  // Role user dikirim dari index.php sebagai query param ?role=...
  const roleParam = e.parameter.role || "";
  const roleInfo  = parseRole(roleParam);
  const skipRegion = CATEGORY_SKIP_REGION_FILTER.indexOf(roleInfo.cat) !== -1;

  const sheet = SpreadsheetApp.openById(SPREADSHEET_ID).getSheetByName(SHEET_ORDER);
  const lastRow = sheet.getLastRow();

  if (lastRow < START_ROW) {
    return jsonOutput({ success: true, total: 0, open: 0, issue: 0, confirm: 0, tasks: [], onAir: [], task_by_program: {}, task_by_region: {} });
  }

  const values = sheet.getRange(START_ROW, 1, lastRow - START_ROW + 1, sheet.getLastColumn())
                      .getDisplayValues();

  let total = 0, openTask = 0, issueTask = 0, confirmTask = 0;
  let priorityTasks = [];
  let onAirTasks = [];
  let programCount = {};
  let regionCount = {};

  for (let i = 0; i < values.length; i++) {
    const r = values[i];

    const idTask       = r[IDX.SITE_ID]   || "";
    const nimOrder      = r[IDX.NIM_ORDER] || "";
    const program        = r[IDX.PROGRAM]   || "";
    const programChart    = r[IDX.PROGRAM_CHART] || "";
    const siteName        = r[IDX.SITE_NAME] || "";
    const region           = r[IDX.REGION]    || "";

    // Status HANYA dari kolom AV (kolom U sudah tidak dipakai lagi)
    const statusFinal      = (r[IDX.STATUS_FINAL] || "").toString().trim();
    const responsibility     = (r[IDX.RESPONSIBILITY] || "").toString().trim();
    const ttdDays             = parseFloat(r[IDX.TTD_DAYS]) || 0;

    if (!idTask && !program && !statusFinal) continue;

    // ------------------------------------------------------
    // New Site On Air: TANPA FILTER ROLE - tampil sama untuk semua role
    // Sumber tanggal UTAMA kolom AU, fallback ke T kalau AU kosong
    // ------------------------------------------------------
    const tanggalOnAir = r[IDX.OA_DATE_MSO] || r[IDX.OA_DATE_TIF] || "";

    if (tanggalOnAir) {
      onAirTasks.push({
        tanggal: tanggalOnAir,
        siteid: idTask,
        siteName: siteName,
        nim: nimOrder,
        program: program
      });
    }

    // ------------------------------------------------------
    // Filter untuk Summary Card, Priority Order, & Chart:
    // kategori (AZ) saja - New Site On Air TIDAK ikut filter ini
    // ------------------------------------------------------
    const matchCategoryRegion = isCategoryMatch(roleInfo.cat, responsibility);

    if (!matchCategoryRegion) continue;

    // ------------------------------------------------------
    // CHART (Program & Regional): mengikuti filter role
    // Pie -> kolom I (programChart) | Bar -> kolom Z (region)
    // ------------------------------------------------------
    if (programChart) programCount[programChart] = (programCount[programChart] || 0) + 1;
    if (region)        regionCount[region]         = (regionCount[region] || 0) + 1;

    total++;

    // Status kosong dihitung sebagai "Open" (sesuai spek: Column AV = Open / "String Kosong")
    if (statusFinal === "" || statusFinal.toLowerCase().indexOf("open") !== -1) openTask++;
    if (statusFinal.indexOf("Issue") !== -1) issueTask++;
    if (statusFinal.indexOf("Closed") !== -1) confirmTask++;

    // Priority Order: hanya yang TTD (kolom BI) > 20 hari
    if (ttdDays > 20) {
      priorityTasks.push({
        id: idTask,
        tipe: program,
        customer: siteName,
        area: region,
        status: statusFinal,
        ttd: ttdDays
      });
    }
  }

  // Priority Order: yang paling lama (TTD terbesar) di atas
  priorityTasks.sort((a, b) => b.ttd - a.ttd);

  // New Site On Air: urutkan berdasarkan tanggal OA PALING BARU (terbesar) duluan
  onAirTasks.sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));
  onAirTasks = onAirTasks.slice(0, 10);

  return jsonOutput({
    success: true,
    total: total,
    open: openTask,
    issue: issueTask,
    confirm: confirmTask,
    tasks: priorityTasks,
    onAir: onAirTasks,
    task_by_program: programCount,
    task_by_region: regionCount
  });
}

// ============================================================
// DO POST (login dari login.php)
// ============================================================
function doPost(e) {
  const username = e.parameter.username || "";
  const password = e.parameter.password || "";
  return jsonOutput(doLogin(username, password));
}

// ============================================================
// HELPER
// ============================================================
function jsonOutput(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}