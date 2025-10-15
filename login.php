<?php
session_start();
require_once 'db_connect.php';
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($email==='' || $password==='') $error = 'Isi email dan password.';
    else {
        $stmt = $mysqli->prepare("SELECT user_id,name,password_hash,active FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s",$email); $stmt->execute(); $res = $stmt->get_result();
        if ($res->num_rows===1) {
            $u = $res->fetch_assoc();
            if (!$u['active']) { $error = 'Akun non-aktif.'; }
            elseif (password_verify($password, $u['password_hash'])) {
                $_SESSION['user_id'] = $u['user_id']; $_SESSION['user_name'] = $u['name'];
                header('Location: index.php'); exit;
            } else { $error = 'Email atau password salah.'; }
        } else { $error = 'Email tidak terdaftar.'; }
        $stmt->close();
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-sakura flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-xl shadow p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-pink-600 mb-4 text-center">Masuk ke Hana & Sora</h2>
    <?php if($error): ?><div class="bg-red-100 text-red-700 p-3 rounded mb-3"><?=$error?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <div><label class="block text-sm">Email</label><input type="email" name="email" class="w-full border rounded p-2" required></div>
      <div><label class="block text-sm">Password</label><input type="password" name="password" class="w-full border rounded p-2" required></div>
      <button name="login" class="w-full bg-pink-600 text-white py-2 rounded">Login</button>
    </form>
    <p class="mt-4 text-center text-sm">Belum punya akun? <a href="register.php" class="text-pink-600 font-semibold">Daftar</a></p>
  </div>
</body></html>
