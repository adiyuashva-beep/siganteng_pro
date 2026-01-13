<?php
session_start();
include 'koneksi.php';

// --- LOGIKA LOGIN (BACKEND) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $u = mysqli_real_escape_string($koneksi, $_POST['username']);
    $p = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    // 1. CEK BACKDOOR SUPER ADMIN
    if ($u == 'admin' && $p == '123') {
        $_SESSION['status'] = "login";
        $_SESSION['role'] = "super";
        $_SESSION['username'] = "admin";
        $_SESSION['nama'] = "Super Administrator";
        echo json_encode(['status' => 'success', 'role' => 'admin', 'target' => 'admin.php', 'nama' => 'Super Admin']);
        exit();
    }

    // 2. CEK DI TABEL USER (GURU / ADMIN / BK / KURIKULUM)
    $q_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='$u'");
    if (mysqli_num_rows($q_user) > 0) {
        $d = mysqli_fetch_assoc($q_user);
        if ($p == $d['password']) {
            $_SESSION['status'] = "login";
            $_SESSION['role'] = $d['level']; // admin, guru, bk, kurikulum
            $_SESSION['username'] = $d['username'];
            $_SESSION['nama'] = $d['nama_lengkap'];
            
            // Tentukan Target Halaman
            $target = 'guru.php';
            if ($d['level'] == 'admin') $target = 'admin.php';
            elseif ($d['level'] == 'bk') $target = 'admin_bk.php';
            elseif ($d['level'] == 'kurikulum') $target = 'admin_kbm.php';
            
            echo json_encode(['status' => 'success', 'role' => $d['level'], 'target' => $target, 'nama' => $d['nama_lengkap']]);
            exit();
        }
    }

    // 3. CEK DI TABEL SISWA
    $q_siswa = mysqli_query($koneksi, "SELECT * FROM tb_siswa WHERE nisn='$u'");
    if (mysqli_num_rows($q_siswa) > 0) {
        $d = mysqli_fetch_assoc($q_siswa);
        
        // Cek Password Siswa
        if ($p == $d['password']) {
            $_SESSION['status'] = "login";
            $_SESSION['role'] = "siswa";
            $_SESSION['username'] = $d['nisn'];
            $_SESSION['nama'] = $d['nama_siswa'];
            echo json_encode(['status' => 'success', 'role' => 'siswa', 'target' => 'siswa.php', 'nama' => $d['nama_siswa']]);
            exit();
        }
        
        // Cek Mode Orang Tua (Password = "ortu" + NISN)
        $passOrtu = "ortu" . $d['nisn'];
        if ($p == $passOrtu) {
            // Disini sessionnya tetap dianggap siswa tapi nanti di halaman ortu beda perlakuan
            // Untuk sementara kita arahkan ke siswa.php tapi kasih flag
            $_SESSION['status'] = "login";
            $_SESSION['role'] = "ortu";
            $_SESSION['username'] = $d['nisn']; // Tetap pegang NISN anak
            echo json_encode(['status' => 'success', 'role' => 'ortu', 'target' => 'siswa.php', 'nama' => 'Wali Murid ' . $d['nama_siswa']]);
            exit();
        }
    }

    // GAGAL LOGIN
    echo json_encode(['status' => 'error', 'pesan' => 'Username atau Password Salah!']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Si Ganteng SMAN 1 Pejagoan</title>

    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="logo.png">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            margin: 0; font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            height: 100vh; display: flex; align-items: center; justify-content: center;
            color: white; overflow: hidden;
        }
        
        .bg-pattern {
            position: absolute; width: 100%; height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: 1; pointer-events: none;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1); padding: 40px 30px;
            border-radius: 24px; width: 100%; max-width: 380px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); text-align: center;
            position: relative; z-index: 10;
        }

        .logo-container {
            width: 100px; height: 100px; margin: 0 auto 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px; padding: 10px;
            box-shadow: 0 0 30px rgba(56, 189, 248, 0.3);
            display: flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: float 6s ease-in-out infinite;
        }
        .logo-img {
            width: 100%; height: 100%; object-fit: contain;
            filter: drop-shadow(0 0 5px rgba(255,255,255,0.5));
        }

        h2 { margin: 0; font-weight: 800; letter-spacing: -0.5px; font-size: 1.8rem; }
        p { color: #94a3b8; font-size: 0.9em; margin-top: 5px; margin-bottom: 30px; font-weight: 300; }
        
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; color: #cbd5e1; font-size: 0.75em; margin-bottom: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .input-field {
            width: 100%; padding: 14px 16px; background: rgba(15, 23, 42, 0.6);
            border: 1px solid #334155; border-radius: 12px; color: white;
            font-size: 1em; outline: none; transition: all 0.3s; box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }
        .input-field:focus { border-color: #38bdf8; box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.1); background: rgba(15, 23, 42, 0.8); }
        
        .btn-login {
            width: 100%; padding: 16px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white; border: none; border-radius: 14px; font-weight: 700; font-size: 1em;
            cursor: pointer; transition: 0.3s; margin-top: 10px; letter-spacing: 0.5px;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.6); }
        .btn-login:active { transform: scale(0.98); }

        .link-help {
            display: inline-block; margin-top: 25px; color: #64748b; text-decoration: none;
            font-size: 0.85em; transition: 0.3s; 
        }
        .link-help:hover { color: #38bdf8; }
        
        .footer { position: absolute; bottom: 20px; width: 100%; text-align: center; color: #475569; font-size: 0.7em; letter-spacing: 1px; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        
        #loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.95); z-index: 50;
            display: none; justify-content: center; align-items: center; flex-direction: column;
        }
    </style>
</head>
<body>

    <div class="bg-pattern"></div>

    <div id="loader">
        <i class='bx bx-loader-alt bx-spin' style="font-size: 50px; color: #38bdf8;"></i>
        <p style="margin-top: 15px; color: white; font-weight: 600; letter-spacing: 1px;">MEMERIKSA DATA...</p>
    </div>

    <div id="layar-sambut" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background-color: #0f172a; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease;">
        <div style="text-align: center;">
            <img src="logo.png" style="width: 100px; margin-bottom: 20px; filter: drop-shadow(0 0 10px rgba(56,189,248,0.5));" alt="Logo" onerror="this.src='https://ui-avatars.com/api/?name=SG&background=transparent&color=fff&size=128'">
            <h1 style="color: white; font-family: 'Outfit'; font-weight: 800; font-size: 1.5rem;">SiGanteng</h1>
        </div>
    </div>

    <div class="login-card">
        <div class="logo-container">
            <img src="logo.png" alt="SiGanteng Logo" class="logo-img" onerror="this.src='https://ui-avatars.com/api/?name=SG&background=transparent&color=fff&size=128'">
        </div>

        <h2>SI GANTENG</h2>
        <p>SMAN 1 PEJAGOAN</p>

        <form id="formLogin">
            <div class="input-group">
                <label>Username / NISN</label>
                <input type="text" id="username" name="username" class="input-field" placeholder="Masukan NISN / NIP..." required autocomplete="off">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" id="password" name="password" class="input-field" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">MASUK <i class='bx bx-right-arrow-alt'></i></button>
        </form>

        <a href="#" class="link-help" onclick="Swal.fire('Lupa Password?','Hubungi Admin IT Sekolah.','info')">
            <i class='bx bx-search'></i> Lupa NISN? Cari Disini
        </a>
    </div>

    <div class="footer">&copy; 2025 TIM IT SMAN 1 PEJAGOAN</div>

    <script>
        // --- SPLASH SCREEN LOGIC ---
        window.addEventListener('load', () => {
            setTimeout(() => {
                const splash = document.getElementById('layar-sambut');
                if(splash) {
                    splash.style.opacity = '0';
                    setTimeout(() => { splash.style.display = 'none'; }, 500); 
                }
            }, 1500);
        });

        // --- LOGIN LOGIC PHP ---
        document.getElementById('formLogin').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const u = document.getElementById('username').value.trim();
            const p = document.getElementById('password').value.trim();
            const loader = document.getElementById('loader');

            if(!u || !p) return;

            loader.style.display = 'flex';

            const formData = new FormData();
            formData.append('username', u);
            formData.append('password', p);

            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                loader.style.display = 'none';

                if(result.status === 'success') {
                    // Simpan data di SessionStorage (Agar JS di halaman lain tetap jalan normal)
                    const sessionData = {
                        username: u,
                        nama: result.nama,
                        role: result.role,
                        // Kelas bisa diambil nanti di halaman masing-masing via API
                    };
                    sessionStorage.setItem('user_siganteng', JSON.stringify(sessionData));
                    
                    Swal.fire({
                        icon: 'success',
                        title: `Halo, ${result.nama}!`,
                        text: 'Mengalihkan ke dashboard...',
                        timer: 1500,
                        showConfirmButton: false,
                        background: '#1e293b', color: '#fff'
                    }).then(() => {
                        window.location.href = result.target;
                    });
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Gagal Masuk', text: result.pesan,
                        confirmButtonColor: '#3b82f6', background: '#1e293b', color: '#fff'
                    });
                }

            } catch (error) {
                console.error(error);
                loader.style.display = 'none';
                Swal.fire({
                    icon: 'error', title: 'Error Server', text: 'Gagal menghubungi database.',
                    confirmButtonColor: '#ef4444', background: '#1e293b', color: '#fff'
                });
            }
        });
    </script>
</body>
</html>