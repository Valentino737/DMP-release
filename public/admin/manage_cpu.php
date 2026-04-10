<?php
/**
 * Správa procesorů (CPU)
 * 
 * CRUD operace pro CPU v databázi.
 * Podporuje přidání, editaci a smazání záznamů. Pouze pro adminy.
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) { header("Location: /dmp/public/login.php"); exit; }

$items = [];
$stmt = $pdo->prepare('SELECT * FROM cpu ORDER BY id DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('INSERT INTO cpu (name, price, socket, microarchitecture, cores, threads, core_clock, boost_clock, ram, ram_count, tdp, graphics, l2_cache, l3_cache) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $_POST['name'], $_POST['price'] ?? 0, $_POST['socket'] ?? '', $_POST['microarchitecture'] ?? '',
                empty($_POST['cores']) ? null : $_POST['cores'],
                empty($_POST['threads']) ? null : $_POST['threads'],
                empty($_POST['core_clock']) ? null : $_POST['core_clock'],
                empty($_POST['boost_clock']) ? null : $_POST['boost_clock'],
                empty($_POST['ram']) ? null : $_POST['ram'],
                empty($_POST['ram_count']) ? null : $_POST['ram_count'],
                empty($_POST['tdp']) ? null : $_POST['tdp'],
                empty($_POST['graphics']) ? null : $_POST['graphics'],
                empty($_POST['l2_cache']) ? null : $_POST['l2_cache'],
                empty($_POST['l3_cache']) ? null : $_POST['l3_cache']
            ]);
            header("Location: /dmp/public/admin/manage_cpu.php");
            exit;
        } catch (Exception $e) { }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (csrf_validate()) {
        $pdo->prepare('DELETE FROM cpu WHERE id = ?')->execute([$_POST['id']]);
        header("Location: /dmp/public/admin/manage_cpu.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('UPDATE cpu SET name=?,price=?,socket=?,microarchitecture=?,cores=?,threads=?,core_clock=?,boost_clock=?,ram=?,ram_count=?,tdp=?,graphics=?,l2_cache=?,l3_cache=? WHERE id=?');
            $stmt->execute([
                $_POST['name'], $_POST['price'], $_POST['socket'], $_POST['microarchitecture'],
                empty($_POST['cores']) ? null : $_POST['cores'],
                empty($_POST['threads']) ? null : $_POST['threads'],
                empty($_POST['core_clock']) ? null : $_POST['core_clock'],
                empty($_POST['boost_clock']) ? null : $_POST['boost_clock'],
                empty($_POST['ram']) ? null : $_POST['ram'],
                empty($_POST['ram_count']) ? null : $_POST['ram_count'],
                empty($_POST['tdp']) ? null : $_POST['tdp'],
                empty($_POST['graphics']) ? null : $_POST['graphics'],
                empty($_POST['l2_cache']) ? null : $_POST['l2_cache'],
                empty($_POST['l3_cache']) ? null : $_POST['l3_cache'],
                $_POST['id']
            ]);
            header("Location: /dmp/public/admin/manage_cpu.php");
            exit;
        } catch (Exception $e) { }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Procesory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html { background: linear-gradient(135deg, #f9fafb 0%, #e2e8e2 100%); }
        body { min-height: 100vh; }
        .container { max-width: 1400px; margin: 2rem auto; }
        .section { background: white; padding: 2rem; margin-bottom: 2rem; border-radius: 8px; }
        .btn-save { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .btn-edit { background: #007bff; color: white; padding: 6px 12px; border: none; cursor: pointer; margin-right: 6px; display: inline-block; text-decoration: none; }
        .btn-delete { background: #dc3545; color: white; padding: 6px 12px; border: none; cursor: pointer; }
        .btn-back { background: #6c757d; color: white; padding: 10px 20px; border: none; text-decoration: none; display: inline-block; margin-bottom: 1.5rem; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/dmp/public/admin/index.php" class="btn-back">← Zpět</a>
        <div class="section">
            <h2>⚙️ Správa procesorů</h2>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div><label>Název *</label><input type="text" name="name" required></div>
                    <div><label>Cena</label><input type="number" name="price" step="0.01"></div>
                    <div><label>Socket *</label><input type="text" name="socket" placeholder="LGA1700, AM5..." required></div>
                    <div><label>Mikroarchitektura</label><input type="text" name="microarchitecture" placeholder="Zen 5, Raptor Lake..."></div>
                    <div><label>Jádra</label><input type="number" name="cores" min="1"></div>
                    <div><label>Vlákna</label><input type="number" name="threads" min="1"></div>
                    <div><label>Základní takt (GHz)</label><input type="number" name="core_clock" step="0.1"></div>
                    <div><label>Turbo takt (GHz)</label><input type="number" name="boost_clock" step="0.1"></div>
                    <div><label>Paměť</label><select name="ram"><option value="">Vybrat...</option><option>DDR2</option><option>DDR3</option><option>DDR4</option><option>DDR5</option></select></div>
                    <div><label>Paměťové kanály</label><input type="number" name="ram_count" min="1"></div>
                    <div><label>TDP (W)</label><input type="number" name="tdp" min="0"></div>
                    <div><label>Integrovaná grafika</label><input type="text" name="graphics" placeholder="Intel UHD 770..."></div>
                    <div><label>L2 Cache (KB)</label><input type="number" name="l2_cache" min="0"></div>
                    <div><label>L3 Cache (KB)</label><input type="number" name="l3_cache" min="0"></div>
                </div>
                <button type="submit" class="btn-save">+ Přidat</button>
            </form>
        </div>
        <div class="section">
            <div style="margin-bottom:1rem;"><input type="text" id="filterInput" placeholder="Filtrovat..." style="max-width:300px;margin-bottom:0"></div>
            <table>
                <thead><tr><th>ID</th><th>Název</th><th>Socket</th><th>Jádra/Vlákna</th><th>TDP</th><th>Cena</th><th>Akce</th></tr></thead>
                <tbody id="tableBody">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['socket']) ?></td>
                        <td><?= $item['cores'] ?? '-' ?> / <?= $item['threads'] ?? '-' ?></td>
                        <td><?= $item['tdp'] ? $item['tdp'] . 'W' : '-' ?></td>
                        <td><?= $item['price'] ?> Kč</td>
                        <td>
                            <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= $item['price'] ?>" data-socket="<?= htmlspecialchars($item['socket']) ?>" data-micro="<?= htmlspecialchars($item['microarchitecture'] ?? '') ?>" data-cores="<?= $item['cores'] ?? '' ?>" data-threads="<?= $item['threads'] ?? '' ?>" data-core-clock="<?= $item['core_clock'] ?? '' ?>" data-boost-clock="<?= $item['boost_clock'] ?? '' ?>" data-ram="<?= $item['ram'] ?? '' ?>" data-ram-count="<?= $item['ram_count'] ?? '' ?>" data-tdp="<?= $item['tdp'] ?? '' ?>" data-graphics="<?= htmlspecialchars($item['graphics'] ?? '') ?>" data-l2="<?= $item['l2_cache'] ?? '' ?>" data-l3="<?= $item['l3_cache'] ?? '' ?>">Upravit</button>
                            <form method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('Opravdu smazat?')">Smazat</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Upravit procesor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="modalId">
                    <div class="modal-body">
                        <div class="form-row">
                            <div><label>Název</label><input type="text" name="name" id="modalName" required></div>
                            <div><label>Cena</label><input type="number" name="price" id="modalPrice" step="0.01"></div>
                            <div><label>Socket</label><input type="text" name="socket" id="modalSocket" required></div>
                            <div><label>Mikroarchitektura</label><input type="text" name="microarchitecture" id="modalMicro"></div>
                            <div><label>Jádra</label><input type="number" name="cores" id="modalCores" min="1"></div>
                            <div><label>Vlákna</label><input type="number" name="threads" id="modalThreads" min="1"></div>
                            <div><label>Základní takt (GHz)</label><input type="number" name="core_clock" id="modalCoreClock" step="0.1"></div>
                            <div><label>Turbo takt (GHz)</label><input type="number" name="boost_clock" id="modalBoostClock" step="0.1"></div>
                            <div><label>Paměť</label><select name="ram" id="modalRam"><option value="">Vybrat...</option><option>DDR2</option><option>DDR3</option><option>DDR4</option><option>DDR5</option></select></div>
                            <div><label>Paměťové kanály</label><input type="number" name="ram_count" id="modalRamCount" min="1"></div>
                            <div><label>TDP (W)</label><input type="number" name="tdp" id="modalTdp" min="0"></div>
                            <div><label>Integrovaná grafika</label><input type="text" name="graphics" id="modalGraphics"></div>
                            <div><label>L2 Cache (KB)</label><input type="number" name="l2_cache" id="modalL2" min="0"></div>
                            <div><label>L3 Cache (KB)</label><input type="number" name="l3_cache" id="modalL3" min="0"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn-save">Uložit změny</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
            const b = e.relatedTarget;
            document.getElementById('modalId').value = b.getAttribute('data-id');
            document.getElementById('modalName').value = b.getAttribute('data-name');
            document.getElementById('modalPrice').value = b.getAttribute('data-price');
            document.getElementById('modalSocket').value = b.getAttribute('data-socket');
            document.getElementById('modalMicro').value = b.getAttribute('data-micro');
            document.getElementById('modalCores').value = b.getAttribute('data-cores');
            document.getElementById('modalThreads').value = b.getAttribute('data-threads');
            document.getElementById('modalCoreClock').value = b.getAttribute('data-core-clock');
            document.getElementById('modalBoostClock').value = b.getAttribute('data-boost-clock');
            document.getElementById('modalRam').value = b.getAttribute('data-ram');
            document.getElementById('modalRamCount').value = b.getAttribute('data-ram-count');
            document.getElementById('modalTdp').value = b.getAttribute('data-tdp');
            document.getElementById('modalGraphics').value = b.getAttribute('data-graphics');
            document.getElementById('modalL2').value = b.getAttribute('data-l2');
            document.getElementById('modalL3').value = b.getAttribute('data-l3');
        });
        document.getElementById('filterInput').addEventListener('input', function() {
            const f = this.value.toLowerCase();
            document.querySelectorAll('#tableBody tr').forEach(function(r) {
                r.style.display = r.textContent.toLowerCase().includes(f) ? '' : 'none';
            });
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Check admin access
if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) {
    header("Location: /dmp/public/login.php");
    exit;
}

$username = $_SESSION['username'];
$cpus = [];
$addErrors = [];
$editErrors = [];
$editId = null;
$editCpu = null;
$successMessage = '';

// Fetch all CPUs
$stmt = $pdo->prepare('SELECT * FROM cpu ORDER BY id DESC');
$stmt->execute();
$cpus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle add CPU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_cpu') {
    if (!csrf_validate()) {
        $addErrors[] = 'Neplatný CSRF token';
    } else {
        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $socket = trim($_POST['socket'] ?? '');
        $microarchitecture = trim($_POST['microarchitecture'] ?? '');
        $cores = trim($_POST['cores'] ?? '');
        $threads = trim($_POST['threads'] ?? '');
        $core_clock = trim($_POST['core_clock'] ?? '');
        $boost_clock = trim($_POST['boost_clock'] ?? '');
        $ram = trim($_POST['ram'] ?? '');
        $ram_count = trim($_POST['ram_count'] ?? '');
        $tdp = trim($_POST['tdp'] ?? '');
        $graphics = trim($_POST['graphics'] ?? '');
        $l2_cache = trim($_POST['l2_cache'] ?? '');
        $l3_cache = trim($_POST['l3_cache'] ?? '');

        if (empty($name)) $addErrors[] = 'Název je povinný';
        if (empty($socket)) $addErrors[] = 'Socket je povinný';
        if (!is_numeric($price) || $price < 0) $addErrors[] = 'Cena musí být číslo >= 0';
        if (!empty($cores) && (!is_numeric($cores) || $cores < 0)) $addErrors[] = 'Jádra musí být číslo';
        if (!empty($threads) && (!is_numeric($threads) || $threads < 0)) $addErrors[] = 'Vlákna musí být číslo';
        if (!empty($tdp) && (!is_numeric($tdp) || $tdp < 0)) $addErrors[] = 'TDP musí být číslo';

        if (empty($addErrors)) {
            try {
                $stmt = $pdo->prepare('
                    INSERT INTO cpu (name, price, socket, microarchitecture, cores, threads, core_clock, boost_clock, ram, ram_count, tdp, graphics, l2_cache, l3_cache)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $name, $price, $socket, $microarchitecture, 
                    empty($cores) ? null : $cores,
                    empty($threads) ? null : $threads,
                    empty($core_clock) ? null : $core_clock,
                    empty($boost_clock) ? null : $boost_clock,
                    empty($ram) ? null : $ram,
                    empty($ram_count) ? null : $ram_count,
                    empty($tdp) ? null : $tdp,
                    empty($graphics) ? null : $graphics,
                    empty($l2_cache) ? null : $l2_cache,
                    empty($l3_cache) ? null : $l3_cache
                ]);
                $successMessage = 'CPU úspěšně přidáno';
                header("Location: /dmp/public/admin/manage_cpu.php");
                exit;
            } catch (Exception $e) {
                error_log('CPU add error: ' . $e->getMessage());
                $addErrors[] = 'Chyba při přidávání CPU. Zkuste to znovu.';
            }
        }
    }
}

// Handle edit CPU
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM cpu WHERE id = ?');
    $stmt->execute([$editId]);
    $editCpu = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$editCpu) {
        $editId = null;
    }
}

// Handle update CPU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_cpu') {
    if (!csrf_validate()) {
        $editErrors[] = 'Neplatný CSRF token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $editErrors[] = 'Neplatné ID';
        } else {
            $name = trim($_POST['name'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $socket = trim($_POST['socket'] ?? '');
            $microarchitecture = trim($_POST['microarchitecture'] ?? '');
            $cores = trim($_POST['cores'] ?? '');
            $threads = trim($_POST['threads'] ?? '');
            $core_clock = trim($_POST['core_clock'] ?? '');
            $boost_clock = trim($_POST['boost_clock'] ?? '');
            $ram = trim($_POST['ram'] ?? '');
            $ram_count = trim($_POST['ram_count'] ?? '');
            $tdp = trim($_POST['tdp'] ?? '');
            $graphics = trim($_POST['graphics'] ?? '');
            $l2_cache = trim($_POST['l2_cache'] ?? '');
            $l3_cache = trim($_POST['l3_cache'] ?? '');

            if (empty($name)) $editErrors[] = 'Název je povinný';
            if (empty($socket)) $editErrors[] = 'Socket je povinný';
            if (!is_numeric($price) || $price < 0) $editErrors[] = 'Cena musí být číslo >= 0';

            if (empty($editErrors)) {
                try {
                    $stmt = $pdo->prepare('
                        UPDATE cpu SET name = ?, price = ?, socket = ?, microarchitecture = ?, cores = ?, threads = ?, core_clock = ?, boost_clock = ?, ram = ?, ram_count = ?, tdp = ?, graphics = ?, l2_cache = ?, l3_cache = ?
                        WHERE id = ?
                    ');
                    $stmt->execute([
                        $name, $price, $socket, $microarchitecture,
                        empty($cores) ? null : $cores,
                        empty($threads) ? null : $threads,
                        empty($core_clock) ? null : $core_clock,
                        empty($boost_clock) ? null : $boost_clock,
                        empty($ram) ? null : $ram,
                        empty($ram_count) ? null : $ram_count,
                        empty($tdp) ? null : $tdp,
                        empty($graphics) ? null : $graphics,
                        empty($l2_cache) ? null : $l2_cache,
                        empty($l3_cache) ? null : $l3_cache,
                        $id
                    ]);
                    $successMessage = 'CPU úspěšně aktualizováno';
                    header("Location: /dmp/public/admin/manage_cpu.php");
                    exit;
                } catch (Exception $e) {
                    error_log('CPU edit error: ' . $e->getMessage());
                    $editErrors[] = 'Chyba při aktualizaci CPU. Zkuste to znovu.';
                }
            }
        }
    }
}

// Handle delete CPU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_cpu') {
    if (!csrf_validate()) {
        $editErrors[] = 'Neplatný CSRF token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM cpu WHERE id = ?');
                $stmt->execute([$id]);
                $successMessage = 'CPU úspěšně odstraněno';
                header("Location: /dmp/public/admin/manage_cpu.php");
                exit;
            } catch (Exception $e) {
                error_log('CPU delete error: ' . $e->getMessage());
                $editErrors[] = 'Chyba při odstraňování CPU. Zkuste to znovu.';
            }
        }
    }
}

// Refresh CPU list after operations
$stmt = $pdo->prepare('SELECT * FROM cpu ORDER BY id DESC');
$stmt->execute();
$cpus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Admin - Spravovat CPU</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        html {
            background: linear-gradient(135deg, #f9fafb 0%, #e2e8e2 100%);
        }
        body {
            min-height: 100vh;
        }
        .admin-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .section h2 {
            border-bottom: 2px solid #DEE5E5;
            padding-bottom: 12px;
            margin-bottom: 1.5rem;
            color: #0A0908;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
            color: #0A0908;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
        }
        .btn-save {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-save:hover {
            background: #218838;
        }
        .btn-edit {
            background: #007bff;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            display: inline-block;
            text-decoration: none;
        }
        .btn-edit:hover {
            background: #0056b3;
            text-decoration: none;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .btn-back:hover {
            background: #5a6268;
            text-decoration: none;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background: #f5f5f5;
            font-weight: 700;
            color: #0A0908;
        }
        .table tr:hover {
            background: #f9f9f9;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <a href="/dmp/public/admin/index.php" class="btn-back">← Zpět na Admin Panel</a>

        <div class="section">
            <h2>⚙️ Spravovat CPU</h2>

            <?php if (!empty($addErrors)): ?>
                <div class="alert alert-error">
                    <strong>Chyby:</strong><br>
                    <?= implode('<br>', array_map('htmlspecialchars', $addErrors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_cpu">

                <div class="form-row">
                    <div class="form-group">
                        <label>Název *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Socket *</label>
                        <input type="text" name="socket" placeholder="LGA1700, AM5..." required>
                    </div>
                    <div class="form-group">
                        <label>Cena (Kč)</label>
                        <input type="number" name="price" step="0.01" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>Mikroarchitektura</label>
                        <input type="text" name="microarchitecture" placeholder="Zen 5, Raptor Lake...">
                    </div>
                    <div class="form-group">
                        <label>Jádra</label>
                        <input type="number" name="cores" min="1">
                    </div>
                    <div class="form-group">
                        <label>Vlákna</label>
                        <input type="number" name="threads" min="1">
                    </div>
                    <div class="form-group">
                        <label>Základní takt (GHz)</label>
                        <input type="number" name="core_clock" step="0.1">
                    </div>
                    <div class="form-group">
                        <label>Turbo takt (GHz)</label>
                        <input type="number" name="boost_clock" step="0.1">
                    </div>
                    <div class="form-group">
                        <label>Paměť</label>
                        <select name="ram">
                            <option value="">Vybrat...</option>
                            <option value="DDR2">DDR2</option>
                            <option value="DDR3">DDR3</option>
                            <option value="DDR4">DDR4</option>
                            <option value="DDR5">DDR5</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Počet paměťových kanálů</label>
                        <input type="number" name="ram_count" min="1">
                    </div>
                    <div class="form-group">
                        <label>TDP (W)</label>
                        <input type="number" name="tdp" min="0">
                    </div>
                    <div class="form-group">
                        <label>Integrovaná grafika</label>
                        <input type="text" name="graphics" placeholder="Intel UHD 770...">
                    </div>
                    <div class="form-group">
                        <label>L2 Cache (KB)</label>
                        <input type="number" name="l2_cache" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>L3 Cache (KB)</label>
                        <input type="number" name="l3_cache" min="0" value="0">
                    </div>
                </div>

                <button type="submit" class="btn-save">+ Přidat CPU</button>
            </form>
        </div>

        <div class="section">
            <h2>📋 CPU v databázi (<?= count($cpus) ?>)</h2>

            <?php if (empty($cpus)): ?>
                <p style="color: #666;">Žádné CPU zatím.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Název</th>
                            <th>Socket</th>
                            <th>Jádra/Vlákna</th>
                            <th>TDP</th>
                            <th>Cena</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cpus as $cpu): ?>
                            <tr>
                                <td><?= $cpu['id'] ?></td>
                                <td><?= htmlspecialchars($cpu['name']) ?></td>
                                <td><?= htmlspecialchars($cpu['socket']) ?></td>
                                <td><?= $cpu['cores'] ?? '-' ?> / <?= $cpu['threads'] ?? '-' ?></td>
                                <td><?= $cpu['tdp'] ? $cpu['tdp'] . 'W' : '-' ?></td>
                                <td><?= $cpu['price'] ?> Kč</td>
                                <td>
                                    <div class="actions">
                                        <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $cpu['id'] ?>" data-name="<?= htmlspecialchars($cpu['name']) ?>" data-socket="<?= htmlspecialchars($cpu['socket']) ?>" data-price="<?= $cpu['price'] ?>" data-micro="<?= htmlspecialchars($cpu['microarchitecture'] ?? '') ?>" data-cores="<?= $cpu['cores'] ?? '' ?>" data-threads="<?= $cpu['threads'] ?? '' ?>" data-core-clock="<?= $cpu['core_clock'] ?? '' ?>" data-boost-clock="<?= $cpu['boost_clock'] ?? '' ?>" data-ram="<?= $cpu['ram'] ?? '' ?>" data-ram-count="<?= $cpu['ram_count'] ?? '' ?>" data-tdp="<?= $cpu['tdp'] ?? '' ?>" data-graphics="<?= htmlspecialchars($cpu['graphics'] ?? '') ?>" data-l2="<?= $cpu['l2_cache'] ?? '' ?>" data-l3="<?= $cpu['l3_cache'] ?? '' ?>">Editovat</button>
                                        <form method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_cpu">
                                            <input type="hidden" name="id" value="<?= $cpu['id'] ?>">
                                            <button type="submit" class="btn-delete" onclick="return confirm('Opravdu smazat?')">Smazat</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if ($editCpu): ?>
            <div class="section">
                <h2>Editovat CPU: <?= htmlspecialchars($editCpu['name']) ?></h2>

                <?php if (!empty($editErrors)): ?>
                    <div class="alert alert-error">
                        <strong>Chyby:</strong><br>
                        <?= implode('<br>', array_map('htmlspecialchars', $editErrors)) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_cpu">
                    <input type="hidden" name="id" value="<?= $editCpu['id'] ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label>Název *</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($editCpu['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Socket *</label>
                            <input type="text" name="socket" value="<?= htmlspecialchars($editCpu['socket']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Cena (Kč)</label>
                            <input type="number" name="price" step="0.01" min="0" value="<?= $editCpu['price'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Mikroarchitektura</label>
                            <input type="text" name="microarchitecture" value="<?= htmlspecialchars($editCpu['microarchitecture'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Jádra</label>
                            <input type="number" name="cores" min="1" value="<?= $editCpu['cores'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Vlákna</label>
                            <input type="number" name="threads" min="1" value="<?= $editCpu['threads'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Základní takt (GHz)</label>
                            <input type="number" name="core_clock" step="0.1" value="<?= $editCpu['core_clock'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Turbo takt (GHz)</label>
                            <input type="number" name="boost_clock" step="0.1" value="<?= $editCpu['boost_clock'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Paměť</label>
                            <select name="ram">
                                <option value="">Vybrat...</option>
                                <option value="DDR2" <?= $editCpu['ram'] === 'DDR2' ? 'selected' : '' ?>>DDR2</option>
                                <option value="DDR3" <?= $editCpu['ram'] === 'DDR3' ? 'selected' : '' ?>>DDR3</option>
                                <option value="DDR4" <?= $editCpu['ram'] === 'DDR4' ? 'selected' : '' ?>>DDR4</option>
                                <option value="DDR5" <?= $editCpu['ram'] === 'DDR5' ? 'selected' : '' ?>>DDR5</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Počet paměťových kanálů</label>
                            <input type="number" name="ram_count" min="1" value="<?= $editCpu['ram_count'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>TDP (W)</label>
                            <input type="number" name="tdp" min="0" value="<?= $editCpu['tdp'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Integrovaná grafika</label>
                            <input type="text" name="graphics" value="<?= htmlspecialchars($editCpu['graphics'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>L2 Cache (KB)</label>
                            <input type="number" name="l2_cache" min="0" value="<?= $editCpu['l2_cache'] ?>">
                        </div>
                        <div class="form-group">
                            <label>L3 Cache (KB)</label>
                            <input type="number" name="l3_cache" min="0" value="<?= $editCpu['l3_cache'] ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn-save">💾 Uložit změny</button>
                    <a href="?edit=" class="btn-back" style="margin-left: 12px;">Zrušit</a>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editovat CPU</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_cpu">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="modal-body">
                        <?php if (!empty($editErrors)): ?>
                            <div class="alert alert-error">
                                <strong>Chyby:</strong><br>
                                <?= implode('<br>', array_map('htmlspecialchars', $editErrors)) ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Název *</label>
                                <input type="text" name="name" id="editName" required>
                            </div>
                            <div class="form-group">
                                <label>Socket *</label>
                                <input type="text" name="socket" id="editSocket" required>
                            </div>
                            <div class="form-group">
                                <label>Cena (Kč)</label>
                                <input type="number" name="price" id="editPrice" step="0.01" min="0">
                            </div>
                            <div class="form-group">
                                <label>Mikroarchitektura</label>
                                <input type="text" name="microarchitecture" id="editMicro">
                            </div>
                            <div class="form-group">
                                <label>Jádra</label>
                                <input type="number" name="cores" id="editCores" min="1">
                            </div>
                            <div class="form-group">
                                <label>Vlákna</label>
                                <input type="number" name="threads" id="editThreads" min="1">
                            </div>
                            <div class="form-group">
                                <label>Základní takt (GHz)</label>
                                <input type="number" name="core_clock" id="editCoreClock" step="0.1">
                            </div>
                            <div class="form-group">
                                <label>Turbo takt (GHz)</label>
                                <input type="number" name="boost_clock" id="editBoostClock" step="0.1">
                            </div>
                            <div class="form-group">
                                <label>Paměť</label>
                                <select name="ram" id="editRam">
                                    <option value="">Vybrat...</option>
                                    <option value="DDR2">DDR2</option>
                                    <option value="DDR3">DDR3</option>
                                    <option value="DDR4">DDR4</option>
                                    <option value="DDR5">DDR5</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Počet paměťových kanálů</label>
                                <input type="number" name="ram_count" id="editRamCount" min="1">
                            </div>
                            <div class="form-group">
                                <label>TDP (W)</label>
                                <input type="number" name="tdp" id="editTdp" min="0">
                            </div>
                            <div class="form-group">
                                <label>Integrovaná grafika</label>
                                <input type="text" name="graphics" id="editGraphics">
                            </div>
                            <div class="form-group">
                                <label>L2 Cache (KB)</label>
                                <input type="number" name="l2_cache" id="editL2" min="0">
                            </div>
                            <div class="form-group">
                                <label>L3 Cache (KB)</label>
                                <input type="number" name="l3_cache" id="editL3" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn-save">💾 Uložit změny</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('editId').value = button.getAttribute('data-id');
            document.getElementById('editName').value = button.getAttribute('data-name');
            document.getElementById('editSocket').value = button.getAttribute('data-socket');
            document.getElementById('editPrice').value = button.getAttribute('data-price');
            document.getElementById('editMicro').value = button.getAttribute('data-micro');
            document.getElementById('editCores').value = button.getAttribute('data-cores');
            document.getElementById('editThreads').value = button.getAttribute('data-threads');
            document.getElementById('editCoreClock').value = button.getAttribute('data-core-clock');
            document.getElementById('editBoostClock').value = button.getAttribute('data-boost-clock');
            document.getElementById('editRam').value = button.getAttribute('data-ram');
            document.getElementById('editRamCount').value = button.getAttribute('data-ram-count');
            document.getElementById('editTdp').value = button.getAttribute('data-tdp');
            document.getElementById('editGraphics').value = button.getAttribute('data-graphics');
            document.getElementById('editL2').value = button.getAttribute('data-l2');
            document.getElementById('editL3').value = button.getAttribute('data-l3');
        });
    </script>

    <footer style="background: #0A0908; color: white; text-align: center; padding: 20px; margin-top: 40px;">
        <p>&copy; 2026 DMP Configurator Admin. Všechna práva vyhrazena.</p>
    </footer>

    <script>
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('editId').value = button.getAttribute('data-id');
            document.getElementById('editName').value = button.getAttribute('data-name');
            document.getElementById('editSocket').value = button.getAttribute('data-socket');
            document.getElementById('editPrice').value = button.getAttribute('data-price');
            document.getElementById('editMicro').value = button.getAttribute('data-micro');
            document.getElementById('editCores').value = button.getAttribute('data-cores');
            document.getElementById('editThreads').value = button.getAttribute('data-threads');
            document.getElementById('editCoreClock').value = button.getAttribute('data-core-clock');
            document.getElementById('editBoostClock').value = button.getAttribute('data-boost-clock');
            document.getElementById('editRam').value = button.getAttribute('data-ram');
            document.getElementById('editRamCount').value = button.getAttribute('data-ram-count');
            document.getElementById('editTdp').value = button.getAttribute('data-tdp');
            document.getElementById('editGraphics').value = button.getAttribute('data-graphics');
            document.getElementById('editL2').value = button.getAttribute('data-l2');
            document.getElementById('editL3').value = button.getAttribute('data-l3');
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
