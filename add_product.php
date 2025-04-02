<?php
require_once 'includes/header.php';

if (!isset($_SESSION['session_username'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    $id_category = $_POST['category'] ?? null;

    // Санітарна очистка
    $title = htmlspecialchars(trim($title));
    $description = htmlspecialchars(trim($description));
    $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);
    $id_category = filter_var($id_category, FILTER_SANITIZE_NUMBER_INT);

    // Валідація
    if (empty($title) || empty($price) || empty($quantity) || $id_category === null) {
        echo "<script>alert('Будь ласка, заповніть всі поля.'); window.location.href='add_product.php';</script>";
        exit();
    }

    if ($price <= 0 || $quantity <= 0) {
        echo "<script>alert('Ціна та кількість повинні бути більші за 0.'); window.location.href='add_product.php';</script>";
        exit();
    }

    try {
        $stmt_insert_product = $pdo->prepare("INSERT INTO products (title, description, price, quantity) VALUES (:title, :description, :price, :quantity)");
        $stmt_insert_product->bindParam(':title', $title);
        $stmt_insert_product->bindParam(':description', $description);
        $stmt_insert_product->bindParam(':price', $price);
        $stmt_insert_product->bindParam(':quantity', $quantity);

        if ($stmt_insert_product->execute()) {
            $id_product = $pdo->lastInsertId();

            $stmt_insert_product_category = $pdo->prepare("INSERT INTO product_category (id_product, id_category) VALUES (:id_product, :id_category)");
            $stmt_insert_product_category->bindParam(':id_product', $id_product);
            $stmt_insert_product_category->bindParam(':id_category', $id_category);

            if ($stmt_insert_product_category->execute()) {
                echo "<script>alert('Товар успішно додано.'); window.location.href='add_product.php';</script>";
            } else {
                echo "<script>alert('Сталася помилка при додаванні категорії товару.'); window.location.href='add_product.php';</script>";
            }
        } else {
            echo "<script>alert('Сталася помилка при додаванні товару.'); window.location.href='add_product.php';</script>";
        }
    } catch (PDOException $e) {
        echo "Помилка: " . htmlspecialchars($e->getMessage());
    }
}

try {
    $stmt_categories = $pdo->prepare("SELECT id_category, title FROM categories");
    $stmt_categories->execute();
    $result_categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Помилка: " . htmlspecialchars($e->getMessage());
}
?>

<div class="container mform">
    <h2>Додати товар</h2>
    <form action="add_product.php" method="post">
        <label for="title">Назва:</label><br>
        <input class="input" type="text" name="title" required><br>
    
        <label for="description">Опис:</label><br>
        <textarea class="input" name="description"></textarea><br>
    
        <label for="price">Ціна:</label><br>
        <input class="input" type="number" name="price" step="0.01" required><br>
    
        <label for="quantity">Кількість:</label><br>
        <input class="input" type="number" name="quantity" required><br>
    
        <label for="category">Категорія:</label><br>
        <select class="input" name="category" required>
            <option value="">Оберіть категорію</option>
            <?php foreach ($result_categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['id_category']); ?>">
                    <?php echo htmlspecialchars($category['title']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>
    
        <input class="button" type="submit" value="Створити">
    </form>
</div>

<?php
require_once 'includes/footer.php'; 
?>
