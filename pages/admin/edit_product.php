<?php
include '../../includes/db.php';
session_start();

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get product ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Product not found.";
    exit();
}

$successMessage = "";

// Handle form submission
if (isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Check if new image uploaded
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $uploadDir = __DIR__ . "/../../images/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    } else {
        $image = $product['image']; // keep old image
    }

    // Update database
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE id = ?");
    $stmt->execute([$name, $price, $description, $image, $product_id]);

    $successMessage = "Product updated successfully!";
    
    // Refresh product data
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { width: 50%; margin: 50px auto; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        form { display: flex; flex-direction: column; }
        label { margin: 10px 0 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="number"], textarea, input[type="file"] { padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        textarea { resize: vertical; height: 100px; }
        button { background-color: #4CAF50; color: white; padding: 15px; font-size: 16px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s ease; }
        button:hover { background-color: #45a049; }
        .message { color: green; text-align: center; margin-top: 20px; font-size: 18px; }
        .back-link { display: block; text-align: center; margin-top: 20px; font-size: 14px; }
        .back-link a { color: #4CAF50; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        img { max-width: 150px; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Product</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Product Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']); ?>" required>

        <label>Price:</label>
        <input type="number" step="0.01" name="price" value="<?= $product['price']; ?>" required>

        <label>Description:</label>
        <textarea name="description" required><?= htmlspecialchars($product['description']); ?></textarea>

        <label>Current Image:</label>
        <img src="../../images/<?= htmlspecialchars($product['image']); ?>" alt="Product Image">

        <label>Change Image (optional):</label>
        <input type="file" name="image">

        <button type="submit" name="update_product">Update Product</button>
    </form>

    <?php if (!empty($successMessage)): ?>
        <div class="message"><?= htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <div class="back-link">
        <a href="manage_products.php">Back to Manage Products</a>
    </div>
</div>
</body>
</html>
