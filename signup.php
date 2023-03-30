<?php

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/styles.css">
  <title>Signup</title>
</head>
<body>
    <div class="login-form">
        <h1>Todo 新規会員登録</h1>
        <form action="register.php" method="post">
        <div class="username">
            <input type="text" name="name" placeholder="お名前" required>
        </div>
        <div class="usermail">
            <input type="email" name="mail" placeholder="メールアドレス" required>
        </div>
        <div class="userpass">
            <input type="password" name="pass" placeholder="パスワード" required>
        </div>
        <div class="userbutton">
            <input type="submit" value="新規登録">
        </div>
        </form>
        <p>すでに登録済みの方は<a href="login.php">こちら</a></p>          
    </div>
</body>
</html>