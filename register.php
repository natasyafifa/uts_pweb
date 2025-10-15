<?php
session_start();
require_once 'db_connect.php';
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if ($name===''||$email===''||$password===''||$password2==='') $error='Isi semua field.';
    elseif ($password !== $password2) $error='Password tidak sama.';
    else {
        $h = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (name,email,password_hash,phone,role) VALUES (?,?,?,?, 'customer')");
        $stmt->bind_param("ssss",$name,$email,$h,$phone);
        if ($stmt->execute()) { $success='Registrasi berhasil. Silakan login.'; }
        else { $error='Gagal registrasi. Email mungkin sudah dipakai.'; }
        $stmt->close();
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Register</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-sakura flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-xl shadow p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-pink-600 mb-4 text-center">Daftar Akun</h2>
    <?php if($error): ?><div class="bg-red-100 text-red-700 p-3 rounded mb-3"><?=$error?></div><?php endif; ?>
    <?php if($success): ?><div class="bg-emerald-100 text-emerald-800 p-3 rounded mb-3"><?=$success?></div><?php endif; ?>
    <form method="post" class="space-y-3">
      <input name="name" placeholder="Nama" class="w-full border rounded p-2" required>
      <input name="email" placeholder="Email" type="email" class="w-full border rounded p-2" required>
      <input name="phone" placeholder="Telepon (opsional)" class="w-full border rounded p-2">
      <input name="password" placeholder="Password" type="password" class="w-full border rounded p-2" required>
      <input name="password2" placeholder="Ulangi password" type="password" class="w-full border rounded p-2" required>
      <button name="register" class="w-full bg-pink-600 text-white py-2 rounded">Daftar</button>
    </form>
    <p class="mt-4 text-center text-sm">Sudah punya akun? <a href="login.php" class="text-pink-600 font-semibold">Login</a></p>
  </div>
</body></html>
