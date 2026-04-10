<?php
/**
 * Navigační lišta aplikace
 * 
 * Zobrazuje hlavní menu s odkazy na stránky a přihlašovací stav uživatele.
 * Rozlišuje role: běžný uživatel, admin (roleId=2), moderátor (roleId=3).
 * Vyžaduje aktivní session (session_start() musí být voláno před includováním).
 */
$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['username'] : '';
?>

<nav class="navbar navbar-expand-lg navbar-light navbar-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/dmp/public/index.php">PC Konfigurátor</a>

    <!-- Přepínač pro mobil -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAuth" aria-controls="navbarAuth" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Odkazy přihlášení (pravá strana) -->
    <div class="collapse navbar-collapse" id="navbarAuth">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <?php if ($loggedIn): ?>
          <li class="nav-item">
            <span class="nav-link">Ahoj, <strong><a href="/dmp/public/dashboard.php"><?= htmlspecialchars($username) ?></a></strong>!</span>
          </li>
          <?php if ($_SESSION['roleId'] === 2): ?>
          <li class="nav-item">
            <a class="btn btn-outline-info" href="/dmp/public/admin/index.php">Admin panel</a>
          </li>
          <?php elseif ($_SESSION['roleId'] === 3): ?>
          <li class="nav-item">
            <a class="btn btn-outline-warning" href="/dmp/public/moderator/index.php">Panel moderátora</a>
          </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="btn btn-outline-danger" href="/dmp/public/logout.php">Odhlásit se</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/dmp/public/login.php">Přihlásit se</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-primary" href="/dmp/public/register.php">Registrovat se</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<nav class="navbar navbar-expand-lg navbar-bottom">
  <div class="container">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMain">
      <!-- Navigační odkazy (vlevo) -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'configurator.php' ? 'active' : '' ?>" href="/dmp/public/configurator.php"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16">
  <path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3q0-.405-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z"/>
</svg> Konfigurátor</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'builds.php' ? 'active' : '' ?>" href="/dmp/public/builds.php"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pc" viewBox="0 0 16 16">
  <path d="M5 0a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm.5 14a.5.5 0 1 1 0 1 .5.5 0 0 1 0-1m2 0a.5.5 0 1 1 0 1 .5.5 0 0 1 0-1M5 1.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5M5.5 3h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1"/>
</svg> Sestavy</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'parts.php' ? 'active' : '' ?>" href="/dmp/public/parts.php"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pci-card" viewBox="0 0 16 16">
  <path d="M0 1.5A.5.5 0 0 1 .5 1h1a.5.5 0 0 1 .5.5V4h13.5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5H2v2.5a.5.5 0 0 1-1 0V2H.5a.5.5 0 0 1-.5-.5"/>
  <path d="M3 12.5h3.5v1a.5.5 0 0 1-.5.5H3.5a.5.5 0 0 1-.5-.5zm4 0h4v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5z"/>
</svg> Komponenty</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'forum.php' ? 'active' : '' ?>" href="/dmp/public/forum.php"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
</svg> Fórum</a>
        </li>
      </ul>

      <!-- Vyhledávací lišta (vpravo) -->
      <div class="search-bar ms-auto">
        <form class="d-flex" method="GET" action="/dmp/public/parts.php">
          <input 
            class="form-control form-control-sm" 
            type="search" 
            name="q" 
            placeholder="Hledat komponenty..." 
            aria-label="Search"
          >
          <button class="btn btn-outline-secondary btn-sm ms-2" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
</svg></button>
        </form>
      </div>
    </div>
  </div>
</nav>