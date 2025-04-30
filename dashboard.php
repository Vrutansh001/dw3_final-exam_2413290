<?php
// Include database connection and session
require_once "db.php";
require_once "session.php";

// Check if user is logged in
check_login();

// Get user ID
$user_id = get_user_id();

// Fetch all books for the current user
$books = [];
$sql = "SELECT * FROM books WHERE user_id = :user_id ORDER BY created_at DESC";

if($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    
    if($stmt->execute()) {
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Book Catalog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Book Catalog</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars(get_username()); ?></span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </header>
        
        <div class="dashboard-content">
            <div class="dashboard-actions">
                <h2>My Books</h2>
                <a href="add_item.php" class="btn btn-primary">Add New Book</a>
            </div>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <div class="books-grid">
                <?php if(count($books) > 0): ?>
                    <?php foreach($books as $book): ?>
                        <div class="book-card">
                            <div class="book-cover">
                                <?php if(!empty($book['cover_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($book['cover_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php else: ?>
                                    <div class="no-cover">No Cover</div>
                                <?php endif; ?>
                            </div>
                            <div class="book-info">
                                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p class="book-author">By <?php echo htmlspecialchars($book['author']); ?></p>
                                <p class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></p>
                                <div class="book-actions">
                                    <a href="add_item.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <a href="delete_item.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-books">
                        <p>You haven't added any books yet.</p>
                        <a href="add_item.php" class="btn btn-primary">Add Your First Book</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
