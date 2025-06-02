<?php
require 'db.php';
session_start();

$location = isset($_GET['location']) ? $_GET['location'] : '';
$checkIn = isset($_GET['checkIn']) ? $_GET['checkIn'] : '';
$checkOut = isset($_GET['checkOut']) ? $_GET['checkOut'] : '';
$propertyType = isset($_GET['propertyType']) ? $_GET['propertyType'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc';

$query = "SELECT * FROM properties WHERE 1=1";
$params = [];

if ($location) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}
if ($propertyType) {
    $query .= " AND property_type = ?";
    $params[] = $propertyType;
}

if ($sort == 'price_asc') {
    $query .= " ORDER BY price ASC";
} elseif ($sort == 'price_desc') {
    $query .= " ORDER BY price DESC";
} elseif ($sort == 'rating_desc') {
    $query .= " ORDER BY rating DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - Airbnb Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        header { background: #fff; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .filters { max-width: 1200px; margin: 20px auto; display: flex; gap: 10px; }
        .filters select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .property-grid { max-width: 1200px; margin: 20px auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .property-card { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); cursor: pointer; }
        .property-card img { width: 100%; height: 200px; object-fit: cover; }
        .property-card h3 { padding: 10px; font-size: 18px; }
        .property-card p { padding: 0 10px 10px; color: #666; }
        .property-card .price { font-weight: bold; color: #ff5a5f; }
        @media (max-width: 768px) {
            .filters { flex-direction: column; padding: 0 20px; }
            .filters select { width: 100%; }
            .property-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align: center; color: #ff5a5f;">Properties</h1>
    </header>
    <div class="filters">
        <select id="sort" onchange="applyFilters()">
            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
            <option value="rating_desc" <?php echo $sort == 'rating_desc' ? 'selected' : ''; ?>>Best Rated</option>
        </select>
    </div>
    <div class="property-grid">
        <?php foreach ($properties as $property): ?>
            <div class="property-card" onclick="goToBooking(<?php echo $property['property_id']; ?>)">
                <img src="<?php echo htmlspecialchars($property['image']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                <p><?php echo htmlspecialchars($property['location']); ?></p>
                <p class="price">$<?php echo number_format($property['price'], 2); ?>/night</p>
                <p>Rating: <?php echo $property['rating']; ?> (<?php echo $property['reviews']; ?> reviews)</p>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function applyFilters() {
            const sort = document.getElementById('sort').value;
            const params = new URLSearchParams({
                location: '<?php echo $location; ?>',
                checkIn: '<?php echo $checkIn; ?>',
                checkOut: '<?php echo $checkOut; ?>',
                propertyType: '<?php echo $propertyType; ?>',
                sort: sort
            });
            window.location.href = `hotels.php?${params.toString()}`;
        }
        function goToBooking(propertyId) {
            const params = new URLSearchParams({
                propertyId: propertyId,
                checkIn: '<?php echo $checkIn; ?>',
                checkOut: '<?php echo $checkOut; ?>'
            });
            window.location.href = `booking.php?${params.toString()}`;
        }
    </script>
</body>
</html>
