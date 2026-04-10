<?php
/**
 * Správa obrázku sestavy
 * 
 * Umožňuje nahrát nebo změnit náhledový obrázek sestavy.
 * Vyžaduje přihlášení a vlastnictví sestavy.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/csrf.php';

// Kontrola přihlášení uživatele
if (!isset($_SESSION['user_id'])) {
    header('Location: /dmp/public/login.php');
    exit;
}

$buildId = (int)($_GET['id'] ?? 0);

if ($buildId === 0) {
    header('Location: /dmp/public/builds.php');
    exit;
}

// Získá sestavu z DB a ověří vlastnictví
$buildStmt = $pdo->prepare('SELECT id, userId, name, image_path FROM builds WHERE id = ?');
$buildStmt->execute([$buildId]);
$build = $buildStmt->fetch(PDO::FETCH_ASSOC);

if (!$build || $build['userId'] !== $_SESSION['user_id']) {
    header('Location: /dmp/public/builds.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upravit obrázek - <?= htmlspecialchars($build['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        .image-preview {
            max-width: 100%;
            max-height: 400px;
            border-radius: 12px;
            margin: 1rem 0;
            object-fit: cover;
        }
        .placeholder-image {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            text-align: center;
        }
        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9ff;
        }
        .upload-area:hover {
            border-color: #764ba2;
            background: #f0f2ff;
        }
        .upload-area.dragover {
            border-color: #28a745;
            background: #f0f8f4;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
        }
        .btn-delete:hover {
            background: #c82333;
            color: white;
        }
        .container-main {
            max-width: 700px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container-main">
        <div class="card">
            <div class="card-header bg-light border-0">
                <h2 class="mb-0">🖼️ Upravit obrázek</h2>
                <small class="text-muted">Sestava: <strong><?= htmlspecialchars($build['name']) ?></strong></small>
            </div>
            
            <div class="card-body">
                <!-- Náhled obrázku -->
                <div class="mb-4">
                    <label class="form-label">Aktuální obrázek:</label>
                    <?php if ($build['image_path'] && file_exists(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/dmp/' . $build['image_path']))): ?>
                        <img src="/dmp/<?= htmlspecialchars($build['image_path']) ?>" alt="Build image" class="image-preview">
                        <br>
                        <button type="button" class="btn btn-delete btn-sm" onclick="deleteImage()">🗑️ Smazat obrázek</button>
                    <?php else: ?>
                        <div class="placeholder-image">
                            📸<br>Žádný obrázek
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Oblast nahrávání -->
                <div class="mb-4">
                    <label class="form-label">Nahrát nový obrázek:</label>
                    <div class="upload-area" id="uploadArea">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">⬆️</div>
                        <p class="mb-1"><strong>Klikněte či přetáhněte soubor sem</strong></p>
                        <small class="text-muted">Podporovány: JPG, PNG, GIF, WebP (max 5MB)</small>
                    </div>
                    <input type="file" id="fileInput" accept="image/*" style="display: none;">
                </div>

                <!-- Stav nahrávání -->
                <div id="uploadStatus" style="display: none; margin: 1rem 0;">
                    <div class="progress mb-2" style="height: 25px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                             style="width: 0%;">0%</div>
                    </div>
                    <small id="statusText" class="text-muted">Nahrávání...</small>
                </div>

                <!-- Chybová/Úspěšná zpráva -->
                <div id="alertBox" style="display: none; margin: 1rem 0;"></div>

                <!-- Back Button -->
                <div class="mt-4 pt-3 border-top">
                    <a href="/dmp/public/build.php?id=<?= $buildId ?>" class="btn btn-outline-secondary">
                        ← Zpět na sestavu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadStatus = document.getElementById('uploadStatus');
        const progressBar = document.getElementById('progressBar');
        const statusText = document.getElementById('statusText');
        const alertBox = document.getElementById('alertBox');

        // Kliknutí pro nahrání
        uploadArea.addEventListener('click', () => fileInput.click());

        // Přetahování souboru
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                handleFile(e.dataTransfer.files[0]);
            }
        });

        // Změna vstupu souboru
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                handleFile(e.target.files[0]);
            }
        });

        function handleFile(file) {
            // Ověření typu souboru
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showAlert('Neplatný typ souboru. Podporovány: JPG, PNG, GIF, WebP', 'danger');
                return;
            }

            // Ověření velikosti souboru (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showAlert('Soubor je příliš velký (max 5MB)', 'danger');
                return;
            }

            uploadFile(file);
        }

        function uploadFile(file) {
            const formData = new FormData();
            formData.append('buildId', <?= $buildId ?>);
            formData.append('action', 'upload');
            formData.append('image', file);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');

            // Zobrazit průběh nahrávání
            uploadStatus.style.display = 'block';
            alertBox.style.display = 'none';

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = Math.round(percentComplete) + '%';
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showAlert('Obrázek byl úspěšně nahrán! Stránka se znovunačte za 2 sekundy...', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showAlert(response.message || 'Chyba při nahrávání', 'danger');
                        uploadStatus.style.display = 'none';
                    }
                } else {
                    const response = JSON.parse(xhr.responseText);
                    showAlert(response.message || 'Chyba serveru', 'danger');
                    uploadStatus.style.display = 'none';
                }
            });

            xhr.addEventListener('error', () => {
                showAlert('Chyba připojení', 'danger');
                uploadStatus.style.display = 'none';
            });

            xhr.open('POST', '/dmp/api/builds/upload-image.php');
            xhr.send(formData);
        }

        function deleteImage() {
            if (!confirm('Opravdu chcete smazat obrázek?')) return;

            const formData = new FormData();
            formData.append('buildId', <?= $buildId ?>);
            formData.append('action', 'delete');
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');

            fetch('/dmp/api/builds/upload-image.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('Obrázek byl smazán! Stránka se znovunačte za 2 sekundy...', 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert(data.message || 'Chyba', 'danger');
                }
            })
            .catch(() => showAlert('Chyba připojení', 'danger'));
        }

        function showAlert(message, type) {
            alertBox.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
            </div>`;
            alertBox.style.display = 'block';
            uploadStatus.style.display = 'none';
        }
    </script>

    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
