<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$flash = $_SESSION['flash'] ?? null; if($flash) { unset($_SESSION['flash']); }

/* add reservation */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_reservation'])) {
    $date = $_POST['reservation_date'] ?? null;
    $time = $_POST['reservation_time'] ?? null;
    $guests = (int)($_POST['number_of_guests'] ?? 1);
    $note = $_POST['special_request'] ?? null;
    $stmt = $mysqli->prepare("INSERT INTO reservations (user_id,reservation_date,reservation_time,number_of_guests,special_request) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issis", $user_id, $date, $time, $guests, $note);
    $stmt->execute();
    $stmt->close();
    $_SESSION['flash'] = ['msg'=>'Reservasi dibuat','type'=>'success'];
    header('Location: reservasi.php');
    exit;
}

/* update reservation */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_reservation'])) {
    $rid = (int)$_POST['reservation_id'];
    $date = $_POST['reservation_date']; $time = $_POST['reservation_time'];
    $guests = (int)$_POST['number_of_guests']; $note = $_POST['special_request'];
    $stmt = $mysqli->prepare("UPDATE reservations SET reservation_date=?, reservation_time=?, number_of_guests=?, special_request=? WHERE reservation_id=? AND user_id=?");
    $stmt->bind_param("ssisii", $date, $time, $guests, $note, $rid, $user_id);
    $stmt->execute(); $stmt->close();
    $_SESSION['flash']=['msg'=>'Reservasi diubah','type'=>'success'];
    header('Location: reservasi.php'); exit;
}

/* delete */
if (isset($_GET['delete_reservation'])) {
    $rid = (int)$_GET['delete_reservation'];
    $stmt = $mysqli->prepare("DELETE FROM reservations WHERE reservation_id=? AND user_id=?");
    $stmt->bind_param("ii", $rid, $user_id); $stmt->execute(); $stmt->close();
    $_SESSION['flash']=['msg'=>'Reservasi dihapus','type'=>'success'];
    header('Location: reservasi.php'); exit;
}

/* fetch user's reservations */
$stmt = $mysqli->prepare("SELECT * FROM reservations WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result();
$stmt->close();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reservasi Saya - Hana & Sora</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-sakura">
  <!-- navbar copy -->
  <main class="max-w-4xl mx-auto px-4 pt-28 pb-12">
    <h1 class="text-2xl font-bold text-pink-600 mb-4">Reservasi Saya</h1>
    <?php if($flash): ?>
      <div class="mb-4 p-3 rounded <?= $flash['type']==='success' ? 'bg-emerald-200 text-emerald-800' : 'bg-red-100 text-red-700' ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded p-6 mb-6 shadow">
      <form method="post" class="grid md:grid-cols-5 gap-3">
        <input type="date" name="reservation_date" class="border rounded p-2" required>
        <input type="time" name="reservation_time" class="border rounded p-2" required>
        <input type="number" name="number_of_guests" class="border rounded p-2" placeholder="Jumlah tamu" required>
        <input type="text" name="special_request" class="border rounded p-2" placeholder="Catatan (opsional)">
        <button name="add_reservation" class="bg-pink-600 text-white rounded px-4 py-2">Buat Reservasi</button>
      </form>
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 text-left">Tanggal</th><th class="p-3 text-left">Waktu</th><th class="p-3 text-left">Tamu</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Catatan</th><th class="p-3 text-left">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while($r = $reservations->fetch_assoc()): ?>
            <tr class="border-t">
              <form method="post">
                <td class="p-2"><input type="date" name="reservation_date" value="<?= $r['reservation_date'] ?>" class="border rounded p-1"></td>
                <td class="p-2"><input type="time" name="reservation_time" value="<?= $r['reservation_time'] ?>" class="border rounded p-1"></td>
                <td class="p-2"><input type="number" name="number_of_guests" value="<?= $r['number_of_guests'] ?>" class="border rounded p-1 w-20"></td>
                <td class="p-2"><?= htmlspecialchars($r['status']) ?></td>
                <td class="p-2"><input type="text" name="special_request" value="<?= htmlspecialchars($r['special_request']) ?>" class="border rounded p-1"></td>
                <td class="p-2">
                  <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                  <button name="update_reservation" class="bg-yellow-400 text-white px-3 py-1 rounded">Ubah</button>
                  <a href="?delete_reservation=<?= $r['reservation_id'] ?>" onclick="return confirm('Hapus?')" class="bg-red-500 text-white px-3 py-1 rounded">Hapus</a>
                </td>
              </form>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
