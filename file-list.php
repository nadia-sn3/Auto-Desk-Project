<?php
session_start();
require_once("config.php");
require_once("functions.php");

// Example directory where files are stored after upload
$upload_dir = 'uploads/';

// Get list of files in the directory
$files = scandir($upload_dir);
$allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'mp4', 'obj'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Files</title>
    <link rel="stylesheet" href="style/file-list.css">
</head>
<body>

<!-- Include Header -->
<?php include('includes/header.php'); ?>

<!-- Upload Section -->
<section class="file-upload-section">
    <h2>Upload Files</h2>
    <form action="upload-handler.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file-upload[]" id="file-upload" multiple required>
        <button type="submit">Upload</button>
    </form>
</section>

<!-- Display Files Section -->
<section class="file-list-section">
    <h2>Uploaded Files</h2>
    <div class="file-list">
        <?php
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $file_extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($file_extension, $allowed_extensions)) {
                    echo '<div class="file-item">';
                    echo '<a href="' . $upload_dir . $file . '" target="_blank">';
                    echo '<div class="file-icon">';
                    
                    // Display the file icon based on type
                    if (in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                        echo '<img src="' . $upload_dir . $file . '" alt="Image">';
                    } elseif ($file_extension === 'pdf') {
                        echo '<img src="icons/pdf-icon.png" alt="PDF">';
                    } elseif ($file_extension === 'mp4') {
                        echo '<img src="icons/video-icon.png" alt="Video">';
                    } elseif ($file_extension === 'obj') {
                        echo '<img src="icons/3d-icon.png" alt="3D Model">';
                    } else {
                        echo '<img src="icons/file-icon.png" alt="File">';
                    }

                    echo '</div>';
                    echo '<div class="file-name">' . htmlspecialchars($file) . '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            }
        }
        ?>
    </div>
</section>

<!-- Include Footer -->
<?php include('includes/footer.php'); ?>

</body>
</html>
