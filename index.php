<?php
session_start();
require_once 'db_connect.php';
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? null;

/* fetch top rated menus (limit 6) */
$menus_q = "
  SELECT m.*, 
         ROUND(AVG(r.rating),1) AS avg_rating, 
         COUNT(r.review_id) AS total_reviews
  FROM menu m
  LEFT JOIN reviews r ON m.menu_id = r.menu_id
  WHERE m.availability_status = 'available'
  GROUP BY m.menu_id
  HAVING avg_rating IS NOT NULL
  ORDER BY avg_rating DESC, total_reviews DESC
  LIMIT 6
";
$menus = $mysqli->query($menus_q);

/* fetch latest testimonials (reviews + menu + user) */
$test_q = "
  SELECT r.comment, r.rating, u.name AS user_name, m.name AS menu_name
  FROM reviews r
  JOIN users u ON r.user_id = u.user_id
  JOIN menu m ON r.menu_id = m.menu_id
  ORDER BY r.created_at DESC
  LIMIT 6
";
$testimonials = $mysqli->query($test_q);

/* flash message */
$flash = $_SESSION['flash'] ?? null;
if ($flash) {
  unset($_SESSION['flash']);
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Hana & Sora - Restoran Jepang</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <style>
    .sakura-bg {
      background: linear-gradient(180deg, #ffeaf2 0%, #fff8fb 100%);
    }

    nav.scrolled {
      background: rgba(255, 255, 255, 0.95) !important;
      backdrop-filter: blur(8px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>

<body class="min-h-screen text-gray-800 bg-pink-50">

  <!-- Navbar -->
  <nav class="fixed w-full top-0 z-40 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex items-center justify-between h-16">
        <a href="index.php" class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-pink-500 flex items-center justify-center text-white font-bold text-xl shadow">桜</div>
          <span class="font-semibold text-pink-700 text-lg tracking-wide">Hana & Sora</span>
        </a>

        <div class="hidden md:flex items-center gap-6 text-sm font-medium">
          <a href="index.php" class="hover:text-pink-600">Beranda</a>
          <a href="menu.php" class="hover:text-pink-600">Menu</a>
          <a href="reservasi.php" class="hover:text-pink-600">Reservasi</a>
          <a href="ulasan.php" class="hover:text-pink-600">Rating</a>
          <a href="akun.php" class="hover:text-pink-600">Akun</a>
          <?php if ($is_logged_in): ?>
            <span class="text-pink-600 font-semibold">Hi, <?= htmlspecialchars($user_name) ?></span>
            <a href="logout.php" class="bg-pink-500 text-white px-3 py-1 rounded hover:bg-pink-600 transition">Logout</a>
          <?php else: ?>
            <a href="login.php" class="bg-pink-500 text-white px-3 py-1 rounded hover:bg-pink-600 transition">Login</a>
          <?php endif; ?>
        </div>

        <div class="md:hidden">
          <button id="btnMobileMenu" class="text-pink-600 font-bold">☰</button>
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
      <?php if ($is_logged_in): ?>
        <a href="logout.php" class="text-pink-600 font-semibold">Logout</a>
      <?php else: ?>
        <a href="login.php" class="text-pink-600 font-semibold">Login</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="fixed right-6 top-24 z-50">
      <div class="px-4 py-3 rounded-lg shadow <?= $flash['type'] === 'success' ? 'bg-emerald-500' : 'bg-red-500' ?> text-white">
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Hero -->
  <header class="sakura-bg h-[85vh] flex flex-col justify-center items-center text-center px-4 relative overflow-hidden">
    <div data-aos="fade-up" class="z-10">
      <h1 class="text-5xl md:text-6xl font-bold text-pink-600 drop-shadow-sm">Selamat Datang di <span class="text-pink-700">Hana & Sora</span></h1>
      <p class="mt-4 text-gray-700 max-w-2xl mx-auto text-lg">Rasakan cita rasa Jepang dengan sentuhan modern. Hidangan segar, suasana hangat, dan nuansa sakura yang menenangkan.</p>
      <div class="mt-8 flex gap-4 justify-center">
        <a href="menu.php" class="bg-pink-600 text-white px-6 py-3 rounded-lg shadow hover:bg-pink-700 transition">Lihat Menu</a>
        <a href="reservasi.php" class="border border-pink-600 text-pink-600 px-6 py-3 rounded-lg hover:bg-pink-50 transition">Reservasi</a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 py-16">

    <!-- Tentang Kami -->
    <section id="about" class="text-center mb-16" data-aos="fade-up">
      <h2 class="text-3xl font-bold text-pink-600">Tentang Kami</h2>
      <p class="text-gray-600 mt-3 max-w-3xl mx-auto text-lg leading-relaxed">
        Hana & Sora adalah restoran Jepang yang menghadirkan harmoni antara tradisi dan modernitas. Kami menggunakan bahan lokal berkualitas tinggi untuk menciptakan pengalaman kuliner yang autentik dengan suasana lembut seperti bunga sakura.
      </p>
    </section>

    <!-- Menu Pilihan -->
    <section id="menu" class="mb-20" data-aos="fade-up">
      <h2 class="text-2xl font-bold mb-6 text-center text-pink-600">Menu Terfavorit</h2>
      <div class="grid md:grid-cols-3 gap-8">
        <?php if ($menus && $menus->num_rows > 0): ?>
          <?php while ($m = $menus->fetch_assoc()): ?>
            <?php if (($m['avg_rating'] ?? 0) < 4.0) continue;  
            ?>

            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-4 flex flex-col" data-aos="zoom-in">
              <?php
              $imgPath = (!empty($m['image_menu'])) ? $m['image_menu'] : 'https://via.placeholder.com/600x400?text=No+Image';
              ?>
              <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($m['name']) ?>" class="w-full h-48 object-cover rounded-lg mb-3">
              <h3 class="font-semibold text-lg text-pink-700"><?= htmlspecialchars($m['name']) ?></h3>
              <p class="text-sm text-gray-600 flex-grow mt-2"><?= htmlspecialchars(substr($m['description'], 0, 100)) ?>...</p>
              <div class="mt-4 flex justify-between items-center">
                <span class="text-pink-600 font-bold">Rp <?= number_format($m['price'], 0, ',', '.') ?></span>
                <span class="text-sm text-gray-500">⭐ <?= $m['avg_rating'] ?: '0.0' ?> (<?= $m['total_reviews'] ?>)</span>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-center text-gray-500 col-span-3">Belum ada menu dengan ulasan.</p>
        <?php endif; ?>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer class="bg-pink-600 text-white py-8">
    <div class="max-w-7xl mx-auto px-4 text-center">
      <h3 class="font-semibold text-lg">Kontak Kami</h3>
      <p class="mt-2 text-sm md:text-base">Email: <a href="mailto:hello@hanasora.test" class="underline">hello@hanasora.test</a> • Telp: 0812-3456-7890</p>
      <p class="mt-4 text-sm opacity-80">© <?= date('Y') ?> Hana & Sora. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $('#btnMobileMenu').on('click', () => $('#mobileMenu').toggleClass('hidden'));
    $(window).on('scroll', function() {
      if ($(this).scrollTop() > 50) $('nav').addClass('scrolled');
      else $('nav').removeClass('scrolled');
    });
    setTimeout(() => $('.fixed.right-6').fadeOut('slow'), 3000);
  </script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>

</html>