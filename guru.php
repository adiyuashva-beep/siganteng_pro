<?php
session_start();
// Cek Login Guru
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] == 'siswa') {
    header("location:index.php"); exit();
}
$nip_session = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Guru - SiGanteng</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f1f5f9; -webkit-tap-highlight-color: transparent; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .glass-header { background: linear-gradient(135deg, #0f172a 0%, #334155 100%); }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Custom Radio Button */
        .radio-label input { display: none; }
        .radio-label span { display: inline-block; padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: bold; cursor: pointer; border: 1px solid #e2e8f0; color: #94a3b8; transition: all 0.2s; }
        .radio-label input:checked + span.opt-h { background-color: #d1fae5; color: #059669; border-color: #10b981; }
        .radio-label input:checked + span.opt-s { background-color: #dbeafe; color: #2563eb; border-color: #3b82f6; }
        .radio-label input:checked + span.opt-i { background-color: #ffedd5; color: #ea580c; border-color: #f97316; }
        .radio-label input:checked + span.opt-a { background-color: #fee2e2; color: #dc2626; border-color: #ef4444; }
        .radio-label input:checked + span.opt-d { background-color: #f3e8ff; color: #7e22ce; border-color: #a855f7; }
    </style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col pb-24">

    <header class="glass-header p-6 pb-24 rounded-b-[2.5rem] shadow-xl relative z-10 text-white">
        <div class="flex justify-between items-start mb-6" id="header-top-row">
            <div><p class="text-slate-300 text-[10px] font-bold uppercase tracking-[0.2em]">Panel Pendidik</p><h1 class="text-2xl font-black">SiGanteng <span class="text-emerald-400">Guru</span></h1></div>
            <button onclick="logout()" class="bg-white/10 p-2 rounded-full hover:bg-red-500 transition"><i data-lucide="log-out" class="w-5 h-5"></i></button>
        </div>
        <div class="flex items-center gap-4">
            <div class="relative group cursor-pointer active:scale-95 transition" onclick="bukaKameraProfil()">
                <div class="w-16 h-16 rounded-2xl bg-white p-1 shadow-lg">
                    <img id="foto-guru" src="" class="w-full h-full rounded-xl object-cover" onerror="this.src='https://ui-avatars.com/api/?background=random&color=fff'">
                </div>
                <div class="absolute -bottom-2 -right-2 bg-blue-600 text-white p-1.5 rounded-lg border-2 border-white shadow-sm"><i data-lucide="camera" class="w-3 h-3"></i></div>
            </div>
            <div>
                <h2 id="nama-guru" class="text-lg font-bold leading-tight">...</h2>
                <p id="nip-guru" class="text-xs font-mono text-slate-300 mt-1">...</p>
                <div id="badge-wali" class="hidden mt-2 inline-block bg-orange-500 text-white text-[9px] font-black px-2 py-0.5 rounded">WALI KELAS</div>
            </div>
        </div>
    </header>

    <main class="flex-1 px-5 -mt-16 relative z-20 space-y-6 fade-in">
        <div id="section-menu-dinamis" class="hidden bg-white p-4 rounded-[2rem] shadow-lg border border-slate-100">
            <h3 class="font-bold text-slate-700 text-sm mb-3 ml-1 flex items-center gap-2"><i data-lucide="grid" class="w-4 h-4 text-blue-500"></i> Layanan Guru & Staf</h3>
            <div id="grid-menu-dinamis" class="grid grid-cols-2 gap-3"></div>
        </div>

        <div id="panel-wali-kelas" class="hidden bg-orange-50 p-6 rounded-[2rem] shadow-lg border border-orange-100 relative overflow-hidden">
            <div class="absolute -right-5 -top-5 w-24 h-24 bg-orange-200 rounded-full opacity-20 blur-xl"></div>
            <h3 class="font-bold text-orange-800 text-sm uppercase tracking-widest mb-2 flex items-center gap-2"><i data-lucide="shield-check" class="w-4 h-4"></i> Kelas Anda: <span id="nama-kelas-wali">...</span></h3>
            <div class="grid grid-cols-3 gap-2 text-center mt-4 mb-4">
                <div class="bg-white p-3 rounded-xl shadow-sm border border-emerald-100"><h4 class="text-emerald-500 font-black text-xl" id="wali-hadir">0</h4><p class="text-[9px] font-bold text-slate-400">HADIR</p></div>
                <div class="bg-white p-3 rounded-xl shadow-sm border border-blue-100"><h4 class="text-blue-500 font-black text-xl" id="wali-izin">0</h4><p class="text-[9px] font-bold text-slate-400">IZIN</p></div>
                <div class="bg-white p-3 rounded-xl shadow-sm border border-red-100"><h4 class="text-red-500 font-black text-xl" id="wali-alpha">0</h4><p class="text-[9px] font-bold text-slate-400">ALPHA</p></div>
            </div>
            <button onclick="bukaModalWali()" class="w-full bg-white text-orange-600 border border-orange-200 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-orange-600 hover:text-white transition shadow-sm flex items-center justify-center gap-2"><i data-lucide="eye" class="w-3 h-3"></i> CEK STATUS SISWA</button>
        </div>

        <div class="bg-white p-6 rounded-[2rem] shadow-lg border border-slate-100">
            <h3 class="font-bold text-slate-700 text-sm uppercase tracking-widest mb-4 flex items-center gap-2"><i data-lucide="book-open" class="w-4 h-4 text-blue-500"></i> Jurnal Mengajar</h3>
            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="text-[10px] font-bold text-slate-400 ml-1">MULAI JAM KE-</label><select id="jam-mulai" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs font-bold text-slate-600 outline-none"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option></select></div>
                    <div><label class="text-[10px] font-bold text-slate-400 ml-1">SAMPAI JAM KE-</label><select id="jam-selesai" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs font-bold text-slate-600 outline-none"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option></select></div>
                </div>
                <select id="jurnal-kelas" onchange="loadSiswaUntukAbsen()" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs font-bold text-slate-600 outline-none"><option value="">- Pilih Kelas -</option></select>
                <select id="jurnal-mapel" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs font-bold text-slate-600 outline-none"><option value="">- Pilih Mapel -</option></select>
                <div id="area-absen-mapel" class="hidden bg-slate-50 p-4 rounded-xl border border-slate-200 max-h-60 overflow-y-auto">
                    <h4 class="text-xs font-bold text-slate-500 mb-3 flex justify-between items-center sticky top-0 bg-slate-50 pb-2 border-b border-slate-200"><span>Absensi Jam Ini</span><span id="jml-siswa-mapel" class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-[9px]">0 Siswa</span></h4>
                    <p class="text-[9px] text-slate-400 mb-2 italic">*Klik D untuk Dispensasi.</p>
                    <div id="list-absen-mapel" class="space-y-2"></div>
                </div>
                <textarea id="jurnal-materi" rows="2" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs font-bold text-slate-600 outline-none" placeholder="Materi / Bahasan Hari Ini..."></textarea>
                <div class="flex items-center gap-3"><button onclick="bukaKameraJurnal()" class="bg-slate-100 p-3 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-200 transition"><i data-lucide="camera" class="w-5 h-5"></i></button><div id="preview-foto-jurnal" class="text-xs text-slate-400 italic">Wajib foto bukti KBM.</div></div>
                <button onclick="kirimJurnal()" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-xs shadow-lg shadow-blue-500/30 active:scale-95 transition">SIMPAN JURNAL</button>
            </div>
            
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div><label class="text-[10px] font-bold text-slate-400 ml-1">DARI TANGGAL</label><input type="date" id="filter-mulai" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs font-bold text-slate-600 outline-none"></div>
                    <div><label class="text-[10px] font-bold text-slate-400 ml-1">SAMPAI TANGGAL</label><input type="date" id="filter-selesai" class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-xs font-bold text-slate-600 outline-none"></div>
                </div>
                <button onclick="downloadRekapJurnal()" class="w-full bg-emerald-50 text-emerald-700 py-3 rounded-xl font-bold text-xs border border-emerald-100 hover:bg-emerald-100 transition flex items-center justify-center gap-2"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i> DOWNLOAD JURNAL</button>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <h4 class="font-bold text-xs text-slate-400 uppercase mb-3">Riwayat Hari Ini</h4>
                <div id="list-riwayat-jurnal" class="space-y-2"><div class="text-center text-slate-300 text-xs italic py-2">Belum ada jurnal hari ini.</div></div>
            </div>
        </div>
    </main>

    <div id="modalKamera" class="hidden fixed inset-0 z-50 bg-black flex flex-col items-center justify-center">
        <div class="absolute top-0 w-full p-6 flex justify-between z-30"><h3 id="judul-kamera" class="text-white font-bold bg-black/50 px-3 py-1 rounded-full text-xs">KAMERA</h3><button onclick="tutupKamera()" class="text-white"><i data-lucide="x" class="w-8 h-8"></i></button></div>
        <div class="relative w-full h-full bg-black"><video id="videoStream" autoplay playsinline muted class="w-full h-full object-cover"></video><canvas id="canvasFoto" class="hidden"></canvas></div>
        <div class="absolute bottom-10 z-30 flex items-center gap-6"><button onclick="gantiKamera()" class="w-12 h-12 rounded-full bg-white/20 backdrop-blur border border-white/30 text-white flex items-center justify-center hover:bg-white/40"><i data-lucide="refresh-ccw" class="w-6 h-6"></i></button><button onclick="jepret()" class="w-16 h-16 bg-white rounded-full border-4 border-slate-300 shadow-xl active:scale-95 transition"></button><div class="w-12"></div></div>
    </div>

    <div id="modal-wali" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/90 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="bg-orange-50 p-4 border-b border-orange-100 flex justify-between items-center shrink-0"><h3 class="font-bold text-orange-800">Detail Kelas <span id="judul-modal-wali"></span></h3><button onclick="document.getElementById('modal-wali').classList.add('hidden')" class="p-2 bg-white rounded-full text-slate-400 hover:text-red-500"><i data-lucide="x" class="w-4 h-4"></i></button></div>
            <div class="overflow-y-auto p-4 space-y-2 bg-slate-50 flex-1" id="list-siswa-wali"></div>
        </div>
    </div>

    <script>
        const NIP = "<?php echo $nip_session; ?>";
        let streamKamera, tipeAbsenAktif, fotoBuktiJurnal;

        lucide.createIcons();
        document.addEventListener('DOMContentLoaded', initApp);

        function initApp() {
            fetch(`api_guru.php?action=init_data&nip=${NIP}`).then(r=>r.json()).then(d => {
                document.getElementById('nama-guru').innerText = d.guru.nama_lengkap;
                document.getElementById('nip-guru').innerText = "NIP: " + d.guru.username;
                if(d.guru.foto_profil) document.getElementById('foto-guru').src = 'uploads/profil_guru/'+d.guru.foto_profil;
                
                if(d.wali_data) {
                    document.getElementById('panel-wali-kelas').classList.remove('hidden');
                    document.getElementById('nama-kelas-wali').innerText = d.wali_data.kelas;
                    document.getElementById('judul-modal-wali').innerText = d.wali_data.kelas;
                    document.getElementById('badge-wali').classList.remove('hidden');
                    document.getElementById('wali-hadir').innerText = d.wali_data.hadir;
                    document.getElementById('wali-izin').innerText = d.wali_data.izin;
                    document.getElementById('wali-alpha').innerText = d.wali_data.alpha;
                }

                const sk = document.getElementById('jurnal-kelas'); const sm = document.getElementById('jurnal-mapel');
                d.kelas.forEach(k => sk.add(new Option(k, k))); d.mapel.forEach(m => sm.add(new Option(m, m)));
                
                const menuBox = document.getElementById('grid-menu-dinamis');
                if(d.menu && d.menu.length > 0) {
                    document.getElementById('section-menu-dinamis').classList.remove('hidden');
                    d.menu.forEach(m => {
                        menuBox.innerHTML += `<a href="${m.link_url}" class="p-4 border rounded-2xl flex flex-col items-center gap-2 active:scale-95 transition"><div class="text-2xl">${m.icon}</div><span class="text-[10px] font-bold uppercase">${m.judul}</span></a>`;
                    });
                }
                
                // Portal Access
                const role = d.guru.level;
                if(['admin','super','bk','kurikulum'].includes(role)) {
                    const btn = document.createElement('button');
                    let label = "PANEL ADMIN"; let link = "admin.php";
                    if(role=='bk'){label="PANEL BK";link="admin_bk.php";}
                    else if(role=='kurikulum'){label="PANEL KBM";link="admin_kbm.php";}
                    btn.className = `bg-yellow-500/20 text-yellow-600 border border-yellow-500 px-3 py-1.5 rounded-full text-[10px] font-bold mr-2 flex items-center gap-1`;
                    btn.innerHTML = `<i data-lucide='layout-dashboard' class='w-3 h-3'></i> ${label}`;
                    btn.onclick = () => window.location.href = link;
                    document.querySelector('#header-top-row div.flex').insertBefore(btn, document.querySelector('#header-top-row div.flex button'));
                    lucide.createIcons();
                }
            });
            loadRiwayat();
        }

        function loadSiswaUntukAbsen() {
            const kls = document.getElementById('jurnal-kelas').value;
            const area = document.getElementById('area-absen-mapel');
            const list = document.getElementById('list-absen-mapel');
            if (!kls) { area.classList.add('hidden'); return; }
            area.classList.remove('hidden'); list.innerHTML = 'Loading...';
            fetch('api_guru.php?action=get_siswa_kelas&kelas='+kls).then(r=>r.json()).then(data => {
                list.innerHTML = ''; document.getElementById('jml-siswa-mapel').innerText = data.length + ' Siswa';
                data.forEach(s => {
                    list.innerHTML += `<div class="flex items-center justify-between bg-white p-2 rounded-lg border border-slate-100 siswa-item" data-nisn="${s.nisn}" data-nama="${s.nama_siswa}"><span class="text-xs font-bold text-slate-700 truncate w-32">${s.nama_siswa}</span><div class="flex gap-1"><label class="radio-label"><input type="radio" name="absen_${s.nisn}" value="H" checked><span class="opt-h">H</span></label><label class="radio-label"><input type="radio" name="absen_${s.nisn}" value="S"><span class="opt-s">S</span></label><label class="radio-label"><input type="radio" name="absen_${s.nisn}" value="I"><span class="opt-i">I</span></label><label class="radio-label"><input type="radio" name="absen_${s.nisn}" value="A"><span class="opt-a">A</span></label><label class="radio-label"><input type="radio" name="absen_${s.nisn}" value="D"><span class="opt-d">D</span></label></div></div>`;
                });
            });
        }

        window.bukaModalWali = () => {
            const kls = document.getElementById('nama-kelas-wali').innerText; document.getElementById('modal-wali').classList.remove('hidden');
            const list = document.getElementById('list-siswa-wali'); list.innerHTML = 'Loading...';
            fetch(`api_guru.php?action=get_detail_wali&kelas=${kls}`).then(r=>r.json()).then(data => {
                list.innerHTML = '';
                data.forEach(s => {
                    let st = `<span class="bg-slate-200 text-slate-500 px-2 py-0.5 rounded text-[10px] font-bold">Belum</span>`;
                    if(s.status_display.includes("Masuk")) st = `<span class="bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded text-[10px] font-bold">${s.status_display}</span>`;
                    else if(s.status_display.includes("Sakit")) st = `<span class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-[10px] font-bold">Sakit</span>`;
                    else if(s.status_display.includes("Izin")) st = `<span class="bg-orange-100 text-orange-600 px-2 py-0.5 rounded text-[10px] font-bold">Izin</span>`;
                    list.innerHTML += `<div class="flex justify-between bg-white p-2 rounded border mb-2"><span class="text-xs font-bold">${s.nama_siswa}</span>${st}</div>`;
                });
            });
        }

        // KAMERA
        function bukaKameraProfil(){tipeAbsenAktif="Profil";document.getElementById('modalKamera').classList.remove('hidden');navigator.mediaDevices.getUserMedia({video:{facingMode:'user'}}).then(s=>{streamKamera=s;document.getElementById('videoStream').srcObject=s;});}
        function bukaKameraJurnal(){tipeAbsenAktif="Jurnal";document.getElementById('modalKamera').classList.remove('hidden');navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'}}).then(s=>{streamKamera=s;document.getElementById('videoStream').srcObject=s;});}
        function tutupKamera(){document.getElementById('modalKamera').classList.add('hidden');if(streamKamera)streamKamera.getTracks().forEach(t=>t.stop());}
        function gantiKamera(){currentFacingMode=(currentFacingMode=='user')?'environment':'user'; navigator.mediaDevices.getUserMedia({video:{facingMode:currentFacingMode}}).then(s=>{streamKamera=s;document.getElementById('videoStream').srcObject=s;});}
        
        function jepret(){const v=document.getElementById('videoStream');const c=document.getElementById('canvasFoto');c.width=480;c.height=480;c.getContext('2d').drawImage(v,0,0,480,480);const f=c.toDataURL('image/jpeg',0.7);
            if(tipeAbsenAktif=='Profil'){ tutupKamera(); Swal.fire({title:'Upload...',didOpen:()=>Swal.showLoading()}); const fd=new FormData(); fd.append('action','update_profil'); fd.append('nip',NIP); fd.append('foto',f); fetch('api_guru.php',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{Swal.fire('Sukses','','success'); location.reload();}); } 
            else { fotoBuktiJurnal=f; document.getElementById('preview-foto-jurnal').innerText="Foto Terlampir ✔"; tutupKamera(); }
        }

        function kirimJurnal(){
            const kls=document.getElementById('jurnal-kelas').value; const mapel=document.getElementById('jurnal-mapel').value; const materi=document.getElementById('jurnal-materi').value;
            if(!kls||!mapel||!materi)return Swal.fire('Lengkapi Data','','warning'); if(!fotoBuktiJurnal)return Swal.fire('Foto Wajib','','warning');
            
            let absensiSiswa=[]; document.querySelectorAll('.siswa-item').forEach(el=>{ const nisn=el.getAttribute('data-nisn'); const nama=el.getAttribute('data-nama'); const status=document.querySelector(`input[name="absen_${nisn}"]:checked`).value; absensiSiswa.push({nisn,nama,status}); });
            
            const fd=new FormData(); fd.append('action','simpan_jurnal'); fd.append('nip',NIP); fd.append('nama_guru',document.getElementById('nama-guru').innerText);
            fd.append('kelas',kls); fd.append('mapel',mapel); fd.append('materi',materi); fd.append('jam_ke',`${document.getElementById('jam-mulai').value} - ${document.getElementById('jam-selesai').value}`);
            fd.append('foto',fotoBuktiJurnal); fd.append('absen_siswa',JSON.stringify(absensiSiswa));
            
            Swal.fire({title:'Menyimpan...',didOpen:()=>Swal.showLoading()});
            fetch('api_guru.php',{method:'POST',body:fd}).then(r=>r.json()).then(res=>{ if(res.status=='success'){Swal.fire('Berhasil','','success');loadRiwayat();fotoBuktiJurnal=null;document.getElementById('jurnal-materi').value='';} else Swal.fire('Gagal',res.pesan,'error'); });
        }
        
        function loadRiwayat(){fetch(`api_guru.php?action=get_riwayat&nip=${NIP}`).then(r=>r.json()).then(d=>{const c=document.getElementById('list-riwayat-jurnal');c.innerHTML='';if(d.length===0){c.innerHTML='Nihil';return;}d.forEach(j=>{const tm=j.waktu.split(' ')[1].substring(0,5);c.innerHTML+=`<div class="flex items-center gap-3 bg-slate-50 p-2 rounded-xl border border-slate-100"><img src="uploads/jurnal/${j.foto_kegiatan}" class="w-10 h-10 rounded bg-slate-200 object-cover"><div><div class="text-xs font-bold">${j.kelas} - ${j.mapel}</div><div class="text-[10px] text-slate-500">${tm}</div></div></div>`;});});}
        function logout(){if(confirm("Keluar?")) window.location.href='logout.php';}
        
        window.downloadRekapJurnal = () => {
            const m = document.getElementById('filter-mulai').value; const s = document.getElementById('filter-selesai').value;
            if(!m||!s) return Swal.fire('Tanggal','Pilih range tanggal dulu','warning');
            Swal.fire({title:'Mengunduh...',didOpen:()=>Swal.showLoading()});
            // Karena ini local PHP, kita pakai window.open ke file PHP export saja nanti, tapi sementara pakai JS Export logic
            // Karena api_guru.php kita belum ada endpoint export, kita skip dulu implementasi backend export detailnya, 
            // tapi logika frontend tetap jalan.
            Swal.close(); Swal.fire('Info', 'Fitur download Excel Jurnal sedang disiapkan di backend.', 'info');
        }
    </script>
</body>
</html>