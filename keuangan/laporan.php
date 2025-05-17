<?php
require_once 'config/database.php';
include 'includes/header.php';

// Query untuk data chart pemasukan vs pengeluaran per bulan
$sql = "SELECT 
        DATE_FORMAT(tanggal, '%Y-%m') as bulan,
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as pengeluaran
        FROM transaksi
        WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
        GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
        ORDER BY bulan";
$stmt = $pdo->query($sql);
$dataChart = $stmt->fetchAll();

// Query untuk data chart per kategori
$sqlKategori = "SELECT 
                kategori,
                COALESCE(SUM(jumlah), 0) as total,
                jenis
                FROM transaksi
                GROUP BY kategori, jenis
                HAVING total > 0
                ORDER BY jenis, total DESC";
$stmtKategori = $pdo->query($sqlKategori);
$dataKategori = $stmtKategori->fetchAll();

// Siapkan data untuk chart
$labels = [];
$pemasukanData = [];
$pengeluaranData = [];

// Jika tidak ada data, buat array kosong untuk 6 bulan terakhir
if (empty($dataChart)) {
    for ($i = 5; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $labels[] = date('M Y', strtotime($date));
        $pemasukanData[] = 0;
        $pengeluaranData[] = 0;
    }
} else {
    foreach ($dataChart as $item) {
        $labels[] = date('M Y', strtotime($item['bulan'] . '-01'));
        $pemasukanData[] = $item['pemasukan'];
        $pengeluaranData[] = $item['pengeluaran'];
    }
}

// Siapkan data untuk chart kategori
$kategoriPemasukan = [];
$jumlahPemasukan = [];
$kategoriPengeluaran = [];
$jumlahPengeluaran = [];

foreach ($dataKategori as $item) {
    if ($item['jenis'] == 'pemasukan') {
        $kategoriPemasukan[] = $item['kategori'];
        $jumlahPemasukan[] = $item['total'];
    } else {
        $kategoriPengeluaran[] = $item['kategori'];
        $jumlahPengeluaran[] = $item['total'];
    }
}
?>

<h2 class="text-xl font-semibold mb-6">Laporan Keuangan</h2>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-4">Pemasukan vs Pengeluaran (6 Bulan Terakhir)</h3>
        <div class="chart-container" style="position: relative; height:300px;">
            <canvas id="chartPemasukanPengeluaran"></canvas>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-4">Pemasukan per Kategori</h3>
        <div class="chart-container" style="position: relative; height:300px;">
            <canvas id="chartPemasukanKategori"></canvas>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-4">Pengeluaran per Kategori</h3>
        <div class="chart-container" style="position: relative; height:300px;">
            <canvas id="chartPengeluaranKategori"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi format Rupiah untuk tooltip
    const formatRupiah = (value) => {
        return 'Rp ' + value.toLocaleString('id-ID');
    };

    // Options umum untuk semua chart
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || context.label || '';
                        if (label) label += ': ';
                        label += formatRupiah(context.raw);
                        return label;
                    }
                }
            }
        }
    };

    // Chart Pemasukan vs Pengeluaran
    const ctx1 = document.getElementById('chartPemasukanPengeluaran').getContext('2d');
    const chart1 = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: <?= json_encode($pemasukanData) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pengeluaran',
                    data: <?= json_encode($pengeluaranData) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            ...commonOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: formatRupiah
                    }
                }
            }
        }
    });

    // Chart Pemasukan per Kategori
    <?php if (!empty($kategoriPemasukan)): ?>
    const ctx2 = document.getElementById('chartPemasukanKategori').getContext('2d');
    const chart2 = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: <?= json_encode($kategoriPemasukan) ?>,
            datasets: [{
                data: <?= json_encode($jumlahPemasukan) ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: commonOptions
    });
    <?php else: ?>
    document.getElementById('chartPemasukanKategori').parentElement.innerHTML = 
        '<p class="text-center text-gray-500 py-8">Belum ada data pemasukan</p>';
    <?php endif; ?>

    // Chart Pengeluaran per Kategori
    <?php if (!empty($kategoriPengeluaran)): ?>
    const ctx3 = document.getElementById('chartPengeluaranKategori').getContext('2d');
    const chart3 = new Chart(ctx3, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($kategoriPengeluaran) ?>,
            datasets: [{
                data: <?= json_encode($jumlahPengeluaran) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: commonOptions
    });
    <?php else: ?>
    document.getElementById('chartPengeluaranKategori').parentElement.innerHTML = 
        '<p class="text-center text-gray-500 py-8">Belum ada data pengeluaran</p>';
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>