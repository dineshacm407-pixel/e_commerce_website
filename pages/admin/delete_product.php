<?php
include '../../includes/db.php';
session_start();

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product to get image filename
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    // Delete image file if exists
    $imagePath = __DIR__ . "/../../images/" . $product['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
}

// Redirect back to manage products
header("Location: manage_products.php");
exit();
