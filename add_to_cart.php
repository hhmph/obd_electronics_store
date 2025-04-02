<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Будь ласка, увійдіть у систему, щоб додати товар до кошика.'); window.location.href='login.php';</script>";
    exit();
}

$id_product = isset($_POST['id_product']) ? filter_var($_POST['id_product'], FILTER_SANITIZE_NUMBER_INT) : null;
$id_user = $_SESSION['user_id'];

if ($id_product === null) {
    echo "<script>alert('Необхідно вказати ID товару.'); window.location.href='products.php';</script>";
    exit();
}

try {
    $stmt_cart = $pdo->prepare("SELECT id_cart FROM carts WHERE id_user = :id_user");
    $stmt_cart->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->fetch(PDO::FETCH_ASSOC);

    if (!$result_cart) {
        $stmt_create_cart = $pdo->prepare("INSERT INTO carts (id_user) VALUES (:id_user)");
        $stmt_create_cart->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt_create_cart->execute();
        
        $id_cart = $pdo->lastInsertId();
    } else {
        $id_cart = $result_cart['id_cart'];
    }

    $stmt_check_item = $pdo->prepare("SELECT id_cart_item, quantity, price FROM cart_items WHERE id_cart = :id_cart AND id_product = :id_product");
    $stmt_check_item->bindParam(':id_cart', $id_cart, PDO::PARAM_INT);
    $stmt_check_item->bindParam(':id_product', $id_product, PDO::PARAM_INT);
    $stmt_check_item->execute();
    
    if ($stmt_check_item->rowCount() > 0) {
        $item_data = $stmt_check_item->fetch(PDO::FETCH_ASSOC);
        $new_quantity = $item_data['quantity'] + 1;
        $new_price = ($item_data['price'] / $item_data['quantity']) * $new_quantity; 

        $stmt_update_item = $pdo->prepare("UPDATE cart_items SET quantity = :quantity, price = :price WHERE id_cart_item = :id_cart_item");
        $stmt_update_item->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
        $stmt_update_item->bindParam(':price', $new_price);
        $stmt_update_item->bindParam(':id_cart_item', $item_data['id_cart_item'], PDO::PARAM_INT);
        
        if ($stmt_update_item->execute()) {
            echo "<script>alert('Кількість товару в кошику оновлено.'); window.location.href='products.php';</script>";
        } else {
            echo "<script>alert('Сталася помилка при оновленні товару в кошику.'); window.location.href='products.php';</script>";
        }
        
    } else {
        $stmt_price_query = $pdo->prepare("SELECT price FROM products WHERE id_product = :id_product");
        $stmt_price_query->bindParam(':id_product', $id_product, PDO::PARAM_INT);
        $stmt_price_query->execute();
        
        if ($result_price_query = $stmt_price_query->fetch(PDO::FETCH_ASSOC)) {
            $price = floatval($result_price_query['price']);
            
            $stmt_add_item = $pdo->prepare("INSERT INTO cart_items (id_cart, id_product, quantity, price) VALUES (:id_cart, :id_product, :quantity, :price)");
            $quantity = 1;
            
            if ($stmt_add_item) {
                $stmt_add_item->bindParam(':id_cart', $id_cart, PDO::PARAM_INT);
                $stmt_add_item->bindParam(':id_product', $id_product, PDO::PARAM_INT);
                $stmt_add_item->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmt_add_item->bindParam(':price', $price);
                
                if ($stmt_add_item->execute()) {
                    echo "<script>alert('Товар успішно додано до кошика.'); window.location.href='products.php';</script>";
                } else {
                    echo "<script>alert('Сталася помилка при додаванні товару до кошика.'); window.location.href='products.php';</script>";
                }
            }
            
        } else {
            echo "<script>alert('Товар не знайдено.'); window.location.href='products.php';</script>";
            exit();
        }
    }
} catch (PDOException $e) {
    echo "Помилка: " . htmlspecialchars($e->getMessage());
}
?>
