<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Будь ласка, увійдіть у систему, щоб додати товар до обраного.'); window.location.href='login.php';</script>";
    exit();
}

$id_product = isset($_POST['id_product']) ? filter_var($_POST['id_product'], FILTER_SANITIZE_NUMBER_INT) : null;
$id_user = $_SESSION['user_id'];

if ($id_product === null) {
    echo "<script>alert('Необхідно вказати ID товару.'); window.location.href='products.php';</script>";
    exit();
}

try {
    $stmt_check_favorites = $pdo->prepare("SELECT id_favorites FROM favorites WHERE id_user = :id_user AND id_product = :id_product");
    $stmt_check_favorites->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt_check_favorites->bindParam(':id_product', $id_product, PDO::PARAM_INT);
    $stmt_check_favorites->execute();

    if ($stmt_check_favorites->rowCount() > 0) {
        echo "<script>alert('Цей товар вже є у вашому обраному.'); window.location.href='products.php';</script>";
        exit();
    }

    $stmt_insert_favorite = $pdo->prepare("INSERT INTO favorites (id_user, id_product) VALUES (:id_user, :id_product)");
    $stmt_insert_favorite->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt_insert_favorite->bindParam(':id_product', $id_product, PDO::PARAM_INT);

    if ($stmt_insert_favorite->execute()) {
        echo "<script>alert('Товар успішно додано до обраного.'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Сталася помилка при додаванні товару до обраного.'); window.location.href='products.php';</script>";
    }
} catch (PDOException $e) {
    echo "Помилка: " . htmlspecialchars($e->getMessage());
}
?>
