<?php

	$user_id=$_POST['confirm_id'];
	$user_name=$_POST['confirm_name'];
	$user_pass=$_POST['confirm_pass'];
	
	echo "あなたのユーザー情報は"."<br>";
	echo "お名前：".$user_name."<br>";
	echo "ID：".$user_id."<br>";
	echo "パスワード：".$user_pass."<br>";
	echo "です";
	echo "<br>";

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title>ユーザー情報確認ページ</title>
</head>

<body>
	<a href="http://co-19-208.99sv-coco.com/kadai_3/loginpage.html">掲示板へ</a>
</body>
</html>

