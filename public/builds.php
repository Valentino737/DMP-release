<?php
/**
 * Přehled veřejných sestav
 * 
 * Zobrazuje všechny veřejné sestavy uživatelů s náhledem komponent.
 * Podporuje stránkování a odkaz na detail sestavy.
 */
session_start();
require_once __DIR__ . '/../db/connection.php';

$currentPage = 'builds.php';

// Funkce pro získání komponent sestavy
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
                $components[] = ['type' => 'Matka', 'name' => $result['name']];
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

// Získá všechny veřejné sestavy z databáze
$stmt = $pdo->prepare("
    SELECT b.id, b.name, b.description, b.userId, u.username, u.roleId, b.createdAt, b.image_path
    FROM builds b
    LEFT JOIN users u ON b.userId = u.id
    WHERE b.isPublic = 1
    ORDER BY b.createdAt DESC
");
$stmt->execute();
$builds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Získá komponenty pro každou sestavu
foreach ($builds as &$build) {
    $build['components'] = getBuildComponents($pdo, $build['id']);
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veřejné Sestavy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dmp/assets/css/style.css">
    <style>
        body {
            background: #f5f5f5;
        }

        .builds-container {
            min-height: calc(100vh - 400px);
        }

        .page-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: #0a0908;
            font-weight: 700;
            font-size: 2.5rem;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .build-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            transition: box-shadow 0.25s ease, transform 0.25s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .build-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .build-card-thumb {
            position: relative;
            height: 160px;
            overflow: hidden;
        }

        .build-card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .build-card-thumb-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #dce8d4 0%, #c8dabb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .build-card-thumb-placeholder svg {
            width: 48px;
            height: 48px;
            color: #618B4A;
            opacity: 0.35;
        }

        .build-card-thumb .author-chip {
            position: absolute;
            bottom: 8px;
            left: 8px;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(4px);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .author-chip .role-label {
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .role-label.admin { background: rgba(250,204,21,0.85); color: #422006; }
        .role-label.mod   { background: rgba(96,165,250,0.85); color: #1e3a5f; }
        .role-label.user  { background: rgba(161,161,170,0.55); color: #fff; }

        .build-card-body {
            padding: 1rem 1.15rem 1.15rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .build-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 0.35rem;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .build-description {
            color: #666;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 0.85rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .build-description.empty {
            color: #aaa;
            font-style: italic;
        }

        .build-components {
            list-style: none;
            margin: 0 0 0.9rem;
            padding: 0;
        }

        .build-components li {
            font-size: 0.8rem;
            color: #555;
            padding: 0.3rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid #f3f3f3;
        }

        .build-components li:last-child {
            border-bottom: none;
        }

        .comp-type {
            display: inline-block;
            width: 52px;
            flex-shrink: 0;
            font-size: 0.68rem;
            font-weight: 700;
            color: #618B4A;
            text-transform: uppercase;
        }

        .comp-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #333;
        }

        .build-card-footer {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 0.75rem;
            border-top: 1px solid #f0f0f0;
        }

        .build-date {
            font-size: 0.78rem;
            color: #999;
            font-weight: 500;
        }

        .btn-view-details {
            font-size: 0.78rem;
            font-weight: 600;
            color: #618B4A;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: gap 0.15s ease;
        }

        .btn-view-details:hover {
            color: #4a6a38;
            gap: 6px;
        }
    
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .empty-state-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #618B4A 0%, #4a6a38 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }

        .empty-state-icon svg {
            width: 50px;
            height: 50px;
            color: white;
        }

        .empty-state h3 {
            color: #0a0908;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .empty-state-text {
            color: #666;
            font-size: 1.05rem;
            margin-bottom: 2rem;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
            height: 100%;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>🖥️ Veřejné Sestavy</h1>
            <p>Procházejte sestavy ostatních uživatelů a nechte se inspirovat</p>
        </div>
    </div>

    <div class="container builds-container py-5">
        <?php if (count($builds) > 0): ?>
            <div class="row g-4">
                <?php foreach ($builds as $build): ?>
                    <div class="col-sm-6 col-lg-4">
                        <a href="/dmp/public/build.php?id=<?= htmlspecialchars($build['id']) ?>" class="card-link">
                            <div class="build-card">
                                <!-- Thumbnail -->
                                <div class="build-card-thumb">
                                    <?php if ($build['image_path'] && file_exists(str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . '/dmp/' . $build['image_path']))): ?>
                                        <img src="/dmp/<?= htmlspecialchars($build['image_path']) ?>" alt="<?= htmlspecialchars($build['name']) ?>">
                                    <?php else: ?>
                                        <div class="build-card-thumb-placeholder">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25Z" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <span class="author-chip">
                                        <?= htmlspecialchars($build['username'] ?? 'Anonym') ?>
                                        <?php if (($build['roleId'] ?? 1) == 2): ?>
                                            <span class="role-label admin">Admin</span>
                                        <?php elseif (($build['roleId'] ?? 1) == 3): ?>
                                            <span class="role-label mod">Mod</span>
                                        <?php else: ?>
                                            <span class="role-label user">User</span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="build-card-body">
                                    <h5 class="build-title"><?= htmlspecialchars($build['name']) ?></h5>

                                    <?php if ($build['description']): ?>
                                        <p class="build-description"><?= htmlspecialchars($build['description']) ?></p>
                                    <?php else: ?>
                                        <p class="build-description empty">Bez popisu</p>
                                    <?php endif; ?>

                                    <?php if (!empty($build['components'])): ?>
                                        <ul class="build-components">
                                            <?php foreach ($build['components'] as $component): ?>
                                                <li>
                                                    <span class="comp-type"><?= htmlspecialchars($component['type']) ?></span>
                                                    <span class="comp-name" title="<?= htmlspecialchars($component['name']) ?>"><?= htmlspecialchars($component['name']) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <div class="build-card-footer">
                                        <span class="build-date"><?= date('d. m. Y', strtotime($build['createdAt'])) ?></span>
                                        <span class="btn-view-details">Zobrazit <span>→</span></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.094c0-.577.316-.1.92-1.01.606-.905.583-1.538.583-1.849 0-1.596-1.211-2.798-2.665-2.798-1.393 0-2.645.809-2.645 2.114 0 .155.012.31.039.465z"/>
                        <circle cx="8" cy="12.5" r=".5"/>
                    </svg>
                </div>
                <h3>Zatím žádné veřejné sestavy</h3>
                <p class="empty-state-text">Začněte tím, že vytvoříte novou sestavu a zveřejníte ji</p>
                <a href="/dmp/public/configurator.php" class="btn btn-primary btn-lg" style="background: #618B4A; border: none; font-weight: 600;">
                    ➕ Vytvořit první sestavu
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include_once __DIR__ . '/../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>