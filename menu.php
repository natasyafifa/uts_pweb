<?php
session_start();
require_once 'db_connect.php';
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? null;

/* handle add review (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    if (!$is_logged_in) {
        $_SESSION['flash'] = ['msg' => 'Login terlebih dahulu untuk menulis ulasan.', 'type' => 'danger'];
        header('Location: login.php');
        exit;
    }
    $menu_id = (int)$_POST['menu_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    if ($menu_id && $rating>=1 && $rating<=5 && $comment!=='') {
        $stmt = $mysqli->prepare("INSERT INTO reviews (user_id, menu_id, rating, comment) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis", $user_id, $menu_id, $rating, $comment);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash'] = ['msg' => 'Terima kasih! Ulasan dikirim.', 'type' => 'success'];
    } else {
        $_SESSION['flash'] = ['msg' => 'Isi form dengan benar.', 'type' => 'danger'];
    }
    header('Location: menu.php');
    exit;
}

/* fetch menus */
$menus_q = "SELECT m.*, ROUND(AVG(r.rating),1) AS avg_rating, COUNT(r.review_id) AS total_reviews
           FROM menu m
           LEFT JOIN reviews r ON m.menu_id=r.menu_id
           GROUP BY m.menu_id
           ORDER BY m.created_at DESC";
$menus = $mysqli->query($menus_q);
$flash = $_SESSION['flash'] ?? null; if($flash) { unset($_SESSION['flash']); }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Menu - Hana & Sora</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
</head>
<body class="bg-sakura min-h-screen text-gray-800">

<nav class="fixed w-full top-0 z-40"><!-- reuse the same navbar code as index or copy/paste --> </nav>

<main class="max-w-7xl mx-auto px-4 pt-28 pb-16">
  <h1 class="text-3xl font-bold text-pink-600 mb-4">Menu</h1>
  <?php if($flash): ?>
    <div class="mb-4 p-3 rounded <?= $flash['type']==='success'?'bg-emerald-200 text-emerald-800':'bg-red-100 text-red-700' ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

  <div class="grid md:grid-cols-3 gap-6">
  <?php while($m = $menus->fetch_assoc()): ?>
    <div class="bg-white rounded-xl shadow p-4" data-aos="fade-up">
      <img src="<?= htmlspecialchars($m['image_url']?:'https://via.placeholder.com/600x400') ?>" class="w-full h-44 object-cover rounded mb-3">
      <h3 class="text-xl font-semibold"><?= htmlspecialchars($m['name']) ?></h3>
      <p class="text-sm text-gray-600"><?= htmlspecialchars($m['description']) ?></p>
      <div class="flex justify-between items-center mt-3">
        <div class="text-pink-600 font-bold">Rp <?= number_format($m['price'],0,',','.') ?></div>
        <div class="text-sm text-gray-500">⭐ <?= $m['avg_rating']?:'0.0' ?> (<?= $m['total_reviews'] ?>)</div>
      </div>

      <!-- show latest 3 reviews for this menu -->
      <div class="mt-3">
        <h4 class="font-semibold text-sm mb-2">Ulasan Terbaru</h4>
        <?php
          $stmt = $mysqli->prepare("SELECT r.*, u.name AS user_name FROM reviews r JOIN users u ON r.user_id=u.user_id WHERE r.menu_id=? ORDER BY r.created_at DESC LIMIT 3");
          $stmt->bind_param("i", $m['menu_id']);
          $stmt->execute();
          $resr = $stmt->get_result();
          if ($resr->num_rows === 0) echo '<p class="text-sm text-gray-500">Belum ada ulasan.</p>';
          else {
            while($rv = $resr->fetch_assoc()){
              echo '<div class="border rounded p-2 mb-2 text-sm">';
              echo '<div class="flex justify-between"><strong>'.htmlspecialchars($rv['user_name']).'</strong><span class="text-yellow-500">'.intval($rv['rating']).'★</span></div>';
              echo '<div class="mt-1 text-gray-700">'.htmlspecialchars($rv['comment']).'</div>';
              echo '</div>';
            }
          }
          $stmt->close();
        ?>
      </div>

      <!-- add review button / login prompt -->
      <div class="mt-3">
        <?php if($is_logged_in): ?>
          <button class="open-review-modal w-full bg-pink-600 text-white py-2 rounded" data-menuid="<?= $m['menu_id'] ?>" data-menuname="<?= htmlspecialchars($m['name'],ENT_QUOTES) ?>">Tulis Ulasan</button>
        <?php else: ?>
          <div class="bg-yellow-100 p-2 rounded text-center">Silakan <a class="text-pink-600 font-semibold" href="login.php">login</a> untuk menulis ulasan.</div>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>
  </div>

</main>

<!-- review modal -->
<div id="reviewModal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/40"></div>
  <div class="bg-white rounded-lg p-6 z-10 w-full max-w-md">
    <div class="flex justify-between items-center">
      <h3 id="modalTitle" class="text-lg font-semibold">Tulis Ulasan</h3>
      <button id="closeModal" class="text-gray-500">&times;</button>
    </div>
    <form method="post" class="mt-4 space-y-3">
      <input type="hidden" name="menu_id" id="modal_menu_id" value="">
      <div>
        <label class="text-sm">Rating</label>
        <select name="rating" required class="w-28 border rounded p-2">
          <option value="">Pilih</option>
          <option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option>
        </select>
      </div>
      <div>
        <label class="text-sm">Komentar</label>
        <textarea name="comment" rows="4" class="w-full border rounded p-2" required></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelModal" class="px-4 py-2 border rounded">Batal</button>
        <button type="submit" name="add_review" class="px-4 py-2 bg-pink-600 text-white rounded">Kirim</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
  $('.open-review-modal').on('click', function(){
    const id = $(this).data('menuid');
    const name = $(this).data('menuname');
    $('#modal_menu_id').val(id);
    $('#modalTitle').text('Tulis Ulasan — ' + name);
    $('#reviewModal').removeClass('hidden').addClass('flex');
  });
  $('#closeModal, #cancelModal').on('click', function(){ $('#reviewModal').addClass('hidden').removeClass('flex'); });
</script>
</body>
</html>
