<?php
/**
 * Výběr základní desky
 * 
 * Zobrazuje seznam dostupných základních desek a umožňuje výběr do sestavy.
 * Vybraná deska se uloží do $_SESSION['build']['motherboard'].
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
    $mbId = (int)$_GET['select'];

    $stmt = $pdo->prepare("SELECT * FROM motherboard WHERE id = ?");
    $stmt->execute([$mbId]);
    $mb = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mb) {
        $_SESSION['build']['motherboard'] = $mb;
    }

    header("Location: $redirectUrl");
    exit;
}

  // Handlování odstranění
  if (isset($_GET['remove'])) {
    $_SESSION['build']['motherboard'] = null;
    header("Location: $redirectUrl");
    exit;
  }

// --- Filtry ---
$q             = $_GET['q']           ?? '';
$price_max     = $_GET['price_max']   ?? '';
$f_socket      = $_GET['socket']      ?? '';
$f_chipset     = $_GET['chipset']     ?? '';
$f_form_factor = $_GET['form_factor'] ?? '';
$f_ram_type    = $_GET['ram_type']    ?? '';

$sockets     = $pdo->query("SELECT DISTINCT socket FROM motherboard WHERE socket IS NOT NULL AND socket <> '' ORDER BY socket")->fetchAll(PDO::FETCH_COLUMN);
$chipsets    = $pdo->query("SELECT DISTINCT chipset FROM motherboard WHERE chipset IS NOT NULL AND chipset <> '' ORDER BY chipset")->fetchAll(PDO::FETCH_COLUMN);
$formFactors = $pdo->query("SELECT DISTINCT form_factor FROM motherboard WHERE form_factor IS NOT NULL AND form_factor <> '' ORDER BY form_factor")->fetchAll(PDO::FETCH_COLUMN);
$ramTypes    = $pdo->query("SELECT DISTINCT ram_type FROM motherboard WHERE ram_type IS NOT NULL AND ram_type <> '' ORDER BY ram_type")->fetchAll(PDO::FETCH_COLUMN);

$params = [];
$where = ' WHERE 1=1 ';
if ($q !== '') {
    $where .= " AND (name LIKE ? OR socket LIKE ? OR chipset LIKE ? OR color LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($price_max !== '' && is_numeric(str_replace(',', '.', $price_max))) {
    $normalized = (float) str_replace(',', '.', $price_max);
    $where .= " AND price <= ?";
    $params[] = $normalized;
}
if ($f_socket !== '') {
    $where .= " AND socket = ?";
    $params[] = $f_socket;
}
if ($f_chipset !== '') {
    $where .= " AND chipset = ?";
    $params[] = $f_chipset;
}
if ($f_form_factor !== '') {
    $where .= " AND form_factor = ?";
    $params[] = $f_form_factor;
}
if ($f_ram_type !== '') {
    $where .= " AND ram_type = ?";
    $params[] = $f_ram_type;
}

$sql = "SELECT * FROM motherboard " . $where . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$motherboards = $stmt->fetchAll(PDO::FETCH_ASSOC);
$selectedMb = $_SESSION['build']['motherboard'] ?? null;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vybrat základní desku</title>
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
    <h1 class="h2 mb-1">Vybrat základní desku</h1>
    <p class="text-muted mb-0">Zvolte základní desku pro svou sestavu</p>
  </div>
  <form method="get" class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Hledat podle názvu...">
    </div>
    <div class="col-md-1">
      <input type="number" step="0.01" name="price_max" value="<?= htmlspecialchars($price_max) ?>" class="form-control" placeholder="Max (Kč)">
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
      <select name="chipset" class="form-select">
        <option value="">Čipset – vše</option>
        <?php foreach ($chipsets as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>" <?= $f_chipset === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="form_factor" class="form-select">
        <option value="">Formát – vše</option>
        <?php foreach ($formFactors as $ff): ?>
          <option value="<?= htmlspecialchars($ff) ?>" <?= $f_form_factor === $ff ? 'selected' : '' ?>><?= htmlspecialchars($ff) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1">
      <select name="ram_type" class="form-select">
        <option value="">RAM</option>
        <?php foreach ($ramTypes as $rt): ?>
          <option value="<?= htmlspecialchars($rt) ?>" <?= $f_ram_type === $rt ? 'selected' : '' ?>><?= htmlspecialchars($rt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1 d-grid">
      <button type="submit" class="btn filter-btn" aria-label="Apply filters">Hledat</button>
    </div>
  </form>

  <?php if (!empty($selectedMb) && is_array($selectedMb)): ?>
    <div class="alert alert-success mb-4">
      <strong>Vybráno:</strong> <?= htmlspecialchars($selectedMb['name'] ?? 'Neznámá') ?>
      <a href="?remove=1" class="btn btn-sm btn-warning ms-2">Odebrat</a>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($motherboards as $mb): ?>
      <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title mb-3"><?= htmlspecialchars($mb['name']) ?></h5>
            <?php
            $fieldLabels = [
                'price' => 'Cena', 'socket' => 'Patice', 'chipset' => 'Čipset',
                'form_factor' => 'Form faktor', 'max_ram' => 'Max RAM',
                'ram_slots' => 'Sloty RAM', 'ram_type' => 'Typ paměti', 'ram_speed' => 'Rychlost RAM',
                'pcie16_slots' => 'PCIe x16 sloty', 'pcie1_slots' => 'PCIe x1 sloty',
                'm2_slots' => 'M.2 sloty', 'sata_slots' => 'SATA porty',
                'color' => 'Barva', 'brand' => 'Značka',
            ];
            ?>
            <ul class="list-group list-group-flush mb-3">
            <?php
            foreach ($mb as $field => $value) {
                if (in_array($field, ['id', 'name'])) {
                    continue;
                }
                if (!is_null($value) && $value !== '') {
                    $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
                    $display = $value;
                    if ($field === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                    if ($field === 'max_ram') $display = $value . ' GB';
                    if ($field === 'ram_speed') $display = $value . ' MHz';
                    echo "<li class='list-group-item py-1'><strong>" . htmlspecialchars($label) . ":</strong> " . htmlspecialchars($display) . "</li>\n";
                }
            }
            ?>
            </ul>
            <a href="?select=<?= $mb['id'] ?>" class="btn btn-success w-100">
              Vybrat tuto desku
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
