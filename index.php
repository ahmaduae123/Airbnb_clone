<?php
require 'db.php';
session_start();

// Fetch featured properties
$stmt = $pdo->query("SELECT * FROM properties ORDER BY rating DESC LIMIT 3");
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airbnb Clone - Home</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f5f5; }
        header { background: #fff; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-bar { max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .search-bar input, .search-bar select { padding: 10px; margin: 10px; border: 1px solid #ddd; border-radius: 5px; width: 200px; }
        .search-bar button { padding: 10px 20px; background: #ff5a5f; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        .search-bar button:hover { background: #e04e52; }
        .featured { max-width: 1200px; margin: 40px auto; }
        .featured h2 { text-align: center; margin-bottom spokeswoman: 20px; }
        .property-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .property-card { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .property-card img { width: 100%; height: 200px; object-fit: cover; }
        .property-card h3 { padding: 10px; font-size: 18px; }
        .property-card p { padding: 0 10px 10px; color: #666; }
        .property-card .price { font-weight: bold; color: #ff5a5f; }
        @media (max-width: 768px) {
            .search-bar input, .search-bar select { width: 100%; }
            .property-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align: center; color: #ff5a5f;">Airbnb Clone</h1>
    </header>
    <div class="search-bar">
        <form id="searchForm">
            <input type="text" id="location" placeholder="Destination" required>
            <input type="date" id="checkIn" required>
            <input type="date" id="checkOut" required>
            <select id="propertyType">
                <option value="">Property Type</option>
                <option value="Apartment">Apartment</option>
                <option value="House">House</option>
                <option value="Villa">Villa</option>
                <option value="Hotel">Hotel</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="featured">
        <h2>Featured Stays</h2>
        <div class="property-grid">
            <?php foreach ($featured as $property): ?>
                <div class="property-card">
                    <img src="<?php echo htmlspecialchars($property['image']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                    <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                    <p><?php echo htmlspecialchars($property['location']); ?></p>
                    <p class="price">$<?php echo number_format($property['price'], 2); ?>/night</p>
                    <p>Rating: <?php echo $property['rating']; ?> (<?php echo $property['reviews']; ?> reviews)</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const location = document.getElementById('location').value;
            const checkIn = document.getElementById('checkIn').value;
            const checkOut = document.getElementById('checkOut').value;
            const propertyType = document.getElementById('propertyType').value;
            const params = new URLSearchParams({ location, checkIn, checkOut, propertyType });
            window.location.href = `hotels.php?${params.toString()}`;
        });
    </script>
</body>
</html>
