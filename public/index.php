<!-- Úvod - hlavní stránka aplikace s hero sekcí a přehledem funkcí -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DMP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="/dmp/assets/css/style.css">
  
  <style>
    .modal-backdrop.show {
      backdrop-filter: blur(6px);
      background-color: rgba(0, 0, 0, 0.4);
    }

  </style>

</head>



<body>
<?php
session_start(); 
require_once __DIR__ . '/../db/connection.php';
include_once('../includes/navbar.php');

$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';

// Funkce získá komponenty sestavy podle ID sestavy
function getBuildComponents($pdo, $buildId) {
    $components = [];
    
    // Získá všechny části pro tuto sestavu
    $stmt = $pdo->prepare("
        SELECT p.id, p.partId_cpu, p.partId_gpu, p.partId_ram, p.partId_mboard, 
               p.partId_storage, p.partId_psu, p.partId_case, p.partId_cooler
        FROM used_parts up
        JOIN parts p ON up.partId = p.id
        WHERE up.buildId = ?
    ");
    $stmt->execute([$buildId]);
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($parts as $part) {
        if ($part['partId_cpu']) {
            $stmt = $pdo->prepare("SELECT name FROM `cpu` WHERE id = ?");
            $stmt->execute([$part['partId_cpu']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $components[] = ['type' => 'CPU', 'name' => $result['name']];
            }
        } elseif ($part['partId_gpu']) {
            $stmt = $pdo->prepare("SELECT name FROM `gpu` WHERE id = ?");
            $stmt->execute([$part['partId_gpu']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $components[] = ['type' => 'GPU', 'name' => $result['name']];
            }
        } elseif ($part['partId_ram']) {
            $stmt = $pdo->prepare("SELECT name FROM `ram` WHERE id = ?");
            $stmt->execute([$part['partId_ram']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $components[] = ['type' => 'RAM', 'name' => $result['name']];
            }
        } elseif ($part['partId_mboard']) {
            $stmt = $pdo->prepare("SELECT name FROM `motherboard` WHERE id = ?");
            $stmt->execute([$part['partId_mboard']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $components[] = ['type' => 'MBOARD', 'name' => $result['name']];
            }
        } elseif ($part['partId_storage']) {
            $stmt = $pdo->prepare("SELECT name FROM `storage` WHERE id = ?");
            $stmt->execute([$part['partId_storage']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $components[] = ['type' => 'Úložiště', 'name' => $result['name']];
            }
        } elseif ($part['partId_psu']) {
            $stmt = $pdo->prepare("SELECT name FROM `psu` WHERE id = ?");
            $stmt->execute([$part['partId_psu']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $components[] = ['type' => 'PSU', 'name' => $result['name']];
            }
        } elseif ($part['partId_case']) {
            $stmt = $pdo->prepare("SELECT name FROM `case` WHERE id = ?");
            $stmt->execute([$part['partId_case']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $components[] = ['type' => 'Skříň', 'name' => $result['name']];
            }
        } elseif ($part['partId_cooler']) {
            $stmt = $pdo->prepare("SELECT name FROM `cooler` WHERE id = ?");
            $stmt->execute([$part['partId_cooler']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $components[] = ['type' => 'Chladič', 'name' => $result['name']];
            }
        }
    }
    
    return $components;
}

// Vezme všechny veřejné sestavy a jejich komponenty pro zobrazení na úvodní stránce
$stmt = $pdo->prepare("
    SELECT b.id, b.name, b.description, b.userId, u.username, u.roleId, b.createdAt, b.image_path
    FROM builds b
    LEFT JOIN users u ON b.userId = u.id
    WHERE b.isPublic = 1
");
$stmt->execute();
$allBuilds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Odstraní duplicitní sestavy podle ID a zamíchá je, aby se na úvodní stránce objevovaly různé sestavy při každé návštěvě
$seenIds = [];
$uniqueBuilds = [];
foreach ($allBuilds as $build) {
    if (!in_array($build['id'], $seenIds)) {
        $seenIds[] = $build['id'];
        $uniqueBuilds[] = $build;
    }
}

// Zamíchá a vezme pouze první 3
shuffle($uniqueBuilds);
$builds = array_slice($uniqueBuilds, 0, 3);

// Pole náhradních obrázků pro index. Nevypadá dobře, když je prázdný - proto jsem to takto udělal
$placeholders = [
    '/dmp/assets/images/gaming_pc.png',
    '/dmp/assets/images/gaming_pc2.png',
    '/dmp/assets/images/gaming_pc3.png',
    '/dmp/assets/images/gaming_pc4.png',
    '/dmp/assets/images/gaming_pc5.png',
    '/dmp/assets/images/gaming_pc6.png',
    '/dmp/assets/images/gaming_pc7.png'
];

// Přiřadí náhodné, neopakující se náhradní obrázky sestavám bez obrázků
$availablePlaceholders = $placeholders;
shuffle($availablePlaceholders);
$placeholderIndex = 0;
foreach ($builds as &$build) {
    if (!$build['image_path']) {
        $build['image_path'] = $availablePlaceholders[$placeholderIndex % count($availablePlaceholders)];
        $placeholderIndex++;
        $build['is_placeholder'] = true;
    } else {
        $build['is_placeholder'] = false;
    }
}
unset($build);

// Získá komponenty pro každou sestavu
foreach ($builds as &$build) {
    $build['components'] = getBuildComponents($pdo, $build['id']);
}
unset($build); // přeruší referenci, aby se předešlo klasickému PHP foreach reference bug

// --- Statistiky ---
$statsBuilds = (int) $pdo->query("SELECT COUNT(*) FROM builds")->fetchColumn();
$statsUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$statsComponents = 0;
foreach (['cpu', 'gpu', 'ram', 'motherboard', 'storage', 'psu', 'case', 'cooler'] as $tbl) {
    $statsComponents += (int) $pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
}

// --- Populární komponenty ---
$popularCpu = $pdo->query("
    SELECT c.name, COUNT(*) as cnt
    FROM used_parts up
    JOIN parts p ON up.partId = p.id
    JOIN cpu c ON p.partId_cpu = c.id
    WHERE p.partId_cpu IS NOT NULL
    GROUP BY c.id, c.name
    ORDER BY cnt DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$popularGpu = $pdo->query("
    SELECT g.name, COUNT(*) as cnt
    FROM used_parts up
    JOIN parts p ON up.partId = p.id
    JOIN gpu g ON p.partId_gpu = g.id
    WHERE p.partId_gpu IS NOT NULL
    GROUP BY g.id, g.name
    ORDER BY cnt DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$popularRam = $pdo->query("
    SELECT r.name, COUNT(*) as cnt
    FROM used_parts up
    JOIN parts p ON up.partId = p.id
    JOIN ram r ON p.partId_ram = r.id
    WHERE p.partId_ram IS NOT NULL
    GROUP BY r.id, r.name
    ORDER BY cnt DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);
?>

<section class="hero">
  <div class="container" style="max-width: 700px;">
    <h1 class="display-5 fw-bold mb-3">Postav si sestavu snů</h1>
    <p class="lead mb-4 text-muted">
      Tvoř, konfiguruj a spravuj vlastní PC sestavy se strukturovanými komponenty.
    </p>
    <?php if ($loggedIn): ?>
        <div class="text-center mt-3">
          <a href="/dmp/public/configurator.php" class="btn btn-primary btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pc-display" viewBox="0 0 16 16">
  <path d="M8 1a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1zm1 13.5a.5.5 0 1 0 1 0 .5.5 0 0 0-1 0m2 0a.5.5 0 1 0 1 0 .5.5 0 0 0-1 0M9.5 1a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM9 3.5a.5.5 0 0 0 .5.5h5a.5.5 0 0 0 0-1h-5a.5.5 0 0 0-.5.5M1.5 2A1.5 1.5 0 0 0 0 3.5v7A1.5 1.5 0 0 0 1.5 12H6v2h-.5a.5.5 0 0 0 0 1H7v-4H1.5a.5.5 0 0 1-.5-.5v-7a.5.5 0 0 1 .5-.5H7V2z"/>
</svg> Začni stavět
          </a>
        </div>
    <?php else: ?>
        <div class="text-center mt-3">
          <a href="/dmp/public/login.php" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pc-display" viewBox="0 0 16 16">
  <path d="M8 1a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1zm1 13.5a.5.5 0 1 0 1 0 .5.5 0 0 0-1 0m2 0a.5.5 0 1 0 1 0 .5.5 0 0 0-1 0M9.5 1a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM9 3.5a.5.5 0 0 0 .5.5h5a.5.5 0 0 0 0-1h-5a.5.5 0 0 0-.5.5M1.5 2A1.5 1.5 0 0 0 0 3.5v7A1.5 1.5 0 0 0 1.5 12H6v2h-.5a.5.5 0 0 0 0 1H7v-4H1.5a.5.5 0 0 1-.5-.5v-7a.5.5 0 0 1 .5-.5H7V2z"/>
</svg> Začni stavět
          </a>
        </div>
    <?php endif; ?>
  </div>
</section>


<section class="container py-2 ">
  <h2 class="text-start mt-5 mb-4 ps-4 fw-bold">Uživatelské sestavy</h2>
  <div class="row g-4 py-3 px-4">
    <?php if (count($builds) > 0): ?>
      <?php foreach ($builds as $build): ?>
        <div class="col-sm-6 col-lg-4">
          <a href="/dmp/public/build.php?id=<?= htmlspecialchars($build['id']) ?>" style="text-decoration: none; color: inherit;">
            <div class="card feature-card h-100" style="cursor: pointer; transition: transform 0.2s ease;">
              <?php if ($build['image_path']): ?>
                <div class="card-img-top" style="height: 180px; overflow: hidden; background: #f0f0f0;">
                  <img src="<?= $build['is_placeholder'] ? htmlspecialchars($build['image_path']) : '/dmp/' . htmlspecialchars($build['image_path']) ?>" 
                       alt="<?= htmlspecialchars($build['name']) ?>" 
                       style="width: 100%; height: 100%; object-fit: cover;">
                </div>
              <?php else: ?>
                <div class="card-img-top" style="height: 180px; background: linear-gradient(135deg, #618B4A 0%, #4a6a38 100%);"></div>
              <?php endif; ?>
              <div class="card-body">
                <h3 class="h5"><?= htmlspecialchars($build['name']) ?></h3>
                <p class="text-muted mb-0"><?= htmlspecialchars($build['username'] ?? 'Anonymní') ?></p>
                <?php if ($build['description']): ?>
                  <p class="text-muted small mt-1" style="font-size: 0.85rem;"><?= htmlspecialchars(mb_substr($build['description'], 0, 80, 'UTF-8')) ?><?= mb_strlen($build['description'], 'UTF-8') > 80 ? '...' : '' ?></p>
                <?php endif; ?>
                <ul class="list-unstyled mt-2">
                  <?php 
                    $displayComponents = array_slice($build['components'], 0, 4);
                    foreach ($displayComponents as $component): 
                  ?>
                    <li><strong><?= htmlspecialchars($component['type']) ?>:</strong> <?= htmlspecialchars(mb_strlen($component['name'], 'UTF-8') > 25 ? mb_substr($component['name'], 0, 25, 'UTF-8') . '...' : $component['name']) ?></li>
                  <?php endforeach; ?>
                </ul>
                <div class="mt-auto pt-3">
                  <button class="btn btn-primary w-100" style="pointer-events: auto;">
                    Podívejte se!
                  </button>
                </div>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-info text-center py-5" role="alert">
          <p class="mb-0">Zatím žádné veřejné sestavy. Buďte první!</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>


<section class="container py-4">
  <h2 class="text-start mb-4 ps-4 fw-bold">Statistiky</h2>
  <div class="row g-4 px-4">
    <?php if ($popularCpu): ?>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <p class="text-muted small mb-1">Nejpoužívanější</p>
          <div class="mb-2"><span class="badge bg-primary fs-6">CPU</span></div>
          <h5 class="card-title"><?= htmlspecialchars($popularCpu['name']) ?></h5>
          <p class="text-muted small mb-0">Použito v <?= (int)$popularCpu['cnt'] ?> <?= $popularCpu['cnt'] == 1 ? 'sestavě' : 'sestavách' ?></p>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($popularGpu): ?>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <p class="text-muted small mb-1">Nejpoužívanější</p>
          <div class="mb-2"><span class="badge bg-success fs-6">GPU</span></div>
          <h5 class="card-title"><?= htmlspecialchars($popularGpu['name']) ?></h5>
          <p class="text-muted small mb-0">Použito v <?= (int)$popularGpu['cnt'] ?> <?= $popularGpu['cnt'] == 1 ? 'sestavě' : 'sestavách' ?></p>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($popularRam): ?>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <p class="text-muted small mb-1">Nejpoužívanější</p>
          <div class="mb-2"><span class="badge bg-warning text-dark fs-6">RAM</span></div>
          <h5 class="card-title"><?= htmlspecialchars($popularRam['name']) ?></h5>
          <p class="text-muted small mb-0">Použito v <?= (int)$popularRam['cnt'] ?> <?= $popularRam['cnt'] == 1 ? 'sestavě' : 'sestavách' ?></p>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php if (!$popularCpu && !$popularGpu && !$popularRam): ?>
    <div class="col-12">
      <p class="text-muted text-center">Zatím nedostatek dat pro zobrazení oblíbených komponent.</p>
    </div>
    <?php endif; ?>
  </div>
</section>


<section class="container py-4">
  <div class="row g-4 text-center px-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm p-4">
        <div class="display-6 fw-bold text-primary"><?= $statsBuilds ?></div>
        <div class="text-muted mt-1">Vytvořených sestav</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm p-4">
        <div class="display-6 fw-bold text-primary"><?= $statsComponents ?></div>
        <div class="text-muted mt-1">Komponent v databázi</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm p-4">
        <div class="display-6 fw-bold text-primary"><?= $statsUsers ?></div>
        <div class="text-muted mt-1">Registrovaných uživatelů</div>
      </div>
    </div>
  </div>
</section>


<section class="steps py-5 text-center body-variation-bg">
  <div class="container">
    <h2 class="mb-4">Jak to funguje?</h2>
    <ol class="text-center mx-auto" style="max-width: 500px; font-size: 1.1rem; color: #555; list-style-position: inside; padding-left: 0;">
      <li>Vytvořte uživatelský účet</li>
      <li>Začněte novou sestavu PC</li>
      <li>Vyberte kompatibilní komponenty</li>
      <li>Uložte nebo aktualizujte svou sestavu kdykoli</li>
    </ol>
  </div>
</section>



<!-- Modal pro výzvu k přihlášení nebo registraci -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">Přihlašte se</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">
        <p class="py-2 text-center" style="margin: 1rem;">
          Konfigurátor funguje nejlépe s účtem.
          Přihlásit se?
        </p>
      </div>

      <div class="modal-footer justify-content-center gap-2">
        <a href="/dmp/public/configurator.php" class="btn btn-secondary">
          Teď ne
        </a>
        <a href="/dmp/public/login.php" class="btn btn-primary">
          Přihlásit se
        </a>
      </div>
    </div>
  </div>
</div>


<?php include_once('../includes/footer.php'); ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
