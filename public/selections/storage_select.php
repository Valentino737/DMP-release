<?php
/**
 * Výběr úložiště
 * 
 * Zobrazuje seznam dostupných disků a umožňuje výběr do sestavy.
 * Vybrané úložiště se uloží do $_SESSION['build']['storage'].
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

// Handlování výběru
if (isset($_GET['select'])) {
    $storageId = (int)$_GET['select'];

    $stmt = $pdo->prepare("SELECT * FROM storage WHERE id = ?");
    $stmt->execute([$storageId]);
    $storage = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($storage) {
        // Přidat disk do pole místo nahrazení
        if (!isset($_SESSION['build']['storage'])) {
            $_SESSION['build']['storage'] = [];
        }
        // Zkontrolování, zda tento disk již není vybrán
        $alreadySelected = false;
        foreach ($_SESSION['build']['storage'] as $item) {
            if (is_array($item) && isset($item['id']) && $item['id'] === $storage['id']) {
                $alreadySelected = true;
                break;
            }
        }
        if (!$alreadySelected) {
            $_SESSION['build']['storage'][] = $storage;
        }
    }

    // Zůstat na stránce pro možnost přidání dalších disků, zachovat editovací příznak
    $nextUrl = '/dmp/public/selections/storage_select.php?added=1';
    if (isset($_GET['edit'])) {
        $nextUrl .= '&edit=1';
    }
    header("Location: $nextUrl");
    exit;
}

// Handlování odstranění
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    if (isset($_SESSION['build']['storage'])) {
        $_SESSION['build']['storage'] = array_filter($_SESSION['build']['storage'], function($item) use ($removeId) {
            return !(is_array($item) && isset($item['id']) && $item['id'] === $removeId);
        });
        $_SESSION['build']['storage'] = array_values($_SESSION['build']['storage']); // Re-index array
    }
    $nextUrl = '/dmp/public/selections/storage_select.php?removed=1';
    if (isset($_GET['edit'])) {
        $nextUrl .= '&edit=1';
    }
    header("Location: $nextUrl");
    exit;
}
$q             = $_GET['q']           ?? '';
$price_max     = $_GET['price_max']   ?? '';
$f_type        = $_GET['type']        ?? '';
$f_interface   = $_GET['interface']   ?? '';
$f_form_factor = $_GET['form_factor'] ?? '';

$storageTypes = $pdo->query("SELECT DISTINCT type FROM storage WHERE type IS NOT NULL AND type <> '' ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);
$interfaces   = $pdo->query("SELECT DISTINCT interface FROM storage WHERE interface IS NOT NULL AND interface <> '' ORDER BY interface")->fetchAll(PDO::FETCH_COLUMN);
$formFactors  = $pdo->query("SELECT DISTINCT form_factor FROM storage WHERE form_factor IS NOT NULL AND form_factor <> '' ORDER BY form_factor")->fetchAll(PDO::FETCH_COLUMN);

$params = [];
$where = ' WHERE 1=1 ';
if ($q !== '') {
    $where .= " AND (name LIKE ? OR interface LIKE ? OR type LIKE ?)";
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
if ($f_interface !== '') {
    $where .= " AND interface = ?";
    $params[] = $f_interface;
}
if ($f_form_factor !== '') {
    $where .= " AND form_factor = ?";
    $params[] = $f_form_factor;
}

$sql = "SELECT * FROM storage " . $where . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$storages = $stmt->fetchAll(PDO::FETCH_ASSOC);
$selectedDrives = $_SESSION['build']['storage'] ?? [];
$added = isset($_GET['added']) ? true : false;
$removed = isset($_GET['removed']) ? true : false;
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vybrat úložiště</title>
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
    <h1 class="h2 mb-1">Vybrat úložiště</h1>
    <p class="text-muted mb-0">Zvolte úložiště pro svou sestavu (můžete přidat více disků)</p>
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
        <?php foreach ($storageTypes as $t): ?>
          <option value="<?= htmlspecialchars($t) ?>" <?= $f_type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="interface" class="form-select">
        <option value="">Rozhraní – vše</option>
        <?php foreach ($interfaces as $iface): ?>
          <option value="<?= htmlspecialchars($iface) ?>" <?= $f_interface === $iface ? 'selected' : '' ?>><?= htmlspecialchars($iface) ?></option>
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
    <div class="col-md-1 d-grid">
      <button type="submit" class="btn filter-btn" aria-label="Apply filters">Hledat</button>
    </div>
  </form>

  <?php if ($added): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      Disk byl úspěšně přidán!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if ($removed): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      Disk byl odebrán.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($selectedDrives) && is_array($selectedDrives)): ?>
    <div class="mb-5">
      <h3 class="h5 mb-3">Vybrané disky</h3>
      <div class="row g-3">
        <?php foreach ($selectedDrives as $drive): ?>
          <?php if (is_array($drive) && isset($drive['id']) && isset($drive['name'])): ?>
            <div class="col-md-6">
              <div class="card border-success bg-light">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($drive['name']) ?></h5>
                  <?php
                  $fieldLabels = [
                      'price' => 'Cena', 'form_factor' => 'Form faktor', 'type' => 'Typ',
                      'capacity' => 'Kapacita', 'interface' => 'Rozhraní', 'read_speed' => 'Rychlost čtení',
                      'write_speed' => 'Rychlost zápisu', 'tdp' => 'TDP', 'lifespan' => 'Životnost',
                      'brand' => 'Značka', 'color' => 'Barva',
                  ];
                  ?>
                  <ul class="list-group list-group-flush mb-3">
                    <?php
                      $iLabel = $fieldLabels['interface'] ?? 'Rozhraní';
                      $interface = ucwords(str_replace('_', ' ', $drive['interface'] ?? ''));
                      echo "<li class='list-group-item py-1'><strong>" . htmlspecialchars($iLabel) . ":</strong> " . htmlspecialchars($interface) . "</li>\n";
                      foreach ($drive as $field => $value) {
                          if (in_array($field, ['id', 'name', 'interface'])) continue;
                          if (!is_null($value) && $value !== '') {
                              $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
                              $display = $value;
                              if ($field === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                              if ($field === 'capacity') $display = $value . ' GB';
                              if ($field === 'read_speed' || $field === 'write_speed') $display = $value . ' MB/s';
                              if ($field === 'tdp') $display = $value . ' W';
                              if ($field === 'lifespan') $display = $value . ' TBW';
                              echo "<li class='list-group-item py-1'><strong>" . htmlspecialchars($label) . ":</strong> " . htmlspecialchars($display) . "</li>\n";
                          }
                      }
                    ?>
                  </ul>
                  <a href="?remove=<?= $drive['id'] ?>" class="btn btn-sm btn-outline-danger">Odebrat</a>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <h3 class="h5 mb-3">Dostupné disky</h3>
  <div class="row g-3">
    <?php foreach ($storages as $storage): ?>
      <?php if (is_array($storage) && isset($storage['id'])): ?>
        <?php 
          // Check jestli není tento disk již vybrán, pokud ano, zvýraznit ho
          $isSelected = false;
          foreach ($selectedDrives as $selected) {
              if (is_array($selected) && isset($selected['id']) && $selected['id'] === $storage['id']) {
                  $isSelected = true;
                  break;
              }
          }
        ?>
        <div class="col-md-6">
          <div class="card h-100 shadow-sm border-0 <?= $isSelected ? 'bg-light' : '' ?>">
            <div class="card-body">
              <h5 class="card-title mb-3"><?= htmlspecialchars($storage['name'] ?? 'Neznámé') ?></h5>
              <?php
              $fieldLabels = [
                  'price' => 'Cena', 'form_factor' => 'Form faktor', 'type' => 'Typ',
                  'capacity' => 'Kapacita', 'interface' => 'Rozhraní', 'read_speed' => 'Rychlost čtení',
                  'write_speed' => 'Rychlost zápisu', 'tdp' => 'TDP', 'lifespan' => 'Životnost',
                  'brand' => 'Značka', 'color' => 'Barva',
              ];
              ?>
              <ul class="list-group list-group-flush mb-3">
              <?php
              foreach ($storage as $field => $value) {
                  if (in_array($field, ['id', 'name'])) {
                      continue;
                  }
                  if (!is_null($value) && $value !== '') {
                      $label = $fieldLabels[$field] ?? ucwords(str_replace('_', ' ', $field));
                      $display = $value;
                      if ($field === 'price') $display = number_format($value, 0, ',', ' ') . ' Kč';
                      if ($field === 'capacity') $display = $value . ' GB';
                      if ($field === 'read_speed' || $field === 'write_speed') $display = $value . ' MB/s';
                      if ($field === 'tdp') $display = $value . ' W';
                      if ($field === 'lifespan') $display = $value . ' TBW';
                      echo "<li class='list-group-item py-1'><strong>" . htmlspecialchars($label) . ":</strong> " . htmlspecialchars($display) . "</li>\n";
                  }
              }
              ?>
              </ul>
              <?php if ($isSelected): ?>
                <span class="badge bg-success w-100 py-2">✓ Již vybráno</span>
              <?php else: ?>
                <a href="?select=<?= $storage['id'] ?>" class="btn btn-success w-100">
                  Přidat tento disk
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <div class="mt-5 d-flex gap-2">
    <a href="/dmp/public/configurator.php" class="btn btn-primary">
      Hotovo – zpět na konfigurátor
    </a>
    <?php if (empty($selectedDrives)): ?>
      <p class="text-muted my-auto">← Musíte vybrat alespoň jeden disk</p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
