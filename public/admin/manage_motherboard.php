<?php
/**
 * Správa základních desek
 * 
 * CRUD operace pro základní desky v databázi.
 * Podporuje přidání, editaci a smazání záznamů. Pouze pro adminy.
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) { header("Location: /dmp/public/login.php"); exit; }

$items = [];
$stmt = $pdo->prepare('SELECT * FROM motherboard ORDER BY id DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('INSERT INTO motherboard (name, price, socket, form_factor, sata_slots, m2_slots) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$_POST['name'], $_POST['price'] ?? 0, $_POST['socket'], $_POST['form_factor'] ?? 'ATX', $_POST['sata_slots'] ?? 0, $_POST['m2_slots'] ?? 0]);
            header("Location: /dmp/public/admin/manage_motherboard.php");
            exit;
        } catch (Exception $e) { }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (csrf_validate()) {
        $pdo->prepare('DELETE FROM motherboard WHERE id = ?')->execute([$_POST['id']]);
        header("Location: /dmp/public/admin/manage_motherboard.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('UPDATE motherboard SET name=?,price=?,socket=?,form_factor=?,sata_slots=?,m2_slots=? WHERE id=?');
            $stmt->execute([$_POST['name'], $_POST['price'], $_POST['socket'], $_POST['form_factor'], $_POST['sata_slots'] ?? 0, $_POST['m2_slots'] ?? 0, $_POST['id']]);
            header("Location: /dmp/public/admin/manage_motherboard.php");
            exit;
        } catch (Exception $e) { }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Základní desky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html { background: linear-gradient(135deg, #f9fafb 0%, #e2e8e2 100%); }
        body { min-height: 100vh; }
        .container { max-width: 1400px; margin: 2rem auto; }
        .section { background: white; padding: 2rem; margin-bottom: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .btn-save { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-edit { background: #007bff; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; margin-right: 6px; display: inline-block; text-decoration: none; }
        .btn-delete { background: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-back { background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; text-decoration: none; display: inline-block; margin-bottom: 1.5rem; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/dmp/public/admin/index.php" class="btn-back">← Zpět</a>
        <div class="section">
            <h2>🖥️ Správa základních desek</h2>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div><label>Název *</label><input type="text" name="name" required></div>
                    <div><label>Cena</label><input type="number" name="price" step="0.01"></div>
                    <div><label>Socket *</label><input type="text" name="socket" placeholder="LGA1700" required></div>
                    <div><label>Formát</label><select name="form_factor"><option>ATX</option><option>Micro-ATX</option><option>Mini-ITX</option></select></div>
                    <div><label>SATA sloty</label><input type="number" name="sata_slots" min="0" value="0"></div>
                    <div><label>M.2 sloty</label><input type="number" name="m2_slots" min="0" value="0"></div>
                </div>
                <button type="submit" class="btn-save">+ Přidat</button>
            </form>
        </div>
        <div class="section">
            <div style="margin-bottom:1rem;"><input type="text" id="filterInput" placeholder="Filtrovat..." style="max-width:300px;margin-bottom:0"></div>
            <table>
                <thead><tr><th>ID</th><th>Název</th><th>Socket</th><th>SATA</th><th>M.2</th><th>Akce</th></tr></thead>
                <tbody id="tableBody">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['socket']) ?></td>
                        <td><?= $item['sata_slots'] ?></td>
                        <td><?= $item['m2_slots'] ?></td>
                        <td>
                            <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= $item['price'] ?>" data-socket="<?= htmlspecialchars($item['socket']) ?>" data-form-factor="<?= htmlspecialchars($item['form_factor']) ?>" data-sata="<?= $item['sata_slots'] ?? '' ?>" data-m2="<?= $item['m2_slots'] ?? '' ?>">Upravit</button>
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
                    <h5 class="modal-title" id="editModalLabel">Upravit základní desku</h5>
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
                            <div><label>Formát</label><select name="form_factor" id="modalFormFactor"><option>ATX</option><option>Micro-ATX</option><option>Mini-ITX</option></select></div>
                            <div><label>SATA sloty</label><input type="number" name="sata_slots" id="modalSata" min="0"></div>
                            <div><label>M.2 sloty</label><input type="number" name="m2_slots" id="modalM2" min="0"></div>
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
            document.getElementById('modalSocket').value = button.getAttribute('data-socket');
            document.getElementById('modalFormFactor').value = button.getAttribute('data-form-factor');
            document.getElementById('modalSata').value = button.getAttribute('data-sata') || 0;
            document.getElementById('modalM2').value = button.getAttribute('data-m2') || 0;
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
