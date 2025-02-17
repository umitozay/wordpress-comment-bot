<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress Comment Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h1 class="text-center">WordPress Comment Bot</h1>
    <form method="post" class="mb-4">
        <div class="mb-3">
            <label class="form-label">Site Adresi</label>
            <input type="text" name="site_url" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Post ID</label>
            <input type="text" name="post_id" class="form-control" required>
        </div>
        <button type="submit" name="add_site" class="btn btn-primary">Site Ekle</button>
    </form>
    
    <?php
    if (isset($_POST['add_site'])) {
        $new_site = $_POST['site_url'] . ',' . $_POST['post_id'] . "\n";
        file_put_contents('siteler.txt', $new_site, FILE_APPEND);
        echo "<div class='alert alert-success'>Site eklendi!</div>";
    }
    
    function post_comment($site_url, $post_id, $author, $email, $comment, $website = '') {
        $comment_url = rtrim($site_url, '/') . '/wp-comments-post.php';
        
        $data = [
            'comment_post_ID' => $post_id,
            'author' => $author,
            'email' => $email,
            'url' => $website,
            'comment' => $comment,
            'submit' => 'Post Comment'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $comment_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            if (check_comment_published($site_url, $comment)) {
                file_put_contents('gidenler.txt', "$site_url,$post_id\n", FILE_APPEND);
                echo "<div class='alert alert-success'>Yorum yayınlandı: $site_url</div>";
            } else {
                file_put_contents('gitmeyenler.txt', "$site_url,$post_id\n", FILE_APPEND);
                echo "<div class='alert alert-warning'>Yorum onaya gitti veya başarısız: $site_url</div>";
            }
        } else {
            file_put_contents('gitmeyenler.txt', "$site_url,$post_id\n", FILE_APPEND);
            echo "<div class='alert alert-danger'>Yorum gönderilemedi ($http_code): $site_url</div>";
        }
    }

    function check_comment_published($site_url, $comment) {
        $page_content = file_get_contents($site_url);
        return strpos($page_content, $comment) !== false;
    }

    $sites = file('siteler.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    $author = 'John Doe';
    $email = 'johndoe@example.com';
    $comment = 'Great post! Thanks for sharing.';
    $website = 'https://mywebsite.com';
    
    echo "<h2>Yorum Gönderme Sonuçları</h2>";
    foreach ($sites as $site) {
        list($url, $post_id) = explode(',', $site);
        post_comment($url, $post_id, $author, $email, $comment, $website);
    }
    echo "<h2>İşlem Tamamlandı</h2>";
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
