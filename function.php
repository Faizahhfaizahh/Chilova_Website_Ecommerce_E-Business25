<?php 
include_once 'koneksi.php';

function registerUser($username, $password) {
    global $conn;

    // Cek username sudah digunakan belum
    $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        return "USERNAME_EXISTS";
    }

    // Enkripsi password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    $stmt->bind_param("ss", $username, $hashed);

    return $stmt->execute() ? "SUCCESS" : "FAILED";
}

function loginUser($username, $password) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("ss", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])){
            return $user;
        }
    }
        return false;
}

// Update profile user
function updateProfile($user_id, $username, $password = null) {
    global $conn;
    
    try {
        if ($password) {
            // Jika ada password baru, hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
        } else {
            // Jika tidak ada password baru, hanya update username
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
            $stmt->bind_param("si", $username, $user_id);
        }
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error updateProfile: " . $e->getMessage());
        return false;
    }
}

// Cek apakah username sudah digunakan oleh user lain
function isUsernameTaken($username, $current_user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->bind_param("si", $username, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// Ambil semua alamat user
function getUserAddresses($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM alamat WHERE user_id = ? ORDER BY is_default_alamat DESC, alamat_id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}


// Ambil alamat berdasarkan ID
function getAddressById($alamat_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM alamat WHERE alamat_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $alamat_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}


// Tambah alamat baru
function addAddress($user_id, $nama_penerima, $no_telepon, $alamat_lengkap, $provinsi, $kota, $kode_pos) {
    global $conn;

    $no_telepon = formatPhoneForDatabase($no_telepon);

    // Jika belum punya alamat → jadikan default otomatis
    $is_default_alamat = (getUserAddresses($user_id)->num_rows == 0) ? 1 : 0;

    if ($is_default_alamat) {
        unsetDefaultAddress($user_id);
    }

    $stmt = $conn->prepare("INSERT INTO alamat
        (user_id, nama_penerima, no_telepon, alamat_lengkap, provinsi, kota, kode_pos, is_default_alamat)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $user_id, $nama_penerima, $no_telepon, $alamat_lengkap, $provinsi, $kota, $kode_pos, $is_default_alamat);

    return $stmt->execute();
}


// Update / Edit alamat
function updateAddress($alamat_id, $user_id, $nama_penerima, $no_telepon, $alamat_lengkap, $provinsi, $kota, $kode_pos) {
    global $conn;
    $stmt = $conn->prepare("UPDATE alamat SET 
        nama_penerima = ?, 
        no_telepon = ?, 
        alamat_lengkap = ?, 
        provinsi = ?, 
        kota = ?, 
        kode_pos = ?
        WHERE alamat_id = ? AND user_id = ?");
    $stmt->bind_param("ssssssii", $nama_penerima, $no_telepon, $alamat_lengkap, $provinsi, $kota, $kode_pos, $alamat_id, $user_id);

    return $stmt->execute();
}


// Hapus alamat
function deleteAddress($alamat_id, $user_id) {
    global $conn;

    // Cek apakah yang dihapus adalah default
    $address = getAddressById($alamat_id, $user_id);
    $isDefault = $address['is_default_alamat'];

    // Hapus alamat
    $stmt = $conn->prepare("DELETE FROM alamat WHERE alamat_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $alamat_id, $user_id);
    $result = $stmt->execute();

    // Jika default dihapus → set default yg terbaru
    if ($isDefault) {
        $newDefault = $conn->query("SELECT alamat_id FROM alamat WHERE user_id = $user_id ORDER BY alamat_id DESC LIMIT 1");
        if ($newDefault->num_rows > 0) {
            $row = $newDefault->fetch_assoc();
            setDefaultAddress($row['alamat_id'], $user_id);
        }
    }

    return $result;
}


// Set alamat default
function setDefaultAddress($alamat_id, $user_id) {
    global $conn;

    unsetDefaultAddress($user_id);

    $stmt = $conn->prepare("UPDATE alamat SET is_default_alamat = 1 WHERE alamat_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $alamat_id, $user_id);
    return $stmt->execute();
}


// Helper — Hapus default sebelumnya
function unsetDefaultAddress($user_id) {
    global $conn;
    $conn->query("UPDATE alamat SET is_default_alamat = 0 WHERE user_id = $user_id");
}


// Ambil alamat default user
function getDefaultAddress($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM alamat WHERE user_id = ? AND is_default_alamat = 1 LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

//  untuk memformat nomor telepon
function formatPhoneForDatabase($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (substr($phone, 0, 1) === '0') {
        $phone = substr($phone, 1);
    }
    
    if (substr($phone, 0, 2) !== '62') {
        $phone = '62' . $phone;
    }
    
    return $phone;
}

// untuk menampilkan nomor telepon
function displayPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Pastikan format 62
    if (substr($phone, 0, 1) === '0') {
        $phone = '62' . substr($phone, 1);
    } elseif (substr($phone, 0, 2) !== '62') {
        $phone = '62' . $phone;
    }
    
    // Format: +62 812 3456 7890
    $formatted = '+62 ' . substr($phone, 2, 3) . ' ' . substr($phone, 5, 4);
    if (strlen($phone) > 9) {
        $formatted .= ' ' . substr($phone, 9);
    }
    
    return trim($formatted);
}


?>