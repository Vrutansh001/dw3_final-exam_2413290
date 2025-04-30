<?php
// Include database connection and session
require_once "db.php";
require_once "session.php";

// Check if user is logged in
check_login();

// Get user ID
$user_id = get_user_id();

// Check if book ID is provided
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $book_id = trim($_GET["id"]);
    
    // Get book info to check if it belongs to the current user and to get cover URL
    $sql = "SELECT * FROM books WHERE id = :id AND user_id = :user_id";
    
    if($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":id", $book_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            if($stmt->rowCount() == 1) {
                $book = $stmt->fetch(PDO::FETCH_ASSOC);
                $cover_url = $book["cover_url"];
                
                // Delete the book from database
                $sql = "DELETE FROM books WHERE id = :id AND user_id = :user_id";
                
                if($stmt = $pdo->prepare($sql)) {
                    $stmt->bindParam(":id", $book_id, PDO::PARAM_INT);
                    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                    
                    if($stmt->execute()) {
                        // Delete cover image file if it exists
                        if(!empty($cover_url) && file_exists($cover_url)) {
                            unlink($cover_url);
                        }
                        
                        // Redirect to dashboard
                        header("location: dashboard.php?success=Book deleted successfully");
                        exit();
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                    
                    unset($stmt);
                }
            } else {
                // Book doesn't exist or doesn't belong to user
                header("location: dashboard.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        unset($stmt);
    }
} else {
    // No ID parameter provided
    header("location: dashboard.php");
    exit();
}

// Close connection
unset($pdo);
?>
