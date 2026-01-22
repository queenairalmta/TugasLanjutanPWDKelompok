<?php
require_once 'config.php';
require_once 'Product.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID produk diperlukan']);
    exit();
}

$productModel = new Product();
$product = $productModel->getById(intval($_GET['id']));

if ($product) {
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
}
?>