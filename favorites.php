<?php
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Будь ласка, увійдіть у систему, щоб переглянути обране.'); window.location.href='login.php';</script>";
    exit();
}

$id_user = $_SESSION['user_id'];

try {
    $sql_favorites = "SELECT p.id_product, p.title, p.description, p.price, p.image_url 
                      FROM favorites f 
                      JOIN products p ON f.id_product = p.id_product 
                      WHERE f.id_user = :id_user";
    
    $stmt_favorites = $pdo->prepare($sql_favorites);
    $stmt_favorites->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt_favorites->execute();
    $result_favorites = $stmt_favorites->fetchAll(PDO::FETCH_ASSOC);

    if (count($result_favorites) > 0) {
        echo "<h2>Ваші обрані товари</h2>";
        echo "<table id='products_table'>
                <tr>
                    <th>Назва</th>
                    <th>Опис</th>
                    <th>Ціна</th>
                    <th>Дії</th>
                </tr>";
        
        foreach ($result_favorites as $favorite) {
            // Санітарна очистка виведених даних
            $title = htmlspecialchars($favorite['title'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($favorite['description'], ENT_QUOTES, 'UTF-8');
            $price = htmlspecialchars($favorite['price'], ENT_QUOTES, 'UTF-8');
            $id_product = htmlspecialchars($favorite['id_product'], ENT_QUOTES, 'UTF-8');
            
            echo "<tr>
                    <td>{$title}</td>
                    <td>{$description}</td>
                    <td>{$price}</td>
                    <td>
                        <form action='add_to_cart.php' method='post' style='display:inline;'>
                            <input type='hidden' name='id_product' value='{$id_product}' />
                            <input type='submit' value='Додати у корзину' />
                        </form>
                        <form action='remove_from_favorites.php' method='post' style='display:inline;'>
                            <input type='hidden' name='id_product' value='{$id_product}' />
                            <input type='submit' value='Видалити з обраного' />
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>У вас немає обраних товарів.</p>";
    }
} catch (PDOException $e) {
    echo "Помилка при отриманні обраних товарів: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
require_once 'includes/footer.php';
?>
