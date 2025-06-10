<?php
// ayelectronic_store/privacy_policy.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';
?>
<title>Kebijakan Privasi - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Kebijakan Privasi']
    ]);
    ?>

    <div class="container my-5 fade-in-on-scroll">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-lg-5">
                        <h1 class="text-center mb-4 section-heading">Kebijakan Privasi</h1>
                        <p class="text-center text-muted mb-5">Terakhir diperbarui: <?= date('d F Y') ?>. Privasi Anda penting bagi kami.</p>

                        <div class="accordion" id="privacyAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Informasi yang Kami Kumpulkan
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#privacyAccordion">
                                    <div class="accordion-body text-justify">
                                        Kami mengumpulkan informasi yang Anda berikan secara langsung saat mendaftar atau melakukan pemesanan. Ini termasuk nama lengkap, alamat email, nomor telepon, dan alamat pengiriman. Informasi ini diperlukan untuk memproses pesanan Anda dan berkomunikasi dengan Anda.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Bagaimana Kami Menggunakan Informasi Anda
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#privacyAccordion">
                                    <div class="accordion-body text-justify">
                                        Informasi pribadi yang kami kumpulkan digunakan untuk tujuan berikut:
                                        <ul>
                                            <li>Memproses dan mengirimkan pesanan Anda.</li>
                                            <li>Mengirimkan konfirmasi pesanan dan pembaruan status pengiriman.</li>
                                            <li>Menjawab pertanyaan dan memberikan dukungan pelanggan.</li>
                                            <li>Mengirimkan informasi promosi atau buletin jika Anda memilih untuk menerimanya.</li>
                                        </ul>
                                        Kami tidak akan membagikan informasi pribadi Anda kepada pihak ketiga tanpa persetujuan Anda, kecuali sebagaimana diwajibkan oleh hukum.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        Keamanan Data
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#privacyAccordion">
                                    <div class="accordion-body text-justify">
                                        Kami menerapkan langkah-langkah keamanan yang wajar untuk melindungi informasi Anda dari akses, penggunaan, atau pengungkapan yang tidak sah. Password akun pelanggan disimpan dalam format terenkripsi (hashed) untuk keamanan tambahan.
                                    </div>
                                </div>
                            </div>
                             <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        Hubungi Kami
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#privacyAccordion">
                                    <div class="accordion-body text-justify">
                                        Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami melalui halaman <a href="contact.php">Kontak</a> kami.
                                    </div>
                                </div>
                            </div>
                        </div>

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