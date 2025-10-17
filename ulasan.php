<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
$flash = $_SESSION['flash'] ?? null;
if ($flash) unset($_SESSION['flash']);

// Tambah atau perbarui review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_review'])) {
  $menu_id = (int)$_POST['menu_id'];
  $rating = (int)$_POST['rating'];
  $comment = trim($_POST['comment']);

  // cek apakah sudah ada review user untuk menu ini
  $check = $mysqli->prepare("SELECT review_id FROM reviews WHERE user_id=? AND menu_id=?");
  $check->bind_param("ii", $user_id, $menu_id);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    // update
    $stmt = $mysqli->prepare("UPDATE reviews SET rating=?, comment=? WHERE user_id=? AND menu_id=?");
    $stmt->bind_param("isii", $rating, $comment, $user_id, $menu_id);
  } else {
    // insert
    $stmt = $mysqli->prepare("INSERT INTO reviews (user_id, menu_id, rating, comment, created_at) VALUES (?,?,?,?,NOW())");
    $stmt->bind_param("iiis", $user_id, $menu_id, $rating, $comment);
  }
  $stmt->execute();
  $stmt->close();
  $_SESSION['flash'] = ['msg' => 'Ulasan disimpan!', 'type' => 'success'];
  header('Location: ulasan.php');
  exit;
}

// Hapus review
if (isset($_GET['delete_review'])) {
  $menu_id = (int)$_GET['delete_review'];
  $stmt = $mysqli->prepare("DELETE FROM reviews WHERE user_id=? AND menu_id=?");
  $stmt->bind_param("ii", $user_id, $menu_id);
  $stmt->execute();
  $stmt->close();
  $_SESSION['flash'] = ['msg' => 'Ulasan dihapus!', 'type' => 'success'];
  header('Location: ulasan.php');
  exit;
}

// Ambil semua menu + review user
$query = "
    SELECT m.menu_id, m.name AS menu_name, m.image_menu, 
           r.rating, r.comment
    FROM menu m
    LEFT JOIN reviews r ON m.menu_id = r.menu_id AND r.user_id = ?
    ORDER BY m.menu_id ASC
";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$menus = $stmt->get_result();
$stmt->close();
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ulasan Saya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-pink-50">
  <main class="max-w-6xl mx-auto px-4 pt-20 pb-12">
    <h1 class="text-3xl font-bold text-pink-600 mb-6">Ulasan Menu</h1>
    <?php if ($flash): ?>
      <div class="mb-4 p-3 rounded <?= $flash['type'] === 'success' ? 'bg-emerald-200' : 'bg-red-100' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 text-left">Foto</th>
            <th class="p-3 text-left">Menu</th>
            <th class="p-3 text-left">Rating</th>
            <th class="p-3 text-left">Komentar</th>
            <th class="p-3 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($m = $menus->fetch_assoc()): ?>
            <tr class="border-t align-top">
              <form method="post">
                <td class="p-2">
                  <?php if (!empty($m['image_menu'])): ?>
                    <img src="<?= htmlspecialchars($m['image_menu']) ?>"
                      alt="<?= htmlspecialchars($m['menu_name']) ?>"
                      class="w-24 h-24 object-cover rounded">
                  <?php else: ?>
                    <div class="w-24 h-24 bg-gray-200 flex items-center justify-center rounded text-gray-400">No Image</div>
                  <?php endif; ?>
                </td>
                <td class="p-2 font-medium"><?= htmlspecialchars($m['menu_name']) ?></td>
                <td class="p-2">
                  <div class="flex space-x-1 star-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <label>
                        <input type="radio" name="rating" value="<?= $i ?>" class="hidden"
                          <?= ($m['rating'] ?? 0) == $i ? 'checked' : '' ?>>
                        <span class="cursor-pointer text-xl <?= ($m['rating'] ?? 0) >= $i ? 'text-yellow-400' : 'text-gray-300' ?>">★</span>
                      </label>
                    <?php endfor; ?>
                  </div>
                </td>
                <td class="p-2">
                  <input type="text" name="comment" value="<?= htmlspecialchars($m['comment'] ?? '') ?>" class="border rounded p-1 w-full">
                </td>
                <td class="p-2">
                  <input type="hidden" name="menu_id" value="<?= $m['menu_id'] ?>">
                  <button name="save_review" class="bg-pink-500 text-white px-3 py-1 rounded hover:bg-pink-600">Simpan</button>
                  <?php if (!empty($m['rating'])): ?>
                    <a href="?delete_review=<?= $m['menu_id'] ?>" onclick="return confirm('Hapus ulasan ini?')" class="bg-red-500 text-white px-3 py-1 rounded ml-1">Hapus</a>
                  <?php endif; ?>
                </td>
              </form>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
  <script>
    document.querySelectorAll('.star-rating').forEach(group => {
      const stars = group.querySelectorAll('label span');
      const radios = group.querySelectorAll('input[type="radio"]');

      stars.forEach((star, index) => {
        star.addEventListener('click', () => {
          // update warna bintang
          stars.forEach((s, i) => {
            s.classList.toggle('text-yellow-400', i <= index);
            s.classList.toggle('text-gray-300', i > index);
          });
          // ubah nilai radio
          radios[index].checked = true;
        });
      });
    });
  </script>
</body>

</html>