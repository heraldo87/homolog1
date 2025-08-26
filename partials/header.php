<?php
// Espera $page_title (opcional) e $brand (opcional)
$page_title = $page_title ?? 'COHIDRO AI — Dashboard';
?>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($page_title) ?></title>

  <!-- Bootstrap & Icons (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    :root{
      --bg:#0b0f14; --card:#111826; --text:#e5e7eb; --muted:#9ca3af;
      --field:#0f1723; --stroke:#273244; --accent:#22d3ee; --accent-2:#06b6d4;
    }
    html,body{height:100%}
    body{
      background:
        radial-gradient(1200px 600px at 80% -10%, rgba(100,100,255,.12), transparent 60%),
        radial-gradient(900px 500px at -10% 110%, rgba(0,180,140,.10), transparent 60%),
        var(--bg);
      color:var(--text);
    }

    /* Topbar */
    .topbar{ backdrop-filter: blur(6px); border-bottom: 1px solid rgba(255,255,255,.06); }

    /* Sidebar (container) */
    .sidebar{
      position: fixed; top: 56px; left: 0; bottom: 0;
      width: 260px; background: var(--card);
      border-right: 1px solid rgba(255,255,255,.08);
      padding: 14px; overflow-y: auto; z-index: 1000;
      transition: width .2s ease, transform .2s ease;
    }
    /* Sidebar (menu interno) */
    .sidebar .logo{ font-weight: 800; letter-spacing:.4px; margin: 4px 8px 12px; }
    .nav-aside .nav-link{
      color: #cbd5e1; border-radius: 10px; padding: .6rem .75rem; display: flex; align-items: center;
    }
    .nav-aside .nav-link .bi{ width: 1.2rem; margin-right: .5rem; }
    .nav-aside .nav-link:hover, .nav-aside .nav-link.active{ background: #0e1521; color: #e5e7eb; }
    hr.border-secondary-subtle{ border-color: rgba(255,255,255,.12)!important; }

    /* Colapso */
    body.sb-collapsed .sidebar{ width: 76px }
    body.sb-collapsed .content{ margin-left: 76px }
    body.sb-collapsed .sidebar .text{ display:none }

    /* Content */
    .content{ margin-left: 260px; padding: 16px; transition: margin-left .2s ease; }

    /* Cards */
    .card-d{
      background: var(--card);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.35);
    }
    .kpi .label{ color: var(--muted); font-size: .95rem }
    .kpi .value{ font-size: 1.4rem; font-weight: 800 }

    /* Charts */
    .chart{ height: 320px; }

    /* Table */
    .table thead th{ border-bottom-color: rgba(255,255,255,.15)!important; }
    .table>:not(caption)>*>*{ background-color: transparent!important; color: var(--text) }

    /* Mobile */
    @media (max-width: 991.98px){
      .sidebar{ transform: translateX(-100%); width: 260px }
      .sidebar.open{ transform: translateX(0) }
      .content{ margin-left: 0 }
    }
  </style>
</head>
