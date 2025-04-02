<?php require_once("includes/connection.php");
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title> Магазин електроніки </title>
	<link href="css/style.css" media="screen" rel="stylesheet">
</head>

<body>
	<header>
		<nav>
			<ul>
				<li><a href="index.php">Головна</a></li>
				<li><a href="products.php">Товари</a></li>
				<li><a href="favorites.php">Обране</a></li>
				<li><a href="cart.php">Кошик</a></li>
				<?php if (isset($_SESSION['session_username']) && $_SESSION['session_username']): ?>
					<li><a href="edit_products.php">Змінити товари</a></li>
					<li><a href="logout.php">Вийти</a></li>
				<?php else: ?>
					<li><a href="login.php">Увійти</a>/<a href="register.php">Зареєструватися</a></li>
				<?php endif; ?>
			</ul>
		</nav>
	</header>