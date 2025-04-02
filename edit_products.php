<?php
require_once 'includes/header.php';

if (!isset($_SESSION['session_username'])) {
    header('Location: index.php');
    exit();
}

$show_available = isset($_POST['show_available']) ? true : false;

try {
    $stmt_categories = $pdo->prepare("SELECT * FROM categories");
    $stmt_categories->execute();
    $result_categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

    if (count($result_categories) > 0) {
        echo '<form method="post" action="">';
        echo '<label><p><input type="checkbox" name="show_available" value="1" ' . ($show_available ? 'checked' : '') . '> Показувати лише наявні товари</label>  ';
        echo '<input type="submit" value="Застосувати"></p>';
        echo '<p><a href="add_product.php">Додати товар</a></p>';
        echo '</form>';

        foreach ($result_categories as $category) {
            $id_category = $category['id_category'];

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
                echo "<h2> Категорія: " . htmlspecialchars($category['title']) . "</h2>";
                echo "<table id='products_table'>
                        <tr>
                            <th>Назва</th>
                            <th>Опис</th>
                            <th>Ціна</th>
                            <th>Кількість</th>
                            <th>Дії</th>
                        </tr>";

                foreach ($result_products as $product) {
                    echo "<tr>
                            <td>" . htmlspecialchars($product['title']) . "</td>
                            <td>" . htmlspecialchars($product['description']) . "</td>
                            <td>" . htmlspecialchars($product['price']) . "</td>
                            <td>" . htmlspecialchars($product['quantity']) . "</td>
                            <td>
                                <form action='edit.php' method='post' style='display:inline;'>
                                    <input type='hidden' name='id_product' value='" . htmlspecialchars($product['id_product']) . "' />
                                    <input type='submit' value='Редагувати' />
                                </form>
                                <form action='delete_product.php' method='post' style='display:inline;'>
                                    <input type='hidden' name='id_product' value='" . htmlspecialchars($product['id_product']) . "' />
                                    <input type='submit' value='Видалити' />
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
    echo "Помилка: " . htmlspecialchars($e->getMessage());
}

require_once 'includes/footer.php';
?>
