<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「プロフィール編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//==============================
//画面処理
//==============================
//DBからユーザーデータを取得 functionでの処理 getUser セッションにuser_idを格納する処理が必要 signupの段階で格納している。$dbFromData[キー]とすることでセッションの中にあるdbの情報をgetUser(取得)できる
$dbFormData=getUser($_SESSION['user_id']);

debug('取得したユーザー情報：'.print_r($dbFormData,true));
debug('取得したユーザー情報：'.print_r($_SESSION['user_id'],true));

//post送信されていた場合
if(!empty($_POST)){
	debug('POST送信があります。');
	debug('POST情報：'.print_r($_POST,true));
	debug('FILE情報：'.print_r($_FILES,true));

	//変数にユーザー情報を代入
	$surname=$_POST['surname'];
	$name=$_POST['name'];
	$surkanaName=$_POST['surkanaName'];
	$kanaName=$_POST['kanaName'];
	$email=$_POST['email'];
	$tel=$_POST['tel'];
	$zip=$_POST['zip'];
	$addr=$_POST['addr'];
	$age=(!empty($_POST['age'])) ? $_POST['age'] : 0; //後続のバリデーションに引っかかるため、空で送信されてきたら0を入れる
	$pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
		// 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
	$pic = ( empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
	$company_flg = (!empty($_POST['company_flg'])) ? 1 : 0 ;

	//DBの情報と入力情報が異なる場合にバリデーションを行う
	if($dbFormData['surname'] !==$surname){
		//未入力のチェック
		validRequired($surname,'surname');
		// 名字チェック
		validName($surname, 'surname');
	}
	if($dbFormData['name'] !==$name){
		//未入力のチェック
		validRequired($name,'name');
		// 名前チェック
		validName($name, 'name');
	}
	if($dbFormData['surkanaName'] !== $surkanaName){
		//未入力のチェック
		validRequired($surkanaName,'surkanaName');
		//カナ文字のチェック
		validkanaName($surkanaName,'surkanaName');
	}
	if($dbFormData['kanaName'] !== $kanaName){
		//未入力のチェック
		validRequired($kanaName,'kanaName');
		//カナ文字のチェック
		validkanaName($kanaName,'kanaName');
	}
	if($dbFormData['email'] !==$email){
		//未入力のチェック
		validRequired($email,'email');        
		// emailの形式チェック
		validEmail($email,'email');
	}
	if($dbFormData['tel'] !==$tel && !empty($tel)){
		//電話番号の形式チェック
		validTel($tel,'tel');    
	}
	if( $dbFormData['zip'] !==$zip  && !empty($zip)){
		//郵便番号の形式チェック
		validZip($zip,'zip');    
	}
	if($dbFormData['addr'] !==$addr  && !empty($addr)){
		//住所の最大文字数チェック
		validMaxLen($addr,'addr');
	}
	//DBデータをint型にキャスト（型変換）して比較
	if((int)$dbFormData['age'] !==$age && !empty($age)){
		//年齢の最大文字数チェック 
		validMaxLen($age,'age');
		//年齢の半角数字チェック
		validNumber($age,'age');
	}
	if(empty($err_msg)){
		debug('バリデーションOKです。');

		//例外処理
		try{
			//DBへ接続
			$dbh=dbConnect();
			//SQL文作成
			$sql = 'UPDATE users SET surname= :surname, name= :name, surkanaName=:surkanaName, kanaName=:kanaName, email=:email, tel=:tel, zip=:zip, addr=:addr,age=:age, pic=:pic, company_flg=:company_flg WHERE id = :u_id';
			$data = array(':surname' => $surname, ':name' => $name, ':surkanaName' => $surkanaName,':kanaName' => $kanaName, ':email' => $email, ':tel' => $tel, ':zip' => $zip, ':addr' => $addr,':age' => $age, ':pic' => $pic, ':company_flg' => $company_flg, ':u_id' =>$dbFormData['id']);
			//クエリ実行
			$stmt = queryPost($dbh, $sql, $data);

			//クエリ成功の場合 $_SESSIONにプロフィールを変更したことを記録する
			if($stmt){
				$_SESSION['msg_success'] = SUC02;
				debug('マイページへ遷移します。');
				header("Location:mypage.php"); //マイページへ
			}
		} catch (Exception $e) {
			error_log('エラー発生：' . $e->getMessage());
			$err_msg['common'] = MSG07;
		}
	}    
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'マイページ';
require('head.php'); 
?>
<body>
	<?php
	require('header.php');
	?>
	<div id="main" class="site-width">
		<div class="text">
			<h1>プロフィール編集</h1>
			<div>以下のフォームに詳細情報をご入力ください。</div>
			<p>※詳細情報は登録者にのみ公開されます</p>
		</div>
		<div id="profile">
			<div class="area-msg">
				<?php
				if(!empty($err_msg['common'])) echo $err_msg['common'];
				?>
			</div>
			<form method="post" enctype="multipart/form-data" class="profile-form">
				<table border="1" cellpadding="20" cellspacing="0">
					<tr>
						<th style="background:rgb(237,237,237)">
							<p>お名前</p>
							<p>必須</p>
						</th>
						<td>
							<div class="fixed" style="border-right:1px solid #444;">
								<label class="<?php if(!empty($err_msg['surname'])) echo 'err'; ?>">
									<input type="text" name="surname" value="<?php echo getFormData('surname'); ?>">
								</label>
								<span>例)山田</span>
								<div class="table-msg">
									<?php if(!empty($err_msg['surname'])) echo $err_msg['surname']; ?>
								</div>
							</div>
							<div class="fixed">
								<label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
									<input type="text" name="name" value="<?php echo getFormData('name'); ?>">
								</label>
								<span>例)太郎</span>
								<div class="table-msg">
									<?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th style="background:rgb(237,237,237)">
							<p>お名前(フリガナ）</p>
							<p>必須</p>
						</th>
						<td>
							<div class="fixed">
								<label class="<?php if(!empty($err_msg['surkanaName'])) echo 'err'; ?>">
									<input type="text" name="surkanaName" value="<?php echo getFormData('surkanaName'); ?>"> <br>
								</label>
								<span>例)ヤマダ</span>
								<div class="table-msg">
									<?php if(!empty($err_msg['surkanaName'])) echo $err_msg['surkanaName']; ?>
								</div>
							</div>
							<div class="fixed">
								<label class="<?php if(!empty($err_msg['kanaName'])) echo 'err'; ?>">
									<input type="text" name="kanaName" value="<?php echo getFormData('kanaName'); ?>"> <br>
								</label>
								<span>例)タロウ</span>
								<div class="table-msg">
									<?php if(!empty($err_msg['kanaName'])) echo $err_msg['kanaName']; ?>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th style="background:rgb(237,237,237)">
							<p>メールアドレス</p>
							<p>必須</p>
						</th>
						<td>
						<label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
							<input type="text" name="email" value="<?php echo getFormData('email'); ?>"> <br>
						</label>
						<span>例)info@example.com ※半角数字</span>
						<div class="table-msg">
							<?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
						</div>
						</td>
					</tr>
					<tr>
						<th style="background:rgb(237,237,237)">
							<p>電話番号</p>
							<p>任意</p>
						</th>
						<td>
							<label class="<?php if(!empty($err_msg['tel'])) echo 'err'; ?>">
								<input type="text" name="tel" value="<?php echo getFormData('tel'); ?>"> <br>
							</label>
							<span>※ハイフン無しでご入力ください ※半角数字</span>
							<div class="table-msg">
								<?php if(!empty($err_msg['tel'])) echo $err_msg['tel']; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th style="background:rgb(237,237,237)">
							<p>郵便番号</p>
							<p>任意</p>
						</th>
						<td>
							<label class="<?php if(!empty($err_msg['zip'])) echo 'err'; ?>">
								<input type="text" name="zip" value="<?php echo getFormData('zip'); ?>"> <br>
							</label>
							<span>※ハイフン無しでご入力ください ※半角数字</span>
							<div class="table-msg">
								<?php if(!empty($err_msg['zip'])) echo $err_msg['zip']; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th style="background:rgb(237,237,237)">
							<p>住所</p>
							<p>任意</p>
						</th>
						<td>
							<label class="<?php if(!empty($err_msg['zip'])) echo 'err'; ?>">
								<input type="text" name="addr" value="<?php echo getFormData('addr'); ?>"> <br>
							</label>
							<span>例）大阪府大阪市〜</span>
							<div class="table-msg">
								<?php if(!empty($err_msg['addr'])) echo $err_msg['addr']; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th style="background:rgb(237,237,237)">
							<p>年齢</p>
							<p>任意</p>
						</th>
						<td>
							<label class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
								<input type="number" name="age" value="<?php if(!empty(getFormData('age'))){echo getFormData('age');  }?>">歳
							</label>
							<span>例)30</span>
							<div class="table-msg">
								<?php if(!empty($err_msg['age'])) echo $err_msg['age']; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th style="background:rgb(237,237,237)">
							<p>画像</p>
							<p>任意</p>
						</th>
						<td>
							<label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>">
								<input type="hidden" name="MAX_FILE_SIZE" value="3145728">
								<input type="file" name="pic" class="input-file">	
								<img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
								ドラッグ&ドロップ 
							</label> 
							<div class="area-msg">
								<?php 
								if(!empty($err_msg['pic'])) echo $err_msg['pic'];
								 ?>
							</div>
						</td>
					</tr>
				</table>
        <label class="company_checkbox">
          <input type="checkbox" name="company_flg">会社登録する
          <p class="flg_text">※登録後連絡掲示板が利用できます</p>
        </label>				
				<div class="btn-container">
					<input type="submit" class="btn btn-mid" value="変更する"> 
				</div>
			</form>
			<?php
			require('sidebar.php');
			?>
		</div>
	</div>
	<?php
	require('footer.php');
	?>
</body>
</html>