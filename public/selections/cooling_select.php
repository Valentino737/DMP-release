<?php
/**
 * Výběr chlazení
 * 
 * Zobrazuje seznam dostupných chladičů a umožňuje výběr do sestavy.
 * Vybrané chlazení se uloží do $_SESSION['build']['cooling'].
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
    $coolingId = (int)$_GET['select'];
    $stmt = $pdo->prepare("SELECT * FROM cooler WHERE id = ?");
    $stmt->execute([$coolingId]);
    $cooling = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cooling) {
        $_SESSION['build']['cooling'] = $cooling;
    }

    header("Location: $redirectUrl");
    exit;
}

if (isset($_GET['remove'])) {
    $_SESSION['build']['cooling'] = null;
    header("Location: $redirectUrl");
    exit;
}

// --- Filtry ---
$q          = $_GET['q']              ?? '';
$price_max  = $_GET['price_max']      ?? '';
$f_type     = $_GET['type']           ?? '';
$f_socket   = $_GET['socket_support'] ?? '';

$coolerTypes   = $pdo->query("SELECT DISTINCT type FROM cooler WHERE type IS NOT NULL AND type <> '' ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);
$socketSupport = $pdo->query("SELECT DISTINCT socket_support FROM cooler WHERE socket_support IS NOT NULL AND socket_support <> '' ORDER BY socket_support")->fetchAll(PDO::FETCH_COLUMN);

$params = [];
$where = ' WHERE 1=1 ';
if ($q !== '') {
    $where .= " AND (name LIKE ? OR type LIKE ? OR socket_support LIKE ?)";
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
if ($f_socket !== '') {
    $where .= " AND socket_support = ?";
    $params[] = $f_socket;
}

$sql = "SELECT * FROM cooler " . $where . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$coolings = $stmt->fetchAll(PDO::FETCH_ASSOC);
$selectedCooling = $_SESSION['build']['cooling'] ?? null;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vybrat chlazení</title>
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
    <a href="/dmp/public/configurator.php" class="back-btn">← Zpět na konfigurátor</a>
  </div>
</nav>

<div class="container mt-5">
  <div class="page-header">
    <h2>Vybrat chlazení</h2>
    <p class="text-muted">Zvolte chlazení procesoru pro svou sestavu</p>
  </div>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-4">
      <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Hledat podle názvu...">
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="price_max" value="<?= htmlspecialchars($price_max) ?>" class="form-control" placeholder="Max cena (Kč)">
    </div>
    <div class="col-md-2">
      <select name="type" class="form-select">
        <option value="">Typ – vše</option>
        <?php foreach ($coolerTypes as $t): ?>
          <option value="<?= htmlspecialchars($t) ?>" <?= $f_type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="socket_support" class="form-select">
        <option value="">Patice – vše</option>
        <?php foreach ($socketSupport as $ss): ?>
          <option value="<?= htmlspecialchars($ss) ?>" <?= $f_socket === $ss ? 'selected' : '' ?>><?= htmlspecialchars($ss) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn filter-btn" aria-label="Apply filters">Hledat</button>
    </div>
  </form>

  <?php if ($selectedCooling && is_array($selectedCooling)): ?>
    <div class="alert alert-success mb-4">
      <strong>Vybráno:</strong> <?= htmlspecialchars($selectedCooling['name'] ?? 'Neznámé') ?>
      <a href="?remove=1" class="btn btn-sm btn-warning ms-2">Odebrat</a>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($coolings as $cooling): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-light">
            <h5 class="mb-0"><?= htmlspecialchars($cooling['name'] ?? 'Neznámý chladič') ?></h5>
          </div>
          <?php
          $fieldLabels = [
              'price' => 'Cena', 'type' => 'Typ', 'socket_support' => 'Podporované patice',
              'height' => 'Výška', 'radiator_size' => 'Velikost radiátoru', 'fan_size' => 'Velikost ventilátoru',
              'noise_level' => 'Hlučnost', 'tdp' => 'TDP', 'rgb' => 'RGB',
              'color' => 'Barva', 'brand' => 'Značka',
          ];
          ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($cooling as $field => $value): ?>
              <?php if (in_array($field, ['id', 'name'])) continue; ?>
              <?php if (!is_null($value) && $value !== ''): ?>
                <?php
                  $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                  $display = $value;
                  if ($field === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                  if ($field === 'height') $display = $value . ' mm';
                  if ($field === 'radiator_size' || $field === 'fan_size') $display = $value . ' mm';
                  if ($field === 'noise_level') $display = $value . ' dB';
                  if ($field === 'tdp') $display = $value . ' W';
                ?>
                <li class="list-group-item">
                  <strong><?= htmlspecialchars($label) ?>:</strong>
                  <?= htmlspecialchars($display) ?>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
          <div class="card-body">
            <a href="?select=<?= $cooling['id'] ?>" class="btn btn-success w-100">Vybrat tento chladič</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
