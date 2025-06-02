<?php
require 'db.php';
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$propertyId = isset($_GET['propertyId']) ? (int)$_GET['propertyId'] : 0;
$checkIn = isset($_GET['checkIn']) ? $_GET['checkIn'] : '';
$checkOut = isset($_GET['checkOut']) ? $_GET['checkOut'] : '';
$confirmation = '';
$error = '';

if ($propertyId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = ?");
        $stmt->execute([$propertyId]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$property) {
            $error = "Property not found.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    $error = "Invalid property ID.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
    $userId = 1; // Default user ID
    $checkIn = $_POST['checkIn'] ?? '';
    $checkOut = $_POST['checkOut'] ?? '';

    // Validate dates
    if (empty($checkIn) || empty($checkOut)) {
        $error = "Check-in and check-out dates are required.";
    } elseif (strtotime($checkIn) === false || strtotime($checkOut) === false) {
        $error = "Invalid date format.";
    } elseif (strtotime($checkIn) >= strtotime($checkOut)) {
        $error = "Check-out date must be after check-in date.";
    } else {
        try {
            // Check if user exists, create if not
            $userStmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $userStmt->execute([$userId]);
            if (!$userStmt->fetch()) {
                $insertUser = $pdo->prepare("INSERT INTO users (user_id, username, email, password) VALUES (?, ?, ?, ?)");
                $insertUser->execute([$userId, 'defaultuser', 'default@example.com', password_hash('defaultpass', PASSWORD_DEFAULT)]);
            }

            $days = (strtotime($checkOut) - strtotime($checkIn)) / (60 * 60 * 24);
            $totalPrice = $days * $property['price'];

            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, property_id, check_in, check_out, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $propertyId, $checkIn, $checkOut, $totalPrice]);
            $confirmation = "Booking confirmed for {$property['title']} from $checkIn to $checkOut. Total: $$totalPrice";
        } catch (PDOException $e) {
            $error = "Booking failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Property - Airbnb Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        header { background: #fff; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .booking-container { max-width: 800px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .booking-container img { width: 100%; height: 300px; object-fit: cover; border-radius: 10px; }
        .booking-container h2 { margin: 20px 0; }
        .booking-form input { padding: 10px; margin: 10px 0; width: 100%; border: 1px solid #ddd; border-radius: 5px; }
        .booking-form button { padding: 10px 20px; background: #ff5a5f; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        .booking-form button:hover { background: #e04e52; }
        .confirmation { color: green; margin: 20px 0; }
        .error { color: red; margin: 20px 0; }
        @media (max-width: 768px) {
            .booking-container { margin: 20px; }
        }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align: center; color: #ff5a5f;">Book Your Stay</h1>
    </header>
    <div class="booking-container">
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($property): ?>
            <img src="<?php echo htmlspecialchars($property['image']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
            <h2><?php echo htmlspecialchars($property['title']); ?></h2>
            <p><?php echo htmlspecialchars($property['location']); ?> - $<?php echo number_format($property['price'], 2); ?>/night</p>
            <p>Amenities: <?php echo htmlspecialchars($property['amenities']); ?></p>
            <p>Rating: <?php echo $property['rating']; ?> (<?php echo $property['reviews']; ?> reviews)</p>
            <form class="booking-form" method="POST">
                <input type="date" name="checkIn" value="<?php echo htmlspecialchars($checkIn); ?>" required>
                <input type="date" name="checkOut" value="<?php echo htmlspecialchars($checkOut); ?>" required>
                <button type="submit">Book Now</button>
            </form>
            <?php if ($confirmation): ?>
                <p class="confirmation"><?php echo $confirmation; ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p>Property not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
