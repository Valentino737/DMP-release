<?php
/**
 * Výběr operační paměti (RAM)
 * 
 * Zobrazuje seznam dostupných RAM modulů a umožňuje výběr do sestavy.
 * Vybraná RAM se uloží do $_SESSION['build']['ram'].
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

// Handlování výběru
if (isset($_GET['select'])) {
    $ramId = (int)$_GET['select'];

    $stmt = $pdo->prepare("SELECT * FROM ram WHERE id = ?");
    $stmt->execute([$ramId]);
    $ram = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ram) {
        $_SESSION['build']['ram'] = $ram;
    }

    header("Location: $redirectUrl");
    exit;
}

  // Handlování odstranění
  if (isset($_GET['remove'])) {
    $_SESSION['build']['ram'] = null;
    header("Location: $redirectUrl");
    exit;
  }

// --- Filtry ---
$q          = $_GET['q']         ?? '';
$price_max  = $_GET['price_max'] ?? '';
$f_speed    = $_GET['speed']     ?? '';
$f_modules  = $_GET['modules']   ?? '';
$f_stick_gb = $_GET['stick_gb']  ?? '';

$speeds      = $pdo->query("SELECT DISTINCT speed FROM ram WHERE speed IS NOT NULL AND speed <> '' ORDER BY speed")->fetchAll(PDO::FETCH_COLUMN);
$modulesList = $pdo->query("SELECT DISTINCT modules FROM ram WHERE modules IS NOT NULL AND modules <> '' ORDER BY modules")->fetchAll(PDO::FETCH_COLUMN);
$stickGbs    = $pdo->query("SELECT DISTINCT stick_gb FROM ram WHERE stick_gb IS NOT NULL ORDER BY stick_gb")->fetchAll(PDO::FETCH_COLUMN);

$params = [];
$where = ' WHERE 1=1 ';
if ($q !== '') {
    $where .= " AND (name LIKE ? OR speed LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
}
if ($price_max !== '') {
    $where .= " AND price <= ?";
    $params[] = (float)$price_max;
}
if ($f_speed !== '') {
    $where .= " AND speed = ?";
    $params[] = $f_speed;
}
if ($f_modules !== '') {
    $where .= " AND modules = ?";
    $params[] = $f_modules;
}
if ($f_stick_gb !== '') {
    $where .= " AND stick_gb = ?";
    $params[] = $f_stick_gb;
}

$sql = "SELECT * FROM ram " . $where . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rams = $stmt->fetchAll(PDO::FETCH_ASSOC);
$selectedRam = $_SESSION['build']['ram'] ?? null;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vybrat paměť</title>
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
    <h1 class="h2 mb-1">Vybrat paměť</h1>
    <p class="text-muted mb-0">Zvolte operační paměť pro svou sestavu</p>
  </div>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Hledat podle názvu...">
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="price_max" value="<?= htmlspecialchars($price_max) ?>" class="form-control" placeholder="Max cena (Kč)">
    </div>
    <div class="col-md-2">
      <select name="speed" class="form-select">
        <option value="">Rychlost – vše</option>
        <?php foreach ($speeds as $sp): ?>
          <option value="<?= htmlspecialchars($sp) ?>" <?= $f_speed === $sp ? 'selected' : '' ?>><?= htmlspecialchars($sp) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="modules" class="form-select">
        <option value="">Moduly – vše</option>
        <?php foreach ($modulesList as $mod): ?>
          <option value="<?= htmlspecialchars($mod) ?>" <?= $f_modules === $mod ? 'selected' : '' ?>><?= htmlspecialchars($mod) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="stick_gb" class="form-select">
        <option value="">Kapacita/tyčka – vše</option>
        <?php foreach ($stickGbs as $sg): ?>
          <option value="<?= htmlspecialchars($sg) ?>" <?= $f_stick_gb == $sg ? 'selected' : '' ?>><?= htmlspecialchars($sg) ?> GB</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1 d-grid">
      <button type="submit" class="btn filter-btn" aria-label="Apply filters">Hledat</button>
    </div>
  </form>

  <?php if (!empty($selectedRam) && is_array($selectedRam)): ?>
    <div class="alert alert-success mb-4">
      <strong>Vybráno:</strong> <?= htmlspecialchars($selectedRam['name'] ?? 'Neznámá') ?>
      <a href="?remove=1" class="btn btn-sm btn-warning ms-2">Odebrat</a>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($rams as $ram): ?>
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title mb-3"><?= htmlspecialchars($ram['name']) ?></h5>
            <?php
            $fieldLabels = [
                'price' => 'Cena', 'speed' => 'Rychlost', 'modules' => 'Moduly',
                'stick_gb' => 'Kapacita modulu', 'capacity' => 'Celková kapacita',
                'type' => 'Typ', 'cl' => 'CL', 'trcd' => 'tRCD', 'trp' => 'tRP', 'tras' => 'tRAS',
                'color' => 'Barva', 'brand' => 'Značka',
            ];
            ?>
            <ul class="list-group list-group-flush mb-3">
            <?php
            foreach ($ram as $field => $value) {
                if (in_array($field, ['id', 'name'])) {
                    continue;
                }
                if (!is_null($value) && $value !== '') {
                    $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
                    $display = $value;
                    if ($field === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                    if ($field === 'speed') $display = $value . ' MHz';
                    if ($field === 'stick_gb' || $field === 'capacity') $display = $value . ' GB';
                    echo "<li class='list-group-item py-1'><strong>" . htmlspecialchars($label) . ":</strong> " . htmlspecialchars($display) . "</li>\n";
                }
            }
            ?>
            </ul>
            <a href="?select=<?= $ram['id'] ?>" class="btn btn-success w-100">Vybrat tuto paměť</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
