<?php
/**
 * Správa PC skříní
 * 
 * CRUD operace pro skříně v databázi.
 * Podporuje přidání, editaci a smazání záznamů. Pouze pro adminy.
 */
session_start();
require_once __DIR__ . '/../../db/connection.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['roleId'] ?? 1) !== 2) { header("Location: /dmp/public/login.php"); exit; }

$items = [];
$stmt = $pdo->prepare('SELECT * FROM `case` ORDER BY id DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('INSERT INTO `case` (name, price, max_gpu, mboard_type, psu_type, case_type, max_cooler, expansion_slots, front_rad, top_rad, max_psu, rear_rad) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$_POST['name'], $_POST['price'] ?? 0, $_POST['max_gpu'] ?? 300, $_POST['mboard_type'] ?? 'ATX', $_POST['psu_type'] ?? 'ATX', $_POST['case_type'] ?? 'Mid', $_POST['max_cooler'] ?? 160, 7, 280, 120, 200, 120]);
            header("Location: /dmp/public/admin/manage_case.php");
            exit;
        } catch (Exception $e) { }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (csrf_validate()) {
        $pdo->prepare('DELETE FROM `case` WHERE id = ?')->execute([$_POST['id']]);
        header("Location: /dmp/public/admin/manage_case.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (csrf_validate()) {
        try {
            $stmt = $pdo->prepare('UPDATE `case` SET name=?,price=?,max_gpu=?,mboard_type=?,psu_type=?,case_type=? WHERE id=?');
            $stmt->execute([$_POST['name'], $_POST['price'], $_POST['max_gpu'], $_POST['mboard_type'], $_POST['psu_type'], $_POST['case_type'], $_POST['id']]);
            header("Location: /dmp/public/admin/manage_case.php");
            exit;
        } catch (Exception $e) { }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Skříně</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html { background: linear-gradient(135deg, #f9fafb 0%, #e2e8e2 100%); }
        body { min-height: 100vh; }
        .container { max-width: 1400px; margin: 2rem auto; }
        .section { background: white; padding: 2rem; margin-bottom: 2rem; border-radius: 8px; }
        .btn-save { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
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
            <h2>📦 Správa skříní</h2>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div><label>Název *</label><input type="text" name="name" required></div>
                    <div><label>Cena</label><input type="number" name="price" step="0.01"></div>
                    <div><label>Max délka GPU (mm)</label><input type="number" name="max_gpu" value="300"></div>
                    <div><label>Typ základní desky</label><select name="mboard_type"><option>ATX</option><option>Micro-ATX</option><option>Mini-ITX</option></select></div>
                    <div><label>Typ zdroje</label><select name="psu_type"><option>ATX</option><option>SFX</option></select></div>
                    <div><label>Typ skříně</label><select name="case_type"><option>SFF</option><option>Mini</option><option>Mid</option><option>Full</option></select></div>
                </div>
                <button type="submit" class="btn-save">+ Přidat</button>
            </form>
        </div>
        <div class="section">
            <div style="margin-bottom:1rem;"><input type="text" id="filterInput" placeholder="Filtrovat..." style="max-width:300px;margin-bottom:0"></div>
            <table>
                <thead><tr><th>ID</th><th>Název</th><th>Max GPU</th><th>Typ</th><th>Cena</th><th>Akce</th></tr></thead>
                <tbody id="tableBody">
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['max_gpu'] ?>mm</td>
                        <td><?= $item['case_type'] ?></td>
                        <td><?= $item['price'] ?></td>
                        <td>
                            <button type="button" class="btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= $item['price'] ?>" data-max-gpu="<?= $item['max_gpu'] ?>" data-mboard-type="<?= htmlspecialchars($item['mboard_type']) ?>" data-psu-type="<?= htmlspecialchars($item['psu_type']) ?>" data-case-type="<?= htmlspecialchars($item['case_type']) ?>">Upravit</button>
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
                    <h5 class="modal-title" id="editModalLabel">Upravit skříň</h5>
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
                            <div><label>Max délka GPU (mm)</label><input type="number" name="max_gpu" id="modalMaxGpu"></div>
                            <div><label>Typ základní desky</label><select name="mboard_type" id="modalMboardType"><option>ATX</option><option>Micro-ATX</option><option>Mini-ITX</option></select></div>
                            <div><label>Typ zdroje</label><select name="psu_type" id="modalPsuType"><option>ATX</option><option>SFX</option></select></div>
                            <div><label>Typ skříně</label><select name="case_type" id="modalCaseType"><option>SFF</option><option>Mini</option><option>Mid</option><option>Full</option></select></div>
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
            document.getElementById('modalMaxGpu').value = button.getAttribute('data-max-gpu');
            document.getElementById('modalMboardType').value = button.getAttribute('data-mboard-type');
            document.getElementById('modalPsuType').value = button.getAttribute('data-psu-type');
            document.getElementById('modalCaseType').value = button.getAttribute('data-case-type');
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
