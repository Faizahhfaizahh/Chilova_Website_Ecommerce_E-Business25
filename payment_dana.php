<?php
session_start();
require "koneksi.php";
require "function.php";

if(!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$order_id = $_GET['order_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Ambil data order
$query = "SELECT 
    o.order_id,
    o.order_number,
    o.total_harga,
    o.status as order_status,
    o.metode_pembayaran
FROM orders o
WHERE o.order_id = $order_id 
AND o.user_id = $user_id";

$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

// Validasi order
if(!$order) {
    $_SESSION['error'] = "Order tidak ditemukan";
    header("location: diproses.php");
    exit;
}

// Validasi status dan metode pembayaran
if($order['order_status'] != 'Menunggu Pembayaran' && $order['order_status'] != 'Menunggu Dibayar') {
    $_SESSION['error'] = "Order sudah diproses atau bukan status menunggu pembayaran";
    header("location: diproses.php");
    exit;
}

$metode = strtolower($order['metode_pembayaran'] ?? '');
if(!in_array($metode, ['dana', 'danay', 'dang'])) {
    $_SESSION['error'] = "Metode pembayaran bukan DANA";
    header("location: diproses.php");
    exit;
}

// Jika tombol upload ditekan
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $upload_dir = "uploads/bukti_pembayaran/";
    if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    
    $filename = "DANA_" . $order['order_number'] . "_" . time();
    $file_ext = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
    $target_file = $upload_dir . $filename . "." . $file_ext;
    
    // Validasi file
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if(!in_array(strtolower($file_ext), $allowed_types)) {
        $error = "Format file tidak didukung. Gunakan JPG, PNG, atau PDF.";
    } elseif($_FILES['bukti_pembayaran']['size'] > $max_size) {
        $error = "Ukuran file terlalu besar. Maksimal 5MB.";
    } elseif(move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
        // Update status order
        mysqli_query($conn, "UPDATE orders SET status = 'Menunggu Verifikasi' WHERE order_id = $order_id");
        
        // Simpan bukti dan catatan
        $bukti_path = mysqli_real_escape_string($conn, $target_file);
        $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
        
        // Gunakan 'Dang' karena itu yang ada di enum tabel payments
        $metode_pembayaran = 'Dang';
        
        // Cek apakah sudah ada payment untuk order ini
        $check_query = mysqli_query($conn, "SELECT * FROM payments WHERE order_id = $order_id");
        
        if(mysqli_num_rows($check_query) > 0) {
            // Update existing payment
            $update_query = "UPDATE payments SET 
                proof_image = '$bukti_path',
                status = 'menunggu verifikasi',
                catatan = '$catatan',
                metode_pembayaran = '$metode_pembayaran'
                WHERE order_id = $order_id";
        } else {
            // Insert new payment
            $update_query = "INSERT INTO payments (
                order_id, 
                user_id, 
                metode_pembayaran, 
                status, 
                proof_image, 
                catatan, 
                created_at
            ) VALUES (
                $order_id, 
                $user_id, 
                '$metode_pembayaran', 
                'menunggu verifikasi', 
                '$bukti_path', 
                '$catatan', 
                NOW()
            )";
        }
        
        mysqli_query($conn, $update_query);
        
        $_SESSION['success'] = "Bukti pembayaran berhasil diupload! Admin akan memverifikasi dalam 1x24 jam.";
        header("location: diproses.php");
        exit;
    } else {
        $error = "Gagal mengupload file. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran via DANA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .payment-guide {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .step-card {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 10px;
            font-weight: bold;
        }
        .dana-info {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .upload-area {
            border: 2px dashed #007bff;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        .upload-area:hover {
            background: #e9ecef;
            border-color: #0056b3;
        }
        .upload-area.dragover {
            background: #007bff20;
            border-color: #0056b3;
        }
        .file-preview {
            max-width: 300px;
            margin: 20px auto;
        }
        .file-preview img {
            max-width: 100%;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body style="background: #f8f9fa; min-height: 100vh;">

<div class="container py-4">
    <div class="payment-guide">
        <!-- Header -->
        <div class="text-center mb-4">
            <img src="images/logo-dana.png" alt="DANA" height="40" class="mb-3">
            
            <h2 class="fw-bold">Pembayaran via DANA</h2>
            <p class="text-muted">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
            
            <div class="alert alert-info">
                <h5 class="mb-1">Total Pembayaran</h5>
                <h2 class="text-danger fw-bold">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></h2>
            </div>
        </div>

        <!-- Info Nomor DANA -->
        <div class="dana-info text-center">
            <h4><i class="bi bi-phone me-2"></i> Kirim ke Nomor DANA:</h4>
            <h1 class="fw-bold mb-3">0877-4577-0076</h1>
            <p class="mb-0">a/n <strong>CHILOVA</strong></p>
            <p class="mb-0">Pastikan nomor dan nama penerima sudah benar!</p>
        </div>

        <!-- Alert Penting untuk Keterangan -->
        <div class="alert alert-warning">
            <h5><i class="bi bi-exclamation-triangle-fill"></i> INSTRUKSI PENTING!</h5>
            <p class="mb-1">Saat transfer di DANA, <strong>WAJIB tulis di kolom catatan:</strong></p>
            <div class="bg-light p-3 my-2 rounded">
                <code class="fs-5 text-danger">ORDER_<?php echo $order['order_number']; ?></code>
            </div>
            <p class="mb-0 text-danger">
                <i class="bi bi-x-circle"></i> Tanpa keterangan ini, pembayaran TIDAK akan diverifikasi!
            </p>
        </div>

        <!-- Petunjuk Pembayaran -->
        <div class="mb-5">
            <h4 class="mb-3"><i class="bi bi-list-ol text-primary me-2"></i> Langkah-langkah Pembayaran:</h4>
            
            <div class="step-card">
                <div class="d-flex align-items-start mb-3">
                    <span class="step-number">1</span>
                    <div>
                        <h5 class="fw-bold mb-1">Buka Aplikasi DANA</h5>
                        <p class="mb-0">Pastikan saldo DANA Anda cukup atau gunakan metode pembayaran lain di DANA.</p>
                    </div>
                </div>
            </div>
            
            <div class="step-card">
                <div class="d-flex align-items-start mb-3">
                    <span class="step-number">2</span>
                    <div>
                        <h5 class="fw-bold mb-1">Pilih "Kirim Uang"</h5>
                        <p class="mb-0">Di halaman utama DANA, pilih menu "Kirim Uang".</p>
                    </div>
                </div>
            </div>
            
            <div class="step-card">
                <div class="d-flex align-items-start mb-3">
                    <span class="step-number">3</span>
                    <div>
                        <h5 class="fw-bold mb-1">Masukkan Nomor DANA</h5>
                        <p class="mb-0">Masukkan nomor: <strong>0877-4577-0076</strong></p>
                        <p class="mb-0">Pastikan nama penerima: <strong>QHAULAN SYAQILA</strong></p>
                    </div>
                </div>
            </div>
            
            <div class="step-card">
                <div class="d-flex align-items-start mb-3">
                    <span class="step-number">4</span>
                    <div>
                        <h5 class="fw-bold mb-1">Masukkan Jumlah Transfer</h5>
                        <p class="mb-0">Masukkan nominal: <strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></p>
                        <p class="mb-0 text-danger">Harus tepat sesuai total order!</p>
                    </div>
                </div>
            </div>
            
            <div class="step-card">
                <div class="d-flex align-items-start mb-3">
                    <span class="step-number">5</span>
                    <div>
                        <h5 class="fw-bold mb-1">Tambahkan Keterangan</h5>
                        <p class="mb-0">Di kolom catatan/pesan, tulis: <strong>ORDER_<?php echo htmlspecialchars($order['order_number']); ?></strong></p>
                        <p class="mb-0 text-danger">Tanpa ini, pembayaran TIDAK akan diproses!</p>
                    </div>
                </div>
            </div>
            
            <div class="step-card">
                <div class="d-flex align-items-start mb-3">
                    <span class="step-number">6</span>
                    <div>
                        <h5 class="fw-bold mb-1">Konfirmasi Pembayaran</h5>
                        <p class="mb-0">Cek kembali semua data, lalu konfirmasi pembayaran.</p>
                        <p class="mb-0">Simpan bukti transfer/screenshot.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Upload Bukti -->
        <div class="mb-4">
            <h4 class="mb-3"><i class="bi bi-upload text-success me-2"></i> Upload Bukti Pembayaran</h4>
            <p class="text-muted mb-4">Setelah transfer selesai, upload bukti pembayaran untuk diverifikasi admin.</p>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-area" id="dropArea" onclick="document.getElementById('fileInput').click()">
                    <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3"></i>
                    <h5 class="fw-bold">Klik atau drag file ke sini</h5>
                    <p class="text-muted mb-2">Format: JPG, PNG, PDF (maks. 5MB)</p>
                    <p class="text-muted small">Screenshot bukti transfer dari aplikasi DANA</p>
                    
                    <input type="file" name="bukti_pembayaran" id="fileInput" class="d-none" 
                           accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="previewFile(event)">
                </div>
                
                <!-- File Preview -->
                <div class="file-preview text-center" id="filePreview" style="display: none;">
                    <h6 class="fw-bold mb-2">Preview:</h6>
                    <img id="previewImage" src="#" alt="Preview" style="display: none;">
                    <div id="previewPdf" style="display: none;">
                        <i class="bi bi-file-earmark-pdf display-1 text-danger"></i>
                        <p id="fileName" class="mt-2"></p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeFile()">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
                
                <!-- Keterangan -->
                <div class="mt-4">
                    <label class="form-label fw-bold">Catatan Tambahan:</label>
                    <textarea name="catatan" class="form-control" rows="3" required 
                              placeholder="Tuliskan Nomor Order Mu, contoh: ORDER_<?php echo htmlspecialchars($order['order_number']); ?>"></textarea>
                </div>
                
                <!-- Tombol Submit -->
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                        <i class="bi bi-check-circle me-2"></i> Kirim Bukti Pembayaran
                    </button>
                    <a href="diproses.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>

        <!-- Informasi Penting -->
        <div class="alert alert-warning">
            <h5><i class="bi bi-exclamation-triangle me-2"></i> Penting!</h5>
            <ul class="mb-0">
                <li>Pembayaran akan diverifikasi manual oleh admin dalam 1x24 jam</li>
                <li>Pastikan nominal transfer sesuai total order</li>
                <li>Status order akan berubah menjadi "Menunggu Konfirmasi" setelah upload bukti</li>
                <li>Hubungi WhatsApp 0877-4577-0076 jika ada kendala</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Drag & Drop functionality
const dropArea = document.getElementById('dropArea');
const fileInput = document.getElementById('fileInput');
const filePreview = document.getElementById('filePreview');
const previewImage = document.getElementById('previewImage');
const previewPdf = document.getElementById('previewPdf');
const fileName = document.getElementById('fileName');
const submitBtn = document.getElementById('submitBtn');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    dropArea.classList.add('dragover');
}

function unhighlight() {
    dropArea.classList.remove('dragover');
}

dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    if(files.length > 0) {
        fileInput.files = files;
        previewFile({ target: { files: files } });
    }
}

function previewFile(event) {
    const file = event.target.files[0];
    if(!file) return;
    
    const fileType = file.type;
    const fileSize = file.size / 1024 / 1024; // MB
    
    // Validasi ukuran
    if(fileSize > 5) {
        alert('Ukuran file terlalu besar! Maksimal 5MB');
        return;
    }
    
    // Tampilkan preview
    filePreview.style.display = 'block';
    fileName.textContent = file.name;
    
    if(fileType.startsWith('image/')) {
        previewImage.style.display = 'block';
        previewPdf.style.display = 'none';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
        }
        reader.readAsDataURL(file);
    } else if(fileType === 'application/pdf') {
        previewImage.style.display = 'none';
        previewPdf.style.display = 'block';
    }
    
    // Aktifkan tombol submit
    submitBtn.disabled = false;
}

function removeFile() {
    fileInput.value = '';
    filePreview.style.display = 'none';
    previewImage.src = '#';
    previewImage.style.display = 'none';
    previewPdf.style.display = 'none';
    submitBtn.disabled = true;
}

// Konfirmasi sebelum submit
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    if(!fileInput.files[0]) {
        e.preventDefault();
        alert('Silakan pilih file bukti pembayaran terlebih dahulu!');
        return;
    }
    
    // Validasi catatan
    const catatan = document.querySelector('textarea[name="catatan"]');
    if(!catatan.value.trim()) {
        e.preventDefault();
        alert('Harap isi catatan pembayaran!');
        catatan.focus();
        return;
    }
    
    if(confirm('Apakah Anda yakin ingin mengirim bukti pembayaran? Pastikan data sudah benar.')) {
        // Tampilkan loading
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
        submitBtn.disabled = true;
    } else {
        e.preventDefault();
    }
});

// Auto focus ke drop area jika file di-paste
document.addEventListener('paste', function(e) {
    if(e.clipboardData.files.length > 0) {
        fileInput.files = e.clipboardData.files;
        previewFile({ target: { files: e.clipboardData.files } });
        dropArea.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>
</body>
</html>