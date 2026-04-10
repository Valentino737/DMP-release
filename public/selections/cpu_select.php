<?php
/**
 * Výběr procesoru (CPU)
 * 
 * Zobrazuje seznam dostupných CPU a umožňuje výběr do sestavy.
 * Vybraný CPU se uloží do $_SESSION['build']['cpu'].
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';

// Inicializace pole sestavy, pokud ještě neexistuje
$_SESSION['build'] ??= [
    'cpu' => null,
    'gpu' => null,
    'ram' => null,
    'motherboard' => null,
    'psu' => null,
    'case' => null,
    'storage' => [],
    'cooling' => null
];

$redirectUrl = '/dmp/public/configurator.php';
if (isset($_GET['edit'])) {
    $redirectUrl .= '?edit=1';
}

if (isset($_GET['select'])) {
    $cpuId = (int)$_GET['select'];

    $stmt = $pdo->prepare("SELECT * FROM cpu WHERE id = ?");
    $stmt->execute([$cpuId]); 
    $cpu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cpu) {
        $_SESSION['build']['cpu'] = $cpu;
    }

    header("Location: $redirectUrl");
    exit;
}


if (isset($_GET['remove'])) {
  $_SESSION['build']['cpu'] = null;
  header("Location: $redirectUrl");
  exit;
}

// --- Filtry ---
$q           = $_GET['q']         ?? '';
$price_max   = $_GET['price_max'] ?? '';
$f_socket    = $_GET['socket']    ?? '';
$f_ram       = $_GET['ram_type']  ?? '';
$f_microarch = $_GET['microarch'] ?? '';

$sockets    = $pdo->query("SELECT DISTINCT socket FROM cpu WHERE socket IS NOT NULL AND socket <> '' ORDER BY socket")->fetchAll(PDO::FETCH_COLUMN);
$ramTypes   = $pdo->query("SELECT DISTINCT ram FROM cpu WHERE ram IS NOT NULL AND ram <> '' ORDER BY ram")->fetchAll(PDO::FETCH_COLUMN);
$microarchs = $pdo->query("SELECT DISTINCT microarchitecture FROM cpu WHERE microarchitecture IS NOT NULL AND microarchitecture <> '' ORDER BY microarchitecture")->fetchAll(PDO::FETCH_COLUMN);

$params = [];
$where = ' WHERE 1=1 ';
if ($q !== '') {
    $where .= " AND (name LIKE ? OR socket LIKE ? OR microarchitecture LIKE ? OR graphics LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($price_max !== '') {
    $where .= " AND price <= ?";
    $params[] = (float)$price_max;
}
if ($f_socket !== '') {
    $where .= " AND socket = ?";
    $params[] = $f_socket;
}
if ($f_ram !== '') {
    $where .= " AND ram = ?";
    $params[] = $f_ram;
}
if ($f_microarch !== '') {
    $where .= " AND microarchitecture = ?";
    $params[] = $f_microarch;
}

$sql = "SELECT * FROM cpu " . $where . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cpus = $stmt->fetchAll(PDO::FETCH_ASSOC);
$selectedCpu = $_SESSION['build']['cpu'] ?? null;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vybrat procesor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f9fafb; }
    .navbar { background-color: white; border-bottom: 1px solid #dee2e6; }
    .back-btn { color: #618B4A; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; }
    .back-btn:hover { color: #4f6f3a; text-decoration: none; }
    .page-header { padding: 24px 0; border-bottom: 1px solid #dee2e6; margin-bottom: 32px; }
    .filter-btn {
      background: linear-gradient(180deg,#618B4A 0%,#4f6f3a 100%);
      border: none;
      color: #fff;
      box-shadow: 0 6px 18px rgba(97,139,74,0.08);
      padding: 0.45rem 0.75rem;
      font-weight: 600;
      transition: transform .06s ease, filter .06s ease;
    }
    .filter-btn:hover { filter: brightness(0.95); }
    .filter-btn:active { transform: translateY(1px); }
  </style>
</head>

<body class="bg-light">

<nav class="navbar">
  <div class="container">
    <a href="/dmp/public/configurator.php" class="back-btn">
      <span>←</span> <span>Zpět na konfigurátor</span>
    </a>
  </div>
</nav>

<div class="container py-5">
  <div class="page-header">
    <h1 class="h2 mb-1">Vybrat procesor</h1>
    <p class="text-muted mb-0">Zvolte procesor pro svou sestavu</p>
  </div>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Hledat podle názvu...">
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="price_max" value="<?= htmlspecialchars($price_max) ?>" class="form-control" placeholder="Max cena (Kč)">
    </div>
    <div class="col-md-2">
      <select name="socket" class="form-select">
        <option value="">Patice – vše</option>
        <?php foreach ($sockets as $s): ?>
          <option value="<?= htmlspecialchars($s) ?>" <?= $f_socket === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="ram_type" class="form-select">
        <option value="">Typ paměti – vše</option>
        <?php foreach ($ramTypes as $rt): ?>
          <option value="<?= htmlspecialchars($rt) ?>" <?= $f_ram === $rt ? 'selected' : '' ?>><?= htmlspecialchars($rt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="microarch" class="form-select">
        <option value="">Architektura – vše</option>
        <?php foreach ($microarchs as $m): ?>
          <option value="<?= htmlspecialchars($m) ?>" <?= $f_microarch === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1 d-grid">
      <button type="submit" class="btn filter-btn" aria-label="Apply filters">Hledat</button>
    </div>
  </form>

  <?php if (!empty($selectedCpu) && is_array($selectedCpu)): ?>
    <div class="alert alert-success mb-4">
      <strong>Vybráno:</strong> <?= htmlspecialchars($selectedCpu['name'] ?? 'Neznámý') ?>
      <a href="?remove=1" class="btn btn-sm btn-warning ms-2">Odebrat</a>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($cpus as $cpu): ?>
      <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title mb-3"><?= htmlspecialchars($cpu['name']) ?></h5>
            <?php
            $fieldLabels = [
                'price' => 'Cena', 'socket' => 'Patice', 'microarchitecture' => 'Architektura',
                'cores' => 'Jádra', 'threads' => 'Vlákna', 'core_clock' => 'Základní takt',
                'boost_clock' => 'Turbo takt', 'ram' => 'Typ paměti', 'ram_count' => 'Kanály paměti',
                'tdp' => 'TDP', 'graphics' => 'Grafika', 'l2_cache' => 'L2 cache',
                'l3_cache' => 'L3 cache', 'brand' => 'Značka', 'color' => 'Barva',
            ];
            ?>
            <ul class="list-group list-group-flush mb-3">
            <?php
            foreach ($cpu as $field => $value) {
                if (in_array($field, ['id', 'name'])) {
                    continue;
                }
                if ($field === 'graphics' && (is_null($value) || $value === '')) {
                    echo "<li class='list-group-item py-1'><strong>Grafika:</strong> Bez integrované grafiky</li>\n";
                    continue;
                }
                if (!is_null($value) && $value !== '') {
                    $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
                    $display = $value;
                    if ($field === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                    if ($field === 'core_clock' || $field === 'boost_clock') $display = $value . ' GHz';
                    if ($field === 'tdp') $display = $value . ' W';
                    if ($field === 'l2_cache' || $field === 'l3_cache') $display = $value . ' MB';
                    echo "<li class='list-group-item py-1'><strong>" . htmlspecialchars($label) . ":</strong> " . htmlspecialchars($display) . "</li>\n";
                }
            }
            ?>
            </ul>
            <a href="?select=<?= $cpu['id'] ?>" class="btn btn-success w-100">
              Vybrat tento procesor
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
