<?php
require_once 'includes/header.php';

$show_available = isset($_POST['show_available']) && $_POST['show_available'] === '1'; // Валідна перевірка для чекбоксу

try {
    // Санітарна очистка для категорій
    $sql_categories = "SELECT * FROM categories";
    $stmt_categories = $pdo->prepare($sql_categories);
    $stmt_categories->execute();
    $result_categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

    if (count($result_categories) > 0) {
        echo '<form method="post" action="">';
        // Перевірка на наявність значення у чекбоксі для фільтра
        echo '<label><p><input type="checkbox" name="show_available" value="1" ' . ($show_available ? 'checked' : '') . '> Показувати лише наявні товари</label>  ';
        echo '<input type="submit" value="Застосувати"></p>';
        echo '</form>';

        foreach ($result_categories as $category) {
            $id_category = intval($category['id_category']); // Перевірка і санітарна очистка ID категорії

            $sql_products = "SELECT p.id_product, p.title, p.description, p.price, p.quantity 
                             FROM products p 
                             JOIN product_category pc ON p.id_product = pc.id_product 
                             WHERE pc.id_category = :id_category";

            if ($show_available) {
                $sql_products .= " AND p.quantity > 0";
            }

            $stmt_products = $pdo->prepare($sql_products);
            $stmt_products->bindParam(':id_category', $id_category, PDO::PARAM_INT);
            $stmt_products->execute();
            $result_products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

            if (count($result_products) > 0) {
                echo "<h2> Категорія: " . htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') . "</h2>";
                echo "<table id='products_table'>
                        <tr>
                            <th>Назва</th>
                            <th>Опис</th>
                            <th>Ціна</th>
                            <th>Кількість</th>
                            <th>Дії</th>
                        </tr>";

                foreach ($result_products as $product) {
                    // Санітарна очистка для виведення даних продукту
                    $product_id = intval($product['id_product']); // Перевірка ID продукту
                    $product_title = htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8');
                    $product_description = htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8');
                    $product_price = htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8');
                    $product_quantity = htmlspecialchars($product['quantity'], ENT_QUOTES, 'UTF-8');

                    echo "<tr>
                            <td>{$product_title}</td>
                            <td>{$product_description}</td>
                            <td>{$product_price}</td>
                            <td>{$product_quantity}</td>
                            <td>
                                <form action='add_to_favorites.php' method='post' style='display:inline;'>
                                    <input type='hidden' name='id_product' value='" . $product_id . "' />
                                    <input type='submit' value='Додати у обране' />
                                </form>
                                <form action='add_to_cart.php' method='post' style='display:inline;'>
                                    <input type='hidden' name='id_product' value='" . $product_id . "' />
                                    <input type='submit' value='Додати у кошик' />
                                </form>
                            </td>
                          </tr>";
                }
                echo "</table>";
            }
        }
    } else {
        echo "<p>Категорії не знайдено.</p>";
    }
} catch (PDOException $e) {
    // Санітарна очистка для виведення повідомлень про помилки
    echo "Помилка: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once 'includes/footer.php';
?>
