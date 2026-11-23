document.addEventListener("DOMContentLoaded", function () {
    const clockElement = document.getElementById("liveClock");
    if (clockElement) {
        setInterval(() => {
            const now = new Date();
            clockElement.textContent = now.toLocaleTimeString('id-ID');
        }, 1000);
    }

    const filterSelect = document.getElementById("filterAnomali");
    if (filterSelect) {
        filterSelect.addEventListener("change", function () {
            const selected = this.value.toLowerCase();
            const rows = document.querySelectorAll("#tabelPresensi tbody tr");

            rows.forEach(row => {
                const status = row.getAttribute("data-status").toLowerCase();
                if (selected === "all" || status === selected) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    }
});

function konfirmasiHapus(id) {
    if (confirm("Yakin ingin menghapus log presensi ini?")) {
        window.location.href = "hapus_presensi.php?id=" + id;
    }
}