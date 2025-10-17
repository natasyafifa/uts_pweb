<?php
require_once 'db_connect.php';

// Ambil semua data dari tabel menu
$query = "SELECT * FROM menu ORDER BY created_at DESC";
$result = $mysqli->query($query);

if (!$result) {
  die("Query error: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Menu | Hana & Sora Café</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f9fafb] text-gray-800">

  <header class="bg-pink-700 text-white py-6 text-center shadow-md">
    <h1 class="text-3xl font-bold tracking-wide">Menu Hana & Sora Café</h1>
    <p class="text-sm text-pink-200">Nikmati hidangan khas Jepang dengan sentuhan hangat dan rasa autentik 🍵</p>
  </header>

  <main class="max-w-6xl mx-auto py-10 px-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php while ($menu = $result->fetch_assoc()): ?>
        <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition p-5 flex flex-col">
          <!-- Gambar (gunakan nama file sesuai folder kamu, misal assets/sushi.jpg) -->
          <?php
          $imgName = strtolower(str_replace(' ', '-', $menu['name'])) . '.jpg';
          $imgPath = 'img/' . $imgName;
          ?>
          <img src="<?= htmlspecialchars($menu['image_menu']) ?>"
            alt="<?= htmlspecialchars($menu['name']) ?>"
            class="rounded-xl h-48 w-full object-cover mb-4">


          <h2 class="text-xl font-semibold text-pink-800 mb-1"><?= htmlspecialchars($menu['name']) ?></h2>
          <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($menu['category']) ?></p>
          <p class="text-gray-700 text-sm flex-grow"><?= htmlspecialchars($menu['description']) ?></p>

          <div class="mt-4 flex justify-between items-center">
            <span class="text-lg font-bold text-pink-700">Rp <?= number_format($menu['price'], 0, ',', '.') ?></span>
            <span class="text-sm <?= $menu['availability_status'] === 'available' ? 'text-pink-600' : 'text-red-500' ?>">
              <?= ucfirst($menu['availability_status']) ?>
            </span>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </main>

  <footer class="text-center py-6 text-sm text-gray-500">
    &copy; <?= date('Y') ?> Hana & Sora Café — All Rights Reserved
  </footer>

</body>

</html>