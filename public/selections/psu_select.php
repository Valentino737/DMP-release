<?php
/**
 * Výběr zdroje (PSU)
 * 
 * Zobrazuje seznam dostupných zdrojů a umožňuje výběr do sestavy.
 * Vybraný zdroj se uloží do $_SESSION['build']['psu'].
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

// Výběr
if (isset($_GET['select'])) {
    $psuId = (int)$_GET['select'];

    $stmt = $pdo->prepare("SELECT * FROM psu WHERE id = ?");
    $stmt->execute([$psuId]);
    $psu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($psu) {
        $_SESSION['build']['psu'] = $psu;
    }

    header("Location: $redirectUrl");
    exit;
}

  // Handlování odstranění
  if (isset($_GET['remove'])) {
    $_SESSION['build']['psu'] = null;
    header("Location: $redirectUrl");
    exit;
  }

// --- Filtry ---
$q            = $_GET['q']          ?? '';
$price_max    = $_GET['price_max']  ?? '';
$f_type       = $_GET['type']       ?? '';
$f_efficiency = $_GET['efficiency'] ?? '';
$f_power_min  = $_GET['power_min']  ?? '';

$types        = $pdo->query("SELECT DISTINCT type FROM psu WHERE type IS NOT NULL AND type <> '' ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);
$efficiencies = $pdo->query("SELECT DISTINCT efficiency FROM psu WHERE efficiency IS NOT NULL AND efficiency <> '' ORDER BY efficiency")->fetchAll(PDO::FETCH_COLUMN);

$params = [];
$where = ' WHERE 1=1 ';
if ($q !== '') {
    $where .= " AND (name LIKE ? OR type LIKE ? OR efficiency LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($price_max !== '') {
    $where .= " AND price <= ?";
    $params[] = (float)$price_max;
}
if ($f_type !== '') {
    $where .= " AND type = ?";
    $params[] = $f_type;
}
if ($f_efficiency !== '') {
    $where .= " AND efficiency = ?";
    $params[] = $f_efficiency;
}
if ($f_power_min !== '') {
    $where .= " AND power >= ?";
    $params[] = (int)$f_power_min;
}

$sql = "SELECT * FROM psu " . $where . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$psus = $stmt->fetchAll(PDO::FETCH_ASSOC);
$selectedPsu = $_SESSION['build']['psu'] ?? null;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vybrat zdroj</title>
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
    <h1 class="h2 mb-1">Vybrat zdroj</h1>
    <p class="text-muted mb-0">Zvolte napájecí zdroj pro svou sestavu</p>
  </div>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Hledat podle názvu...">
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="price_max" value="<?= htmlspecialchars($price_max) ?>" class="form-control" placeholder="Max cena (Kč)">
    </div>
    <div class="col-md-2">
      <select name="type" class="form-select">
        <option value="">Typ – vše</option>
        <?php foreach ($types as $t): ?>
          <option value="<?= htmlspecialchars($t) ?>" <?= $f_type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="efficiency" class="form-select">
        <option value="">Efektivita – vše</option>
        <?php foreach ($efficiencies as $eff): ?>
          <option value="<?= htmlspecialchars($eff) ?>" <?= $f_efficiency === $eff ? 'selected' : '' ?>><?= htmlspecialchars($eff) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <input type="number" name="power_min" value="<?= htmlspecialchars($f_power_min) ?>" class="form-control" placeholder="Min. výkon (W)">
    </div>
    <div class="col-md-1 d-grid">
      <button type="submit" class="btn filter-btn" aria-label="Apply filters">Hledat</button>
    </div>
  </form>

  <?php if (!empty($selectedPsu) && is_array($selectedPsu)): ?>
    <div class="alert alert-success mb-4">
      <strong>Vybráno:</strong> <?= htmlspecialchars($selectedPsu['name'] ?? 'Neznámý') ?>
      <a href="?remove=1" class="btn btn-sm btn-warning ms-2">Odebrat</a>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($psus as $psu): ?>
      <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title mb-3"><?= htmlspecialchars($psu['name']) ?></h5>
            <?php
            $fieldLabels = [
                'price' => 'Cena', 'power' => 'Výkon', 'type' => 'Typ',
                'efficiency' => 'Účinnost', 'modular' => 'Modulární', 'form_factor' => 'Form faktor',
                'length' => 'Délka', 'molex' => 'Molex', 'sata' => 'SATA',
                '6pin' => '6-pin', '6_2pin' => '6+2-pin', '4_4pin' => '4+4-pin',
                '24pin' => '24-pin', '16pin' => '16-pin (12VHPWR)',
                'color' => 'Barva', 'brand' => 'Značka',
            ];
            ?>
            <ul class="list-group list-group-flush mb-3">
            <?php
            foreach ($psu as $field => $value) {
                if (in_array($field, ['id', 'name'])) {
                    continue;
                }
                if (!is_null($value) && $value !== '') {
                    $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
                    $display = $value;
                    if ($field === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                    if ($field === 'power') $display = $value . ' W';
                    if ($field === 'length') $display = $value . ' mm';
                    echo "<li class='list-group-item py-1'><strong>" . htmlspecialchars($label) . ":</strong> " . htmlspecialchars($display) . "</li>\n";
                }
            }
            ?>
            </ul>
            <a href="?select=<?= $psu['id'] ?>" class="btn btn-success w-100">
              Vybrat tento zdroj
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
