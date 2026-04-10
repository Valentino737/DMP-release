<?php
/**
 * Konfigurátor sestav
 * 
 * Hlavní stránka pro sestávání PC. Zobrazuje vybrané komponenty,
 * kontrolu kompatibility a celkovou cenu. Stav se ukládá do session.
 * Podporuje režim nové sestavy i editaci existující.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/build_helpers.php';

$currentPage = 'configurator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_build'])) {
    if (!csrf_validate()) {
        header("Location: /dmp/public/configurator.php");
        exit;
    }
    
    // Zresetuje stav sestavy v session
    $_SESSION['build'] = [
        'cpu' => null,
        'gpu' => null,
        'ram' => null,
        'motherboard' => null,
        'psu' => null,
        'case' => null,
        'storage' => [],
        'cooling' => null
    ];
    $_SESSION['editing_build_id'] = null;
    $_SESSION['is_editing_build'] = false;
    
    header("Location: /dmp/public/configurator.php");
    exit;
}

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

$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';

// Detekuje režim editace - pouze pokud existuje parametr a platná sestava v session
if (isset($_GET['edit'])) {
    // Uživatel se vrací z režimu editace
    if (isset($_SESSION['editing_build_id'])) {
        $_SESSION['is_editing_build'] = true;
    }
}

// Ověří, zda jsme v režimu editace
$editingBuildId = null;
if ($_SESSION['is_editing_build'] ?? false) {
    $editingBuildId = $_SESSION['editing_build_id'] ?? null;
} else {
    // Není v režimu editace, vymaže všechny editovací značky
    $_SESSION['is_editing_build'] = false;
    $_SESSION['editing_build_id'] = null;
}

$build = $_SESSION['build'];

$compat       = checkCompatibility($build);
$errors       = $compat['errors'];
$warnings     = $compat['warnings'];
$cpuError     = $compat['cpuError'] ?? false;
$ramError     = $compat['ramError'] ?? false;
$gpuError     = $compat['gpuError'] ?? false;
$caseError    = $compat['caseError'] ?? false;
$storageError = $compat['storageError'] ?? false;
$psuError     = $compat['psuError'] ?? false;
$coolingError = $compat['coolingError'] ?? false;
$moboError    = $compat['moboError'] ?? false;

$totalPrice   = calculateBuildPrice($build);

$allSelected = isset($build['cpu']) && $build['cpu'] && isset($build['motherboard']) && $build['motherboard'] && isset($build['ram']) && $build['ram'] && isset($build['gpu']) && $build['gpu'] && !empty($build['storage']) && isset($build['psu']) && $build['psu'] && isset($build['case']) && $build['case'] && !empty($build['cooling']);
$canFinish = $allSelected && empty($errors);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Konfigurátor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/dmp/assets/css/style.css">
  <style>
    body {
      background-color: #f9fafb;
      color: #0A0908;
    }

    h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 16px;
      color: #0A0908;
    }

    .subtext {
      font-size: 17px;
      opacity: 0.85;
      max-width: 800px;
      margin-bottom: 24px;
      color: #0A0908;
    }

    .warning-banner {
      background: #f0ad4e;
      color: #333;
      font-weight: 600;
      padding: 12px 18px;
      border-radius: 6px;
      margin-bottom: 16px;
      font-size: 14px;
      display: inline-block;
      border-left: 4px solid #ec971f;
    }

    .error-banner {
      background: #d9534f;
      color: white;
      font-weight: 600;
      padding: 12px 18px;
      border-radius: 6px;
      margin-bottom: 16px;
      font-size: 14px;
      display: inline-block;
      border-left: 4px solid #c9302c;
    }

    .tile {
      background: linear-gradient(135deg, #DEE5E5 0%, #E2E8E2 100%);
      border: 2px solid #b8c1c1;
      border-radius: 8px;
      padding: 30px 20px;
      min-height: 200px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      transition: all 0.3s ease;
      cursor: pointer;
      text-decoration: none;
      color: #0A0908;
      position: relative;
    }

    .tile:hover {
      background: linear-gradient(135deg, #E2E8E2 0%, #F4F6F4 100%);
      border-color: #618B4A;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(97, 139, 74, 0.15);
    }

    .tile.green {
      background: linear-gradient(135deg, #E8F4E6 0%, #F4F9F2 100%);
      border-color: #618B4A;
      box-shadow: 0 0 0 1px #618B4A;
    }

    .tile.red {
      background: linear-gradient(135deg, #FFE8E8 0%, #FFEEEE 100%);
      border-color: #dc2626;
      box-shadow: 0 0 0 1px #dc2626;
    }

    .tile.yellow {
      border-color: #eab308;
    }

    .tile-header {
      font-size: 17px;
      opacity: 0.7;
      margin-bottom: 6px;
    }

    .tile-title {
      font-size: 22px;
      font-weight: 600;
      margin-top: auto;
      color: #0A0908;
    }

    .tile.green::before {
      content: '✓';
      position: absolute;
      top: 10px;
      left: 10px;
      background: #618B4A;
      color: white;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    .components-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 15px;
      margin-bottom: 40px;
    }

    .footer-actions {
      display: flex;
      gap: 15px;
      justify-content: flex-end;
      margin-top: 30px;
    }

    .btn-reset {
      background: rgba(97, 139, 74, 0.1);
      color: #618B4A;
      border: 2px solid #618B4A;
      padding: 10px 20px;
      border-radius: 4px;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-reset:hover {
      background: #618B4A;
      color: white;
    }

    .btn-finish {
      background: #618B4A;
      color: white;
      border: 2px solid #618B4A;
      padding: 10px 20px;
      border-radius: 4px;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-finish:hover:not(:disabled) {
      background: #4f6f3a;
      border-color: #4f6f3a;
    }

    .btn-finish:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .content-wrapper {
      flex: 1;
      padding: 40px 20px;
    }

    .footer-actions {
      margin-bottom: 100px;
    }
  </style>
</head>

<body class="bg-light">

<?php include_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
  <h1>Jdeme stavět?</h1>
  <div class="subtext">Vyberte komponenty pro váš vysněný počítač. Klikněte na dlaždici komponenty pro výběr nebo změnu.</div>
</div>

<div class="container">
    <?php if (!empty($errors) || !empty($warnings)): ?>
      <div class="row g-2">
        <?php foreach ($errors as $msg): ?>
          <div class="col-12">
            <div class="error-banner w-100"><?= htmlspecialchars($msg) ?></div>
          </div>
        <?php endforeach; ?>
        <?php foreach ($warnings as $msg): ?>
          <div class="col-12">
            <div class="warning-banner w-100"><?= htmlspecialchars($msg) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

<div class="container mt-4">
  <div class="row g-4">
    <?php $editParam = ($editingBuildId) ? '?edit=1' : ''; ?>
    <div class="col-12 col-md-3">
      <a href="/dmp/public/selections/cpu_select.php<?= $editParam ?>"
         class="tile <?= (isset($build['cpu']) && $build['cpu'] && !$cpuError) ? 'green' : '' ?> <?= $cpuError ? 'red' : '' ?>"
         style="text-decoration: none;">
        <div class="tile-header">1 | Procesor</div>
        <div class="mt-auto"><?= (isset($build['cpu']) && $build['cpu']) ? htmlspecialchars($build['cpu']['name']) : 'Vybrat procesor' ?></div>
      </a>
    </div>
    <div class="col-12 col-md-3">
      <a href="/dmp/public/selections/motherboard_select.php<?= $editParam ?>"
         class="tile <?= (isset($build['motherboard']) && $build['motherboard'] && !$moboError) ? 'green' : '' ?> <?= $moboError ? 'red' : '' ?>"
         style="text-decoration: none;">
        <div class="tile-header">2 | Základní deska</div>
        <div class="mt-auto"><?= (isset($build['motherboard']) && $build['motherboard']) ? htmlspecialchars($build['motherboard']['name']) : 'Vybrat základní desku' ?></div>
      </a>
    </div>
    <div class="col-12 col-md-3">
      <a href="/dmp/public/selections/ram_select.php<?= $editParam ?>"
         class="tile <?= (isset($build['ram']) && $build['ram'] && !$ramError) ? 'green' : '' ?> <?= $ramError ? 'red' : '' ?>"
         style="text-decoration: none;">
        <div class="tile-header">3 | Paměť</div>
        <div class="mt-auto"><?= (isset($build['ram']) && $build['ram']) ? htmlspecialchars($build['ram']['name']) : 'Vybrat paměť' ?></div>
      </a>
    </div>
    <div class="col-12 col-md-3">
      <a href="/dmp/public/selections/gpu_select.php<?= $editParam ?>" class="tile <?= (isset($build['gpu']) && $build['gpu'] && !$gpuError) ? 'green' : '' ?> <?= $gpuError ? 'red' : '' ?>" style="text-decoration: none;">
        <div class="tile-header">4 | Grafická karta</div>
        <div class="mt-auto"><?= (isset($build['gpu']) && $build['gpu']) ? htmlspecialchars($build['gpu']['name']) : 'Vybrat grafickou kartu' ?></div>
      </a>
    </div>
    <div class="col-12 col-md-3">
      <a href="/dmp/public/selections/storage_select.php<?= $editParam ?>" class="tile <?= (!empty($build['storage']) && !$storageError) ? 'green' : '' ?> <?= $storageError ? 'red' : '' ?>" style="text-decoration: none;">
        <div class="tile-header">5 | Úložiště</div>
        <div class="mt-auto">
          <?php if (!empty($build['storage']) && is_array($build['storage'])): ?>
            <small style="display: block; opacity: 0.7; margin-top: 8px;">
              <?php 
                $driveNames = array_filter(array_map(function($d) { 
                    return (is_array($d) && isset($d['name'])) ? htmlspecialchars($d['name']) : ''; 
                }, $build['storage']));
                echo implode(', ', array_slice($driveNames, 0, 2));
                if (count($driveNames) > 2) echo '...';
              ?>
            </small>
          <?php else: ?>
            Vybrat úložiště
          <?php endif; ?>
        </div>
      </a>
    </div>
    <div class="col-12 col-md-3">
      <a href="/dmp/public/selections/psu_select.php<?= $editParam ?>" class="tile <?= (isset($build['psu']) && $build['psu'] && !$psuError) ? 'green' : '' ?> <?= $psuError ? 'red' : '' ?>" style="text-decoration: none;">
        <div class="tile-header">6 | Zdroj napájení</div>
        <div class="mt-auto"><?= (isset($build['psu']) && $build['psu']) ? htmlspecialchars($build['psu']['name']) : 'Vybrat zdroj' ?></div>
      </a>
    </div>
    <div class="col-12 col-md-3">
      <a href="/dmp/public/selections/case_select.php<?= $editParam ?>" class="tile <?= (isset($build['case']) && $build['case'] && !$caseError) ? 'green' : '' ?> <?= $caseError ? 'red' : '' ?>" style="text-decoration: none;">
        <div class="tile-header">7 | Skříň</div>
        <div class="mt-auto"><?= isset($build['case']) && $build['case'] ? htmlspecialchars($build['case']['name']) : 'Vybrat skříň' ?></div>
      </a>
    </div>
    <div class="col-12 col-md-3">
      <a href="/dmp/public/selections/cooling_select.php<?= $editParam ?>" class="tile <?= (!empty($build['cooling']) && !$coolingError) ? 'green' : '' ?> <?= $coolingError ? 'red' : '' ?>" style="text-decoration: none;">
        <div class="tile-header">8 | Chlazení</div>
        <div class="mt-auto"><?php echo (!empty($build['cooling']) && is_array($build['cooling']) && isset($build['cooling']['name'])) ? htmlspecialchars($build['cooling']['name']) : 'Vybrat chlazení'; ?></div>
      </a>
    </div>
  </div>
</div>

<?php if ($totalPrice > 0): ?>
<div class="container mt-4">
    <div style="text-align: right; font-size: 1.25rem; font-weight: 700; color: #618B4A;">
        Celková cena: <?= number_format($totalPrice, 0, ',', ' ') ?> Kč
    </div>
</div>
<?php endif; ?>

<div class="container footer-actions mt-4">
    <form method="POST" style="display: inline;" onsubmit="return confirm('Opravdu chcete resetovat celou konfiguraci?');">
        <?= csrf_field() ?>
        <button type="submit" name="reset_build" class="btn-reset">Resetovat konfiguraci</button>
    </form>
    <?php if ($editingBuildId): ?>
        <button type="button" id="btn-save-edit" class="btn-finish <?= $canFinish ? '' : 'disabled' ?>" <?= $canFinish ? '' : 'disabled' ?>>Uložit změny</button>
    <?php else: ?>
        <a href="/dmp/public/build_finish.php" class="btn-finish <?= $canFinish ? '' : 'disabled' ?>" <?php if (!$canFinish): ?>onclick="return false;"<?php endif; ?>>Dokončit sestavu</a>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

<script>
    // Zajistí funkčnost tlačítka "Uložit změny" v režimu editace
    const saveBtn = document.getElementById('btn-save-edit');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            if (this.disabled) return;
            this.disabled = true;
            this.textContent = 'Ukládání...';

            fetch('/dmp/api/builds/update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: '<?= csrf_token() ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/dmp/public/dashboard.php';
                } else {
                    alert('Chyba: ' + data.message);
                    this.disabled = false;
                    this.textContent = 'Uložit změny';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Chyba sítě. Prosím zkuste znovu.');
                this.disabled = false;
                this.textContent = 'Uložit změny';
            });
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>