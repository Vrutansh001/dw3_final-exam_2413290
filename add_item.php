<?php
// Include database connection and session
require_once "db.php";
require_once "session.php";

// Check if user is logged in
check_login();

// Get user ID
$user_id = get_user_id();

// Define variables and initialize with empty values
$title = $author = $genre = $cover_url = "";
$title_err = $author_err = $genre_err = $cover_err = "";
$edit_mode = false;
$book_id = 0;

// Check if we're editing an existing book
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $book_id = intval($_GET['id']);
    $edit_mode = true;
    
    // Fetch book data
    $sql = "SELECT * FROM books WHERE id = :id AND user_id = :user_id";
    
    if($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":id", $book_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            if($stmt->rowCount() == 1) {
                $book = $stmt->fetch(PDO::FETCH_ASSOC);
                $title = $book["title"];
                $author = $book["author"];
                $genre = $book["genre"];
                $cover_url = $book["cover_url"];
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
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate title
    if(empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    } else {
        $title = trim($_POST["title"]);
    }
    
    // Validate author
    if(empty(trim($_POST["author"]))) {
        $author_err = "Please enter an author.";
    } else {
        $author = trim($_POST["author"]);
    }
    
    // Validate genre
    if(empty(trim($_POST["genre"]))) {
        $genre_err = "Please enter a genre.";
    } else {
        $genre = trim($_POST["genre"]);
    }
    
    // Handle file upload
    $new_cover_url = $cover_url; // Default to existing cover URL
    
    if(isset($_FILES["cover"]) && $_FILES["cover"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["cover"]["name"];
        $filetype = $_FILES["cover"]["type"];
        $filesize = $_FILES["cover"]["size"];
        
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) {
            $cover_err = "Please select a valid file format (JPG, JPEG, PNG, GIF).";
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) {
            $cover_err = "File size is larger than the allowed limit (5MB).";
        }
        
        // Verify MIME type of the file
        if(in_array($filetype, $allowed)) {
            // Check if uploads directory exists, create if not
            if(!file_exists("uploads")) {
                mkdir("uploads", 0777, true);
            }
            
            // Create unique filename
            $new_filename = uniqid() . "-" . $filename;
            $upload_path = "uploads/" . $new_filename;
            
            // Upload file
            if(move_uploaded_file($_FILES["cover"]["tmp_name"], $upload_path)) {
                $new_cover_url = $upload_path;
            } else {
                $cover_err = "Failed to upload file.";
            }
        } else {
            $cover_err = "There was a problem with the uploaded file.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($title_err) && empty($author_err) && empty($genre_err) && empty($cover_err)) {
        
        if($edit_mode) {
            // Update existing book
            $sql = "UPDATE books SET title = :title, author = :author, genre = :genre, cover_url = :cover_url WHERE id = :id AND user_id = :user_id";
            
            if($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":title", $title, PDO::PARAM_STR);
                $stmt->bindParam(":author", $author, PDO::PARAM_STR);
                $stmt->bindParam(":genre", $genre, PDO::PARAM_STR);
                $stmt->bindParam(":cover_url", $new_cover_url, PDO::PARAM_STR);
                $stmt->bindParam(":id", $book_id, PDO::PARAM_INT);
                $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                
                if($stmt->execute()) {
                    header("location: dashboard.php?success=Book updated successfully");
                    exit();
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                unset($stmt);
            }
        } else {
            // Insert new book
            $sql = "INSERT INTO books (title, author, genre, cover_url, user_id) VALUES (:title, :author, :genre, :cover_url, :user_id)";
            
            if($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":title", $title, PDO::PARAM_STR);
                $stmt->bindParam(":author", $author, PDO::PARAM_STR);
                $stmt->bindParam(":genre", $genre, PDO::PARAM_STR);
                $stmt->bindParam(":cover_url", $new_cover_url, PDO::PARAM_STR);
                $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                
                if($stmt->execute()) {
                    header("location: dashboard.php?success=Book added successfully");
                    exit();
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                unset($stmt);
            }
        }
    }
    
    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? "Edit" : "Add"; ?> Book - Book Catalog</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2><?php echo $edit_mode ? "Edit" : "Add New"; ?> Book</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($edit_mode ? "?id=" . $book_id : "")); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                    <span class="invalid-feedback"><?php echo $title_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author" class="form-control <?php echo (!empty($author_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $author; ?>">
                    <span class="invalid-feedback"><?php echo $author_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Genre</label>
                    <input type="text" name="genre" class="form-control <?php echo (!empty($genre_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $genre; ?>">
                    <span class="invalid-feedback"><?php echo $genre_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Cover Image</label>
                    <?php if(!empty($cover_url)): ?>
                        <div class="current-cover">
                            <img src="<?php echo htmlspecialchars($cover_url); ?>" alt="Current Cover" class="thumbnail">
                            <p>Current cover image</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="cover" class="form-control <?php echo (!empty($cover_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $cover_err; ?></span>
                    <small class="form-text text-muted">Allowed formats: JPG, JPEG, PNG, GIF. Max size: 5MB.</small>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? "Update" : "Add"; ?> Book</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
