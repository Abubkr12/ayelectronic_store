<?php
// ayelectronic_store/contact.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';
?>
<title>Hubungi Kami - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Hubungi Kami']
    ]);
    ?>

    <div class="container my-5">
        <div class="text-center mb-5">
             <h1 class="section-heading">Hubungi Kami</h1>
             <p class="lead text-secondary">Kami siap membantu Anda. Pilih salah satu cara di bawah ini untuk terhubung dengan kami.</p>
        </div>

        <div class="row g-5 fade-in-on-scroll">
            <div class="col-lg-5">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="mb-4 text-primary-futuristic">Informasi Kontak</h4>
                        <ul class="list-unstyled contact-info-list">
                            <li class="d-flex align-items-start mb-3 text-justify">
                                <i class="bi bi-geo-alt-fill fs-4 me-3 mt-1"></i>
                                <div>
                                    <strong>Alamat:</strong><br>
                                    <a href="https://maps.app.goo.gl/NSSKiaAuizeV4cBC9">  Jl. Yakub No. 21F, RT.004/RW.008, Kel. Sukabumi Utara, Kec. Kebon Jeruk, Jakarta Barat, DKI Jakarta, Indonesia, 11540</a>
                                </div>
                            </li>
                            <li class="d-flex align-items-start mb-3">
                                <i class="bi bi-envelope-fill fs-4 me-3 mt-1"></i>
                                <div>
                                    <strong>Email:</strong><br>
                                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=bakaradny6325@gmail.com" target="_blank" rel="noopener noreferrer">bakaradny6325@gmail.com</a>
                                </div>
                            </li>
                            <li class="d-flex align-items-start mb-4">
                                <i class="bi bi-whatsapp fs-4 me-3 mt-1"></i>
                                <div>
                                    <strong>WhatsApp:</strong><br>
                                    <a href="https://wa.me/6285156586974" target="_blank">0851-5658-6974</a>
                                </div>
                            </li>
                        </ul>
                        <hr>
                        <h5 class="mb-3">Temukan Kami di Media Sosial</h5>
                        <div class="d-flex fs-3 social-icons-contact">
                            <a href="https://www.facebook.com/abu.aladny.1/" class="me-3" title="Facebook"><i class="bi bi-facebook"></i></a>
                            <a href="https://www.instagram.com/abubackr._/" class="me-3" title="Instagram"><i class="bi bi-instagram"></i></a>
                            <a href="https://www.tiktok.com/@abubkr_12" class="me-3" title="TikTok"><i class="bi bi-tiktok"></i></a>
                            <a href="https://t.me/abubkr12" class="me-3" title="Telegram"><i class="bi bi-telegram"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                 <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="mb-4 text-primary-futuristic">Kirim Pesan kepada Kami</h4>
                            <div class="container">
                                <?php if (!empty($notification_message)): ?>
                                    <div class="alert alert-<?= htmlspecialchars($notification_type) ?> alert-dismissible fade show mt-3" role="alert">
                                      <?= htmlspecialchars($notification_message) ?>
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        
                        <form action="contact_action.php" method="POST">
                             <div class="mb-3">
                                <label for="contact-name" class="form-label">Nama Anda</label>
                                <input type="text" class="form-control" id="contact-name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact-email" class="form-label">Email Anda</label>
                                <input type="email" class="form-control" id="contact-email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact-subject" class="form-label">Subjek</label>
                                <input type="text" class="form-control" id="contact-subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="contact-message" class="form-label">Pesan</label>
                                <textarea class="form-control" id="contact-message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Kirim Pesan</button>
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