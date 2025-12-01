<?php 
include_once 'koneksi.php';

function registerUser($username, $password) {
    global $conn;

    // Cek username sudah digunakan belum
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        return "USERNAME_EXISTS";
    }

    // Enkripsi password
    $hashed = hash("sha256", $password);

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    $stmt->bind_param("ss", $username, $hashed);

    return $stmt->execute() ? "SUCCESS" : "FAILED";
}

function loginUser($username, $password) {
    global $conn;

    $hashed = hash("sha256", $password);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $hashed);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        return $result->fetch_assoc(); // Berisi id, username, role
    } else {
        return false;
    }
}




// Ambil semua alamat user
function getUserAddresses($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}


// Ambil alamat berdasarkan ID
function getAddressById($address_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}


// Tambah alamat baru
function addAddress($user_id, $nama_penerima, $no_telp, $alamat_lengkap, $provinsi, $kota, $kode_pos) {
    global $conn;

    // Jika belum punya alamat → jadikan default otomatis
    $is_default = (getUserAddresses($user_id)->num_rows == 0) ? 1 : 0;

    if ($is_default) {
        unsetDefaultAddress($user_id);
    }

    $stmt = $conn->prepare("INSERT INTO addresses 
        (user_id, nama_penerima, no_telp, alamat_lengkap, provinsi, kota, kode_pos, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $user_id, $nama_penerima, $no_telp, $alamat_lengkap, $provinsi, $kota, $kode_pos, $is_default);

    return $stmt->execute();
}


// Update / Edit alamat
function updateAddress($address_id, $user_id, $nama_penerima, $no_telp, $alamat_lengkap, $provinsi, $kota, $kode_pos) {
    global $conn;
    $stmt = $conn->prepare("UPDATE addresses SET 
        nama_penerima = ?, 
        no_telp = ?, 
        alamat_lengkap = ?, 
        provinsi = ?, 
        kota = ?, 
        kode_pos = ?
        WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssssii", $nama_penerima, $no_telp, $alamat_lengkap, $provinsi, $kota, $kode_pos, $address_id, $user_id);

    return $stmt->execute();
}


// Hapus alamat
function deleteAddress($address_id, $user_id) {
    global $conn;

    // Cek apakah yang dihapus adalah default
    $address = getAddressById($address_id, $user_id);
    $isDefault = $address['is_default'];

    // Hapus alamat
    $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    $result = $stmt->execute();

    // Jika default dihapus → set default yg terbaru
    if ($isDefault) {
        $newDefault = $conn->query("SELECT id FROM addresses WHERE user_id = $user_id ORDER BY id DESC LIMIT 1");
        if ($newDefault->num_rows > 0) {
            $row = $newDefault->fetch_assoc();
            setDefaultAddress($row['id'], $user_id);
        }
    }

    return $result;
}


// Set alamat default
function setDefaultAddress($address_id, $user_id) {
    global $conn;

    unsetDefaultAddress($user_id);

    $stmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    return $stmt->execute();
}


// Helper — Hapus default sebelumnya
function unsetDefaultAddress($user_id) {
    global $conn;
    $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = $user_id");
}


// Ambil alamat default user
function getDefaultAddress($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

?>