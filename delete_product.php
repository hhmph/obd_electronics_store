<?php
require_once 'includes/header.php';

if (!isset($_SESSION['session_username'])) {
    header('Location: index.php');
    exit();
}

$id_product = isset($_POST['id_product']) ? filter_var($_POST['id_product'], FILTER_SANITIZE_NUMBER_INT) : null;

if ($id_product === null) {
    echo "<script>alert('Необхідно вказати ID товару.'); window.location.href='edit_products.php';</script>";
    exit();
}

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    try {
        $stmt_delete_product = $pdo->prepare("DELETE FROM products WHERE id_product = :id_product");
        $stmt_delete_product->bindParam(':id_product', $id_product, PDO::PARAM_INT);

        if ($stmt_delete_product->execute()) {
            $stmt_delete_category = $pdo->prepare("DELETE FROM product_category WHERE id_product = :id_product");
            $stmt_delete_category->bindParam(':id_product', $id_product, PDO::PARAM_INT);
            $stmt_delete_category->execute();

            echo "<script>alert('Товар успішно видалено.'); window.location.href='edit_products.php';</script>";
        } else {
            echo "<script>alert('Сталася помилка при видаленні товару.'); window.location.href='edit_products.php';</script>";
        }
        
        // Закриття запитів
        $stmt_delete_product->close();
        $stmt_delete_category->close();
        
    } catch (PDOException $e) {
        echo "Помилка: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "<h2>Ви впевнені, що хочете видалити цей товар?</h2>";
    echo "<div class='container mform'>
            <form action='delete_product.php' method='post'>
                <input class='input' type='hidden' name='id_product' value='" . htmlspecialchars($id_product) . "' />
                <input class='button' type='submit' name='confirm' value='yes'/><br><br>
                <a href='edit_products.php'>no</a>
            </form>
            </div>";
}
?>
