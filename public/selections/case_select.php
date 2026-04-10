<?php
/**
 * Výběr PC skříně
 * 
 * Zobrazuje seznam dostupných skříní a umožňuje výběr do sestavy.
 * Vybraná skříň se uloží do $_SESSION['build']['case'].
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
    $caseId = (int)$_GET['select'];

    $stmt = $pdo->prepare("SELECT * FROM `case` WHERE id = ?");
    $stmt->execute([$caseId]); 
    $case = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($case) {
        $_SESSION['build']['case'] = $case;
    }

    header("Location: $redirectUrl");
    exit;
}


  if (isset($_GET['remove'])) {
    $_SESSION['build']['case'] = null;
    header("Location: $redirectUrl");
    exit;
  }

// --- Filtry ---
$q             = $_GET['q']           ?? '';
$price_max     = $_GET['price_max']   ?? '';
$f_case_type   = $_GET['case_type']   ?? '';
$f_mboard_type = $_GET['mboard_type'] ?? '';
$f_psu_type    = $_GET['psu_type']    ?? '';

$caseTypes   = $pdo->query("SELECT DISTINCT case_type FROM `case` WHERE case_type IS NOT NULL AND case_type <> '' ORDER BY case_type")->fetchAll(PDO::FETCH_COLUMN);
$mboardTypes = $pdo->query("SELECT DISTINCT mboard_type FROM `case` WHERE mboard_type IS NOT NULL AND mboard_type <> '' ORDER BY mboard_type")->fetchAll(PDO::FETCH_COLUMN);
$psuTypes    = $pdo->query("SELECT DISTINCT psu_type FROM `case` WHERE psu_type IS NOT NULL AND psu_type <> '' ORDER BY psu_type")->fetchAll(PDO::FETCH_COLUMN);

$params = [];
$where = ' WHERE 1=1 ';
if ($q !== '') {
    $where .= " AND (name LIKE ? OR mboard_type LIKE ? OR case_type LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($price_max !== '') {
    $where .= " AND price <= ?";
    $params[] = (float)$price_max;
}
if ($f_case_type !== '') {
    $where .= " AND case_type = ?";
    $params[] = $f_case_type;
}
if ($f_mboard_type !== '') {
    $where .= " AND mboard_type = ?";
    $params[] = $f_mboard_type;
}
if ($f_psu_type !== '') {
    $where .= " AND psu_type = ?";
    $params[] = $f_psu_type;
}

$sql = "SELECT * FROM `case` " . $where . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
$selectedCase = $_SESSION['build']['case'] ?? null;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vybrat skříň</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body { background-color: #f9fafb; }
    .navbar { background-color: white; border-bottom: 1px solid #dee2e6; }
    .back-btn { color: #618B4A; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; }
    .back-btn:hover { color: #4f6f3a; text-decoration: none; }
    .page-header { padding: 24px 0; border-bottom: 1px solid #dee2e6; margin-bottom: 32px; }
    .case-card { transition: transform 0.2s, box-shadow 0.2s; }
    .case-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
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

<body>

<nav class="navbar">
  <div class="container">
    <a href="/dmp/public/configurator.php" class="back-btn">
      <span>←</span> <span>Zpět na konfigurátor</span>
    </a>
  </div>
</nav>

<div class="container py-5">
  <div class="page-header">
    <h1 class="h2 mb-1">Vybrat skříň</h1>
    <p class="text-muted mb-0">Zvolte skříň pro svou sestavu</p>
  </div>

  <form method="get" class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Hledat podle názvu...">
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="price_max" value="<?= htmlspecialchars($price_max) ?>" class="form-control" placeholder="Max cena (Kč)">
    </div>
    <div class="col-md-2">
      <select name="case_type" class="form-select">
        <option value="">Typ skříně – vše</option>
        <?php foreach ($caseTypes as $ct): ?>
          <option value="<?= htmlspecialchars($ct) ?>" <?= $f_case_type === $ct ? 'selected' : '' ?>><?= htmlspecialchars($ct) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="mboard_type" class="form-select">
        <option value="">Typ MB – vše</option>
        <?php foreach ($mboardTypes as $mt): ?>
          <option value="<?= htmlspecialchars($mt) ?>" <?= $f_mboard_type === $mt ? 'selected' : '' ?>><?= htmlspecialchars($mt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="psu_type" class="form-select">
        <option value="">Typ PSU – vše</option>
        <?php foreach ($psuTypes as $pt): ?>
          <option value="<?= htmlspecialchars($pt) ?>" <?= $f_psu_type === $pt ? 'selected' : '' ?>><?= htmlspecialchars($pt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1 d-grid">
      <button type="submit" class="btn filter-btn" aria-label="Apply filters">Hledat</button>
    </div>
  </form>

  <?php if (!empty($selectedCase) && is_array($selectedCase)): ?>
    <div class="alert alert-success mb-4">
      <strong>Vybráno:</strong> <?= htmlspecialchars($selectedCase['name'] ?? 'Neznámá') ?>
      <a href="?remove=1" class="btn btn-sm btn-warning ms-2">Odebrat</a>
    </div>
  <?php endif; ?>

  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
    <?php foreach ($cases as $case): ?>
      <div class="col">
        <div class="card case-card h-100 shadow-sm border-0">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-3"><?= htmlspecialchars($case['name']) ?></h5>

            <?php
            $fieldLabels = [
                'price' => 'Cena', 'max_gpu' => 'Max délka GPU', 'mboard_type' => 'Typ desky',
                'psu_type' => 'Typ zdroje', 'case_type' => 'Typ skříně', 'max_cooler' => 'Max výška chladiče',
                'expansion_slots' => 'Rozšiřující sloty', 'front_rad' => 'Přední radiátor',
                'top_rad' => 'Horní radiátor', 'max_psu' => 'Max délka zdroje', 'rear_rad' => 'Zadní radiátor',
                'color' => 'Barva', 'brand' => 'Značka',
            ];
            ?>
            <ul class="list-group list-group-flush flex-grow-1 mb-3">
              <?php foreach ($case as $key => $value): ?>
                <?php if ($key !== 'id' && $key !== 'name'): ?>
                  <?php if (!is_null($value) && $value !== ''): ?>
                    <?php
                      $label = $fieldLabels[$key] ?? ucwords(str_replace('_',' ',$key));
                      $display = $value;
                      if ($key === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                      if (in_array($key, ['max_gpu', 'max_cooler', 'max_psu'])) $display = $value . ' mm';
                      if (in_array($key, ['front_rad', 'top_rad', 'rear_rad'])) $display = $value . ' mm';
                    ?>
                    <li class="list-group-item py-1"><strong><?= htmlspecialchars($label) ?>:</strong> <?= htmlspecialchars($display) ?></li>
                  <?php endif; ?>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>

            <a href="?select=<?= $case['id'] ?>" class="btn btn-success w-100">
              Vybrat tuto skříň
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
