<?php
/**
 * Správa grafických karet (GPU)
 * 
 * CRUD operace pro GPU v databázi.
 * Podporuje přidání, editaci a smazání záznamů. Pouze pro adminy.
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) { header("Location: /dmp/public/login.php"); exit; }

$items = [];
$stmt = $pdo->prepare('SELECT * FROM gpu ORDER BY id DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('INSERT INTO gpu (name, price, chipset, vram_size, vram_type, vram_clock, core_clock, boost_clock, hdmi_count, dp_count, vga_count, dvi_count, max_monitors, length, width, height, tdp, connector, connector_count) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $_POST['name'], $_POST['price'] ?? 0, $_POST['chipset'] ?? '',
                empty($_POST['vram_size']) ? null : $_POST['vram_size'],
                $_POST['vram_type'] ?? 'GDDR6',
                empty($_POST['vram_clock']) ? null : $_POST['vram_clock'],
                empty($_POST['core_clock']) ? null : $_POST['core_clock'],
                empty($_POST['boost_clock']) ? null : $_POST['boost_clock'],
                $_POST['hdmi_count'] ?? 0, $_POST['dp_count'] ?? 0, $_POST['vga_count'] ?? 0, $_POST['dvi_count'] ?? 0,
                empty($_POST['max_monitors']) ? null : $_POST['max_monitors'],
                empty($_POST['length']) ? null : $_POST['length'],
                $_POST['width'] ?? 0, $_POST['height'] ?? 0,
                empty($_POST['tdp']) ? null : $_POST['tdp'],
                empty($_POST['connector']) ? 'none' : $_POST['connector'],
                $_POST['connector_count'] ?? 0
            ]);
            header("Location: /dmp/public/admin/manage_gpu.php");
            exit;
        } catch (Exception $e) { }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (csrf_validate()) {
        $pdo->prepare('DELETE FROM gpu WHERE id = ?')->execute([$_POST['id']]);
        header("Location: /dmp/public/admin/manage_gpu.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('UPDATE gpu SET name=?,price=?,chipset=?,vram_size=?,vram_type=?,vram_clock=?,core_clock=?,boost_clock=?,hdmi_count=?,dp_count=?,vga_count=?,dvi_count=?,max_monitors=?,length=?,width=?,height=?,tdp=?,connector=?,connector_count=? WHERE id=?');
            $stmt->execute([
                $_POST['name'], $_POST['price'], $_POST['chipset'],
                empty($_POST['vram_size']) ? null : $_POST['vram_size'],
                $_POST['vram_type'],
                empty($_POST['vram_clock']) ? null : $_POST['vram_clock'],
                empty($_POST['core_clock']) ? null : $_POST['core_clock'],
                empty($_POST['boost_clock']) ? null : $_POST['boost_clock'],
                $_POST['hdmi_count'] ?? 0, $_POST['dp_count'] ?? 0, $_POST['vga_count'] ?? 0, $_POST['dvi_count'] ?? 0,
                empty($_POST['max_monitors']) ? null : $_POST['max_monitors'],
                empty($_POST['length']) ? null : $_POST['length'],
                $_POST['width'] ?? 0, $_POST['height'] ?? 0,
                empty($_POST['tdp']) ? null : $_POST['tdp'],
                empty($_POST['connector']) ? 'none' : $_POST['connector'],
                $_POST['connector_count'] ?? 0,
                $_POST['id']
            ]);
            header("Location: /dmp/public/admin/manage_gpu.php");
            exit;
        } catch (Exception $e) { }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Grafické karty</title>
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
            <h2>🎮 Správa grafických karet</h2>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div><label>Název *</label><input type="text" name="name" required></div>
                    <div><label>Cena</label><input type="number" name="price" step="0.01"></div>
                    <div><label>Chipset</label><input type="text" name="chipset" placeholder="GA102, AD103..."></div>
                    <div><label>VRAM (GB)</label><input type="number" name="vram_size"></div>
                    <div><label>Typ VRAM *</label><select name="vram_type"><option>GDDR5</option><option>GDDR5X</option><option selected>GDDR6</option><option>GDDR6X</option><option>GDDR7</option><option>HBM2</option><option>HBM2e</option><option>HBM3</option><option>HBM3e</option></select></div>
                    <div><label>VRAM takt (GHz)</label><input type="number" name="vram_clock" step="0.1"></div>
                    <div><label>Základní takt (MHz)</label><input type="number" name="core_clock"></div>
                    <div><label>Boost takt (MHz)</label><input type="number" name="boost_clock"></div>
                    <div><label>HDMI</label><input type="number" name="hdmi_count" value="0"></div>
                    <div><label>DisplayPort</label><input type="number" name="dp_count" value="0"></div>
                    <div><label>VGA</label><input type="number" name="vga_count" value="0"></div>
                    <div><label>DVI</label><input type="number" name="dvi_count" value="0"></div>
                    <div><label>Max monitorů</label><input type="number" name="max_monitors"></div>
                    <div><label>Délka (mm)</label><input type="number" name="length"></div>
                    <div><label>Šířka (mm)</label><input type="number" name="width" value="0"></div>
                    <div><label>Výška (mm)</label><input type="number" name="height" value="0"></div>
                    <div><label>TDP (W)</label><input type="number" name="tdp"></div>
                    <div><label>Konektor</label><select name="connector"><option value="none">Žádný</option><option>4-pin</option><option>6-pin</option><option>8-pin</option><option>12-pin</option><option>14-pin</option><option>16-pin</option></select></div>
                    <div><label>Počet konektorů</label><input type="number" name="connector_count" value="0"></div>
                </div>
                <button type="submit" class="btn-save">+ Přidat</button>
            </form>
        </div>
        <div class="section">
            <div style="margin-bottom:1rem;"><input type="text" id="filterInput" placeholder="Filtrovat..." style="max-width:300px;margin-bottom:0"></div>
            <table>
                <thead><tr><th>ID</th><th>Název</th><th>VRAM</th><th>TDP</th><th>Délka</th><th>Cena</th><th>Akce</th></tr></thead>
                <tbody id="tableBody">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['vram_size'] ? $item['vram_size'] . 'GB' : '-' ?></td>
                        <td><?= $item['tdp'] ? $item['tdp'] . 'W' : '-' ?></td>
                        <td><?= $item['length'] ? $item['length'] . 'mm' : '-' ?></td>
                        <td><?= $item['price'] ?> Kč</td>
                        <td>
                            <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= $item['price'] ?>" data-chipset="<?= htmlspecialchars($item['chipset'] ?? '') ?>" data-vram-size="<?= $item['vram_size'] ?? '' ?>" data-vram-type="<?= htmlspecialchars($item['vram_type'] ?? '') ?>" data-vram-clock="<?= $item['vram_clock'] ?? '' ?>" data-core-clock="<?= $item['core_clock'] ?? '' ?>" data-boost-clock="<?= $item['boost_clock'] ?? '' ?>" data-hdmi="<?= $item['hdmi_count'] ?? 0 ?>" data-dp="<?= $item['dp_count'] ?? 0 ?>" data-vga="<?= $item['vga_count'] ?? 0 ?>" data-dvi="<?= $item['dvi_count'] ?? 0 ?>" data-monitors="<?= $item['max_monitors'] ?? '' ?>" data-length="<?= $item['length'] ?? '' ?>" data-width="<?= $item['width'] ?? 0 ?>" data-height="<?= $item['height'] ?? 0 ?>" data-tdp="<?= $item['tdp'] ?? '' ?>" data-connector="<?= htmlspecialchars($item['connector'] ?? 'none') ?>" data-connector-count="<?= $item['connector_count'] ?? 0 ?>">Upravit</button>
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
                    <h5 class="modal-title" id="editModalLabel">Upravit grafickou kartu</h5>
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
                            <div><label>Chipset</label><input type="text" name="chipset" id="modalChipset"></div>
                            <div><label>VRAM (GB)</label><input type="number" name="vram_size" id="modalVramSize"></div>
                            <div><label>Typ VRAM</label><select name="vram_type" id="modalVramType"><option>GDDR5</option><option>GDDR5X</option><option>GDDR6</option><option>GDDR6X</option><option>GDDR7</option><option>HBM2</option><option>HBM2e</option><option>HBM3</option><option>HBM3e</option></select></div>
                            <div><label>VRAM takt (GHz)</label><input type="number" name="vram_clock" id="modalVramClock" step="0.1"></div>
                            <div><label>Základní takt (MHz)</label><input type="number" name="core_clock" id="modalCoreClock"></div>
                            <div><label>Boost takt (MHz)</label><input type="number" name="boost_clock" id="modalBoostClock"></div>
                            <div><label>HDMI</label><input type="number" name="hdmi_count" id="modalHdmi"></div>
                            <div><label>DisplayPort</label><input type="number" name="dp_count" id="modalDp"></div>
                            <div><label>VGA</label><input type="number" name="vga_count" id="modalVga"></div>
                            <div><label>DVI</label><input type="number" name="dvi_count" id="modalDvi"></div>
                            <div><label>Max monitorů</label><input type="number" name="max_monitors" id="modalMonitors"></div>
                            <div><label>Délka (mm)</label><input type="number" name="length" id="modalLength"></div>
                            <div><label>Šířka (mm)</label><input type="number" name="width" id="modalWidth"></div>
                            <div><label>Výška (mm)</label><input type="number" name="height" id="modalHeight"></div>
                            <div><label>TDP (W)</label><input type="number" name="tdp" id="modalTdp"></div>
                            <div><label>Konektor</label><select name="connector" id="modalConnector"><option value="none">Žádný</option><option>4-pin</option><option>6-pin</option><option>8-pin</option><option>12-pin</option><option>14-pin</option><option>16-pin</option></select></div>
                            <div><label>Počet konektorů</label><input type="number" name="connector_count" id="modalConnectorCount"></div>
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
            document.getElementById('modalChipset').value = b.getAttribute('data-chipset');
            document.getElementById('modalVramSize').value = b.getAttribute('data-vram-size');
            document.getElementById('modalVramType').value = b.getAttribute('data-vram-type');
            document.getElementById('modalVramClock').value = b.getAttribute('data-vram-clock');
            document.getElementById('modalCoreClock').value = b.getAttribute('data-core-clock');
            document.getElementById('modalBoostClock').value = b.getAttribute('data-boost-clock');
            document.getElementById('modalHdmi').value = b.getAttribute('data-hdmi');
            document.getElementById('modalDp').value = b.getAttribute('data-dp');
            document.getElementById('modalVga').value = b.getAttribute('data-vga');
            document.getElementById('modalDvi').value = b.getAttribute('data-dvi');
            document.getElementById('modalMonitors').value = b.getAttribute('data-monitors');
            document.getElementById('modalLength').value = b.getAttribute('data-length');
            document.getElementById('modalWidth').value = b.getAttribute('data-width');
            document.getElementById('modalHeight').value = b.getAttribute('data-height');
            document.getElementById('modalTdp').value = b.getAttribute('data-tdp');
            document.getElementById('modalConnector').value = b.getAttribute('data-connector');
            document.getElementById('modalConnectorCount').value = b.getAttribute('data-connector-count');
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
$gpus = [];
$addErrors = [];
$editId = null;
$editGpu = null;

// Fetch all GPUs
$stmt = $pdo->prepare('SELECT * FROM gpu ORDER BY id DESC');
$stmt->execute();
$gpus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle add GPU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_gpu') {
    if (!csrf_validate()) {
        $addErrors[] = 'Neplatný CSRF token';
    } else {
        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $chipset = trim($_POST['chipset'] ?? '');
        $vram_size = trim($_POST['vram_size'] ?? '');
        $vram_type = trim($_POST['vram_type'] ?? '');
        $vram_clock = trim($_POST['vram_clock'] ?? '');
        $core_clock = trim($_POST['core_clock'] ?? '');
        $boost_clock = trim($_POST['boost_clock'] ?? '');
        $hdmi_count = trim($_POST['hdmi_count'] ?? '0');
        $dp_count = trim($_POST['dp_count'] ?? '0');
        $vga_count = trim($_POST['vga_count'] ?? '0');
        $dvi_count = trim($_POST['dvi_count'] ?? '0');
        $max_monitors = trim($_POST['max_monitors'] ?? '');
        $length = trim($_POST['length'] ?? '');
        $width = trim($_POST['width'] ?? '0');
        $height = trim($_POST['height'] ?? '0');
        $tdp = trim($_POST['tdp'] ?? '');
        $connector = trim($_POST['connector'] ?? '');
        $connector_count = trim($_POST['connector_count'] ?? '0');

        if (empty($name)) $addErrors[] = 'Název je povinný';
        if (!is_numeric($price)) $addErrors[] = 'Cena musí být číslo';
        if (empty($vram_type)) $addErrors[] = 'Typ VRAM je povinný';
        if (!is_numeric($width) || $width < 0) $addErrors[] = 'Šířka musí být číslo';
        if (!is_numeric($height) || $height < 0) $addErrors[] = 'Výška musí být číslo';

        if (empty($addErrors)) {
            try {
                $stmt = $pdo->prepare('
                    INSERT INTO gpu (name, price, chipset, vram_size, vram_type, vram_clock, core_clock, boost_clock, hdmi_count, dp_count, vga_count, dvi_count, max_monitors, length, width, height, tdp, connector, connector_count)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $name, $price, $chipset,
                    empty($vram_size) ? null : $vram_size,
                    $vram_type,
                    empty($vram_clock) ? null : $vram_clock,
                    empty($core_clock) ? null : $core_clock,
                    empty($boost_clock) ? null : $boost_clock,
                    $hdmi_count, $dp_count, $vga_count, $dvi_count,
                    empty($max_monitors) ? null : $max_monitors,
                    empty($length) ? null : $length,
                    $width, $height,
                    empty($tdp) ? null : $tdp,
                    empty($connector) ? 'none' : $connector,
                    $connector_count
                ]);
                header("Location: /dmp/public/admin/manage_gpu.php");
                exit;
            } catch (Exception $e) {
                error_log('GPU add error: ' . $e->getMessage());
                $addErrors[] = 'Chyba při přidávání GPU. Zkuste to znovu.';
            }
        }
    }
}

// Handle edit GPU
$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM gpu WHERE id = ?');
    $stmt->execute([$editId]);
    $editGpu = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$editGpu) {
        $editId = null;
    }
}

// Handle update GPU
$editErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_gpu') {
    if (!csrf_validate()) {
        $editErrors[] = 'Neplatný CSRF token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $editErrors[] = 'Neplatné ID';
        } else {
            $name = trim($_POST['name'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $chipset = trim($_POST['chipset'] ?? '');
            $vram_size = trim($_POST['vram_size'] ?? '');
            $vram_type = trim($_POST['vram_type'] ?? '');
            $vram_clock = trim($_POST['vram_clock'] ?? '');
            $core_clock = trim($_POST['core_clock'] ?? '');
            $boost_clock = trim($_POST['boost_clock'] ?? '');
            $hdmi_count = trim($_POST['hdmi_count'] ?? '0');
            $dp_count = trim($_POST['dp_count'] ?? '0');
            $vga_count = trim($_POST['vga_count'] ?? '0');
            $dvi_count = trim($_POST['dvi_count'] ?? '0');
            $max_monitors = trim($_POST['max_monitors'] ?? '');
            $length = trim($_POST['length'] ?? '');
            $width = trim($_POST['width'] ?? '0');
            $height = trim($_POST['height'] ?? '0');
            $tdp = trim($_POST['tdp'] ?? '');
            $connector = trim($_POST['connector'] ?? 'none');
            $connector_count = trim($_POST['connector_count'] ?? '0');

            if (empty($name)) $editErrors[] = 'Název je povinný';
            if (!is_numeric($price)) $editErrors[] = 'Cena musí být číslo';
            if (empty($vram_type)) $editErrors[] = 'Typ VRAM je povinný';

            if (empty($editErrors)) {
                try {
                    $stmt = $pdo->prepare('
                        UPDATE gpu SET name = ?, price = ?, chipset = ?, vram_size = ?, vram_type = ?, vram_clock = ?, core_clock = ?, boost_clock = ?, hdmi_count = ?, dp_count = ?, vga_count = ?, dvi_count = ?, max_monitors = ?, length = ?, width = ?, height = ?, tdp = ?, connector = ?, connector_count = ?
                        WHERE id = ?
                    ');
                    $stmt->execute([
                        $name, $price, $chipset,
                        empty($vram_size) ? null : $vram_size,
                        $vram_type,
                        empty($vram_clock) ? null : $vram_clock,
                        empty($core_clock) ? null : $core_clock,
                        empty($boost_clock) ? null : $boost_clock,
                        $hdmi_count, $dp_count, $vga_count, $dvi_count,
                        empty($max_monitors) ? null : $max_monitors,
                        empty($length) ? null : $length,
                        $width, $height,
                        empty($tdp) ? null : $tdp,
                        $connector,
                        $connector_count,
                        $id
                    ]);
                    header("Location: /dmp/public/admin/manage_gpu.php");
                    exit;
                } catch (Exception $e) {
                    error_log('GPU edit error: ' . $e->getMessage());
                    $editErrors[] = 'Chyba při aktualizaci GPU. Zkuste to znovu.';
                }
            }
        }
    }
}

// Handle delete GPU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_gpu') {
    if (!csrf_validate()) {
        $editErrors[] = 'Neplatný CSRF token';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM gpu WHERE id = ?');
                $stmt->execute([$id]);
                header("Location: /dmp/public/admin/manage_gpu.php");
                exit;
            } catch (Exception $e) {
                error_log('GPU delete error: ' . $e->getMessage());
                $editErrors[] = 'Chyba při odstraňování GPU. Zkuste to znovu.';
            }
        }
    }
}

// Refresh GPU list
$stmt = $pdo->prepare('SELECT * FROM gpu ORDER BY id DESC');
$stmt->execute();
$gpus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Admin - Spravovat GPU</title>
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
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
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
            <h2>🎮 Spravovat GPU</h2>

            <?php if (!empty($addErrors)): ?>
                <div class="alert alert-error">
                    <?= implode('<br>', array_map('htmlspecialchars', $addErrors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_gpu">

                <div class="form-row">
                    <div class="form-group">
                        <label>Název *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Cena (Kč)</label>
                        <input type="number" name="price" step="0.01" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>Chipset</label>
                        <input type="text" name="chipset" placeholder="AD102, RDNA3...">
                    </div>
                    <div class="form-group">
                        <label>VRAM (GB)</label>
                        <input type="number" name="vram_size" min="1">
                    </div>
                    <div class="form-group">
                        <label>Typ VRAM *</label>
                        <select name="vram_type" required>
                            <option value="">Vybrat...</option>
                            <option value="GDDR5">GDDR5</option>
                            <option value="GDDR5X">GDDR5X</option>
                            <option value="GDDR6">GDDR6</option>
                            <option value="GDDR6X">GDDR6X</option>
                            <option value="GDDR7">GDDR7</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Takt VRAM (GHz)</label>
                        <input type="number" name="vram_clock" step="0.1">
                    </div>
                    <div class="form-group">
                        <label>Základní takt (MHz)</label>
                        <input type="number" name="core_clock">
                    </div>
                    <div class="form-group">
                        <label>Turbo takt (MHz)</label>
                        <input type="number" name="boost_clock">
                    </div>
                    <div class="form-group">
                        <label>HDMI porty</label>
                        <input type="number" name="hdmi_count" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>DisplayPort porty</label>
                        <input type="number" name="dp_count" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label>Max monitorů</label>
                        <input type="number" name="max_monitors">
                    </div>
                    <div class="form-group">
                        <label>Délka (mm)</label>
                        <input type="number" name="length">
                    </div>
                    <div class="form-group">
                        <label>Šířka (mm) *</label>
                        <input type="number" name="width" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Výška (mm) *</label>
                        <input type="number" name="height" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>TDP (W)</label>
                        <input type="number" name="tdp">
                    </div>
                    <div class="form-group">
                        <label>Typ konektoru</label>
                        <select name="connector">
                            <option value="none">Žádný</option>
                            <option value="6-pin">6-pin</option>
                            <option value="8-pin">8-pin</option>
                            <option value="12-pin">12-pin</option>
                            <option value="16-pin">16-pin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Počet konektorů</label>
                        <input type="number" name="connector_count" min="0" value="0">
                    </div>
                </div>

                <button type="submit" class="btn-save">+ Přidat GPU</button>
            </form>
        </div>

        <div class="section">
            <h2>📋 GPU v databázi (<?= count($gpus) ?>)</h2>

            <?php if (empty($gpus)): ?>
                <p style="color: #666;">Žádné GPU zatím.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Název</th>
                            <th>VRAM</th>
                            <th>TDP</th>
                            <th>Délka</th>
                            <th>Cena</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gpus as $gpu): ?>
                            <tr>
                                <td><?= $gpu['id'] ?></td>
                                <td><?= htmlspecialchars($gpu['name']) ?></td>
                                <td><?= $gpu['vram_size'] ? $gpu['vram_size'] . 'GB' : '-' ?></td>
                                <td><?= $gpu['tdp'] ? $gpu['tdp'] . 'W' : '-' ?></td>
                                <td><?= $gpu['length'] ? $gpu['length'] . 'mm' : '-' ?></td>
                                <td><?= $gpu['price'] ?> Kč</td>
                                <td>
                                    <div class="actions">
                                        <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $gpu['id'] ?>" data-name="<?= htmlspecialchars($gpu['name']) ?>" data-price="<?= $gpu['price'] ?>" data-chipset="<?= htmlspecialchars($gpu['chipset'] ?? '') ?>" data-vram-size="<?= $gpu['vram_size'] ?? '' ?>" data-vram-type="<?= $gpu['vram_type'] ?? '' ?>" data-vram-clock="<?= $gpu['vram_clock'] ?? '' ?>" data-core-clock="<?= $gpu['core_clock'] ?? '' ?>" data-boost-clock="<?= $gpu['boost_clock'] ?? '' ?>" data-hdmi="<?= $gpu['hdmi_count'] ?? '0' ?>" data-dp="<?= $gpu['dp_count'] ?? '0' ?>" data-vga="<?= $gpu['vga_count'] ?? '0' ?>" data-dvi="<?= $gpu['dvi_count'] ?? '0' ?>" data-monitors="<?= $gpu['max_monitors'] ?? '' ?>" data-length="<?= $gpu['length'] ?? '' ?>" data-width="<?= $gpu['width'] ?? '0' ?>" data-height="<?= $gpu['height'] ?? '0' ?>" data-tdp="<?= $gpu['tdp'] ?? '' ?>" data-connector="<?= $gpu['connector'] ?? '' ?>" data-connector-count="<?= $gpu['connector_count'] ?? '0' ?>">Editovat</button>
                                        <form method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_gpu">
                                            <input type="hidden" name="id" value="<?= $gpu['id'] ?>">
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

        <?php if ($editGpu): ?>
            <div class="section">
                <h2>Editovat GPU: <?= htmlspecialchars($editGpu['name']) ?></h2>

                <?php if (!empty($editErrors)): ?>
                    <div class="alert alert-error">
                        <?= implode('<br>', array_map('htmlspecialchars', $editErrors)) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_gpu">
                    <input type="hidden" name="id" value="<?= $editGpu['id'] ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label>Název *</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($editGpu['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Cena (Kč)</label>
                            <input type="number" name="price" step="0.01" value="<?= $editGpu['price'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Chipset</label>
                            <input type="text" name="chipset" value="<?= htmlspecialchars($editGpu['chipset'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>VRAM (GB)</label>
                            <input type="number" name="vram_size" value="<?= $editGpu['vram_size'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Typ VRAM *</label>
                            <select name="vram_type" required>
                                <option value="GDDR5" <?= $editGpu['vram_type'] === 'GDDR5' ? 'selected' : '' ?>>GDDR5</option>
                                <option value="GDDR6" <?= $editGpu['vram_type'] === 'GDDR6' ? 'selected' : '' ?>>GDDR6</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Délka (mm)</label>
                            <input type="number" name="length" value="<?= $editGpu['length'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>TDP (W)</label>
                            <input type="number" name="tdp" value="<?= $editGpu['tdp'] ?? '' ?>">
                        </div>
                    </div>

        <?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editovat GPU</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_gpu">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group"><label>Název *</label><input type="text" name="name" id="editName" required></div>
                            <div class="form-group"><label>Chipset</label><input type="text" name="chipset" id="editChipset"></div>
                            <div class="form-group"><label>Cena (Kč)</label><input type="number" name="price" id="editPrice" step="0.01"></div>
                            <div class="form-group"><label>VRAM (GB)</label><input type="number" name="vram_size" id="editVramSize"></div>
                            <div class="form-group"><label>Typ VRAM *</label><select name="vram_type" id="editVramType" required><option value="GDDR5">GDDR5</option><option value="GDDR6">GDDR6</option><option value="GDDR7">GDDR7</option></select></div>
                            <div class="form-group"><label>Takt VRAM (MHz)</label><input type="number" name="vram_clock" id="editVramClock"></div>
                            <div class="form-group"><label>Základní takt (MHz)</label><input type="number" name="core_clock" id="editCoreClock"></div>
                            <div class="form-group"><label>Turbo takt (MHz)</label><input type="number" name="boost_clock" id="editBoostClock"></div>
                            <div class="form-group"><label>HDMI</label><input type="number" name="hdmi_count" id="editHdmi" min="0"></div>
                            <div class="form-group"><label>DisplayPort</label><input type="number" name="dp_count" id="editDp" min="0"></div>
                            <div class="form-group"><label>VGA</label><input type="number" name="vga_count" id="editVga" min="0"></div>
                            <div class="form-group"><label>DVI</label><input type="number" name="dvi_count" id="editDvi" min="0"></div>
                            <div class="form-group"><label>Max Monitory</label><input type="number" name="max_monitors" id="editMonitors"></div>
                            <div class="form-group"><label>Délka (mm)</label><input type="number" name="length" id="editLength"></div>
                            <div class="form-group"><label>Šířka (mm)</label><input type="number" name="width" id="editWidth" min="0"></div>
                            <div class="form-group"><label>Výška (mm)</label><input type="number" name="height" id="editHeight" min="0"></div>
                            <div class="form-group"><label>TDP (W)</label><input type="number" name="tdp" id="editTdp"></div>
                            <div class="form-group"><label>Konektor</label><select name="connector" id="editConnector"><option value="none">Žádný</option><option value="6-pin">6-pin</option><option value="8-pin">8-pin</option><option value="12-pin">12-pin</option><option value="16-pin">16-pin</option></select></div>
                            <div class="form-group"><label>Počet konektorů</label><input type="number" name="connector_count" id="editConnectorCount" min="0"></div>
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
            const btn = event.relatedTarget;
            document.getElementById('editId').value = btn.getAttribute('data-id');
            document.getElementById('editName').value = btn.getAttribute('data-name');
            document.getElementById('editChipset').value = btn.getAttribute('data-chipset');
            document.getElementById('editPrice').value = btn.getAttribute('data-price');
            document.getElementById('editVramSize').value = btn.getAttribute('data-vram-size');
            document.getElementById('editVramType').value = btn.getAttribute('data-vram-type');
            document.getElementById('editVramClock').value = btn.getAttribute('data-vram-clock');
            document.getElementById('editCoreClock').value = btn.getAttribute('data-core-clock');
            document.getElementById('editBoostClock').value = btn.getAttribute('data-boost-clock');
            document.getElementById('editHdmi').value = btn.getAttribute('data-hdmi');
            document.getElementById('editDp').value = btn.getAttribute('data-dp');
            document.getElementById('editVga').value = btn.getAttribute('data-vga');
            document.getElementById('editDvi').value = btn.getAttribute('data-dvi');
            document.getElementById('editMonitors').value = btn.getAttribute('data-monitors');
            document.getElementById('editLength').value = btn.getAttribute('data-length');
            document.getElementById('editWidth').value = btn.getAttribute('data-width');
            document.getElementById('editHeight').value = btn.getAttribute('data-height');
            document.getElementById('editTdp').value = btn.getAttribute('data-tdp');
            document.getElementById('editConnector').value = btn.getAttribute('data-connector');
            document.getElementById('editConnectorCount').value = btn.getAttribute('data-connector-count');
        });
    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
