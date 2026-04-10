<?php
session_start();

$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';
?>
<!-- Tento soubor je pro stránku ve výstavbě, teď se již nikde nevyužívá -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stránka ve výstavbě | DMP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/dmp/assets/css/style.css">
  <style>
    body {
       background: linear-gradient(135deg, #DEE5E5 0%, #E2E8E2 100%, #F4F6F4 100%);
    }
    .uc-hero {
      padding: 80px 16px;
      text-align: center;
     
    }
    .uc-card {
      background: #F4F6F4;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 28px;
      max-width: 700px;
      margin: 0 auto;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    .uc-icon {
      font-size: 48px;
      color: #618B4A;
      margin-bottom: 12px;
    }
  </style>
</head>
<body>

<section class="uc-hero">
  <div class="uc-card">
    <div class="uc-icon">🚧</div>
    <h1 class="display-6 fw-bold mb-3">Stránka ve výstavbě</h1>
    <p class="lead mb-4 text-muted">
      Na této sekci právě pracujeme.
    </p>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
      <a href="/dmp/public/index.php" class="btn btn-primary">Zpět na hlavní stránku</a>
      <?php if ($loggedIn): ?>
        <a href="/dmp/public/dashboard.php" class="btn btn-outline-secondary">Přejít na dashboard</a>
      <?php else: ?>
        <a href="/dmp/public/login.php" class="btn btn-outline-secondary">Přihlásit se</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>