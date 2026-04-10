<?php
/**
 * Výběr grafické karty (GPU)
 * 
 * Zobrazuje seznam dostupných GPU a umožňuje výběr do sestavy.
 * Vybraná GPU se uloží do $_SESSION['build']['gpu'].
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

// Výběr a jeho handlování
if (isset($_GET['select'])) {
    $gpuId = (int)$_GET['select'];

    $stmt = $pdo->prepare("SELECT * FROM gpu WHERE id = ?");
    $stmt->execute([$gpuId]);
    $gpu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gpu) {
        $_SESSION['build']['gpu'] = $gpu;
    }

    header("Location: $redirectUrl");
    exit;
}

  // Handlování odstranění
  if (isset($_GET['remove'])) {
    $_SESSION['build']['gpu'] = null;
    header("Location: $redirectUrl");
    exit;
  }

// --- Filtry ---
$q           = $_GET['q']         ?? '';
$price_max   = $_GET['price_max'] ?? '';
$f_chipset   = $_GET['chipset']   ?? '';
$f_vram_type = $_GET['vram_type'] ?? '';
$f_connector = $_GET['connector'] ?? '';

$chipsets   = $pdo->query("SELECT DISTINCT chipset FROM gpu WHERE chipset IS NOT NULL AND chipset <> '' ORDER BY chipset")->fetchAll(PDO::FETCH_COLUMN);
$vramTypes  = $pdo->query("SELECT DISTINCT vram_type FROM gpu WHERE vram_type IS NOT NULL AND vram_type <> '' ORDER BY vram_type")->fetchAll(PDO::FETCH_COLUMN);
$connectors = $pdo->query("SELECT DISTINCT connector FROM gpu WHERE connector IS NOT NULL AND connector <> '' ORDER BY connector")->fetchAll(PDO::FETCH_COLUMN);

$params = [];
$where = ' WHERE 1=1 ';
if ($q !== '') {
    $where .= " AND (name LIKE ? OR chipset LIKE ? OR vram_type LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($price_max !== '') {
    $where .= " AND price <= ?";
    $params[] = (float)$price_max;
}
if ($f_chipset !== '') {
    $where .= " AND chipset = ?";
    $params[] = $f_chipset;
}
if ($f_vram_type !== '') {
    $where .= " AND vram_type = ?";
    $params[] = $f_vram_type;
}
if ($f_connector !== '') {
    $where .= " AND connector = ?";
    $params[] = $f_connector;
}

$sql = "SELECT * FROM gpu " . $where . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$gpus = $stmt->fetchAll(PDO::FETCH_ASSOC);
$selectedGpu = $_SESSION['build']['gpu'] ?? null;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vybrat grafickou kartu</title>
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
    <h1 class="h2 mb-1">Vybrat grafickou kartu</h1>
    <p class="text-muted mb-0">Zvolte grafickou kartu pro svou sestavu</p>
  </div>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Hledat podle názvu...">
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="price_max" value="<?= htmlspecialchars($price_max) ?>" class="form-control" placeholder="Max cena (Kč)">
    </div>
    <div class="col-md-2">
      <select name="chipset" class="form-select">
        <option value="">Čipset – vše</option>
        <?php foreach ($chipsets as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>" <?= $f_chipset === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="vram_type" class="form-select">
        <option value="">Typ VRAM – vše</option>
        <?php foreach ($vramTypes as $vt): ?>
          <option value="<?= htmlspecialchars($vt) ?>" <?= $f_vram_type === $vt ? 'selected' : '' ?>><?= htmlspecialchars($vt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="connector" class="form-select">
        <option value="">Konektor – vše</option>
        <?php foreach ($connectors as $cn): ?>
          <option value="<?= htmlspecialchars($cn) ?>" <?= $f_connector === $cn ? 'selected' : '' ?>><?= htmlspecialchars($cn) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1 d-grid">
      <button type="submit" class="btn filter-btn" aria-label="Apply filters">Hledat</button>
    </div>
  </form>

  <?php if (!empty($selectedGpu) && is_array($selectedGpu)): ?>
    <div class="alert alert-success mb-4">
      <strong>Vybráno:</strong> <?= htmlspecialchars($selectedGpu['name'] ?? 'Neznámá') ?>
      <a href="?remove=1" class="btn btn-sm btn-warning ms-2">Odebrat</a>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($gpus as $gpu): ?>
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title mb-3"><?= htmlspecialchars($gpu['name']) ?></h5>
            <?php
            $fieldLabels = [
                'price' => 'Cena', 'chipset' => 'Čipset', 'vram_size' => 'VRAM',
                'vram_type' => 'Typ VRAM', 'vram_clock' => 'Takt VRAM', 'core_clock' => 'Základní takt',
                'boost_clock' => 'Turbo takt', 'hdmi_count' => 'HDMI', 'dp_count' => 'DisplayPort',
                'vga_count' => 'VGA', 'dvi_count' => 'DVI', 'max_monitors' => 'Max monitorů',
                'length' => 'Délka', 'width' => 'Šířka', 'height' => 'Výška',
                'tdp' => 'TDP', 'connector' => 'Konektor', 'connector_count' => 'Počet konektorů',
                'brand' => 'Značka', 'color' => 'Barva',
            ];
            ?>
            <ul class="list-group list-group-flush mb-3">
            <?php
            foreach ($gpu as $field => $value) {
                if (in_array($field, ['id', 'name'])) {
                    continue;
                }
                if (!is_null($value) && $value !== '') {
                    $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
                    $display = $value;
                    if ($field === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                    if ($field === 'core_clock' || $field === 'boost_clock') $display = $value . ' MHz';
                    if ($field === 'vram_size') $display = $value . ' GB';
                    if ($field === 'vram_clock') $display = $value . ' MHz';
                    if ($field === 'tdp') $display = $value . ' W';
                    if ($field === 'length' || $field === 'width' || $field === 'height') $display = $value . ' mm';
                    echo "<li class='list-group-item py-1'><strong>" . htmlspecialchars($label) . ":</strong> " . htmlspecialchars($display) . "</li>\n";
                }
            }
            ?>
            </ul>
            <a href="?select=<?= $gpu['id'] ?>" class="btn btn-success w-100">Vybrat tuto kartu</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
