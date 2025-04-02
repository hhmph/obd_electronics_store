<?php
require_once 'includes/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Будь ласка, увійдіть у систему, щоб видалити товар з обраного.'); window.location.href='login.php';</script>";
    exit();
}

// Sanitize and validate the product ID
$id_product = isset($_POST['id_product']) ? filter_var($_POST['id_product'], FILTER_SANITIZE_NUMBER_INT) : null;
$id_user = $_SESSION['user_id'];

// Validate that the product ID is a valid integer
if ($id_product === null || !filter_var($id_product, FILTER_VALIDATE_INT)) {
    echo "<script>alert('Необхідно вказати правильний ID товару.'); window.location.href='favorites.php';</script>";
    exit();
}

try {
    // Check if the product exists in the user's favorites
    $stmt_check_favorite = $pdo->prepare("SELECT * FROM favorites WHERE id_user = :id_user AND id_product = :id_product");
    $stmt_check_favorite->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt_check_favorite->bindParam(':id_product', $id_product, PDO::PARAM_INT);
    $stmt_check_favorite->execute();

    if ($stmt_check_favorite->rowCount() > 0) {
        // Proceed to delete the product from favorites if it exists
        $stmt_remove_favorite = $pdo->prepare("DELETE FROM favorites WHERE id_user = :id_user AND id_product = :id_product");
        $stmt_remove_favorite->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt_remove_favorite->bindParam(':id_product', $id_product, PDO::PARAM_INT);

        if ($stmt_remove_favorite->execute()) {
            echo "<script>alert('Товар успішно видалено з обраного.'); window.location.href='favorites.php';</script>";
        } else {
            echo "<script>alert('Сталася помилка при видаленні товару з обраного.'); window.location.href='favorites.php';</script>";
        }
    } else {
        echo "<script>alert('Цей товар не знайдений в вашому обраному.'); window.location.href='favorites.php';</script>";
    }
} catch (PDOException $e) {
    echo "Помилка: " . htmlspecialchars($e->getMessage());
}
?>
