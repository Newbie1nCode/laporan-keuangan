<?php
require_once 'config/database.php';
include 'includes/header.php';

// Query untuk summary
// Query untuk summary
$sql = "SELECT 
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as total_pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as total_pengeluaran,
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as saldo
        FROM transaksi";
$stmt = $pdo->query($sql);
$summary = $stmt->fetch();

// Query untuk transaksi terakhir
$sql = "SELECT * FROM transaksi ORDER BY tanggal DESC LIMIT 5";
$stmt = $pdo->query($sql);
$transaksi = $stmt->fetchAll();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-green-100 p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700">Pemasukan</h3>
        <p class="text-2xl font-bold text-green-600">Rp <?= number_format($summary['total_pemasukan'], 0, ',', '.') ?></p>
    </div>
    <div class="bg-red-100 p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700">Pengeluaran</h3>
        <p class="text-2xl font-bold text-red-600">Rp <?= number_format($summary['total_pengeluaran'], 0, ',', '.') ?></p>
    </div>
    <div class="bg-blue-100 p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-700">Saldo</h3>
        <p class="text-2xl font-bold text-blue-600">Rp <?= number_format($summary['saldo'], 0, ',', '.') ?></p>
    </div>
</div>

<h2 class="text-xl font-semibold mb-4">Transaksi Terakhir</h2>
<div class="bg-white p-6 rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($transaksi as $t): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap"><?= date('d M Y', strtotime($t['tanggal'])) ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?= $t['jenis'] == 'pemasukan' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= ucfirst($t['jenis']) ?>
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap"><?= $t['kategori'] ?></td>
                <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($t['jumlah'], 0, ',', '.') ?></td>
                <td class="px-6 py-4"><?= $t['keterangan'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>