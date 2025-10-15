<?php
session_start();
require_once 'db_connect.php';
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? null;

/* fetch some menus (limit 6) with avg rating */
$menus_q = "
  SELECT m.*, ROUND(AVG(r.rating),1) AS avg_rating, COUNT(r.review_id) AS total_reviews
  FROM menu m
  LEFT JOIN reviews r ON m.menu_id = r.menu_id
  WHERE m.availability_status = 'available'
  GROUP BY m.menu_id
  ORDER BY m.created_at DESC
  LIMIT 6
";
$menus = $mysqli->query($menus_q);

/* fetch few testimonials (latest 3) */
$test_q = "SELECT r.comment, r.rating, u.name AS user_name, m.name AS menu_name
           FROM reviews r
           JOIN users u ON r.user_id = u.user_id
           JOIN menu m ON r.menu_id = m.menu_id
           ORDER BY r.created_at DESC LIMIT 3";
$testimonials = $mysqli->query($test_q);

/* flash */
$flash = $_SESSION['flash'] ?? null;
if ($flash) { unset($_SESSION['flash']); }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Hana & Sora - Restoran</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <style>
    /* sakura pink palette */
    .sakura { background: linear-gradient(120deg,#ffe4e9,#fff0f6); }
    nav.scrolled { background: rgba(255,250,250,0.95) !important; box-shadow:0 6px 18px rgba(0,0,0,0.08); }
  </style>
</head>
<body class="sakura min-h-screen text-gray-800">

<!-- Navbar -->
<nav class="fixed w-full top-0 z-40 transition-all duration-300">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      <a href="index.php" class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-pink-300 flex items-center justify-center text-white font-bold">桜</div>
        <span class="font-semibold text-white">Hana & Sora</span>
      </a>

      <div class="hidden md:flex items-center gap-6">
        <a href="index.php" class="text-white hover:text-pink-100">Beranda</a>
        <a href="menu.php" class="text-white hover:text-pink-100">Menu</a>
        <a href="reservasi.php" class="text-white hover:text-pink-100">Reservasi</a>
        <a href="ulasan.php" class="text-white hover:text-pink-100">Rating</a>
        <a href="akun.php" class="text-white hover:text-pink-100">Akun</a>
        <?php if($is_logged_in): ?>
          <span class="text-white">Hi, <?= htmlspecialchars($user_name) ?></span>
          <a href="logout.php" class="bg-white text-pink-600 px-3 py-1 rounded">Logout</a>
        <?php else: ?>
          <a href="login.php" class="bg-white text-pink-600 px-3 py-1 rounded">Login</a>
        <?php endif; ?>
      </div>

      <div class="md:hidden">
        <button id="btnMobileMenu" class="text-white">Menu</button>
      </div>
    </div>
  </div>
</nav>

<!-- mobile menu -->
<div id="mobileMenu" class="hidden fixed inset-x-0 top-16 bg-white z-30 shadow">
  <div class="p-4 flex flex-col gap-3">
    <a href="index.php">Beranda</a>
    <a href="menu.php">Menu</a>
    <a href="reservasi.php">Reservasi</a>
    <a href="ulasan.php">Rating</a>
    <a href="akun.php">Akun</a>
    <?php if($is_logged_in): ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Login</a>
    <?php endif; ?>
  </div>
</div>

<?php if($flash): ?>
  <div class="fixed right-6 top-24 z-50">
    <div class="px-4 py-3 rounded-lg shadow <?= $flash['type']==='success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' ?>">
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  </div>
<?php endif; ?>

<!-- Hero -->
<header class="h-96 flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(180deg,#ffedf3,#fff)">
  <div class="text-center z-10" data-aos="fade-up">
    <h1 class="text-4xl md:text-5xl font-bold text-pink-600">Selamat Datang di Hana & Sora</h1>
    <p class="mt-3 text-gray-700 max-w-2xl mx-auto">Rasakan cita rasa Jepang dengan sentuhan modern. Makanan segar, suasana nyaman.</p>
    <div class="mt-6 flex gap-3 justify-center">
      <a href="menu.php" class="bg-pink-600 text-white px-5 py-3 rounded-lg shadow">Lihat Menu</a>
      <a href="reservasi.php" class="border border-pink-600 text-pink-600 px-5 py-3 rounded-lg">Reservasi</a>
    </div>
  </div>
  <div class="absolute inset-0 opacity-10"></div>
</header>

<main class="max-w-7xl mx-auto px-4 py-12">

  <!-- About -->
  <section id="about" class="text-center mb-12">
    <h2 class="text-3xl font-bold text-pink-600">Tentang Kami</h2>
    <p class="text-gray-600 mt-3 max-w-3xl mx-auto">Hana & Sora menggabungkan resep tradisional Jepang dengan bahan lokal terbaik. Nikmati suasana hangat ala sakura.</p>
  </section>

  <!-- Menu preview -->
  <section id="menu" class="mb-12">
    <h2 class="text-2xl font-bold mb-4">Menu Pilihan</h2>
    <div class="grid md:grid-cols-3 gap-6">
      <?php while($m = $menus->fetch_assoc()): ?>
        <div class="bg-white rounded-xl shadow p-4" data-aos="zoom-in">
          <img src="<?= htmlspecialchars($m['image_url'] ?: 'https://via.placeholder.com/600x400?text=No+Image') ?>" class="w-full h-40 object-cover rounded mb-3">
          <h3 class="font-semibold"><?= htmlspecialchars($m['name']) ?></h3>
          <p class="text-sm text-gray-600"><?= htmlspecialchars(substr($m['description'],0,120)) ?>...</p>
          <div class="mt-3 flex justify-between items-center">
            <div class="text-pink-600 font-bold">Rp <?= number_format($m['price'],0,',','.') ?></div>
            <div class="text-sm text-gray-500">⭐ <?= $m['avg_rating'] ?: '0.0' ?> (<?= $m['total_reviews'] ?>)</div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="mb-12">
    <h2 class="text-2xl font-bold mb-4">Apa Kata Mereka</h2>
    <div class="grid md:grid-cols-3 gap-6">
      <?php if($testimonials->num_rows > 0): ?>
        <?php while($t = $testimonials->fetch_assoc()): ?>
          <div class="bg-white rounded-lg p-6 shadow">
            <div class="flex justify-between items-center mb-2">
              <div class="font-semibold"><?= htmlspecialchars($t['user_name']) ?></div>
              <div class="text-yellow-500 font-bold"><?= intval($t['rating']) ?>★</div>
            </div>
            <p class="text-gray-700 italic">"<?= htmlspecialchars($t['comment']) ?>"</p>
            <div class="text-sm text-gray-500 mt-2">Untuk: <strong><?= htmlspecialchars($t['menu_name']) ?></strong></div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-center text-gray-500 col-span-3">Belum ada ulasan.</p>
      <?php endif; ?>
    </div>
  </section>

</main>

<!-- Footer -->
<footer class="bg-pink-600 text-white py-8">
  <div class="max-w-7xl mx-auto px-4 text-center">
    <h3 class="font-semibold text-lg">Kontak & Reservasi</h3>
    <p class="mt-2">Email: hello@hanasora.test • Telp: 0812-3456-7890</p>
    <p class="mt-4 text-sm">© <?= date('Y') ?> Hana & Sora. All Rights Reserved.</p>
  </div>
</footer>

<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
  $('#btnMobileMenu').on('click', ()=> $('#mobileMenu').toggleClass('hidden'));
  $(window).on('scroll', function(){ if($(this).scrollTop()>50) $('nav').addClass('scrolled'); else $('nav').removeClass('scrolled'); });
  setTimeout(()=>$('.fixed.right-6').fadeOut('slow'),3000);
</script>
</body>
</html>