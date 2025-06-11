<?php 
    require_once 'db.php'; 
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_id'], $_POST['new_status'])) {
        $updateId = $_POST['update_user_id'];
        $newStatus = $_POST['new_status'];
        $updateStmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'user'");
        $updateStmt->execute([$newStatus, $updateId]);
    }
    $query = $pdo->prepare("SELECT * FROM users WHERE role = 'user'");
    $query->execute();
    $users = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
</head>
<body style="margin:0; font-family: Arial, sans-serif; background-color: white;">
    <div style="background-color: #03AC0E; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <a href="dashboard.php" style="text-decoration: none;"><div style="color: white; font-size: 20px; font-weight: bold;">Tomboypedia</div></a>
        <div>
            <a href="dashboard.php" style="color: white; margin-right: 20px; text-decoration: none;">Products</a>
            <a href="users.php" style="color: white; margin-right: 20px; text-decoration: none;">Users</a>
            <a href="transaction.php" style="color: white; margin-right: 20px; text-decoration: none;">Transaction</a>
            <a href="category.php" style="color: white; margin-right: 20px; text-decoration: none;">Category</a>
            <a href="brands.php" style="color: white; margin-right: 20px; text-decoration: none;">Brands</a>
            <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
        </div>
    </div>
    <h1 style="color: black; font-size: 40px; text-align: center; margin-top: 50px;">Manage Users</h1>
    <?php if (count($users) == 0): ?>
        <p style="text-align: center; font-size: 18px;">Belum ada user yang terdaftar.</p>
    <?php else: ?>
        <table style="width: 90%; margin: 0 auto; border-collapse: collapse; margin-top: 30px;">
            <thead>
                <tr style="background-color: #03AC0E; color: white;">
                    <th style="padding: 12px; border: 1px solid #ccc;">No</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Display Name</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Username</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Email</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Saldo</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">About Me</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Profile Picture</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Status</th>
                    <th style="padding: 12px; border: 1px solid #ccc;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $index => $user): ?>
                    <tr style="text-align: center;">
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo $index + 1; ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo htmlspecialchars($user['display_name']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;">Rp<?php echo number_format($user['saldo'], 0, ',', '.'); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo nl2br(htmlspecialchars($user['about_me'])); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;">
                            <?php if ($user['profile_picture']): ?>
                                <img src="assets/<?php echo htmlspecialchars($user['profile_picture']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <span>No Image</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ccc;"><?php echo htmlspecialchars($user['status']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ccc;">
                            <a href="edit_user_admin.php?id=<?php echo $user['id']; ?>">
                                <button 
                                    type="button" 
                                    style="
                                        background-color: #FFC107; 
                                        color: black; 
                                        padding: 5px 10px; 
                                        border: none; 
                                        border-radius: 5px; 
                                        cursor: pointer;">
                                    Edit
                                </button>
                            </a>
                            <form method="POST" onsubmit="return confirm('Yakin ingin me-nonaktifkan user ini?');" style="display:inline;">
                                <input type="hidden" name="update_user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="new_status" value="<?php echo $user['status'] === 'active' ? 'nonactive' : 'active'; ?>">
                                <button 
                                    type="submit" 
                                    style="
                                        background-color: <?php echo $user['status'] === 'active' ? '#DC3545' : '#007BFF'; ?>; 
                                        color: white; 
                                        padding: 5px 10px; 
                                        border: none; 
                                        border-radius: 5px; 
                                        cursor: pointer;">
                                    <?php echo $user['status'] === 'active' ? 'Delete' : 'Activate'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>