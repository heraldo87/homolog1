<?php
// Usa variáveis definidas no index (com fallback seguro)
$brand   = $brand   ?? 'COHIDRO AI';
$usuario = $usuario ?? 'demo';
$nivel   = $nivel   ?? 2;
?>
<nav class="navbar navbar-dark topbar sticky-top bg-body-tertiary">
  <div class="container-fluid">
    <!-- Botão que colapsa/mostra a sidebar (id mantido p/ JS existente) -->
    <button id="btnToggle" class="btn btn-outline-secondary border-0 text-light me-2">
      <i class="bi bi-list" style="font-size:1.25rem"></i>
    </button>

    <span class="navbar-brand mb-0 h1">
      <?= htmlspecialchars($brand) ?> · Dashboard
    </span>

    <div class="d-flex align-items-center gap-2">
      <span class="text-secondary small d-none d-md-inline">
        Usuário: <?= htmlspecialchars($usuario) ?> · Nível <?= htmlspecialchars((string)$nivel) ?>
      </span>
      <a href="logout.php" class="btn btn-sm btn-outline-secondary">Sair</a>
    </div>
  </div>
</nav>
