<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$flash = $_SESSION['flash'] ?? null; if($flash) { unset($_SESSION['flash']); }

/* fetch user */
$stmt = $mysqli->prepare("SELECT user_id,name,email,phone,role,active FROM users WHERE user_id=?");
$stmt->bind_param("i",$user_id); $stmt->execute();
$user = $stmt->get_result()->fetch_assoc(); $stmt->close();

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_akun'])) {
    $name = trim($_POST['name']); $email = trim($_POST['email']); $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE users SET name=?, email=?, phone=?, password_hash=? WHERE user_id=?");
        $stmt->bind_param("ssssi",$name,$email,$phone,$hash,$user_id);
    } else {
        $stmt = $mysqli->prepare("UPDATE users SET name=?, email=?, phone=? WHERE user_id=?");
        $stmt->bind_param("sssi",$name,$email,$phone,$user_id);
    }
    $stmt->execute(); $stmt->close();
    $_SESSION['user_name'] = $name;
    $_SESSION['flash'] = ['msg'=>'Profil diperbarui','type'=>'success']; header('Location: akun.php'); exit;
}

if (isset($_GET['deactivate'])) {
    $stmt = $mysqli->prepare("UPDATE users SET active=0 WHERE user_id=?");
    $stmt->bind_param("i",$user_id); $stmt->execute(); $stmt->close();
    session_destroy();
    header('Location: index.php'); exit;
}
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Akun Saya</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-sakura">
<main class="max-w-3xl mx-auto px-4 pt-28 pb-12">
  <h1 class="text-2xl font-bold text-pink-600 mb-4">Akun Saya</h1>
  <?php if($flash): ?><div class="mb-4 p-3 rounded <?= $flash['type']==='success' ? 'bg-emerald-200' : 'bg-red-100' ?>"><?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>

  <?php if(!$user || !$user['active']): ?>
    <div class="bg-red-100 p-4 rounded">Akun tidak ditemukan atau non-aktif. Hubungi admin.</div>
  <?php else: ?>
    <form method="post" class="bg-white p-6 rounded shadow space-y-4">
      <div><label class="block text-sm font-semibold">Nama</label><input name="name" value="<?=htmlspecialchars($user['name'])?>" class="w-full border rounded p-2" required></div>
      <div><label class="block text-sm font-semibold">Email</label><input name="email" value="<?=htmlspecialchars($user['email'])?>" class="w-full border rounded p-2" required></div>
      <div><label class="block text-sm font-semibold">Telepon</label><input name="phone" value="<?=htmlspecialchars($user['phone'])?>" class="w-full border rounded p-2"></div>
      <div><label class="block text-sm font-semibold">Password (Kosong = tetap)</label><input type="password" name="password" class="w-full border rounded p-2"></div>
      <div class="flex justify-between items-center">
        <button name="update_akun" class="bg-pink-600 text-white px-4 py-2 rounded">Simpan</button>
        <a href="?deactivate=1" onclick="return confirm('Nonaktifkan akun?')" class="text-red-600">Nonaktifkan Akun</a>
      </div>
    </form>
  <?php endif; ?>
</main>
</body></html>
