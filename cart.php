<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Будь ласка, увійдіть у систему, щоб переглянути кошик.'); window.location.href='login.php';</script>";
    exit();
}

$id_user = $_SESSION['user_id'];

try {
    $sql_cart_items = "SELECT ci.id_cart_item, p.id_product, p.title, p.price, ci.quantity 
                       FROM cart_items ci 
                       JOIN products p ON ci.id_product = p.id_product 
                       JOIN carts c ON ci.id_cart = c.id_cart 
                       WHERE c.id_user = :id_user";
    
    $stmt_cart_items = $pdo->prepare($sql_cart_items);
    $stmt_cart_items->execute([':id_user' => $id_user]);
    $cart_items = $stmt_cart_items->fetchAll(PDO::FETCH_ASSOC);

    if (count($cart_items) > 0) {
        echo "<h2>Ваш кошик</h2>";
        echo "<table id='products_table'>
                <tr>
                    <th>Назва</th>
                    <th>Ціна</th>
                    <th>Кількість</th>
                    <th>Сума</th>
                    <th>Дії</th>
                </tr>";

        $total_price = 0;

        foreach ($cart_items as $cart_item) {
            $item_total_price = $cart_item['price'] * $cart_item['quantity'];
            $total_price += $item_total_price;

            echo "<tr>
                    <td>" . htmlspecialchars($cart_item['title']) . "</td>
                    <td>" . htmlspecialchars($cart_item['price']) . "</td>
                    <td>" . htmlspecialchars($cart_item['quantity']) . "</td>
                    <td>" . htmlspecialchars(number_format($item_total_price, 2)) . "</td>
                    <td>
                        <form action='remove_from_cart.php' method='post' style='display:inline;'>
                            <input type='hidden' name='id_cart_item' value='" . htmlspecialchars($cart_item['id_cart_item']) . "' />
                            <input type='submit' value='Видалити з кошика' />
                        </form>
                    </td>
                  </tr>";
        }

        echo "</table>";
        echo "<h3>Загальна сума: " . htmlspecialchars(number_format($total_price, 2)) . "</h3>";
    } else {
        echo "<p>Ваш кошик порожній.</p>";
    }
} catch (PDOException $e) {
    echo "Помилка при отриманні товарів кошика: " . $e->getMessage();
}

require_once 'includes/footer.php';
?>
