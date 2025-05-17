<?php
require_once 'config/database.php';
include 'includes/header.php';


$sql = "SELECT 
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as total_pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as total_pengeluaran,
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) - 
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as saldo
        FROM transaksi";
$stmt = $pdo->query($sql);
$summary = $stmt->fetch();

$sql = "SELECT * FROM transaksi ORDER BY tanggal DESC";
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
<div class="bg-white p-6 rounded-lg shadow overflow-x-auto max-h-96 overflow-y-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
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
                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($t['kategori']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($t['jumlah'], 0, ',', '.') ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($t['keterangan']) ?></td>
                <td class="px-6 py-4">
                    <form action="hapus.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($transaksi) === 0): ?>
            <tr>
                <td colspan="6" class="text-center px-6 py-4 text-gray-500">Tidak ada data transaksi.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
