<?php

try{

	//DBへ接続
	$pdo = new PDO(
	'mysql:host=localhost;dbname=co_19_208_99sv_coco_com;charaset=utf8',
	'co-19-208.99sv-c',
	'A8cjtYu3',
	[
		PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES=>false,
	]
	);
	
	//echo "接続成功";
	//echo "<br>";
	
	//-----------------【CREATEでテーブルを作る】----------------
	$tb_name='keijiban_userinfo';
	
	$sql= "CREATE TABLE IF NOT EXISTS $tb_name (
	id VARCHAR(30) PRIMARY KEY,
	name VARCHAR(20),
	password VARCHAR(10) 
	)ENGINE=InnoDB DEFAULT CHARSET=utf8";

	$stmt=$pdo->prepare($sql);
	$stmt->execute();
	
	/*------------テーブルの存在を確認--------------
	$rs=$pdo->query("SHOW TABLES");
	$table=$rs->fetchAll(PDO::FETCH_COLUMN);
	if(in_array($tb_name,$table)){
		echo "テーブルの存在を確認しました"."<br>";
		echo "テーブル名：".$tb_name."<br>";
	}else{
		echo "テーブル：".$tb_name."がありません";
	}
	-----------------------------------------------*/
	
	if( isset($_POST['btn_regi']) ) {
	
		//---------未入力のバリデーション--------
		if(empty($_POST['regi_name'])){
			echo '名前を入力してください！';
			exit();
		}
		
		if(empty($_POST['regi_pass'])){
			echo 'パスワードを入力してください！';
			exit();
		}
		
		//---------文字数制限のバリデーション---------
		$number_of_words_ps=mb_strlen($_POST['regi_pass']);
		$number_of_words_nm=mb_strlen($_POST['regi_name']);
		
		if($number_of_words_ps>10){
			echo 'パスワードは10文字以内で入力してください';
			exit();
		}
		
		if($number_of_words_nm>20){
			echo '名前は20文字以内で入力してください';
			exit();
		}
		
		//変数を格納する
		$user_name = $_POST['regi_name'];
		$user_pass = $_POST['regi_pass'];
		$user_id=uniqid();
			
		//INSERTでテーブルに値を追加
		$sql="INSERT INTO $tb_name (id,name,password) VALUES (:id,:name,:password)";
		$stmt=$pdo->prepare($sql);
		$stmt->bindValue(':id',$user_id,PDO::PARAM_STR);
		$stmt->bindValue(':name',$user_name,PDO::PARAM_STR);
		$stmt->bindValue(':password',$user_pass,PDO::PARAM_STR);
		$stmt->execute();
		
		/*---------INSERT内容をブラウザで確認----------
		$stmt=$pdo->prepare("SELECT * FROM $tb_name");
		$stmt->execute();
		
		foreach($stmt as $loop){
			echo "id:".$loop['id']."<br>".
				 "name:".$loop['name']."<br>".
				 "password:".$loop['password']."<br>";
		}
		------------------------------------------------*/
		
		/*------------テーブルを削除する--------------
		$stmt=$pdo->prepare("drop table $tb_name");
		$stmt->execute();
		---------------------------------------------*/
		
		echo "ご登録ありがとうございます";
	}
			
}catch(PDOException $e){
	header('Content_Type:text/plain;charset=UTF-8',true,500);
	exit($e->getMessage());
}

//接続を閉じる
$pdo=null;

?>

<!-------------【ID発行ページ[HTML]】------------->
<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="utf-8">
		<title>ID発行ページ</title>
	</head>
				
	<body>
		<h2>ID発行ページ</h2>
		<p>あなたのユーザーIDは<?php echo $user_id; ?>です</p>
		<form method="post" action="user_confirm.php">
		<input type="hidden" name="confirm_id" value="<?php echo $user_id; ?>">
		<input type="hidden" name="confirm_name" value="<?php echo $user_name; ?>">
		<input type="hidden" name="confirm_pass" value="<?php echo $user_pass; ?>">
		<input type="submit" name="btn_confirm" value="ユーザー情報を確認する">
		</form>
	</body>
</html>


