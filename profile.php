<?php
require_once 'db.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$_SESSION['user']['id']]);
$user = $query->fetch();

// if (isset($_POST['delete'])) {
//     $deleteQuery = $pdo->prepare("DELETE FROM users WHERE id = ?");
//     $deleteQuery->execute([$user['id']]);
//     session_unset();
//     session_destroy();
//     echo "<script>alert('User berhasil dihapus'); window.location='landing.php';</script>";
//     exit();
// }

if (isset($_POST['delete'])) {
    $deleteQuery = $pdo->prepare("UPDATE users SET status = 'nonactive' WHERE id = ?");
    $deleteQuery->execute([$user['id']]);
    session_unset();
    session_destroy();
    echo "<script>alert('Akun Anda telah dinonaktifkan'); window.location='landing.php';</script>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #4A00E0, #8E2DE2);
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-card {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 20px;
            color: #333;
            background-color: rgb(229, 216, 255);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #6A00F0;
            color: #fff;
        }


        .profile-pic {
            border-radius: 50%;
            border: 4px solid #4A00E0;
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 20px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        h2 {
            margin: 10px 0 5px;
            color: #4A00E0;
        }

        p {
            color: #555;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-edit {
            background-color: #4A90E2;
            color: white;
        }

        .btn-delete {
            background-color: #d9534f;
            color: white;
        }

        footer {
            background-color: rgba(255, 255, 255, 0.2);
            text-align: center;
            padding: 10px;
            color: white;
            font-size: 14px;
        }

        @media (max-width: 576px) {
            .profile-card {
                padding: 25px;
            }

            .back-btn {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <main>
        <a href="discover.php" class="back-btn"><i class="bi bi-arrow-left"></i></a>
        <div class="profile-card">
            <img src="assets/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-pic" data-bs-toggle="modal" data-bs-target="#profileModal">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <p><?php echo $user['about_me'] ? htmlspecialchars($user['about_me']) : "Belum ada deskripsi"; ?></p>
            <div class="btn-group">
                <a href="edit.php" class="btn btn-edit">Edit</a>
                <form method="POST">
                    <button type="submit" name="delete" class="btn btn-delete">Delete</button>
                </form>
            </div>
        </div>
    </main>

    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center">
                    <img src="assets/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Full Profile Picture" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Tomboypedia. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>