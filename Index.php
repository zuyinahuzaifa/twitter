?php
// index.php
include 'db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tweet'])) {
    $tweet = $conn->real_escape_string($_POST['tweet']);
    $sql = "INSERT INTO tweets (content, created_at) VALUES ('$tweet', NOW())";
    if ($conn->query($sql) === FALSE) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    } else {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$tweets_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $tweets_per_page;

$result = $conn->query("SELECT * FROM tweets ORDER BY created_at DESC LIMIT $offset, $tweets_per_page");
$total_tweets = $conn->query("SELECT COUNT(*) as count FROM tweets")->fetch_assoc()['count'];
$total_pages = ceil($total_tweets / $tweets_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --twitter-blue: #1d9bf0;
            --twitter-hover-blue: #1a8cd8;
            --twitter-background: #ffffff;
            --twitter-text: #0f1419;
            --twitter-gray: #536471;
            --twitter-border: #eff3f4;
            --twitter-dark-hover: rgba(15, 20, 25, 0.1);
            --twitter-button-hover: rgba(29, 155, 240, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--twitter-background);
            color: var(--twitter-text);
            line-height: 1.4;
        }

        .layout {
            display: flex;
            min-height: 100vh;
            max-width: 1300px;
            margin: 0 auto;
        }

        .sidebar {
            width: 275px;
            padding: 0 12px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .main-content {
            width: 600px;
            border-left: 1px solid var(--twitter-border);
            border-right: 1px solid var(--twitter-border);
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 9999px;
            text-decoration: none;
            color: var(--twitter-text);
            font-size: 20px;
            transition: background-color 0.2s;
            margin-bottom: 8px;
        }

        .nav-item:hover {
            background-color: var(--twitter-dark-hover);
        }

        .nav-item i {
            margin-right: 20px;
            font-size: 24px;
        }

        .tweet-button {
            background-color: var(--twitter-blue);
            color: white;
            border: none;
            border-radius: 9999px;
            padding: 15px 0;
            font-size: 17px;
            font-weight: bold;
            width: 90%;
            cursor: pointer;
            margin-top: 16px;
            transition: background-color 0.2s;
        }

        .tweet-button:hover {
            background-color: var(--twitter-hover-blue);
        }

        .header {
            position: sticky;
            top: 0;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            padding: 0 16px;
            height: 53px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--twitter-border);
            z-index: 1000;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
        }

        .tweet-form-container {
            padding: 16px;
            border-bottom: 1px solid var(--twitter-border);
        }

        .tweet-form {
            display: flex;
            flex-direction: column;
        }

        .tweet-input {
            border: none;
            font-size: 20px;
            padding: 12px 0;
            margin-bottom: 12px;
            resize: none;
        }

        .tweet-input:focus {
            outline: none;
        }

        .tweet-form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tweet-icons {
            display: flex;
            gap: 16px;
        }

        .tweet-icon {
            color: var(--twitter-blue);
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .tweet-icon:hover {
            background-color: var(--twitter-button-hover);
        }

        .post-tweet-button {
            background-color: var(--twitter-blue);
            color: white;
            border: none;
            border-radius: 9999px;
            padding: 8px 16px;
            font-weight: bold;
            cursor: pointer;
            font-size: 15px;
        }

        .post-tweet-button:disabled {
            opacity: 0.5;
            cursor: default;
        }

        .tweet {
            padding: 12px 16px;
            border-bottom: 1px solid var(--twitter-border);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .tweet:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .tweet-header {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }

        .tweet-profile-image {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin-right: 12px;
            background-color: #ccc;
        }

        .tweet-user-info {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 15px;
        }

        .tweet-user-name {
            font-weight: bold;
            color: var(--twitter-text);
        }

        .tweet-user-handle, .tweet-time {
            color: var(--twitter-gray);
        }

        .tweet-content {
            margin-left: 60px;
            font-size: 15px;
            margin-bottom: 12px;
            word-wrap: break-word;
        }

        .tweet-actions {
            margin-left: 60px;
            display: flex;
            justify-content: space-between;
            max-width: 425px;
        }

        .tweet-action {
            color: var(--twitter-gray);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            padding: 4px 8px;
            border-radius: 9999px;
            transition: all 0.2s;
        }

        .tweet-action:hover {
            color: var(--twitter-blue);
            background-color: var(--twitter-button-hover);
        }

        .character-count {
            color: var(--twitter-gray);
            font-size: 13px;
            margin-right: 12px;
        }

        .trending {
            width: 350px;
            padding: 0 24px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .search-container {
            position: sticky;
            top: 0;
            padding: 6px 0;
            background-color: white;
            z-index: 1000;
        }

        .search-input {
            width: 100%;
            padding: 12px 40px;
            border-radius: 9999px;
            border: none;
            background-color: var(--twitter-border);
            font-size: 15px;
        }

        .search-input:focus {
            outline: none;
            background-color: white;
            border: 1px solid var(--twitter-blue);
        }
    </style>
</head>
<body>
    <div class="layout">
        <div class="sidebar">
            <nav class="sidebar-nav">
                <a href="#" class="nav-item">
                    <i class="fab fa-twitter" style="color: var(--twitter-blue)"></i>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-hashtag"></i>
                    <span>Explore</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="far fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="far fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <button class="tweet-button">Tweet</button>
            </nav>
        </div>

        <main class="main-content">
            <div class="header">
                <h1>Home</h1>
            </div>

            <div class="tweet-form-container">
                <form method="POST" id="tweetForm" class="tweet-form">
                    <textarea 
                        class="tweet-input" 
                        name="tweet" 
                        placeholder="What's happening?"
                        required 
                        maxlength="280"
                        rows="3"
                    ></textarea>
                    <div class="tweet-form-footer">
                        <div class="tweet-icons">
                            <i class="far fa-image tweet-icon"></i>
                            <i class="fas fa-poll tweet-icon"></i>
                            <i class="far fa-smile tweet-icon"></i>
                            <i class="far fa-calendar tweet-icon"></i>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span class="character-count">280</span>
                            <button type="submit" class="post-tweet-button">Tweet</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="tweets">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="tweet">
                        <div class="tweet-header">
                            <div class="tweet-profile-image"></div>
                            <div class="tweet-user-info">
                                <span class="tweet-user-name">User</span>
                                <span class="tweet-user-handle">@user</span>
                                <span class="tweet-time">· <?php echo date('M j', strtotime($row['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="tweet-content"><?php echo htmlspecialchars($row['content']); ?></div>
                        <div class="tweet-actions">
                            <a href="#" class="tweet-action">
                                <i class="far fa-comment"></i>
                                <span>0</span>
                            </a>
                            <a href="#" class="tweet-action">
                                <i class="fas fa-retweet"></i>
                                <span>0</span>
                            </a>
                            <a href="#" class="tweet-action">
                                <i class="far fa-heart"></i>
                                <span>0</span>
                            </a>
                            <a href="#" class="tweet-action">
                                <i class="fas fa-upload"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>

        <div class="trending">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search Twitter">
            </div>
        </div>
    </div>

    <script>
        const textarea = document.querySelector('.tweet-input');
        const charCount = document.querySelector('.character-count');
        const tweetButton = document.querySelector('.post-tweet-button');

        textarea.addEventListener('input', function() {
            const remaining = 280 - this.value.length;
            charCount.textContent = remaining;
            tweetButton.disabled = this.value.length === 0 || this.value.length > 280;
        });
    </script>
</body>
</html>
