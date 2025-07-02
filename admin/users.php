<?php
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

$pdo = getConnection();
$success = '';
$error = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $role = $_POST['role'];
        
        if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($role)) {
            $error = 'Semua field wajib diisi.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = 'Username atau email sudah terdaftar.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $role])) {
                    $success = 'User berhasil ditambahkan.';
                } else {
                    $error = 'Terjadi kesalahan saat menambahkan user.';
                }
            }
        }
    }
    
    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $role = $_POST['role'];
        $is_active = (int)$_POST['is_active'];
        
        if (empty($username) || empty($email) || empty($full_name) || empty($role)) {
            $error = 'Field yang wajib tidak boleh kosong.';
        } else {
            // Check if username or email already exists (exclude current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $id]);
            
            if ($stmt->fetch()) {
                $error = 'Username atau email sudah digunakan user lain.';
            } else {
                if (!empty($_POST['password'])) {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, phone = ?, role = ?, is_active = ? WHERE id = ?");
                    $result = $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $role, $is_active, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, role = ?, is_active = ? WHERE id = ?");
                    $result = $stmt->execute([$username, $email, $full_name, $phone, $role, $is_active, $id]);
                }
                
                if ($result) {
                    $success = 'User berhasil diupdate.';
                } else {
                    $error = 'Terjadi kesalahan saat mengupdate user.';
                }
            }
        }
    }
    
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        
        // Don't allow deleting self
        if ($id == $_SESSION['user_id']) {
            $error = 'Tidak dapat menghapus akun sendiri.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role IN ('admin', 'petugas')");
            if ($stmt->execute([$id])) {
                $success = 'User berhasil dihapus.';
            } else {
                $error = 'Terjadi kesalahan saat menghapus user.';
            }
        }
    }
}

// Get users (admin and petugas only)
$stmt = $pdo->prepare("SELECT * FROM users WHERE role IN ('admin', 'petugas') ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

// Get user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role IN ('admin', 'petugas')");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin/Petugas - Toko Bunga</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Kelola Admin/Petugas</h1>
                <a href="?add=1" class="btn btn-primary">Tambah User</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <?php if (isset($_GET['add']) || $edit_user): ?>
            <div class="content-card">
                <h2><?php echo $edit_user ? 'Edit User' : 'Tambah User Baru'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'add'; ?>">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Nama Lengkap *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($edit_user['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="phone">Nomor Telepon</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="">Pilih Role</option>
                                <option value="admin" <?php echo ($edit_user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="petugas" <?php echo ($edit_user['role'] ?? '') === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="password">Password <?php echo $edit_user ? '(kosongkan jika tidak diubah)' : '*'; ?></label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   <?php echo !$edit_user ? 'required' : ''; ?>>
                        </div>
                        
                        <?php if ($edit_user): ?>
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select id="is_active" name="is_active" class="form-control">
                                <option value="1" <?php echo $edit_user['is_active'] ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo !$edit_user['is_active'] ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_user ? 'Update' : 'Tambah'; ?> User
                        </button>
                        <a href="users.php" class="btn btn-outline">Batal</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Users List -->
            <div class="content-card">
                <h2>Daftar Admin/Petugas</h2>
                
                <?php if (empty($users)): ?>
                    <p>Belum ada data user.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="status-badge" style="background: #e3f2fd; color: #1976d2;">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $user['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                    
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 