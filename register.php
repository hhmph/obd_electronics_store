<?php include("includes/header.php"); ?>

<div class="container mform">
    <h1>Реєстрація</h1>
    <form action="register.php" method="post" name="registerform" onsubmit="return validateForm()">

    <p>
        <label for="user_login">Ім'я<br>
        <input class="input" name="first_name" size="20" type="text" value="">
        </label>
    </p>
    <p>
        <label for="user_login">Прізвище<br>
        <input class="input"  name="last_name" size="20" type="text" value="">
        </label>
    </p>
    <p>
        <label for="user_pass">E-mail<br>
        <input class="input" name="email" size="20" type="email" value="">
        </label>
        <span id="emailError" class="error"></span>
    </p>
    <p>
        <label for="user_pass">Логін<br>
        <input class="input" name="login" size="20" type="text" value="">
        </label>
        <span id="loginError" class="error"></span>
    </p>
    <p>
        <label for="user_pass">Пароль<br>
        <input class="input" name="password" size="20" type="password" value="">
        </label>
        <span id="passwordError" class="error"></span>
    </p>
    <p class="submit">
        <input class="button" name="register" type="submit" value=" Зареєструватися">
    </p>
    <p class="regtext">Вже зареєстровані? <a href="login.php">Введіть ім'я користувача!</a></p>
    </form>
</div>

<?php include("includes/footer.php"); ?>

<script>
function validateForm() {
    var email = document.forms["registerform"]["email"].value;
    var login = document.forms["registerform"]["login"].value;
    var password = document.forms["registerform"]["password"].value;

    var emailError = document.getElementById("emailError");
    var loginError = document.getElementById("loginError");
    var passwordError = document.getElementById("passwordError");

    // очистити попередні помилки
    emailError.innerHTML = "";
    loginError.innerHTML = "";
    passwordError.innerHTML = "";

    // Валідація електронної пошти
    var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!email.match(emailPattern)) {
        emailError.innerHTML = "Невірний формат E-mail!";
        return false;
    }

    // Валідація логіну (літери та цифри, від 3 до 20 символів)
    var loginPattern = /^[a-zA-Z0-9]{3,20}$/;
    if (!login.match(loginPattern)) {
        loginError.innerHTML = "Логін повинен бути від 3 до 20 символів і містити тільки літери та цифри!";
        return false;
    }

    // Валідація паролю (мінімум 8 символів, як мінімум 1 цифра та одна велика літера)
    var passwordPattern = /^(?=.*\d)(?=.*[A-Z]).{8,}$/;
    if (!password.match(passwordPattern)) {
        passwordError.innerHTML = "Пароль має містити щонайменше 8 символів, 1 цифру та 1 велику літеру!";
        return false;
    }

    return true;  // Якщо всі перевірки успішні
}
</script>

<?php
if (isset($_POST["register"])) {
    // Basic validation for required fields
    if (!empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['email']) && !empty($_POST['login']) && !empty($_POST['password'])) {

        // Sanitization
        $first_name = htmlspecialchars(trim($_POST['first_name']));
        $last_name = htmlspecialchars(trim($_POST['last_name']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);  // Sanitizing email
        $login = htmlspecialchars(trim($_POST['login']));
        $password = trim($_POST['password']);

        // Validation
        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<p class='error'>Невірний формат E-mail!</p>";
        } 
        // Password validation (minimum 8 characters, at least 1 number, 1 uppercase letter)
        elseif (!preg_match('/^(?=.*\d)(?=.*[A-Z]).{8,}$/', $password)) {
            echo "<p class='error'>Пароль має містити щонайменше 8 символів, 1 цифру та 1 велику літеру!</p>";
        }
        // Login validation (e.g., minimum length, alphanumeric)
        elseif (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $login)) {
            echo "<p class='error'>Логін повинен бути від 3 до 20 символів і містити тільки літери та цифри!</p>";
        } else {
            try {
                // Check if the username already exists
                $stmt_check_user = $pdo->prepare("SELECT * FROM users WHERE login = :login");
                $stmt_check_user->bindParam(':login', $login);
                $stmt_check_user->execute();

                if ($stmt_check_user->rowCount() == 0) {
                    // Insert user into database
                    $stmt_insert_user = $pdo->prepare("INSERT INTO users (first_name, last_name, email, login, password) VALUES (:first_name, :last_name, :email, :login, :password)");
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_insert_user->bindParam(':first_name', $first_name);
                    $stmt_insert_user->bindParam(':last_name', $last_name);
                    $stmt_insert_user->bindParam(':email', $email);
                    $stmt_insert_user->bindParam(':login', $login);
                    $stmt_insert_user->bindParam(':password', $hashed_password);

                    if ($stmt_insert_user->execute()) {
                        echo "<p class='success'>Обліковий запис успішно створено.</p>";
                    } else {
                        echo "<p class='error'>Не вдалося створити обліковий запис.</p>";
                    }
                } else {
                    echo "<p class='error'>Цей логін вже існує! Спробуйте інший.</p>";
                }
            } catch (PDOException $e) {
                echo "Помилка: " . htmlspecialchars($e->getMessage());
            }
        }
    } else {
        echo "<p class='error'>Усі поля є обов'язковими для заповнення!</p>";
    }
}
?>
