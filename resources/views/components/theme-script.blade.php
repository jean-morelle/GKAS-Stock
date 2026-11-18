{{-- Applique le thème avant l'affichage (évite un flash clair). Choix mémorisé, sinon thème du système. --}}
<script>
    (function () {
        var theme = null;
        try { theme = localStorage.getItem('theme'); } catch (e) {}
        var dark = theme ? theme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', dark);
    })();
</script>
