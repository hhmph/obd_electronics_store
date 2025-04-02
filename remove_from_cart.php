<?php
require_once 'includes/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Будь ласка, увійдіть у систему, щоб видалити товар з кошика.'); window.location.href='login.php';</script>";
    exit();
}

// Sanitize and validate the cart item ID
$id_cart_item = isset($_POST['id_cart_item']) ? filter_var($_POST['id_cart_item'], FILTER_SANITIZE_NUMBER_INT) : null;

if ($id_cart_item === null || !filter_var($id_cart_item, FILTER_VALIDATE_INT)) {
    echo "<script>alert('Необхідно вказати правильний ID товару в кошику.'); window.location.href='cart.php';</script>";
    exit();
}

try {
    // Check if the item exists in the user's cart using the cart's user ID
    $stmt_check_item = $pdo->prepare(
        "SELECT ci.* 
         FROM cart_items ci 
         JOIN carts c ON ci.id_cart = c.id_cart
         WHERE ci.id_cart_item = :id_cart_item AND c.id_user = :user_id"
    );
    $stmt_check_item->bindParam(':id_cart_item', $id_cart_item, PDO::PARAM_INT);
    $stmt_check_item->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt_check_item->execute();

    if ($stmt_check_item->rowCount() > 0) {
        // Proceed to delete the item if it exists in the cart
        $stmt_remove_cart_item = $pdo->prepare("DELETE FROM cart_items WHERE id_cart_item = :id_cart_item");
        $stmt_remove_cart_item->bindParam(':id_cart_item', $id_cart_item, PDO::PARAM_INT);

        if ($stmt_remove_cart_item->execute()) {
            echo "<script>alert('Товар успішно видалено з кошика.'); window.location.href='cart.php';</script>";
        } else {
            echo "<script>alert('Сталася помилка при видаленні товару з кошика.'); window.location.href='cart.php';</script>";
        }
    } else {
        echo "<script>alert('Цей товар не знайдений в вашому кошику.'); window.location.href='cart.php';</script>";
    }
} catch (PDOException $e) {
    echo "Помилка: " . htmlspecialchars($e->getMessage());
}
?>
