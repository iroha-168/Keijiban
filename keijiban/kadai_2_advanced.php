<?php

try{

	//DBへ接続
	$pdo = new PDO(
	'mysql:host=localhost;dbname=****',
	'****',
	'****',
	[
		PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES=>false,
	]
	);
	
	//-----------------【CREATEでテーブルを作る】----------------
	$tb_name1='keijiban_new';
	$tb_name2='keijiban_userinfo';
	
	$num_update=<<<_UPDATE_
		SET @i := 0;
		UPDATE $tb_name1 SET number = (@i := @i+1);
_UPDATE_;
	
	$sql= "CREATE TABLE IF NOT EXISTS $tb_name1 (
	number INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(20),
	message VARCHAR(200),
	regi_date DATETIME,
	password VARCHAR(10),
	fname VARCHAR(100),
	extension VARCHAR(100),
	path VARCHAR(100)
	)ENGINE=InnoDB DEFAULT CHARSET=utf8";

	$stmt=$pdo->prepare($sql);
	$stmt->execute();
	
	/*-------テーブルの存在を確認---------
	$rs=$pdo->query("SHOW TABLES");
	$table=$rs->fetchAll(PDO::FETCH_COLUMN);
	if(in_array($tb_name1,$table)){
		echo "テーブルの存在を確認しました"."<br>";
		echo "テーブル名：".$tb_name1."<br>";
	}else{
		echo "テーブル：".$tb_name1."がありません";
	}
	--------------------------------------*/
	
	/*----テーブルの内容(カラムの設定)を確認する-----	
	$select=$pdo->query("SELECT * FROM $tb_name1");
	$total_column=$select->columnCount();
	for($i=0; $i<$total_column; $i++){
		$meta=$select->getColumnMeta($i);
		$column[]=$meta['name'];
	}
	print_r($column);
	echo "<br>";
	-------------------------------------------------*/

	// 変数の初期化
	$now_date = null;
	$data = null;
	$split_data = null;
	$message = array();
	$success_message = null;
	$delete = null;
	$del_con = null;
	$del_data = array();
	$message_array = array();
	
	//-------------------【MySQLにデータを書き込む】-------------------

	if( isset($_POST['btn_submit']) ) {
	
		//配列の初期化等
		$mimetype_array = array(); 
		
		$number=NULL;
		$name=$_POST['view_name'];
		$message=$_POST['message'];
		$password=$_POST['pass'];
		
		//---------未入力のバリデーション--------
		if(empty($name)){
			echo '名前を入力してください！';
			exit();
		}
		
		if(empty($message)){
			echo 'メッセージを入力してください！';
			exit();
		}
		
		if(empty($password)){
			echo 'パスワードを入力してください！';
			exit();
		}
		
		//---------文字数制限のバリデーション---------
		$number_of_words_ps=mb_strlen($password);
		$number_of_words_nm=mb_strlen($name);
		$number_of_words_ms=mb_strlen($message);
		
		if($number_of_words_ps>10){
			echo 'パスワードは10文字以内で入力してください';
			exit();
		}
		
		if($number_of_words_nm>20){
			echo '名前は20文字以内で入力してください';
			exit();
		}
		
		if($number_of_words_ms>200){
			$over = $number_of_words_ms-200;
			echo 'メッセージは200文字以内で入力してください';
			echo $over.'文字多いです';
			exit();
		}
						
		//-------------------【アップロードされたファイルのパスをDBに格納】----------------------
	    if (isset($_FILES['upfile'])) {
	    	
	    	//配列の初期化等
			$mimetype_array = array(); 
			
			//ファイルの名前をユニークなものに設定
			$create_file_name = uniqid();
			$tmp_file = $_FILES['upfile']['tmp_name'];
			
			if(!empty($tmp_file)){
				$file = file_get_contents($tmp_file);
			}
			
			//ファイルの検証
			
			//ファイルのアップロードがなかった時
			if(!is_uploaded_file($tmp_file)){
		
				$sql = "INSERT INTO $tb_name1(number,name,message,regi_date,password) VALUES (:number,:name,:message,now(),:password)";
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue( ':number', $number, PDO::PARAM_INT );
				$stmt->bindValue( ':name', $name, PDO::PARAM_STR );
				$stmt->bindValue( ':message', $message, PDO::PARAM_STR );
				$stmt->bindValue( ':password', $password, PDO::PARAM_STR );
				$stmt -> execute();
				            
				$pdo->exec($num_update);
			
			//ファイルのアップロードがあった時
			}else{  
			
				//MIMEタイプを格納した配列を作成する
				$mimetype_array = array(
					'gif' => 'image/gif',
					'jpg' => 'image/jpeg',
					'png' => 'image/png',
					'mp4' => 'video/mp4',
				);
						
				//アップロードされたファイルの拡張子を$extensionに格納
				$extension = pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION);
				
					
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$type = finfo_file($finfo,$_FILES['upfile']['tmp_name']);
					
				//アップロードされたファイルのmimeタイプが$mimetype_arrayに当てはまれば...
				if(in_array($type, $mimetype_array, true)){
						
					//アップロードされたファイルが画像なら
					if($extension=='gif'||$extension=='jpg'||$extension=='png'){
							
						//画像ファイルの保存先の相対パスを作成
						$img_rela_path = dirname(__FILE__)."/img/".$create_file_name.".".$extension;
						
						//画像ファイルの保存先の絶対パスを
						$img_abs_path = "img/".$create_file_name.".".$extension;
								
						//指定した相対パスへファイルを保存
						move_uploaded_file($_FILES['upfile']['tmp_name'], $img_rela_path);
							
						//パスをDBへ保存
						$sql = "INSERT INTO $tb_name1(number,name,message,regi_date,password,fname, extension, path) VALUES (:number,:name,:message,now(),:password,:fname,:extension,:path)";
					    $stmt = $pdo->prepare($sql);
					    $stmt->bindValue( ':number', $number, PDO::PARAM_INT );
						$stmt->bindValue( ':name', $name, PDO::PARAM_STR );
						$stmt->bindValue( ':message', $message, PDO::PARAM_STR );
						$stmt->bindValue( ':password', $password, PDO::PARAM_STR );
					    $stmt -> bindValue(":fname",$create_file_name, PDO::PARAM_STR);
					    $stmt -> bindValue(":extension",$extension, PDO::PARAM_STR);
					    $stmt -> bindValue(":path",$img_abs_path, PDO::PARAM_STR);
					    $stmt -> execute();
					            
					    $pdo->exec($num_update);
				        
					    //アップロードされたファイルが動画なら        
					}else if($extension=='mp4'){
								
						//動画ファイルの保存先の相対パスを作成
						$vdo_rela_path = dirname(__FILE__)."/vdo/".$create_file_name.".".$extension;
						
						//動画ファイルの保存先の絶対パスを
						$vdo_abs_path = "vdo/".$create_file_name.".".$extension;
								
						//指定した相対パスへファイルを保存
						move_uploaded_file($_FILES['upfile']['tmp_name'], $vdo_rela_path);
								
						//INSERTでデータを入力
					    $sql = "INSERT INTO $tb_name1(number,name,message,regi_date,password,fname, extension, path) VALUES (:number,:name,:message,now(),:password,:fname,:extension,:path)";
					    $stmt = $pdo->prepare($sql);
					    $stmt->bindValue( ':number', $number, PDO::PARAM_INT );
						$stmt->bindValue( ':name', $name, PDO::PARAM_STR );
						$stmt->bindValue( ':message', $message, PDO::PARAM_STR );
						$stmt->bindValue( ':password', $password, PDO::PARAM_STR );
					    $stmt -> bindValue(':fname',$create_file_name, PDO::PARAM_STR);
					    $stmt -> bindValue(':extension',$extension, PDO::PARAM_STR);
					    $stmt -> bindValue(':path',$vdo_abs_path, PDO::PARAM_STR);
					    $stmt -> execute();
					            
					    $pdo->exec($num_update);
					         
					}else{
						echo "対象外のファイルです";
						exit();
						
					} //画像と動画の場合分け END
					
				} //if(in_array($type, $mimetype_array, true)) END
				
			} //if(!is_uploaded_file($tmp_file)) END
			
		} //if (isset($_FILES['upfile'])) END
		
	} //if( isset($_POST['btn_submit']) ) END	
		
		
		/*--------INSERT内容をブラウザで確認--------
		$stmt=$pdo->prepare("SELECT * FROM $tb_name1");
		$stmt->execute();
		
		foreach($stmt as $loop){
			echo "number:".$loop['number']."<br>".
				 "name:".$loop['name']."<br>".
				 "message:".$loop['message']."<br>".
				 "regi_date:".$loop['regi_date']."<br>".
				 "password:".$loop['password']."<br>".
				 "fname:".$loop['fname']."<br>".
				 "extension:".$loop['extension']."<br>".
				 "path:".$loop['path']."<br>";
		}
		--------------------------------------------*/
		
	//----------------【UPDATEで指定した番号のデータをデータを編集】-----------------

	//①入力フォームに再表示するために送信されてきた編集番号と一致する配列の値を取得
	if(isset($_POST['btn_edit'])){

		if((empty($_POST['editNo']))||(empty($_POST['pass']))){
			echo '編集番号とパスワードの両方を入力してください';
			exit();
		}
				
		$editNo = $_POST['editNo'];
		$edit_pass=$_POST['pass'];
		
		$sql="SELECT number,password FROM $tb_name1 WHERE number=$editNo";
		$stmt=$pdo->prepare($sql);
		$stmt->execute();
		
		foreach($stmt as $row){
			$num=$row['number'];
			$pass=$row['password'];
		}
		
		if( $pass==$edit_pass ){
			
			$sql="SELECT * FROM $tb_name1 WHERE number=$num";
			$stmt=$pdo->prepare($sql);
			$stmt->execute();
			
			foreach($stmt as $row){
				$edit_no=$row['number'];
				$edit_name=$row['name'];
				$edit_message=$row['message'];
				$edit_pass=$row['password']; 
			}
			
		}else{
			echo "パスワードまたは編集番号が間違っています";
			exit();
		}	
	}
	
	//②入力フォームから送られてきたメッセージをUPDATEで編集する
	if(isset($_POST['execute_edit'])){
		
		$editNo = $_POST['keep_editNo'];
		
		//$editNoと合うデータをUPDATE
		$sql="UPDATE $tb_name1 SET name=:name,message=:message,regi_date=now(),password=:password WHERE number=$editNo";
		$stmt=$pdo->prepare($sql);
		$stmt->bindValue(':name',$_POST['view_name'],PDO::PARAM_STR);
		$stmt->bindValue(':message',$_POST['message'],PDO::PARAM_STR);
		$stmt->bindValue(':password',$_POST['pass'],PDO::PARAM_STR);
		$stmt->execute();
	}

	//----------------【DELETEで指定したい番号のメッセージを削除】--------------------
	if(isset($_POST["btn_delete"])){
		$deleteNo=$_POST['deleteNo'];
		$delete_pass=$_POST['pass'];
		
		$sql="SELECT number, password FROM $tb_name1 WHERE number=$deleteNo";
		$stmt=$pdo->prepare($sql);
		$stmt->execute();
		
		foreach($stmt as $row){
			$num=$row['number'];
			$pass=$row['password'];
		}
		
		if($pass==$delete_pass){
			$sql="DELETE FROM $tb_name1 WHERE number=$num";
			$stmt=$pdo->prepare($sql);
			$stmt->execute();
			$pdo->exec($num_update);
			
		}else{
			echo "パスワードまたは削除番号が間違っています";
			exit();
		}
	}
	
	//------------------------【クッキーをリクエストする】--------------------------
	if(isset($_POST['btn_login'])){
		
		//未入力のバリデーション
		if((empty($_POST['login_id']))||(empty($_POST['login_pass']))){
			echo 'ログインIDとパスワードの両方を入力してください';
			exit();
		}
		
		$loginID = $_POST['login_id'];
		$loginPASS = $_POST['login_pass'];
		
		/*------------テーブルの存在を確認--------------
		$rs=$pdo->query("SHOW TABLES");
		$table=$rs->fetchAll(PDO::FETCH_COLUMN);
		if(in_array($tb_name2,$table)){
			echo "テーブルの存在を確認しました"."<br>";
			echo "テーブル名：".$tb_name2."<br>";
		}else{
			echo "テーブル：".$tb_name2."がありません"."<br>";
		}
		------------------------------------------------*/
		
		/*-----------カラムの中身を確認する-------------
		$stmt=$pdo->prepare("SELECT * FROM $tb_name2");
		$stmt->execute();
		foreach($stmt as $loop){
			echo "id:".$loop['id']."<br>".
				 "name:".$loop['name']."<br>".
				 "password:".$loop['password']."<br>";
		}
		-----------------------------------------------*/
		
		$sql="SELECT name,password FROM $tb_name2 WHERE id='$loginID'";
		$stmt=$pdo->prepare($sql);
		$stmt->execute();
		
		foreach($stmt as $row){
			$loginUSER=$row['name'];
			$pass=$row['password'];
		}
		
		if( $pass==$loginPASS ){
			
			//クッキーをリクエスト
			setcookie('username',$loginUSER,time()+60*60*24*7);
			setcookie('userid',$loginID,time()+60*60*24*7);
			setcookie('userpass',$loginPASS,time()+60*60*24*7);
			
			/*--------------------------------------------------------
			//クッキーが成功してるかどうか
			if(setcookie('username',$loginUSER,time()+60*60*24*7)){
				echo "true"."<br>";
			}else{
				echo "false"."<br>";
			}
			---------------------------------------------------------*/
			
		}else{
			
			//ログインIDとパスワードに一致する情報が見つからなかった場合
			header("Location:http://co-19-208.99sv-coco.com/kadai_3/plz_regi_info.html");
			exit();
			
		}
	}
	
	//---------------【SELECTでデータを選択してフォーム下に表示する】-----------------
	$stmt=$pdo->prepare("SELECT * FROM $tb_name1");
	$stmt->execute();

	foreach($stmt as $loop){
		$message = array(
			'post_no'=>$loop['number'],
			'view_name'=>$loop['name'],
			'message'=>$loop['message'],
			'post_date'=>$loop['regi_date'],
			'file_extension'=>$loop['extension'],
			'file_path'=>$loop['path'],
		);
		array_unshift($message_array, $message);
	}

	/*--------------【テーブルを削除する】----------------
		$stmt=$pdo->prepare("drop table $tb_name1");
		$stmt->execute();
	----------------------------------------------------*/

}catch(PDOException $e){
	header('Content_Type:text/plain;charset=UTF-8',true,500);
	exit($e->getMessage());
}

//接続を閉じる
$pdo = null;

?>


<!------------------------------【掲示板ページ】----------------------------------->

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>簡易掲示板</title>
</head>

<body>
<h2>簡易掲示板(^-^*)</h2>
	<?php if(!empty($success_message)):?>
		<p class="success_message"><?php echo $success_message;?></p>
	<?php endif; ?>
	
	<form method="post" enctype="multipart/form-data">
		<div>
			<label for="pass">パスワード</label>
			<input type="password" name="pass" value="<?php if(isset($_POST['btn_edit'])){ echo $edit_pass; } ?>">
		</div>
		<div>
			<label for="view_name">名前</label>
			<input type="text" name="view_name" value="<?php if(isset($_POST['btn_login'])){ echo $loginUSER; } ?>">
		</div>
		<div>
			<label for="message">メッセージ</label>
			<textarea name="message"><?php if(isset($_POST['btn_edit'])){ echo $edit_message; } ?></textarea>
			<p><input type="file" name="upfile" accept="image/png,image/gif,image/jpeg,video/mp4"></p>
			 ※画像はjpeg方式，png方式，gif方式に対応しています．動画はmp4方式のみ対応しています．<br>
			<input type="submit" name="btn_submit" value="投稿">
			<input type="hidden" name="keep_editNo" value="<?php if(isset($_POST['btn_edit'])){ echo $edit_no; } ?>">
			<input type="submit" name="execute_edit" value="編集">
		</div>
	</form>
	
	<form method="post" action="kadai_2_06_edit.html">
		<div>
			<input type="submit" name="btn_edit" value="投稿を編集">
		</div>	
	</form>
	
	<form method="post" action="kadai_2_06_delete.html">
		<div>
			<input type="submit" name="btn_delete" value="投稿を削除">
		</div>	
	</form>
		
<hr>
<!-------------------フォーム下に表示----------------------->
<section>
	<?php if( !empty($message_array) ): ?>
	<?php foreach( $message_array as $value ): ?>
	    <div>
	    
	        <h2><?php echo $value['post_no'].":".$value['view_name']; ?></h2>
	        <p><?php echo $value['post_date']; ?></p>
	    	<p><?php echo $value['message']; ?></p>
	    	
	    	<?php if(($value['file_extension']=="jpg")||($value['file_extension']=="png")||($value['file_extension']=="gif")):?>
		    	<img src="<?php echo $value['file_path']; ?>" width="500" height="500"></br>
	    	<?php endif; ?>
	    	
	    	<?php if($value['file_extension']=="mp4"):?>
	    		<video controls autoplay>
	    			<source src="<?php echo $value['file_path']; ?>" type='video/mp4'>
	    		</video>
	    	<?php endif; ?>
	    
	    </div>
	<?php endforeach; ?>
	<?php endif; ?>
</section>
	
</body>
</html>

