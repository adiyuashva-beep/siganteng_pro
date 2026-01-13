<?php
session_start();
// Cek Login Siswa
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login" || $_SESSION['role'] != 'siswa') {
    header("location:index.php"); exit();
}
$nisn_session = $_SESSION['username'];
$nama_session = $_SESSION['nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>7 Kebiasaan Hebat - SiGanteng</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f0fdfa; user-select: none; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        
        /* Card Styles */
        .card-misi { background: white; border: 2px solid #e2e8f0; border-radius: 1.5rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; position: relative; }
        
        /* State: Active (Valid) */
        .card-misi.valid { border-color: #10b981; background-color: #ecfdf5; transform: scale(1.01); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1); }
        
        /* State: Expanded (Sedang Mengisi) */
        .card-misi.expanded { border-color: #6366f1; }

        .check-circle { width: 28px; height: 28px; border-radius: 50%; border: 2px solid #cbd5e1; transition: all 0.3s; display: flex; align-items: center; justify-content: center; background: white; }
        .card-misi.valid .check-circle { background: #10b981; border-color: #10b981; color: white; transform: scale(1.1); }
        
        /* Animation */
        .fade-up { animation: fadeUp 0.5s ease-out forwards; opacity: 0; transform: translateY(20px); }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        
        /* Camera Styles */
        .camera-overlay { background: black; }
        .camera-box { position: relative; width: 100%; height: 100%; overflow: hidden; }
        .preview-img { width: 100%; height: 150px; object-fit: cover; border-radius: 12px; margin-top: 10px; border: 2px dashed #cbd5e1; }
        .input-custom { transition: all 0.3s; }
        .hidden-custom { display: none; }
    </style>
</head>
<body class="pb-40">

    <div class="bg-indigo-600 p-6 pb-12 rounded-b-[3rem] text-white relative shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <button onclick="window.location.href='siswa.php'" class="bg-white/20 p-2 rounded-full hover:bg-white/30"><i data-lucide="arrow-left" class="w-5 h-5"></i></button>
            <div class="bg-indigo-800/50 px-3 py-1 rounded-full text-[10px] font-bold border border-indigo-400/30 flex items-center gap-2">
                🔥 POIN TERKUMPUL: <span id="score-display">0</span>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="relative">
                <img id="foto-profil" src="" class="w-16 h-16 rounded-full border-4 border-indigo-300 bg-indigo-500" onerror="this.src='https://ui-avatars.com/api/?background=random&color=fff&name=Siswa'">
            </div>
            <div class="flex-1">
                <h2 id="nama-siswa" class="text-xl font-black leading-tight">Memuat...</h2>
                <div class="mt-2">
                    <div class="flex justify-between text-[10px] font-bold opacity-80 mb-1">
                        <span>Progress Harian</span>
                        <span id="text-progress">0/7 Selesai</span>
                    </div>
                    <div class="w-full bg-indigo-900/30 h-3 rounded-full overflow-hidden">
                        <div id="bar-progress" class="h-full bg-gradient-to-r from-yellow-400 to-orange-500 w-0 transition-all duration-500"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 -mt-6 relative z-10 space-y-4">

        <div id="card-bangun" class="card-misi p-4 fade-up" style="animation-delay: 0.1s;" onclick="bukaDetail('bangun')">
            <div class="flex items-center gap-4">
                <div class="bg-amber-100 text-amber-600 p-3 rounded-xl"><i data-lucide="sun" class="w-6 h-6"></i></div>
                <div class="flex-1"><h3 class="font-bold text-slate-800">1. Bangun Pagi</h3><p class="text-[10px] text-slate-500" id="status-bangun">Belum diisi</p></div>
                <div class="check-circle"><i data-lucide="check" class="w-4 h-4"></i></div>
            </div>
            <div id="detail-bangun" class="hidden mt-3 pt-3 border-t border-slate-100" onclick="event.stopPropagation()">
                <label class="text-[10px] font-bold text-slate-400 uppercase">Jam Bangun:</label>
                <input type="time" id="val-bangun" onchange="validasi('bangun')" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm font-bold mt-1 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

        <div id="card-ibadah" class="card-misi p-4 fade-up" style="animation-delay: 0.2s;" onclick="bukaDetail('ibadah')">
            <div class="flex items-center gap-4">
                <div class="bg-blue-100 text-blue-600 p-3 rounded-xl"><i data-lucide="heart" class="w-6 h-6"></i></div>
                <div class="flex-1"><h3 class="font-bold text-slate-800">2. Beribadah</h3><p class="text-[10px] text-slate-500" id="status-ibadah">Belum diisi</p></div>
                <div class="check-circle"><i data-lucide="check" class="w-4 h-4"></i></div>
            </div>
            <div id="detail-ibadah" class="hidden mt-3 pt-3 border-t border-slate-100" onclick="event.stopPropagation()">
                <select id="val-ibadah" onchange="cekDropdown('ibadah')" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs font-bold outline-none mb-2">
                    <option value="">- Pilih Ibadah -</option><option value="Sholat Subuh">Sholat Subuh</option><option value="Sholat Dhuha">Sholat Dhuha</option><option value="Sholat Zuhur">Sholat Zuhur</option><option value="Saat Teduh">Saat Teduh / Doa Pagi</option><option value="Membaca Kitab Suci">Membaca Kitab Suci</option><option value="Lainnya">Lainnya (Isi Sendiri)</option>
                </select>
                <input type="text" id="extra-ibadah" onkeyup="validasi('ibadah')" class="hidden-custom w-full bg-indigo-50 border border-indigo-200 rounded-lg p-3 text-xs font-bold text-indigo-700 placeholder-indigo-300" placeholder="Tulis ibadahmu...">
            </div>
        </div>

        <div id="card-olahraga" class="card-misi p-4 fade-up" style="animation-delay: 0.3s;" onclick="bukaDetail('olahraga')">
            <div class="flex items-center gap-4">
                <div class="bg-rose-100 text-rose-600 p-3 rounded-xl"><i data-lucide="activity" class="w-6 h-6"></i></div>
                <div class="flex-1"><h3 class="font-bold text-slate-800">3. Berolahraga</h3><p class="text-[10px] text-slate-500" id="status-olahraga">Belum diisi</p></div>
                <div class="check-circle"><i data-lucide="check" class="w-4 h-4"></i></div>
            </div>
            <div id="detail-olahraga" class="hidden mt-3 pt-3 border-t border-slate-100" onclick="event.stopPropagation()">
                <select id="val-olahraga" onchange="cekDropdown('olahraga')" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs font-bold outline-none mb-2">
                    <option value="">- Pilih Olahraga -</option><option value="Senam Pagi">Senam Pagi</option><option value="Lari / Jogging">Lari / Jogging</option><option value="Push Up / Sit Up">Push Up / Sit Up</option><option value="Bulu Tangkis">Bulu Tangkis</option><option value="Sepak Bola">Sepak Bola</option><option value="Lainnya">Lainnya (Isi Sendiri)</option>
                </select>
                <input type="text" id="extra-olahraga" onkeyup="validasi('olahraga')" class="hidden-custom w-full bg-indigo-50 border border-indigo-200 rounded-lg p-3 text-xs font-bold text-indigo-700 placeholder-indigo-300" placeholder="Olahraga apa...">
            </div>
        </div>

        <div id="card-makan" class="card-misi p-4 fade-up" style="animation-delay: 0.4s;" onclick="bukaDetail('makan')">
            <div class="flex items-center gap-4">
                <div class="bg-green-100 text-green-600 p-3 rounded-xl"><i data-lucide="apple" class="w-6 h-6"></i></div>
                <div class="flex-1"><h3 class="font-bold text-slate-800">4. Makan Bergizi <span class="text-[8px] bg-red-500 text-white px-1 rounded ml-1">FOTO</span></h3><p class="text-[10px] text-slate-500" id="status-makan">Foto wajib diupload</p></div>
                <div class="check-circle"><i data-lucide="check" class="w-4 h-4"></i></div>
            </div>
            <div id="detail-makan" class="hidden mt-3 pt-3 border-t border-slate-100" onclick="event.stopPropagation()">
                <input type="text" id="val-makan" onkeyup="validasi('makan')" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs font-bold mb-2" placeholder="Tulis menumu (Cth: Nasi + Sayur Sop)">
                <button onclick="bukaKamera('makan')" class="w-full bg-slate-100 text-slate-600 py-3 rounded-lg border border-slate-200 text-xs font-bold flex items-center justify-center gap-2 hover:bg-slate-200 active:scale-95 transition"><i data-lucide="camera" class="w-4 h-4"></i> Ambil Foto Makanan</button>
                <img id="preview-makan" class="preview-img hidden"><input type="hidden" id="foto-makan">
            </div>
        </div>

        <div id="card-belajar" class="card-misi p-4 fade-up" style="animation-delay: 0.5s;" onclick="bukaDetail('belajar')">
            <div class="flex items-center gap-4">
                <div class="bg-cyan-100 text-cyan-600 p-3 rounded-xl"><i data-lucide="book-open" class="w-6 h-6"></i></div>
                <div class="flex-1"><h3 class="font-bold text-slate-800">5. Gemar Belajar <span class="text-[8px] bg-red-500 text-white px-1 rounded ml-1">FOTO</span></h3><p class="text-[10px] text-slate-500" id="status-belajar">Foto wajib diupload</p></div>
                <div class="check-circle"><i data-lucide="check" class="w-4 h-4"></i></div>
            </div>
            <div id="detail-belajar" class="hidden mt-3 pt-3 border-t border-slate-100" onclick="event.stopPropagation()">
                <input type="text" id="val-belajar" onkeyup="validasi('belajar')" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs font-bold mb-2" placeholder="Belajar apa? (Cth: Matematika)">
                <button onclick="bukaKamera('belajar')" class="w-full bg-slate-100 text-slate-600 py-3 rounded-lg border border-slate-200 text-xs font-bold flex items-center justify-center gap-2 hover:bg-slate-200 active:scale-95 transition"><i data-lucide="camera" class="w-4 h-4"></i> Foto Aktivitas</button>
                <img id="preview-belajar" class="preview-img hidden"><input type="hidden" id="foto-belajar">
            </div>
        </div>

        <div id="card-sosial" class="card-misi p-4 fade-up" style="animation-delay: 0.6s;" onclick="bukaDetail('sosial')">
            <div class="flex items-center gap-4">
                <div class="bg-purple-100 text-purple-600 p-3 rounded-xl"><i data-lucide="users" class="w-6 h-6"></i></div>
                <div class="flex-1"><h3 class="font-bold text-slate-800">6. Bermasyarakat</h3><p class="text-[10px] text-slate-500" id="status-sosial">Belum diisi</p></div>
                <div class="check-circle"><i data-lucide="check" class="w-4 h-4"></i></div>
            </div>
            <div id="detail-sosial" class="hidden mt-3 pt-3 border-t border-slate-100" onclick="event.stopPropagation()">
                <select id="val-sosial" onchange="cekDropdown('sosial')" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs font-bold outline-none mb-2">
                    <option value="">- Pilih Kegiatan -</option><option value="Membantu Orang Tua">Membantu Orang Tua</option><option value="Gotong Royong">Gotong Royong</option><option value="Piket Kelas">Piket Kelas</option><option value="Membantu Teman">Membantu Teman</option><option value="Lainnya">Lainnya (Isi Sendiri)</option>
                </select>
                <input type="text" id="extra-sosial" onkeyup="validasi('sosial')" class="hidden-custom w-full bg-indigo-50 border border-indigo-200 rounded-lg p-3 text-xs font-bold text-indigo-700 placeholder-indigo-300" placeholder="Kegiatan apa...">
            </div>
        </div>

        <div id="card-tidur" class="card-misi p-4 fade-up" style="animation-delay: 0.7s;" onclick="bukaDetail('tidur')">
            <div class="flex items-center gap-4">
                <div class="bg-slate-100 text-slate-600 p-3 rounded-xl"><i data-lucide="moon" class="w-6 h-6"></i></div>
                <div class="flex-1"><h3 class="font-bold text-slate-800">7. Tidur Cepat</h3><p class="text-[10px] text-slate-500" id="status-tidur">Belum diisi</p></div>
                <div class="check-circle"><i data-lucide="check" class="w-4 h-4"></i></div>
            </div>
            <div id="detail-tidur" class="hidden mt-3 pt-3 border-t border-slate-100" onclick="event.stopPropagation()">
                <label class="text-[10px] font-bold text-slate-400 uppercase">Jam Tidur (Rencana/Kemarin):</label>
                <input type="time" id="val-tidur" onchange="validasi('tidur')" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm font-bold mt-1 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

    </div>

    <div class="fixed bottom-0 w-full p-6 bg-gradient-to-t from-white via-white to-transparent z-20">
        <button onclick="simpanMisi()" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white p-4 rounded-2xl font-black text-lg shadow-xl shadow-indigo-500/30 active:scale-95 transition flex items-center justify-center gap-2">
            <i data-lucide="send" class="w-5 h-5"></i> KLAIM POIN
        </button>
    </div>

    <div id="modalKamera" class="hidden fixed inset-0 z-50 bg-black flex flex-col">
        <div class="absolute top-4 w-full flex justify-between px-6 z-20 text-white">
            <button onclick="tutupKamera()" class="p-2 bg-black/50 rounded-full"><i data-lucide="x" class="w-6 h-6"></i></button>
            <button onclick="switchCamera()" class="p-2 bg-black/50 rounded-full"><i data-lucide="refresh-cw" class="w-6 h-6"></i></button>
        </div>
        <div class="flex-1 relative overflow-hidden"><video id="videoStream" autoplay playsinline class="w-full h-full object-cover"></video><canvas id="canvasFoto" class="hidden"></canvas></div>
        <div class="h-32 bg-black flex items-center justify-center"><button onclick="jepret()" class="w-16 h-16 rounded-full border-4 border-white flex items-center justify-center bg-white/20 active:scale-90 transition"><div class="w-12 h-12 bg-white rounded-full"></div></button></div>
    </div>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-50 bg-black/70 flex flex-col items-center justify-center backdrop-blur-sm"><div class="animate-spin rounded-full h-12 w-12 border-4 border-white border-t-transparent mb-4"></div><p class="text-white font-bold animate-pulse">Mengupload Data...</p></div>

    <script>
        const NISN = "<?php echo $nisn_session; ?>";
        let userSiswa = null; let scoreHariIni = 0; let streamKamera = null; let targetFoto = ""; let facingMode = "environment";
        let statusMisi = { bangun: false, ibadah: false, olahraga: false, makan: false, belajar: false, sosial: false, tidur: false };

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            // Ambil profil lengkap dari API (sama seperti di siswa.php)
            fetch(`api_siswa.php?action=get_profil&nisn=${NISN}`).then(r=>r.json()).then(d => {
                userSiswa = d.siswa;
                document.getElementById('nama-siswa').innerText = userSiswa.nama;
                if(userSiswa.foto) document.getElementById('foto-profil').src = userSiswa.foto;
                cekDataHariIni();
            });
        });

        window.bukaDetail = (id) => { const detail = document.getElementById(`detail-${id}`); const card = document.getElementById(`card-${id}`); if(detail.classList.contains('hidden')) { document.querySelectorAll('[id^="detail-"]').forEach(el => el.classList.add('hidden')); document.querySelectorAll('.card-misi').forEach(el => el.classList.remove('expanded')); detail.classList.remove('hidden'); card.classList.add('expanded'); } else { detail.classList.add('hidden'); card.classList.remove('expanded'); } }
        window.cekDropdown = (id) => { const val = document.getElementById(`val-${id}`).value; const extraInput = document.getElementById(`extra-${id}`); if(val === 'Lainnya') { extraInput.classList.remove('hidden-custom'); extraInput.focus(); } else { extraInput.classList.add('hidden-custom'); } validasi(id); }

        window.validasi = (id) => {
            let isValid = false; const card = document.getElementById(`card-${id}`); const statusTxt = document.getElementById(`status-${id}`);
            if(id === 'bangun' || id === 'tidur') { const val = document.getElementById(`val-${id}`).value; isValid = val !== ""; statusTxt.innerText = isValid ? `Jam: ${val}` : "Belum diisi"; } 
            else if(id === 'makan' || id === 'belajar') { const val = document.getElementById(`val-${id}`).value.trim(); const foto = document.getElementById(`foto-${id}`).value; const imgPreview = document.getElementById(`preview-${id}`).src; const adaFoto = foto !== "" || (imgPreview && imgPreview.includes('http')); isValid = val !== "" && adaFoto; statusTxt.innerText = isValid ? "Data Lengkap" : (val && !adaFoto ? "Kurang Foto" : "Belum Lengkap"); } 
            else { const selectVal = document.getElementById(`val-${id}`).value; if(selectVal === 'Lainnya') { const extraVal = document.getElementById(`extra-${id}`).value.trim(); isValid = extraVal !== ""; statusTxt.innerText = isValid ? extraVal : "Isi keterangan..."; } else { isValid = selectVal !== ""; statusTxt.innerText = isValid ? selectVal : "Belum dipilih"; } }

            if(isValid && !statusMisi[id]) { statusMisi[id] = true; card.classList.add('valid'); scoreHariIni++; } else if(!isValid && statusMisi[id]) { statusMisi[id] = false; card.classList.remove('valid'); scoreHariIni--; }
            updateProgressBar();
        }

        function updateProgressBar() { if(scoreHariIni < 0) scoreHariIni = 0; const pct = (scoreHariIni / 7) * 100; document.getElementById('bar-progress').style.width = pct + '%'; document.getElementById('text-progress').innerText = `${scoreHariIni}/7 Selesai`; document.getElementById('score-display').innerText = scoreHariIni; }

        window.simpanMisi = async () => {
            if (scoreHariIni <= 0) return Swal.fire('Masih Kosong', 'Kerjakan minimal 1 misi!', 'warning');
            document.getElementById('loadingOverlay').classList.remove('hidden');
            
            const getVal = (id) => { const s = document.getElementById(`val-${id}`).value; return s === 'Lainnya' ? document.getElementById(`extra-${id}`).value.trim() : s; }
            const getFoto = (id) => document.getElementById(`foto-${id}`).value;

            const payload = {
                action: 'simpan_hebat', nisn: NISN, nama: userSiswa.nama, kelas: userSiswa.kelas, poin_harian: scoreHariIni,
                bangun_pagi: statusMisi.bangun ? 1 : 0, jam_bangun: statusMisi.bangun ? getVal('bangun') : '-',
                beribadah: statusMisi.ibadah ? 1 : 0, jenis_ibadah: statusMisi.ibadah ? getVal('ibadah') : '-',
                olahraga: statusMisi.olahraga ? 1 : 0, jenis_olahraga: statusMisi.olahraga ? getVal('olahraga') : '-',
                makan_sehat: statusMisi.makan ? 1 : 0, menu_makan: statusMisi.makan ? getVal('makan') : '-', foto_makan: getFoto('makan'),
                gemar_belajar: statusMisi.belajar ? 1 : 0, mapel_belajar: statusMisi.belajar ? getVal('belajar') : '-', foto_belajar: getFoto('belajar'),
                bermasyarakat: statusMisi.sosial ? 1 : 0, keg_sosial: statusMisi.sosial ? getVal('sosial') : '-',
                tidur_cepat: statusMisi.tidur ? 1 : 0, jam_tidur: statusMisi.tidur ? getVal('tidur') : '-'
            };

            try {
                const res = await fetch('api_siswa.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) }).then(r => r.json());
                document.getElementById('loadingOverlay').classList.add('hidden');
                if (res.status === 'success') {
                    try { confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 } }); } catch(e){}
                    Swal.fire({ title: 'Hebat!', text: `Data berhasil disimpan.`, icon: 'success' });
                } else {
                    Swal.fire('Error', res.pesan, 'error');
                }
            } catch (e) {
                document.getElementById('loadingOverlay').classList.add('hidden');
                Swal.fire('Error', e.message, 'error');
            }
        }

        async function cekDataHariIni() {
            try {
                const d = await fetch(`api_siswa.php?action=cek_hebat&nisn=${NISN}`).then(r => r.json());
                if (d) {
                    const setVal = (id, val) => {
                        const sel = document.getElementById(`val-${id}`);
                        let found = false; for(let i=0; i<sel.options.length; i++) { if(sel.options[i].value === val) found = true; }
                        if(found) { sel.value = val; } else { sel.value = 'Lainnya'; cekDropdown(id); document.getElementById(`extra-${id}`).value = val; }
                    }

                    if(d.bangun_pagi == 1) { document.getElementById('val-bangun').value = d.jam_bangun; validasi('bangun'); }
                    if(d.beribadah == 1) { setVal('ibadah', d.jenis_ibadah); validasi('ibadah'); }
                    if(d.olahraga == 1) { setVal('olahraga', d.jenis_olahraga); validasi('olahraga'); }
                    if(d.makan_sehat == 1) { document.getElementById('val-makan').value = d.menu_makan; if(d.foto_makan && d.foto_makan.includes('http')) { document.getElementById('preview-makan').src = d.foto_makan; document.getElementById('preview-makan').classList.remove('hidden'); } validasi('makan'); }
                    if(d.gemar_belajar == 1) { document.getElementById('val-belajar').value = d.mapel_belajar; if(d.foto_belajar && d.foto_belajar.includes('http')) { document.getElementById('preview-belajar').src = d.foto_belajar; document.getElementById('preview-belajar').classList.remove('hidden'); } validasi('belajar'); }
                    if(d.bermasyarakat == 1) { setVal('sosial', d.keg_sosial); validasi('sosial'); }
                    if(d.tidur_cepat == 1) { document.getElementById('val-tidur').value = d.jam_tidur; validasi('tidur'); }
                }
            } catch(e) { console.log(e); }
        }

        // Camera Logic
        window.bukaKamera = (target) => { targetFoto = target; document.getElementById('modalKamera').classList.remove('hidden'); startCamera(); }
        function startCamera() { if (streamKamera) streamKamera.getTracks().forEach(t => t.stop()); navigator.mediaDevices.getUserMedia({ video: { facingMode: facingMode } }).then(s => { streamKamera = s; document.getElementById('videoStream').srcObject = s; }).catch(e => Swal.fire('Error', 'Izin kamera ditolak.', 'error')); }
        window.switchCamera = () => { facingMode = facingMode === "user" ? "environment" : "user"; startCamera(); }
        window.tutupKamera = () => { document.getElementById('modalKamera').classList.add('hidden'); if (streamKamera) streamKamera.getTracks().forEach(t => t.stop()); }
        window.jepret = () => { const v = document.getElementById('videoStream'); const c = document.getElementById('canvasFoto'); c.width=v.videoWidth; c.height=v.videoHeight; c.getContext('2d').drawImage(v,0,0); const d = c.toDataURL('image/jpeg', 0.7); document.getElementById(`preview-${targetFoto}`).src=d; document.getElementById(`preview-${targetFoto}`).classList.remove('hidden'); document.getElementById(`foto-${targetFoto}`).value=d; tutupKamera(); validasi(targetFoto); }
    </script>
</body>
</html>