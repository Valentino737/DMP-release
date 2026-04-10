<?php
/**
 * Správa úložišť
 * 
 * CRUD operace pro úložiště (SSD/HDD) v databázi.
 * Podporuje přidání, editaci a smazání záznamů. Pouze pro adminy.
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) { header("Location: /dmp/public/login.php"); exit; }

$items = [];
$stmt = $pdo->prepare('SELECT * FROM storage ORDER BY id DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('INSERT INTO storage (name, price, form_factor, type, capacity, interface, read_speed, write_speed, iops, cache_mb) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$_POST['name'], $_POST['price'] ?? 0, $_POST['form_factor'] ?? '2.5"', $_POST['type'] ?? 'SSD', $_POST['capacity'] ?? 1000, $_POST['interface'] ?? 'SATA', $_POST['read_speed'] ?? 550, $_POST['write_speed'] ?? 530, $_POST['iops'] ?? 100, $_POST['cache_mb'] ?? 256]);
            header("Location: /dmp/public/admin/manage_storage.php");
            exit;
        } catch (Exception $e) { }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (csrf_validate()) {
        $pdo->prepare('DELETE FROM storage WHERE id = ?')->execute([$_POST['id']]);
        header("Location: /dmp/public/admin/manage_storage.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('UPDATE storage SET name=?,price=?,form_factor=?,type=?,capacity=?,interface=?,read_speed=?,write_speed=?,iops=?,cache_mb=? WHERE id=?');
            $stmt->execute([$_POST['name'], $_POST['price'], $_POST['form_factor'], $_POST['type'], $_POST['capacity'], $_POST['interface'], $_POST['read_speed'], $_POST['write_speed'], $_POST['iops'], $_POST['cache_mb'], $_POST['id']]);
            header("Location: /dmp/public/admin/manage_storage.php");
            exit;
        } catch (Exception $e) { }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Úložiště</title>
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
            <h2>💾 Správa úložišť</h2>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div><label>Název *</label><input type="text" name="name" required></div>
                    <div><label>Cena</label><input type="number" name="price" step="0.01"></div>
                    <div><label>Formát</label><select name="form_factor"><option>2.5"</option><option>3.5"</option><option>M.2</option></select></div>
                    <div><label>Typ</label><select name="type"><option>SSD</option><option>HDD</option><option>NVMe</option></select></div>
                    <div><label>Kapacita (GB)</label><input type="number" name="capacity" value="1000"></div>
                    <div><label>Rozhraní</label><select name="interface"><option>SATA</option><option>NVMe</option><option>PCIe 3.0</option><option>PCIe 4.0</option></select></div>
                    <div><label>Rychlost čtení (MB/s)</label><input type="number" name="read_speed" value="550"></div>
                    <div><label>Rychlost zápisu (MB/s)</label><input type="number" name="write_speed" value="530"></div>
                </div>
                <button type="submit" class="btn-save">+ Přidat</button>
            </form>
        </div>
        <div class="section">
            <div style="margin-bottom:1rem;"><input type="text" id="filterInput" placeholder="Filtrovat..." style="max-width:300px;margin-bottom:0"></div>
            <table>
                <thead><tr><th>ID</th><th>Název</th><th>Kapacita</th><th>Rozhraní</th><th>Rychlost</th><th>Cena</th><th>Akce</th></tr></thead>
                <tbody id="tableBody">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['capacity'] ?>GB</td>
                        <td><?= htmlspecialchars($item['interface']) ?></td>
                        <td><?= $item['read_speed'] ?>MB/s</td>
                        <td><?= $item['price'] ?></td>
                        <td>
                            <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= $item['price'] ?>" data-form-factor="<?= htmlspecialchars($item['form_factor'] ?? '') ?>" data-type="<?= htmlspecialchars($item['type'] ?? '') ?>" data-capacity="<?= $item['capacity'] ?? '' ?>" data-interface="<?= htmlspecialchars($item['interface'] ?? '') ?>" data-read-speed="<?= $item['read_speed'] ?? '' ?>" data-write-speed="<?= $item['write_speed'] ?? '' ?>" data-iops="<?= $item['iops'] ?? '' ?>" data-cache-mb="<?= $item['cache_mb'] ?? '' ?>">Upravit</button>
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

    <!-- Editace -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Upravit úložiště</h5>
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
                            <div><label>Formát</label><select name="form_factor" id="modalFormFactor"><option>2.5"</option><option>3.5"</option><option>M.2</option></select></div>
                            <div><label>Typ</label><select name="type" id="modalType"><option>SSD</option><option>HDD</option><option>NVMe</option></select></div>
                            <div><label>Kapacita (GB)</label><input type="number" name="capacity" id="modalCapacity"></div>
                            <div><label>Rozhraní</label><select name="interface" id="modalInterface"><option>SATA</option><option>NVMe</option><option>PCIe 3.0</option><option>PCIe 4.0</option></select></div>
                            <div><label>Rychlost čtení (MB/s)</label><input type="number" name="read_speed" id="modalReadSpeed"></div>
                            <div><label>Rychlost zápisu (MB/s)</label><input type="number" name="write_speed" id="modalWriteSpeed"></div>
                            <div><label>IOPS</label><input type="number" name="iops" id="modalIops"></div>
                            <div><label>Cache (MB)</label><input type="number" name="cache_mb" id="modalCacheMb"></div>
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
            const button = e.relatedTarget;
            document.getElementById('modalId').value = button.getAttribute('data-id');
            document.getElementById('modalName').value = button.getAttribute('data-name');
            document.getElementById('modalPrice').value = button.getAttribute('data-price');
            document.getElementById('modalFormFactor').value = button.getAttribute('data-form-factor');
            document.getElementById('modalType').value = button.getAttribute('data-type');
            document.getElementById('modalCapacity').value = button.getAttribute('data-capacity');
            document.getElementById('modalInterface').value = button.getAttribute('data-interface');
            document.getElementById('modalReadSpeed').value = button.getAttribute('data-read-speed');
            document.getElementById('modalWriteSpeed').value = button.getAttribute('data-write-speed');
            document.getElementById('modalIops').value = button.getAttribute('data-iops');
            document.getElementById('modalCacheMb').value = button.getAttribute('data-cache-mb');
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
