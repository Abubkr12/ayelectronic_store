<?php
// ayelectronic_store/about.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';
?>
<title>Tentang Kami - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Tentang Kami']
    ]);
    ?>

    <div class="container my-5 fade-in-on-scroll">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-lg-5">
                        <h1 class="text-center mb-4 section-heading">Tentang AYelectronic Store</h1>
                        <p class="lead text-center text-secondary mb-5 text-justify">
                            Destinasi utama Anda untuk gadget dan elektronik futuristik berkualitas tinggi. Kami lebih dari sekadar toko; kami adalah partner Anda dalam menjelajahi dunia teknologi.
                        </p>
                        
                        <hr class="my-5">

                        <div class="row align-items-center mb-5">
                            <div class="col-md-6">
                                <h3>Visi Kami</h3>
                                <p class="text-muted text-justify">Menciptakan pengalaman berbelanja online yang interaktif dan memukau bagi setiap pelanggan. Kami membayangkan sebuah platform yang tidak hanya memamerkan produk terbaru, tetapi juga merangkum esensi inovasi dan desain modern melalui setiap interaksi.</p>
                                <p class="text-muted text-justify">Kami berkomitmen untuk memberikan pengalaman belanja yang memukau dengan produk berkualitas tinggi dan layanan pelanggan yang ramah.</p>
                            </div>
                            <div class="col-md-6 text-center">
                                <i class="bi bi-rocket-takeoff-fill text-primary-futuristic" style="font-size: 8rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                        
                        <div class="row align-items-center">
                             <div class="col-md-6 text-center order-md-2">
                                <i class="bi bi-shield-check text-primary-futuristic" style="font-size: 8rem; opacity: 0.5;"></i>
                            </div>
                            <div class="col-md-6 order-md-1">
                                <h3>Misi & Komitmen</h3>
                                <p class="text-muted text-justify">Untuk mewujudkan visi tersebut, kami berkomitmen untuk:</p>
                                <ul class="list-unstyled text-muted text-justify">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Menyediakan produk pilihan yang inovatif dan berkualitas.</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Menghadirkan teknologi terbaru dengan harga yang kompetitif.</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Memberikan pelayanan pelanggan yang cepat, ramah, dan solutif.</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Menawarkan pengalaman berbelanja yang aman dan terpercaya.</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Terus berinovasi dan beradaptasi dengan perkembangan teknologi dan kebutuhan pelanggan.</li>
                                </ul>
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