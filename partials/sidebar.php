<?php
// espera $__ACTIVE (string) do arquivo que incluiu
$__ACTIVE = $__ACTIVE ?? '';

function active_item(string $k, string $cur){ return $k === $cur ? ' active' : ''; }
function group_open(array $keys, string $cur){ return in_array($cur, $keys, true) ? ' show' : ''; }
function parent_active(array $keys, string $cur){ return in_array($cur, $keys, true) ? ' active' : ''; }

// grupos ativos
$DASH_KEYS = ['dashboard-atividades','dashboard-obras'];
$REL_KEYS  = ['rel-atividades','rel-obras','rel-export'];
$FORM_KEYS = ['form-aai','form-obras'];
?>
<aside id="sidebar" class="sidebar">
  <div class="logo fw-bold mb-2">COHIDRO <span class="text-info">AI</span></div>
  <!-- separador entre logo e menu -->
  <hr class="border-secondary-subtle">

  <nav class="nav nav-pills flex-column nav-aside">

    <!-- BI Dashboard -->
    <a class="nav-link d-flex justify-content-between align-items-center<?= parent_active($DASH_KEYS,$__ACTIVE) ?>"
       data-bs-toggle="collapse" href="#menuDash" role="button"
       aria-expanded="<?= in_array($__ACTIVE,$DASH_KEYS,true) ? 'true' : 'false' ?>" aria-controls="menuDash">
      <span><i class="bi bi-bar-chart-line"></i><span class="text">BI Dashboard</span></span>
      <i class="bi bi-chevron-down small"></i>
    </a>
    <div id="menuDash" class="collapse<?= group_open($DASH_KEYS,$__ACTIVE) ?>">
      <nav class="nav flex-column ms-3 mt-1">
        <a class="nav-link<?= active_item('dashboard-atividades',$__ACTIVE) ?>" href="dashboard_atividades.php">
          <span class="text">Atividades</span>
        </a>
        <a class="nav-link<?= active_item('dashboard-obras',$__ACTIVE) ?>" href="dashboard_obras.php">
          <span class="text">Obras</span>
        </a>
      </nav>
    </div>

    <!-- Relatórios -->
    <a class="nav-link d-flex justify-content-between align-items-center<?= parent_active($REL_KEYS,$__ACTIVE) ?>"
       data-bs-toggle="collapse" href="#menuRel" role="button"
       aria-expanded="<?= in_array($__ACTIVE,$REL_KEYS,true) ? 'true' : 'false' ?>" aria-controls="menuRel">
      <span><i class="bi bi-file-earmark-bar-graph"></i><span class="text">Relatórios</span></span>
      <i class="bi bi-chevron-down small"></i>
    </a>
    <div id="menuRel" class="collapse<?= group_open($REL_KEYS,$__ACTIVE) ?>">
      <nav class="nav flex-column ms-3 mt-1">
        <a class="nav-link<?= active_item('rel-atividades',$__ACTIVE) ?>" href="rel_atividades.php">
          <span class="text">Relatório de atividades</span>
        </a>
        <a class="nav-link<?= active_item('rel-obras',$__ACTIVE) ?>" href="rel_obras.php">
          <span class="text">Relatório de obras</span>
        </a>
        <a class="nav-link<?= active_item('rel-export',$__ACTIVE) ?>" href="rel_exportacao.php">
          <span class="text">Exportação massiva</span>
        </a>
      </nav>
    </div>

    <!-- Formulários -->
    <a class="nav-link d-flex justify-content-between align-items-center<?= parent_active($FORM_KEYS,$__ACTIVE) ?>"
       data-bs-toggle="collapse" href="#menuForms" role="button"
       aria-expanded="<?= in_array($__ACTIVE,$FORM_KEYS,true) ? 'true' : 'false' ?>" aria-controls="menuForms">
      <span><i class="bi bi-ui-checks-grid"></i><span class="text">Formulários</span></span>
      <i class="bi bi-chevron-down small"></i>
    </a>
    <div id="menuForms" class="collapse<?= group_open($FORM_KEYS,$__ACTIVE) ?>">
      <nav class="nav flex-column ms-3 mt-1">
        <a class="nav-link<?= active_item('form-aai',$__ACTIVE) ?>" href="formulario.php">
          <span class="text">Formulário AAI</span>
        </a>
        <a class="nav-link<?= active_item('form-obras',$__ACTIVE) ?>" href="form_obras.php">
          <span class="text">Formulário Obras</span>
        </a>
      </nav>
    </div>

  </nav>
</aside>
