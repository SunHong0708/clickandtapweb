<?php
require __DIR__ . '/db.php';
require __DIR__ . '/telegram.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cart.html');
    exit;
}

$name     = trim($_POST['name'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');
$telegramUser = trim($_POST['telegram'] ?? '');
$cartJson = $_POST['cart_data'] ?? '[]';

$cart = json_decode($cartJson, true);
if (!is_array($cart)) $cart = [];

if ($name === '' || $phone === '' || $address === '' || empty($cart)) {
    die('Missing info or empty cart. <a href="../checkout.html">Go back</a>');
}

// calculate total
$total = 0;
foreach ($cart as $item) {
    $total += floatval($item['price']) * intval($item['qty']);
}

$nameEsc     = mysqli_real_escape_string($conn, $name);
$phoneEsc    = mysqli_real_escape_string($conn, $phone);
$addressEsc  = mysqli_real_escape_string($conn, $address);
$teleEsc     = mysqli_real_escape_string($conn, $telegramUser);

$sqlOrder = "
    INSERT INTO orders (customer_name, customer_phone, customer_address, customer_telegram, total_amount)
    VALUES ('$nameEsc', '$phoneEsc', '$addressEsc', '$teleEsc', $total)
";

mysqli_query($conn, $sqlOrder) or die('Error saving order: ' . mysqli_error($conn));

$orderId = mysqli_insert_id($conn);

// Save items
foreach ($cart as $item) {
    $productName = mysqli_real_escape_string($conn, $item['name']);
    $price = floatval($item['price']);
    $qty   = intval($item['qty']);

    if ($qty <= 0) continue;

    $sqlItem = "
        INSERT INTO order_items (order_id, product_name, product_price, quantity)
        VALUES ($orderId, '$productName', $price, $qty)
    ";

    mysqli_query($conn, $sqlItem);
}

// Create Telegram message
$orderText = "📦 <b>New Order #$orderId</b>\n\n";
$orderText .= "👤 <b>Name:</b> $name\n";
$orderText .= "📞 <b>Phone:</b> $phone\n";
$orderText .= "📨 <b>Telegram:</b> $telegramUser\n";
$orderText .= "📍 <b>Address:</b> $address\n\n";
$orderText .= "🛒 <b>Items:</b>\n";

foreach ($cart as $item) {
    $orderText .= " • {$item['name']} (x{$item['qty']}) — \${$item['price']}\n";
}

$orderText .= "\n💵 <b>Total:</b> \$$total\n";
$orderText .= "⏱ <b>Time:</b> " . date("Y-m-d H:i");

// Send Telegram notification
sendTelegramMessage($orderText);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Confirmed | Click & Tap</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="container nav">
    <div class="logo">Click & <span>Tap</span></div>
    <nav class="nav-links">
      <a href="../index.html">Home</a>
      <a href="../products.html">Products</a>
      <a href="../contact.html">Contact</a>
      <a href="../cart.html">Cart</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="hero small-hero">
    <h1 class="hero-title">Thank you for your order!</h1>
    <p class="hero-subtitle">
      Your order ID is <strong>#<?php echo $orderId; ?></strong>.<br>
      We will contact you via phone or Telegram shortly.
    </p>
  </section>

  <section>
    <a href="../index.html" class="btn-add-cart">Back to Home</a>
    <a href="../products.html" class="btn-add-cart">Continue Shopping</a>
  </section>
</main>

<footer class="site-footer">
  <p>© <span id="year"></span> Click & Tap</p>
</footer>

<script>
  document.getElementById("year").textContent = new Date().getFullYear();
  localStorage.removeItem("clickTapCart"); // clear cart
</script>

</body>
</html>
