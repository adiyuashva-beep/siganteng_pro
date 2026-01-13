<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari NISN - SiGanteng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-900 text-white font-['Outfit'] flex flex-col items-center justify-center min-h-screen p-6">
    
    <div class="w-full max-w-md bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-xl">
        <h2 class="text-xl font-bold text-center mb-1">LUPA NISN?</h2>
        <p class="text-xs text-slate-400 text-center mb-6">Cari namamu di bawah ini.</p>
        
        <form method="GET" class="flex gap-2 mb-4">
            <input type="text" name="q" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-sm focus:border-blue-500 outline-none" placeholder="Ketik Nama Lengkap..." value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>" required>
            <button type="submit" class="bg-blue-600 px-4 py-2 rounded-lg font-bold"><i class='bx bx-search'></i> Cari</button>
        </form>

        <div class="space-y-2 h-64 overflow-y-auto pr-1">
            <?php
            if(isset($_GET['q'])) {
                $q = mysqli_real_escape_string($koneksi, $_GET['q']);
                if(strlen($q) < 3) {
                    echo "<div class='text-center text-yellow-500 text-xs mt-4'>Ketik minimal 3 huruf.</div>";
                } else {
                    $sql = mysqli_query($koneksi, "SELECT nama_siswa, nisn, kelas FROM tb_siswa WHERE nama_siswa LIKE '%$q%' LIMIT 20");
                    if(mysqli_num_rows($sql) > 0) {
                        while($r = mysqli_fetch_assoc($sql)) {
                            echo "
                            <div class='bg-slate-700/50 p-3 rounded-xl border border-slate-600 flex justify-between items-center'>
                                <div>
                                    <div class='font-bold text-sm'>{$r['nama_siswa']}</div>
                                    <div class='text-xs text-slate-400'>Kelas: {$r['kelas']}</div>
                                </div>
                                <div class='bg-slate-900 px-3 py-1 rounded text-blue-400 font-mono font-bold select-all'>{$r['nisn']}</div>
                            </div>";
                        }
                    } else {
                        echo "<div class='text-center text-red-400 text-xs mt-4'>Nama tidak ditemukan.</div>";
                    }
                }
            } else {
                echo "<div class='text-center text-slate-500 text-xs mt-10'>Silakan cari nama Anda.</div>";
            }
            ?>
        </div>

        <a href="login.php" class="block text-center mt-6 text-slate-400 text-xs hover:text-white">Kembali ke Login</a>
    </div>

</body>
</html>