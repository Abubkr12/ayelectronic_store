<?php
// ayelectronic_store/faq.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';

// Ambil semua data FAQ yang aktif dari database, diurutkan berdasarkan kolom 'urutan'
$sql = "SELECT pertanyaan, jawaban FROM faq WHERE aktif = 1 ORDER BY urutan ASC, id ASC";
$faqs = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<title>FAQ (Frequently Asked Questions) - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'FAQ']
    ]);
    ?>

    <div class="container my-5 fade-in-on-scroll">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                     <h1 class="section-heading">Frequently Asked Questions (FAQ)</h1>
                     <p class="lead text-secondary">Temukan jawaban untuk pertanyaan yang paling sering diajukan seputar layanan kami.</p>
                </div>

                <div class="accordion" id="faqAccordion">
                    <?php if (!empty($faqs)): foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-<?= $index ?>">
                                <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse-<?= $index ?>">
                                    <?= htmlspecialchars($faq['pertanyaan']) ?>
                                </button>
                            </h2>
                            <div id="collapse-<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading-<?= $index ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-justify">
                                    <?= nl2br(htmlspecialchars($faq['jawaban'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <p class="text-center text-muted">Belum ada FAQ yang tersedia saat ini.</p>
                    <?php endif; ?>
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