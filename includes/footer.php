    </main> <!-- Close <main> -->
</div> <!-- Close App Container -->

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Global & Sidebar JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            sidebar.classList.toggle("show");
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener("click", function (e) {
            if (window.innerWidth < 768 && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                sidebar.classList.remove("show");
            }
        });
    }
});
</script>

<?php
// Page-specific JS file loader
if (isset($page_js)) {
    $path_prefix = (isset($in_admin_folder) && $in_admin_folder) ? '../' : '';
    echo '<script src="' . $path_prefix . 'assets/js/' . $page_js . '"></script>';
}
?>
</body>
</html>
