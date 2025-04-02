<?php
require_once 'includes/header.php';

if (!isset($_SESSION['session_username'])) {
    header('Location: index.php');
    exit();
}

// Санітарна очистка та перевірка ID продукту
$id_product = isset($_POST['id_product']) ? intval($_POST['id_product']) : null; // Перевірка на ціле число

if ($id_product === null) {
    echo "<script>alert('Необхідно вказати ID товару.'); window.location.href='edit_products.php';</script>";
    exit();
}

try {
    $stmt_product = $pdo->prepare("SELECT p.title, p.description, p.price, p.quantity, pc.id_category 
                                    FROM products p 
                                    JOIN product_category pc ON p.id_product = pc.id_product 
                                    WHERE p.id_product = :id_product");
    $stmt_product->bindParam(':id_product', $id_product, PDO::PARAM_INT);
    $stmt_product->execute();
    $result_product = $stmt_product->fetch(PDO::FETCH_ASSOC);

    if (!$result_product) {
        echo "<script>alert('Товар не знайдено.'); window.location.href='edit_products.php';</script>";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        // Санітарна очистка та перевірка введених даних
        $title = isset($_POST['title']) ? trim(strip_tags($_POST['title'])) : ''; // Очищення та обрізання
        $description = isset($_POST['description']) ? trim(strip_tags($_POST['description'])) : ''; // Очищення та обрізання
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0; // Перевірка на число з плаваючою точкою
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0; // Перевірка на ціле число
        $id_category = isset($_POST['category']) ? intval($_POST['category']) : null; // Перевірка на ціле число

        // Перевірка, чи всі поля заповнені коректно
        if (empty($title) || empty($price) || $id_category === null || $quantity === null) {
            echo "<script>alert('Будь ласка, заповніть всі поля.'); window.location.href='edit.php?id_product=" . htmlspecialchars($id_product) . "';</script>";
            exit();
        }

        // Оновлення продукту в базі даних
        $stmt_update_product = $pdo->prepare("UPDATE products SET title = :title, description = :description, price = :price, quantity = :quantity WHERE id_product = :id_product");
        $stmt_update_product->bindParam(':title', $title);
        $stmt_update_product->bindParam(':description', $description);
        $stmt_update_product->bindParam(':price', $price);
        $stmt_update_product->bindParam(':quantity', $quantity);
        $stmt_update_product->bindParam(':id_product', $id_product);

        if ($stmt_update_product->execute()) {
            // Оновлення категорії продукту в базі даних
            $stmt_update_category = $pdo->prepare("UPDATE product_category SET id_category = :id_category WHERE id_product = :id_product");
            $stmt_update_category->bindParam(':id_category', $id_category);
            $stmt_update_category->bindParam(':id_product', $id_product);

            if ($stmt_update_category->execute()) {
                echo "<script>alert('Товар успішно оновлено.'); window.location.href='edit_products.php';</script>";
            } else {
                echo "<script>alert('Сталася помилка при оновленні категорії товару.'); window.location.href='edit.php?id_product=" . htmlspecialchars($id_product) . "';</script>";
            }
        } else {
            echo "<script>alert('Сталася помилка при оновленні товару.'); window.location.href='edit.php?id_product=" . htmlspecialchars($id_product) . "';</script>";
        }
    }

    $stmt_categories = $pdo->prepare("SELECT id_category, title FROM categories");
    $stmt_categories->execute();
    $result_categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Помилка: " . htmlspecialchars($e->getMessage());
}
?>

<div class="container mform">
    <h2>Редагувати Товар</h2>
    <form action="edit.php" method="post">
        <input class="input" type="hidden" name="id_product" value="<?php echo htmlspecialchars($id_product); ?>">

        <label for="title">Назва:</label>
        <input class="input" type="text" name="title" value="<?php echo htmlspecialchars($result_product['title']); ?>" required><br>

        <label for="description">Опис:</label>
        <textarea class="input" name="description"><?php echo htmlspecialchars($result_product['description']); ?></textarea><br>

        <label for="price">Ціна:</label>
        <input class="input" type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($result_product['price']); ?>" required><br>

        <label for="quantity">Кількість:</label>
        <input class="input" type="number" name="quantity" value="<?php echo htmlspecialchars($result_product['quantity']); ?>" required><br>

        <label for="category">Категорія:</label>
        <select class="input" name="category" required>
            <option value="">Оберіть категорію</option>
            <?php foreach ($result_categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['id_category']); ?>"
                    <?php if ($category['id_category'] == $result_product['id_category']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($category['title']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <input class="button" type="submit" name="update" value="Змінити">
    </form>
</div>

<?php
require_once 'includes/footer.php';
?>
