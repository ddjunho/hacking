<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db = 'my_database';
$user = 'root';
$pass = 'U3DYRePeDDr:';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 허용된 검색 유형 (searchType) 목록
$allowedSearchTypes = ['all', 'title', 'author', 'content'];
$searchType = isset($_GET['searchType']) && in_array($_GET['searchType'], $allowedSearchTypes) ? $_GET['searchType'] : 'all';

// 허용된 정렬 기준 (sortBy) 목록
$allowedSortBy = ['created_at', 'title', 'author', 'file_name'];
$sortBy = isset($_GET['sortBy']) && in_array($_GET['sortBy'], $allowedSortBy) ? $_GET['sortBy'] : 'created_at';

// 허용된 정렬 순서 (sortOrder) 목록
$allowedSortOrder = ['ASC', 'DESC'];
$sortOrder = isset($_GET['sortOrder']) && in_array($_GET['sortOrder'], $allowedSortOrder) ? $_GET['sortOrder'] : 'DESC';

// 검색어 처리 (XSS 방지)
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : "";

// 날짜 입력 값 검증
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : "";
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : "";

// 페이징 설정
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $perPage;

// 검색 조건 구성
$whereClause = "";
if (!empty($search)) {
    switch ($searchType) {
        case "title":
            $whereClause = "WHERE title LIKE '%$search%'";
            break;
        case "author":
            $whereClause = "WHERE author LIKE '%$search%'";
            break;
        case "content":
            $whereClause = "WHERE content LIKE '%$search%'";
            break;
        default:
            $whereClause = "WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR content LIKE '%$search%'";
    }
}

// 날짜 범위 추가
if (!empty($startDate) && !empty($endDate)) {
    $whereClause .= (empty($whereClause) ? "WHERE " : " AND ") . "created_at BETWEEN '$startDate' AND '$endDate'";
}

// 게시글 개수 조회
$countSql = "SELECT COUNT(*) AS total FROM board_posts $whereClause";
$countResult = $conn->query($countSql);
$totalPosts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalPosts / $perPage);

// 게시글 목록 조회
$sql = "SELECT id, title, content, author, created_at, file_name FROM board_posts $whereClause ORDER BY $sortBy $sortOrder LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>게시판</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h3>게시판</h3>
        </header>

        <main>
            <?php if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])): ?>
                <section class="user-greeting">
                    <?php
                    try {
                        $jwt = $_COOKIE['auth_token'];
                        $decoded = JWT::decode($jwt, new Key('YOUR_SECRET_KEY', 'HS256'));
                        echo "<p>안녕하세요, " . htmlspecialchars($decoded->data->username) . "님!</p>";
                    } catch (Exception $e) {
                        echo "<p>세션이 만료되었습니다. 다시 로그인해 주세요.</p>";
                    }
                    ?>
                </section>
            <?php endif; ?>

            <section class="board-list">
                <table>
                    <thead>
                        <tr>
                            <th>제목</th>
                            <th>작성일</th>
                            <th>작성자</th>
                            <th>파일</th>
                            <th>상세보기</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td data-label='제목'>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td data-label='작성일'>" . $row['created_at'] . "</td>";
                                echo "<td data-label='작성자'>" . htmlspecialchars($row['author']) . "</td>";
                                echo "<td data-label='파일'>" . (empty($row['file_name']) ? "없음" : htmlspecialchars($row['file_name'])) . "</td>";
                                echo "<td data-label='상세보기'><a href='view_post.php?id=" . $row['id'] . "'>상세보기</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>게시물이 없습니다.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section class="search-section">
                <form class="search-form" method="GET" action="board_list.php">
                    <div class="search-left" >
                        <div class="form-group" >
                            <label for="search">검색어</label>
                            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="검색어 입력">
                        </div>
                    </div>
                    <div class="search-right">
                        <div class="form-group">
                            <label for="searchType">검색 유형</label>
                            <select id="searchType" name="searchType">
                                <option value="all" <?= $searchType == 'all' ? 'selected' : '' ?>>전체</option>
                                <option value="title" <?= $searchType == 'title' ? 'selected' : '' ?>>제목</option>
                                <option value="author" <?= $searchType == 'author' ? 'selected' : '' ?>>작성자</option>
                                <option value="content" <?= $searchType == 'content' ? 'selected' : '' ?>>내용</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="startDate">시작일</label>
                            <input type="date" id="startDate" name="startDate" value="<?= $startDate ?>">
                        </div>
                        <div class="form-group">
                            <label for="endDate">종료일</label>
                            <input type="date" id="endDate" name="endDate" value="<?= $endDate ?>">
                        </div>
                        <div class="form-group">
                            <label for="sortBy">정렬 기준</label>
                            <select id="sortBy" name="sortBy">
                                <option value="created_at" <?= $sortBy == 'created_at' ? 'selected' : '' ?>>날짜</option>
                                <option value="title" <?= $sortBy == 'title' ? 'selected' : '' ?>>제목</option>
                                <option value="author" <?= $sortBy == 'author' ? 'selected' : '' ?>>작성자</option>
                                <option value="file_name" <?= $sortBy == 'file_name' ? 'selected' : '' ?>>파일명</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sortOrder">정렬 순서</label>
                            <select id="sortOrder" name="sortOrder">
                                <option value="DESC" <?= $sortOrder == 'DESC' ? 'selected' : '' ?>>내림차순</option>
                                <option value="ASC" <?= $sortOrder == 'ASC' ? 'selected' : '' ?>>오름차순</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit">검색</button>
                        </div>
                    </div>
                </form>
            </section>

            <!-- 페이지네이션 -->
            <div class="pagination">
                <?php 
                    $pageGroupSize = 5; 
                    $startPage = max(1, $page - floor($pageGroupSize / 2)); 
                    $endPage = min($totalPages, $startPage + $pageGroupSize - 1); 
                    $startPage = max(1, $endPage - $pageGroupSize + 1); // 페이지 그룹 조정 
                ?>

                <!-- 이전 페이지 버튼 -->
                <?php if ($page > 1) { ?>
                    <?php echo '<a href="?page=1&search=' . urlencode($search) . '">« 처음</a>';?>
                    <?php echo '<a href="?page=' . ($page - 1) . '&search=' . urlencode($search) . '">‹ 이전</a>';?>
                <?php } ?>

                <!-- 페이지 번호 출력 (5개만) -->
                <?php 
                    // 디버깅 출력 - startPage, endPage 확인
                    echo "startPage: $startPage, endPage: $endPage<br>"; 

                    for ($i = $startPage; $i <= $endPage; $i++) { 
                        if ($i == $page) { 
                            echo '<a href="#" class="active">' . $i . '</a>'; 
                        } else { 
                            echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '">' . $i . '</a>'; 
                        } 
                    } 
                ?>

                <!-- 다음 페이지 버튼 -->
                <?php if ($page < $totalPages) { ?>
                    <?php echo '<a href="?page=' . ($page + 1) . '&search=' . urlencode($search) . '">다음 ›</a>';?>
                    <?php echo '<a href="?page=' . $totalPages . '&search=' . urlencode($search) . '">마지막 »</a>';?>
                <?php } ?>
            </div>

            <nav class="navigation">
                <a href="index.html" class="btn">홈으로 가기</a>
                <?php if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) :?>
                    <a href="create_post.php" class="btn">글 작성하기</a>
                <?php else: ?>
                    <a href="login_form.php" class="btn">로그인 하기</a>
                <?php endif; ?>
            </nav>
        </main>
    </div>
</body>
</html>
