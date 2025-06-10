<?php
// ayelectronic_store/my_profile.php (Versi Final, Ditulis Ulang dari Awal)

session_start();
ob_start();

// Keamanan: Wajib login
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['redirect_url'] = 'my_profile.php'; 
    header("Location: customer_login.php");
    exit();
}

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$customer_id = $_SESSION['customer_id'];

// Ambil data customer terbaru untuk ditampilkan di form
$stmt_get_user = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt_get_user->bind_param("i", $customer_id);
$stmt_get_user->execute();
$customer = $stmt_get_user->get_result()->fetch_assoc();
$stmt_get_user->close();

// --- LOGIKA PEMROSESAN FORM YANG DITULIS ULANG TOTAL ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // --- Ambil Data Form ---
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $nomor_hp = trim($_POST['nomor_hp']);
        $jenis_kelamin = $_POST['jenis_kelamin'] ?? null;
        $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
        $agama = trim($_POST['agama']);
        $alamat_jalan = trim($_POST['alamat_jalan']);
        $rt = trim($_POST['rt']); // <-- BARIS BARU
        $rw = trim($_POST['rw']); // <-- BARIS BARU
        $kelurahan = trim($_POST['kelurahan']);
        $kecamatan = trim($_POST['kecamatan']);
        $kota = trim($_POST['kota']);
        $provinsi = trim($_POST['provinsi']);
        $kode_pos = trim($_POST['kode_pos']);
        $maps_latitude = trim($_POST['maps_latitude']);
        $maps_longitude = trim($_POST['maps_longitude']);
        
        // --- Persiapan Query Update ---
        $sql_parts = [
            "nama_lengkap = ?", "email = ?", "nomor_hp = ?", "jenis_kelamin = ?", "tanggal_lahir = ?", "agama = ?", 
            "alamat_jalan = ?", "rt = ?", "rw = ?", "kelurahan = ?", "kecamatan = ?", "kota = ?", "provinsi = ?", "kode_pos = ?",
            "maps_latitude = ?", "maps_longitude = ?"
        ];
        $bind_params = [
            $nama_lengkap, $email, $nomor_hp, $jenis_kelamin, $tanggal_lahir, $agama,
            $alamat_jalan, $rt, $rw, $kelurahan, $kecamatan, $kota, $provinsi, $kode_pos,
            $maps_latitude, $maps_longitude
        ];
        $bind_types = "ssssssssssssssss"; // <-- 's' ditambahkan 2

        // --- Logika untuk Ganti Foto Profil (Tetap Sama) ---
        $foto_profil_path = $customer['foto_profil']; 
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'assets/img/profiles/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
            
            $file_info = pathinfo($_FILES['foto_profil']['name']);
            $file_extension = strtolower($file_info['extension']);
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($file_extension, $allowed_ext)) {
                throw new Exception('Tipe file foto tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP.');
            }

            $new_file_name = 'profile_' . $customer_id . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
                if (!empty($customer['foto_profil']) && strpos($customer['foto_profil'], 'default') === false && file_exists($customer['foto_profil'])) {
                    unlink($customer['foto_profil']);
                }
                $foto_profil_path = $target_file; 
            } else {
                throw new Exception('Gagal memindahkan file yang diunggah. Periksa izin folder (permission) pada "assets/img/profiles/".');
            }
        }
        $sql_parts[] = "foto_profil = ?";
        $bind_params[] = $foto_profil_path;
        $bind_types .= "s";

        // --- Logika untuk Ubah Password (Tetap Sama) ---
        $password_baru = $_POST['password_baru'] ?? '';
        $password_lama = $_POST['password_lama'] ?? '';
        if (!empty($password_baru)) {
            if (empty($password_lama)) {
                throw new Exception('Untuk mengubah password, Anda harus memasukkan password lama.');
            }
            if ($password_baru !== ($_POST['konfirmasi_password'] ?? '')) {
                throw new Exception('Password baru dan konfirmasi tidak cocok.');
            }
            if (!password_verify($password_lama, $customer['password'])) {
                throw new Exception('Password lama yang Anda masukkan salah.');
            }
            $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            $sql_parts[] = "password = ?";
            $bind_params[] = $hashed_password_baru;
            $bind_types .= "s";
        }
        
        // --- Gabungkan dan Jalankan Query UPDATE ---
        $sql = "UPDATE customers SET " . implode(", ", $sql_parts) . " WHERE id = ?";
        $bind_params[] = $customer_id;
        $bind_types .= "i";
        
        $stmt_update = $conn->prepare($sql);
        $stmt_update->bind_param($bind_types, ...$bind_params);
        
        if (!$stmt_update->execute()) {
            throw new Exception('Gagal memperbarui profil di database.');
        }

        $conn->commit();
        $_SESSION['notification'] = ['message' => 'Profil berhasil diperbarui.', 'type' => 'success'];
        $_SESSION['customer_name'] = $nama_lengkap;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['notification'] = ['message' => $e->getMessage(), 'type' => 'danger'];
    }

    header("Location: my_profile.php");
    exit();
}

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';



?>
<title>Profil Saya - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php generate_breadcrumb([['text' => 'Beranda', 'url' => 'index.php'], ['text' => 'Profil Saya']]); ?>

    <div class="container my-5 ">
        <h1 class="text-center mb-5 section-heading">Profil Saya</h1>
        <div class="row justify-content-center fade-in-on-scroll">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-lg-5">
                        <?php if (!empty($notification_message)): ?>
                            <div class="alert alert-<?= htmlspecialchars($notification_type) ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($notification_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="my_profile.php" method="POST" id="profileForm" enctype="multipart/form-data">
                            
                            <h5 class="mt-4">Ubah Password</h5>
                        <hr></hr>
                            <p class="small text-muted">Kosongkan bagian ini jika Anda tidak ingin mengubah password.</p>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="password_lama" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="password_lama" name="password_lama" disabled>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="password_baru" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="password_baru" name="password_baru" disabled>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" disabled>
                                </div>
                            </div>
                            <h5>Informasi Personal</h5>
                            <hr>
                            <div class="row">
                                 <div class="col-md-3 text-center">
                                    <img src="<?= htmlspecialchars($customer['foto_profil'] ?? 'assets/img/profiles/default-profiles.png') ?>" id="profile-pic-preview" class="img-thumbnail rounded-circle mb-2" style="width: 150px; height: 150px; object-fit: cover;">
                                    <label for="foto_profil" class="form-label small">Ganti Foto Profil</label>
                                    <input type="file" class="form-control form-control-sm" id="foto_profil" name="foto_profil" disabled>
                                </div>
                                <div class="col-md6 mb-3"></div>
                                <div class="col-md-6 mb-3"><label for="nama_lengkap" class="form-label">Nama Lengkap</label><input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($customer['nama_lengkap'] ?? '') ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label for="nomor_hp" class="form-label">Nomor HP</label><input type="tel" class="form-control" id="nomor_hp" name="nomor_hp" value="<?= htmlspecialchars($customer['nomor_hp'] ?? '') ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label for="tanggal_lahir" class="form-label">Tanggal Lahir</label><input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= htmlspecialchars($customer['tanggal_lahir'] ?? '') ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label for="jenis_kelamin" class="form-label">Jenis Kelamin</label><select class="form-select" id="jenis_kelamin" name="jenis_kelamin" disabled><option value="">- Pilih -</option><option value="Laki-laki" <?= ($customer['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option><option value="Perempuan" <?= ($customer['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option></select></div>
                                <div class="col-md-6 mb-3"><label for="agama" class="form-label">Agama</label><input type="text" class="form-control" id="agama" name="agama" value="<?= htmlspecialchars($customer['agama'] ?? '') ?>" readonly></div>
                            </div>

                            <h5 class="mt-4">Informasi Alamat</h5>
                            <hr>
                            <div class="mb-3"><label for="alamat_jalan" class="form-label">Alamat Jalan</label><textarea class="form-control" id="alamat_jalan" name="alamat_jalan" rows="2" readonly><?= htmlspecialchars($customer['alamat_jalan'] ?? '') ?></textarea></div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rt" class="form-label">RT</label>
                                    <input type="text" class="form-control" id="rt" name="rt" value="<?= htmlspecialchars($customer['rt'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="rw" class="form-label">RW</label>
                                    <input type="text" class="form-control" id="rw" name="rw" value="<?= htmlspecialchars($customer['rw'] ?? '') ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label for="kelurahan" class="form-label">Kelurahan/Desa</label><input type="text" class="form-control" id="kelurahan" name="kelurahan" value="<?= htmlspecialchars($customer['kelurahan'] ?? '') ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label for="kecamatan" class="form-label">Kecamatan</label><input type="text" class="form-control" id="kecamatan" name="kecamatan" value="<?= htmlspecialchars($customer['kecamatan'] ?? '') ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label for="kota" class="form-label">Kota/Kabupaten</label><input type="text" class="form-control" id="kota" name="kota" value="<?= htmlspecialchars($customer['kota'] ?? '') ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label for="provinsi" class="form-label">Provinsi</label><input type="text" class="form-control" id="provinsi" name="provinsi" value="<?= htmlspecialchars($customer['provinsi'] ?? '') ?>" readonly></div>
                                <div class="col-md-6 mb-3"><label for="kode_pos" class="form-label">Kode Pos</label><input type="text" class="form-control" id="kode_pos" name="kode_pos" value="<?= htmlspecialchars($customer['kode_pos'] ?? '') ?>" readonly></div>
                            </div>
                            
                            <h5 class="mt-4">Titik Lokasi Peta</h5>
                                <hr>
                                <p class="small text-muted">Klik tombol "Edit Profil" lalu klik pada peta untuk menentukan lokasi Anda, atau geser penanda yang ada.</p>

                                <div id="map" style="height: 400px; width: 100%; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 1rem; background-color: #e9e9e9;"></div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="maps_latitude" class="form-label">Latitude</label>
                                        <input type="text" class="form-control" id="maps_latitude" name="maps_latitude" value="<?= htmlspecialchars($customer['maps_latitude'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="maps_longitude" class="form-label">Longitude</label>
                                        <input type="text" class="form-control" id="maps_longitude" name="maps_longitude" value="<?= htmlspecialchars($customer['maps_longitude'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                            
                            <div class="d-flex justify-content-end mt-4" id="form-buttons">
                                <button type="button" class="btn btn-primary" id="edit-btn">Edit Profil</button>
                                <button type="submit" class="btn btn-success d-none" id="save-btn">Simpan Perubahan</button>
                                <button type="button" class="btn btn-secondary ms-2 d-none" id="cancel-btn">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
$conn->close();
ob_end_flush();
?>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBP_v0pJa5ap_oIga9vnb8DoMPqj5DgOBU&callback=initMap" async defer></script>

<script>
    // Variabel global untuk peta dan marker agar bisa diakses oleh fungsi initMap
    let map;
    let marker;
    const latInput = document.getElementById('maps_latitude');
    const lonInput = document.getElementById('maps_longitude');
    
    // Fungsi ini akan dipanggil oleh Google Maps API setelah script-nya selesai dimuat.
    // Kita bisa biarkan kosong karena logika utama ada di dalam DOMContentLoaded.
    function initMap() {
        // Google Maps API is ready.
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Ambil elemen tombol
        const editBtn = document.getElementById('edit-btn');
        const saveBtn = document.getElementById('save-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const formInputs = document.querySelectorAll('#profileForm input, #profileForm textarea, #profileForm select');
        const fotoProfilInput = document.getElementById('foto_profil');
        const profilePicPreview = document.getElementById('profile-pic-preview');
        
        // Simpan nilai awal untuk fungsi "Batal"
        const initialValues = {};
        formInputs.forEach(input => {
            if (input.type !== 'file') initialValues[input.id] = input.value;
        });
        const initialImageSrc = profilePicPreview.src;

        // Fungsi untuk menginisialisasi peta Google Maps
        function setupGoogleMap() {
            const initialLat = parseFloat(latInput.value) || -2.976073; // Default Palembang
            const initialLon = parseFloat(lonInput.value) || 104.775430; // Default Palembang
            const initialPosition = { lat: initialLat, lng: initialLon };
            const hasInitialCoords = !!(latInput.value && lonInput.value);

            // Buat Peta baru
            map = new google.maps.Map(document.getElementById('map'), {
                center: initialPosition,
                zoom: hasInitialCoords ? 15 : 6,
                mapTypeControl: false, 
                streetViewControl: false 
            });

            // Buat Marker baru
            marker = new google.maps.Marker({
                position: initialPosition,
                map: map,
                draggable: true 
            });
            
            // Fungsi untuk update input
            function updateInputs(latLng) {
                latInput.value = latLng.lat().toFixed(6);
                lonInput.value = latLng.lng().toFixed(6);
            }

            // Tambah event listener saat marker selesai digeser
            google.maps.event.addListener(marker, 'dragend', function(event) {
                updateInputs(event.latLng);
            });

            // Tambah event listener saat peta diklik
            google.maps.event.addListener(map, 'click', function(event) {
                marker.setPosition(event.latLng);
                updateInputs(event.latLng);
            });
        }

        editBtn.addEventListener('click', function() {
            formInputs.forEach(input => {
                input.readOnly = false;
                input.disabled = false;
            });
            latInput.readOnly = true;
            lonInput.readOnly = true;

            editBtn.classList.add('d-none');
            saveBtn.classList.remove('d-none');
            cancelBtn.classList.remove('d-none');
            
            // Panggil fungsi untuk setup peta
            setupGoogleMap();
        });

        cancelBtn.addEventListener('click', function() {
            formInputs.forEach(input => {
                input.readOnly = true;
                input.disabled = true;
                if(input.type !== 'file') input.value = initialValues[input.id];
                else input.value = '';
            });
            profilePicPreview.src = initialImageSrc;

            editBtn.classList.remove('d-none');
            saveBtn.classList.add('d-none');
            cancelBtn.classList.add('d-none');
            
            // Kosongkan div peta
            document.getElementById('map').innerHTML = '';
        });

        // Preview foto profil (tetap sama)
        fotoProfilInput.addEventListener('change', function(event) {
            if (event.target.files && event.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicPreview.src = e.target.result;
                }
                reader.readAsDataURL(event.target.files[0]);
            }
        });
    });
</script>