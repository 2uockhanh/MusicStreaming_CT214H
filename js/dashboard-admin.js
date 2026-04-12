document.addEventListener("DOMContentLoaded", function() {
    console.log("Dashboard Admin loaded!");

    const refreshBtn = document.getElementById('refresh-stats');

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            location.reload(); 
        });
    }
});