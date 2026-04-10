<?php
/**
 * Správa operační paměti (RAM)
 * 
 * CRUD operace pro RAM v databázi.
 * Podporuje přidání, editaci a smazání záznamů. Pouze pro adminy.
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) { header("Location: /dmp/public/login.php"); exit; }

$items = [];
$stmt = $pdo->prepare('SELECT * FROM ram ORDER BY id DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('INSERT INTO ram (name, price, speed, modules, stick_gb, capacity, type, cl, trcd, trp, tras, color) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $_POST['name'] ?? '', $_POST['price'] ?? 0, $_POST['speed'] ?? null,
                $_POST['modules'] ?? null, $_POST['stick_gb'] ?? null, $_POST['capacity'] ?? null,
                $_POST['type'] ?? '', $_POST['cl'] ?? null, $_POST['trcd'] ?? 0,
                $_POST['trp'] ?? 0, $_POST['tras'] ?? 0, $_POST['color'] ?? ''
            ]);
            header("Location: /dmp/public/admin/manage_ram.php");
            exit;
        } catch (Exception $e) { }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (csrf_validate()) {
        $pdo->prepare('DELETE FROM ram WHERE id = ?')->execute([$_POST['id']]);
        header("Location: /dmp/public/admin/manage_ram.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('UPDATE ram SET name=?,price=?,speed=?,modules=?,stick_gb=?,capacity=?,type=?,cl=?,trcd=?,trp=?,tras=?,color=? WHERE id=?');
            $stmt->execute([
                $_POST['name'], $_POST['price'], $_POST['speed'] ?? null,
                $_POST['modules'] ?? null, $_POST['stick_gb'] ?? null, $_POST['capacity'] ?? null,
                $_POST['type'], $_POST['cl'] ?? null, $_POST['trcd'] ?? 0,
                $_POST['trp'] ?? 0, $_POST['tras'] ?? 0, $_POST['color'], $_POST['id']
            ]);
            header("Location: /dmp/public/admin/manage_ram.php");
            exit;
        } catch (Exception $e) { }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - RAM</title>
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
            <h2>🧠 Správa RAM</h2>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div><label>Název *</label><input type="text" name="name" required></div>
                    <div><label>Cena</label><input type="number" name="price" step="0.01"></div>
                    <div><label>Rychlost (MHz)</label><input type="number" name="speed"></div>
                    <div><label>Moduly</label><input type="number" name="modules" min="1"></div>
                    <div><label>Modul (GB)</label><input type="number" name="stick_gb"></div>
                    <div><label>Kapacita (GB)</label><input type="number" name="capacity"></div>
                    <div><label>Typ</label><select name="type"><option>DDR4</option><option>DDR5</option></select></div>
                    <div><label>CAS latence</label><input type="number" name="cl"></div>
                    <div><label>Barva</label><input type="text" name="color"></div>
                </div>
                <button type="submit" class="btn-save">+ Přidat</button>
            </form>
        </div>
        <div class="section">
            <div style="margin-bottom:1rem;"><input type="text" id="filterInput" placeholder="Filtrovat..." style="max-width:300px;margin-bottom:0"></div>
            <table>
                <thead><tr><th>ID</th><th>Název</th><th>Kapacita</th><th>Rychlost</th><th>Cena</th><th>Akce</th></tr></thead>
                <tbody id="tableBody">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['capacity'] ?>GB</td>
                        <td><?= $item['speed'] ?>MHz</td>
                        <td><?= $item['price'] ?> Kč</td>
                        <td>
                            <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= $item['price'] ?>" data-speed="<?= $item['speed'] ?? '' ?>" data-modules="<?= $item['modules'] ?? '' ?>" data-stick="<?= $item['stick_gb'] ?? '' ?>" data-capacity="<?= $item['capacity'] ?? '' ?>" data-type="<?= $item['type'] ?? '' ?>" data-cl="<?= $item['cl'] ?? '' ?>" data-trcd="<?= $item['trcd'] ?? '' ?>" data-trp="<?= $item['trp'] ?? '' ?>" data-tras="<?= $item['tras'] ?? '' ?>" data-color="<?= htmlspecialchars($item['color'] ?? '') ?>">Upravit</button>
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
                    <h5 class="modal-title" id="editModalLabel">Upravit RAM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="modalId">
                    <div class="modal-body">
                        <div class="form-row">
                            <div><label>Název *</label><input type="text" name="name" id="modalName" required></div>
                            <div><label>Cena</label><input type="number" name="price" id="modalPrice" step="0.01"></div>
                            <div><label>Rychlost (MHz)</label><input type="number" name="speed" id="modalSpeed"></div>
                            <div><label>Moduly</label><input type="number" name="modules" id="modalModules" min="1"></div>
                            <div><label>Modul (GB)</label><input type="number" name="stick_gb" id="modalStick"></div>
                            <div><label>Kapacita (GB)</label><input type="number" name="capacity" id="modalCapacity"></div>
                            <div><label>Typ</label><select name="type" id="modalType"><option>DDR4</option><option>DDR5</option></select></div>
                            <div><label>CAS latence</label><input type="number" name="cl" id="modalCl"></div>
                            <div><label>tRCD</label><input type="number" name="trcd" id="modalTrcd"></div>
                            <div><label>tRP</label><input type="number" name="trp" id="modalTrp"></div>
                            <div><label>tRAS</label><input type="number" name="tras" id="modalTras"></div>
                            <div><label>Barva</label><input type="text" name="color" id="modalColor"></div>
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
            document.getElementById('modalSpeed').value = b.getAttribute('data-speed');
            document.getElementById('modalModules').value = b.getAttribute('data-modules');
            document.getElementById('modalStick').value = b.getAttribute('data-stick');
            document.getElementById('modalCapacity').value = b.getAttribute('data-capacity');
            document.getElementById('modalType').value = b.getAttribute('data-type');
            document.getElementById('modalCl').value = b.getAttribute('data-cl');
            document.getElementById('modalTrcd').value = b.getAttribute('data-trcd');
            document.getElementById('modalTrp').value = b.getAttribute('data-trp');
            document.getElementById('modalTras').value = b.getAttribute('data-tras');
            document.getElementById('modalColor').value = b.getAttribute('data-color');
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
