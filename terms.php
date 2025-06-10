<?php
// ayelectronic_store/terms.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';
?>
<title>Syarat & Ketentuan - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Syarat & Ketentuan']
    ]);
    ?>

    <div class="container my-5 fade-in-on-scroll">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-lg-5">
                        <h1 class="text-center mb-4 section-heading">Syarat & Ketentuan</h1>
                        <p class="text-center text-muted mb-5">Dengan menggunakan dan berbelanja di situs kami, Anda menyetujui syarat dan ketentuan berikut.</p>

                        <div class="accordion" id="termsAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        1. Ketentuan Umum
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#termsAccordion">
                                    <div class="accordion-body text-justify">
                                        Selamat datang di AYelectronic Store. Syarat dan ketentuan ini mengatur penggunaan Anda atas situs web kami. Dengan mengakses atau menggunakan situs ini, Anda setuju untuk terikat oleh syarat dan ketentuan ini. Jika Anda tidak setuju dengan bagian mana pun dari ketentuan ini, Anda tidak diperkenankan menggunakan situs web kami.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        2. Akun Pengguna
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#termsAccordion">
                                    <div class="accordion-body text-justify">
                                        Untuk mengakses beberapa fitur situs, Anda mungkin diminta untuk mendaftarkan akun. Anda bertanggung jawab untuk menjaga kerahasiaan informasi akun dan password Anda. Semua aktivitas yang terjadi di bawah akun Anda adalah tanggung jawab Anda sepenuhnya.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        3. Produk dan Harga
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#termsAccordion">
                                    <div class="accordion-body text-justify">
                                        Kami berusaha untuk menampilkan informasi produk, termasuk harga dan ketersediaan, seakurat mungkin. Namun, kami tidak menjamin bahwa semua deskripsi atau harga adalah 100% akurat, lengkap, atau bebas dari kesalahan. Kami berhak untuk memperbaiki kesalahan dan mengubah informasi kapan saja tanpa pemberitahuan sebelumnya. Semua harga tercantum dalam Rupiah (IDR).
                                    </div>
                                </div>
                            </div>
                             <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        4. Proses Pemesanan
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#termsAccordion">
                                    <div class="accordion-body text-justify">
                                        Semua pesanan yang dibuat melalui situs ini dianggap sebagai penawaran untuk membeli produk. Kami berhak untuk menerima atau menolak pesanan apa pun atas kebijakan kami. Konfirmasi pesanan akan dilakukan melalui WhatsApp setelah Anda menyelesaikan proses checkout. Pesanan dianggap final setelah konfirmasi dan pembayaran dilakukan.
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