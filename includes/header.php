<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        // Global API helpers
        window.CRM_API = 'https://happycrm.site/happyreports_api/index.php';
        window.fmt = (n) => '৳' + parseFloat(n||0).toLocaleString('en-BD', {minimumFractionDigits:2, maximumFractionDigits:2});
        window.fmtDate = (d) => { if(!d) return '—'; const dt = new Date(d); return dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); };
    </script>
    
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #f0f0eb;
            --panel:     #ffffff;
            --border:    #c8c8c0;
            --border-dark: #a0a098;
            --sidebar-bg:  #1d2a35;
            --sidebar-hover: #263545;
            --sidebar-active: #1a73e8;
            --header-bg: #ffffff;
            --accent:    #1a73e8;
            --accent-dark: #1558b0;
            --green:     #217346;
            --green-bg:  #e8f4ec;
            --red:       #c0392b;
            --red-bg:    #fdecea;
            --orange:    #e67e22;
            --orange-bg: #fef4e8;
            --text:      #1a1a1a;
            --text-muted:#666;
            --text-light:#999;
            --row-alt:   #f7f7f3;
            --row-hover: #e8f0fe;
            --thead-bg:  #e8e8e2;
            --font:      'Inter', 'Segoe UI', Arial, sans-serif;
        }

        html, body { height: 100%; overflow: hidden; }
        body { font-family: var(--font); background: var(--bg); color: var(--text); font-size: 13px; display: flex; }

        /* ── SIDEBAR ──────────────────────────────────── */
        #sidebar {
            width: 210px;
            min-width: 210px;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            height: 100vh;
            border-right: 1px solid #111;
            z-index: 100;
        }
        #sidebar-logo {
            padding: 0 16px;
            height: 44px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #2e3d4d;
            background: #162130;
        }
        #sidebar-logo .logo-icon { color: #1a73e8; font-size: 16px; }
        #sidebar-logo .logo-text { font-size: 14px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; }
        #sidebar-logo .logo-text span { color: #4dabf7; }

        #sidebar nav { flex: 1; overflow-y: auto; padding: 8px 0; }
        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #5a7a90;
            padding: 12px 16px 4px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 16px;
            color: #b0c4d8;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 400;
            transition: background 0.15s, color 0.15s;
            border-left: 3px solid transparent;
        }
        .nav-link i { width: 16px; text-align: center; font-size: 12px; color: #6a8fa8; transition: color 0.15s; }
        .nav-link:hover { background: var(--sidebar-hover); color: #ffffff; border-left-color: #4dabf7; }
        .nav-link:hover i { color: #4dabf7; }
        .nav-link.active { background: #152535; color: #ffffff; border-left-color: var(--sidebar-active); font-weight: 600; }
        .nav-link.active i { color: var(--sidebar-active); }

        #sidebar-footer {
            padding: 10px 16px;
            border-top: 1px solid #2e3d4d;
            color: #5a7a90;
            font-size: 11px;
        }

        /* ── MAIN AREA ────────────────────────────────── */
        #main-wrap { flex: 1; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

        /* ── TOPBAR ───────────────────────────────────── */
        #topbar {
            height: 44px;
            background: var(--header-bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            flex-shrink: 0;
        }
        #topbar .page-title { font-size: 13px; font-weight: 600; color: var(--text); }
        #topbar .topbar-right { display: flex; align-items: center; gap: 12px; }
        #topbar .topbar-user { display: flex; align-items: center; gap: 7px; font-size: 12px; color: var(--text-muted); cursor: pointer; }
        #topbar .topbar-user img { width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border); }
        #topbar .topbar-badge {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 2px 8px;
            border-radius: 2px;
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ── PAGE CONTENT ─────────────────────────────── */
        #page-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            background: var(--bg);
            padding: 0;
        }

        /* ── EXCEL TABLE SYSTEM ───────────────────────── */
        .xl-panel {
            background: var(--panel);
            border: 1px solid var(--border);
            margin: 12px;
        }
        .xl-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: var(--thead-bg);
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            gap: 8px;
        }
        .xl-panel-header .header-left { display: flex; align-items: center; gap: 8px; }
        .xl-panel-header .header-right { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

        .xl-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        .xl-table thead tr { background: var(--thead-bg); }
        .xl-table thead th {
            padding: 6px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 11.5px;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border-dark);
            border-right: 1px solid var(--border);
            white-space: nowrap;
        }
        .xl-table thead th:last-child { border-right: none; }
        .xl-table tbody tr { border-bottom: 1px solid var(--border); }
        .xl-table tbody tr:nth-child(even) { background: var(--row-alt); }
        .xl-table tbody tr:hover { background: var(--row-hover); }
        .xl-table tbody td {
            padding: 5px 10px;
            border-right: 1px solid var(--border);
            vertical-align: middle;
        }
        .xl-table tbody td:last-child { border-right: none; }
        .xl-table tfoot tr { background: var(--thead-bg); border-top: 2px solid var(--border-dark); }
        .xl-table tfoot td {
            padding: 6px 10px;
            font-weight: 600;
            font-size: 12px;
            border-right: 1px solid var(--border);
        }

        .td-num { text-align: right; font-variant-numeric: tabular-nums; }
        .td-green { color: var(--green); font-weight: 600; }
        .td-red { color: var(--red); font-weight: 600; }
        .td-blue { color: #1a73e8; font-weight: 600; }
        .td-muted { color: var(--text-muted); }
        .td-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 2px;
            font-size: 11px;
            font-weight: 500;
            border: 1px solid;
        }
        .badge-green { color: var(--green); border-color: #b7dfc8; background: var(--green-bg); }
        .badge-red { color: var(--red); border-color: #f5b7b1; background: var(--red-bg); }
        .badge-blue { color: var(--accent); border-color: #aecbfa; background: #e8f0fe; }
        .badge-orange { color: var(--orange); border-color: #f5cba7; background: var(--orange-bg); }
        .badge-gray { color: var(--text-muted); border-color: var(--border); background: var(--row-alt); }

        /* ── BUTTONS ──────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            font-size: 12px;
            font-family: var(--font);
            font-weight: 500;
            border: 1px solid var(--border-dark);
            border-radius: 2px;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.12s, border-color 0.12s;
            background: var(--panel);
            color: var(--text);
        }
        .btn:hover { background: var(--row-alt); border-color: #888; }
        .btn-primary { background: var(--accent); color: #fff; border-color: var(--accent-dark); }
        .btn-primary:hover { background: var(--accent-dark); }
        .btn-green { background: var(--green); color: #fff; border-color: #1a5c38; }
        .btn-green:hover { background: #1a5c38; }
        .btn-sm { padding: 3px 8px; font-size: 11px; }

        /* ── FILTER BAR ───────────────────────────────── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--panel);
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }
        .filter-bar label { font-size: 11.5px; color: var(--text-muted); white-space: nowrap; }
        .filter-bar input[type="date"],
        .filter-bar select {
            padding: 3px 6px;
            border: 1px solid var(--border-dark);
            border-radius: 2px;
            font-size: 12px;
            font-family: var(--font);
            background: var(--panel);
            color: var(--text);
            height: 26px;
        }
        .filter-sep { color: var(--border-dark); }

        /* ── MODAL ────────────────────────────────────── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--panel);
            border: 1px solid var(--border-dark);
            min-width: 420px;
            max-width: 640px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 14px;
            background: var(--thead-bg);
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            font-weight: 600;
        }
        .modal-header .modal-close { cursor: pointer; color: var(--text-muted); font-size: 16px; line-height: 1; background: none; border: none; }
        .modal-header .modal-close:hover { color: var(--red); }
        .modal-body { padding: 14px; }
        .modal-footer { padding: 10px 14px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 6px; background: var(--row-alt); }

        /* ── FORM ELEMENTS ────────────────────────────── */
        .form-group { margin-bottom: 10px; }
        .form-label { display: block; font-size: 11.5px; font-weight: 500; color: var(--text-muted); margin-bottom: 3px; }
        .form-control {
            width: 100%;
            padding: 5px 8px;
            border: 1px solid var(--border-dark);
            border-radius: 2px;
            font-size: 12.5px;
            font-family: var(--font);
            color: var(--text);
            background: var(--panel);
        }
        .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(26,115,232,0.15); }
        .form-control[readonly] { background: var(--row-alt); color: var(--text-muted); cursor: not-allowed; }
        .form-row { display: flex; gap: 10px; }
        .form-row .form-group { flex: 1; }
        .form-hint { font-size: 11px; color: var(--text-light); margin-top: 3px; }

        /* ── DEPOSIT ROW SYSTEM ───────────────────────── */
        .deposit-row-item { border: 1px solid var(--border); padding: 10px; margin-bottom: 8px; background: var(--row-alt); position: relative; }
        .deposit-row-item:last-child { margin-bottom: 0; }
        .deposit-row-remove { position: absolute; top: 6px; right: 8px; background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 13px; }
        .deposit-row-remove:hover { color: var(--red); }

        /* ── COMPANY TABS ─────────────────────────────── */
        .co-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--border-dark); background: var(--panel); padding: 0 12px; flex-wrap: wrap; }
        .co-tab {
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            color: var(--text-muted);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: color 0.15s, border-color 0.15s;
            white-space: nowrap;
        }
        .co-tab:hover { color: var(--accent); }
        .co-tab.active { color: var(--accent); border-bottom-color: var(--accent); font-weight: 600; }

        /* ── SUMMARY ROW ──────────────────────────────── */
        .summary-bar {
            display: flex;
            gap: 0;
            background: var(--panel);
            border-bottom: 1px solid var(--border);
        }
        .summary-cell {
            flex: 1;
            padding: 8px 14px;
            border-right: 1px solid var(--border);
            font-size: 12px;
        }
        .summary-cell:last-child { border-right: none; }
        .summary-cell .s-label { color: var(--text-muted); font-size: 11px; margin-bottom: 2px; }
        .summary-cell .s-val { font-weight: 600; font-size: 14px; }

        /* ── SCROLLBAR ────────────────────────────────── */
        ::-webkit-scrollbar { width: 7px; height: 7px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border-dark); }
        ::-webkit-scrollbar-thumb:hover { background: #888; }

        /* ── LOADING SPINNER ──────────────────────────── */
        .loading-row td { text-align: center; padding: 20px; color: var(--text-muted); font-style: italic; }

        /* ── EMPTY STATE ──────────────────────────────── */
        .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 32px; margin-bottom: 10px; opacity: 0.3; }

        /* ── FILE INPUT ───────────────────────────────── */
        input[type="file"] {
            width: 100%;
            padding: 5px;
            border: 1px solid var(--border-dark);
            border-radius: 2px;
            font-size: 12px;
            font-family: var(--font);
            background: var(--row-alt);
        }

        /* ── PAGINATION ───────────────────────────────── */
        .xl-pagination { display: flex; align-items: center; justify-content: space-between; padding: 6px 12px; background: var(--panel); border-top: 1px solid var(--border); font-size: 11.5px; color: var(--text-muted); }
        .xl-pagination .pag-btns { display: flex; gap: 3px; }
        .pag-btn { padding: 2px 8px; border: 1px solid var(--border); background: var(--panel); cursor: pointer; font-size: 11.5px; border-radius: 2px; }
        .pag-btn:hover { background: var(--row-alt); }
        .pag-btn.active { background: var(--accent); color: #fff; border-color: var(--accent-dark); }

        /* ── MISC ─────────────────────────────────────── */
        .divider-v { width: 1px; background: var(--border); height: 18px; margin: 0 2px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw600 { font-weight: 600; }
        .mono { font-family: 'Courier New', monospace; font-size: 11.5px; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside id="sidebar">
        <div id="sidebar-logo">
            <i class="fa-solid fa-table-cells logo-icon"></i>
            <span class="logo-text">Happy<span>Reports</span></span>
        </div>
        <nav>
            <div class="nav-section-label">Overview</div>
            <a href="<?php echo URLROOT; ?>/index.php" class="nav-link" id="nav-dashboard">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>

            <div class="nav-section-label">CRM Data</div>
            <a href="<?php echo URLROOT; ?>/company.php" class="nav-link" id="nav-company">
                <i class="fa-solid fa-building"></i> Companies
            </a>
            <a href="<?php echo URLROOT; ?>/dealers.php" class="nav-link" id="nav-dealer">
                <i class="fa-solid fa-handshake"></i> Dealers
            </a>
            <a href="<?php echo URLROOT; ?>/sr.php" class="nav-link" id="nav-sr">
                <i class="fa-solid fa-user-tie"></i> Sales Reps
            </a>

            <div class="nav-section-label">Operations</div>
            <a href="<?php echo URLROOT; ?>/inventory.php" class="nav-link" id="nav-inventory">
                <i class="fa-solid fa-boxes-stacked"></i> Inventory
            </a>
            <a href="<?php echo URLROOT; ?>/ledger.php" class="nav-link" id="nav-ledger">
                <i class="fa-solid fa-book"></i> Company Ledger
            </a>

        </nav>
        <div id="sidebar-footer">&copy; 2026 CEO Panel</div>
    </aside>

    <!-- MAIN AREA -->
    <div id="main-wrap">
        <!-- TOPBAR -->
        <header id="topbar">
            <div class="page-title" id="pageTitle"><?php echo isset($pageTitle) ? $pageTitle : 'Overview'; ?></div>
            <div class="topbar-right">
                <span class="topbar-badge" id="topbar-date"></span>
                <div class="topbar-user">
                    <img src="https://ui-avatars.com/api/?name=CEO&background=1a73e8&color=fff" alt="CEO">
                    <span>CEO Admin</span>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;color:var(--text-light)"></i>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main id="page-content">
