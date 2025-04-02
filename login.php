<?php require_once("includes/header.php"); ?>

<div class="container mform">
    <h1>Вхід</h1>
    <form action="" method="post">
        <p>
            <label for="user_login">Ім'я користувача<br>
                <input class="input" name="login" size="20" type="text" value="">
            </label>
        </p>

        <p>
            <label for="user_pass">Пароль<br>
                <input class="input" name="password" size="20" type="password" value="">
            </label>
        </p>

        <p class="submit"><input class="button" name="login_btn" type="submit" value="Log In"></p>
        <p class="regtext">Ще не зареєстровані? <a href="register.php">Зареєструватися</a></p>
    </form>
</div>

<?php 
require_once("includes/footer.php");

if (isset($_SESSION["session_username"])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST["login_btn"])) {
    // Перевірка та санітарна очистка вводу
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Перевірка: чи не порожні поля
    if (empty($login) || empty($password)) {
        echo "Усі поля обов'язкові для заповнення!";
    } else {
        // Санітарна очистка: очищаємо спеціальні символи
        $login = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

        // Перевірка на валідність імені користувача (наприклад, тільки букви, цифри і підкреслення)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) {
            echo "Невірне ім'я користувача!";
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT id_user, login, password, role FROM users WHERE login = :login");
            $stmt->bindParam(':login', $login, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password'])) { 
                    $_SESSION['session_username'] = $user['login'];
                    $_SESSION['user_id'] = $user['id_user'];
                    header("Location: index.php");
                    exit();
                } else {
                    echo "Невірний пароль!";
                }
            } else {
                echo "Користувача не знайдено!";
            }
        } catch (PDOException $e) {
            echo "Помилка: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
