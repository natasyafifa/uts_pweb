<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$flash = $_SESSION['flash'] ?? null; if($flash) { unset($_SESSION['flash']); }

/* update review */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_review'])) {
    $rid = (int)$_POST['review_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $stmt = $mysqli->prepare("UPDATE reviews SET rating=?, comment=? WHERE review_id=? AND user_id=?");
    $stmt->bind_param("isii",$rating,$comment,$rid,$user_id);
    $stmt->execute(); $stmt->close();
    $_SESSION['flash']=['msg'=>'Ulasan diubah','type'=>'success']; header('Location: ulasan.php'); exit;
}
if (isset($_GET['delete_review'])) {
    $rid = (int)$_GET['delete_review'];
    $stmt = $mysqli->prepare("DELETE FROM reviews WHERE review_id=? AND user_id=?");
    $stmt->bind_param("ii",$rid,$user_id); $stmt->execute(); $stmt->close();
    $_SESSION['flash']=['msg'=>'Ulasan dihapus','type'=>'success']; header('Location: ulasan.php'); exit;
}

/* fetch reviews of user */
$stmt = $mysqli->prepare("SELECT r.*, m.name AS menu_name FROM reviews r JOIN menu m ON r.menu_id=m.menu_id WHERE r.user_id=? ORDER BY r.created_at DESC");
$stmt->bind_param("i",$user_id); $stmt->execute();
$my_reviews = $stmt->get_result(); $stmt->close();
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ulasan Saya</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-sakura">
<main class="max-w-4xl mx-auto px-4 pt-28 pb-12">
  <h1 class="text-2xl font-bold text-pink-600 mb-4">Ulasan Saya</h1>
  <?php if($flash): ?><div class="mb-4 p-3 rounded <?= $flash['type']==='success'?'bg-emerald-200':'bg-red-100' ?>"><?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>
  <div class="bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full">
      <thead class="bg-gray-100"><tr><th class="p-3 text-left">Menu</th><th class="p-3 text-left">Rating</th><th class="p-3 text-left">Komentar</th><th class="p-3 text-left">Aksi</th></tr></thead>
      <tbody>
        <?php while($rev = $my_reviews->fetch_assoc()): ?>
          <tr class="border-t">
            <form method="post">
              <td class="p-2"><?= htmlspecialchars($rev['menu_name']) ?></td>
              <td class="p-2"><input type="number" name="rating" min="1" max="5" value="<?= $rev['rating'] ?>" class="border rounded p-1 w-20"></td>
              <td class="p-2"><input type="text" name="comment" value="<?= htmlspecialchars($rev['comment']) ?>" class="border rounded p-1 w-full"></td>
              <td class="p-2">
                <input type="hidden" name="review_id" value="<?= $rev['review_id'] ?>">
                <button name="update_review" class="bg-yellow-400 text-white px-3 py-1 rounded">Ubah</button>
                <a href="?delete_review=<?= $rev['review_id'] ?>" onclick="return confirm('Hapus?')" class="bg-red-500 text-white px-3 py-1 rounded">Hapus</a>
              </td>
            </form>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>
</body></html>
