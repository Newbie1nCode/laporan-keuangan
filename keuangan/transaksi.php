<?php
require_once 'config/database.php';
include 'includes/header.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    $tanggal = $_POST['tanggal'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 0;
    $keterangan = $_POST['keterangan'] ?? '';
 
    
    if (empty($tanggal)) $errors[] = "Tanggal harus diisi";
    if (empty($jenis)) $errors[] = "Jenis transaksi harus dipilih";
    if (empty($kategori)) $errors[] = "Kategori harus dipilih";
    if ($jumlah <= 0) $errors[] = "Jumlah harus lebih dari 0";
    
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO transaksi (tanggal, jenis, kategori, jumlah, keterangan) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tanggal, $jenis, $kategori, $jumlah, $keterangan]);
            
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
    
  
    if (!empty($errors)) {
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
        echo '<p class="font-bold">Gagal menyimpan transaksi:</p>';
        foreach ($errors as $error) {
            echo '<p>• ' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
    }
}


$kategori_pemasukan = ['gaji', 'bonus', 'investasi', 'hibah', 'lainnya'];
$kategori_pengeluaran = ['makanan', 'transportasi', 'hiburan', 'kesehatan', 'pendidikan', 'tagihan', 'lainnya'];
?>

<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-6">Tambah Transaksi</h2>
    <form method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                       value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label for="jenis" class="block text-sm font-medium text-gray-700">Jenis Transaksi</label>
                <select id="jenis" name="jenis" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="kategori" class="block text-sm font-medium text-gray-700">Kategori</label>
            <select id="kategori" name="kategori" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                <option value="">Pilih Kategori</option>
                <optgroup label="Pemasukan" id="kategori-pemasukan">
                    <?php foreach ($kategori_pemasukan as $k): ?>
                    <option value="<?= $k ?>"><?= ucfirst($k) ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Pengeluaran" id="kategori-pengeluaran" style="display:none;">
                    <?php foreach ($kategori_pengeluaran as $k): ?>
                    <option value="<?= $k ?>"><?= ucfirst($k) ?></option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </div>
        
        <div class="mb-4">
            <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
            <input type="number" id="jumlah" name="jumlah" min="0" step="100" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
        </div>
        
        <div class="mb-4">
            <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="3" 
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Simpan Transaksi
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('jenis').addEventListener('change', function() {
    const jenis = this.value;
    const pemasukan = document.getElementById('kategori-pemasukan');
    const pengeluaran = document.getElementById('kategori-pengeluaran');
    
    if (jenis === 'pemasukan') {
        pemasukan.style.display = 'block';
        pengeluaran.style.display = 'none';
    } else {
        pemasukan.style.display = 'none';
        pengeluaran.style.display = 'block';
    }
    
  
    document.getElementById('kategori').selectedIndex = 0;
});
</script>

<?php include 'includes/footer.php'; ?>