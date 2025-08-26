(function () {
  const btn = document.getElementById('btnToggle');
  const sidebar = document.getElementById('sidebar');

  if (btn && sidebar) {
    btn.addEventListener('click', () => {
      if (window.innerWidth >= 992) {
        document.body.classList.toggle('sb-collapsed');
      } else {
        sidebar.classList.toggle('open');
      }
      setTimeout(() => {
        if (Array.isArray(window.__charts)) window.__charts.forEach(c => c && c.resize && c.resize());
      }, 260);
    });

    document.addEventListener('click', (e) => {
      if (window.innerWidth < 992 && sidebar.classList.contains('open')) {
        const inside = sidebar.contains(e.target) || btn.contains(e.target);
        if (!inside) sidebar.classList.remove('open');
      }
    });
  }

  const y = document.getElementById('y');
  if (y) y.textContent = new Date().getFullYear();
})();
