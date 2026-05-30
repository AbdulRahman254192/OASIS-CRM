<?php
include 'db.php';
include 'header.php'; // MAGIC! This loads your CSS and Nav bar!
$message = "";

// 1. Logic to Add a Room Type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_type'])) {
    $typeName = $_POST['typeName'];
    $description = $_POST['description'];
    $amenities = $_POST['amenities'];
    $basePrice = $_POST['basePrice'];

    $insertSql = "INSERT INTO ROOM_TYPE (TypeName, Description, Amenities, BasePrice) VALUES (?, ?, ?, ?)";
    $stmt = sqlsrv_query($conn, $insertSql, array($typeName, $description, $amenities, $basePrice));

    if ($stmt === false) { $message = "<div style='color:#ef4444; text-align:center; margin-bottom:20px; font-weight:bold;'>Error adding Room Type!</div>"; } 
    else { $message = "<div style='color:#10b981; text-align:center; margin-bottom:20px; font-weight:bold;'>Room Type added successfully!</div>"; }
}

// 2. Logic to Add a Specific Room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $roomNumber = $_POST['roomNumber'];
    $floor = $_POST['floor'];
    $typeId = $_POST['typeId'];
    $status = $_POST['status'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];

    $insertSql = "INSERT INTO ROOM (RoomNumber, Floor, TypeID, Status, PricePerNight, Capacity) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = sqlsrv_query($conn, $insertSql, array($roomNumber, $floor, $typeId, $status, $price, $capacity));

    if ($stmt === false) { $message = "<div style='color:#ef4444; text-align:center; margin-bottom:20px; font-weight:bold;'>Error adding Room!</div>"; } 
    else { $message = "<div style='color:#10b981; text-align:center; margin-bottom:20px; font-weight:bold;'>Room added successfully!</div>"; }
}
?>

<div class="container">
    <?php echo $message; ?>

    <div class="card">
        <h3>Step 1: Add a Room Type (Category)</h3>
        <form method="POST" action="rooms.php">
            <div class="form-group">
                <input type="text" name="typeName" class="form-control" placeholder="Type Name (e.g. Deluxe Suite)" required>
                <input type="text" name="description" class="form-control" placeholder="Description" required>
            </div>
            <div class="form-group">
                <input type="text" name="amenities" class="form-control" placeholder="Amenities" required>
                <input type="number" name="basePrice" class="form-control" placeholder="Base Price" required>
            </div>
            <button type="submit" name="add_type" class="btn-secondary">Save Room Type</button>
        </form>

        <table>
            <tr><th>Type ID</th><th>Name</th><th>Base Price</th></tr>
            <?php
            if(isset($conn)) {
                $typeSql = "SELECT TypeID, TypeName, BasePrice FROM ROOM_TYPE";
                $typeStmt = sqlsrv_query($conn, $typeSql);
                if ($typeStmt !== false) {
                    while ($tRow = sqlsrv_fetch_array($typeStmt, SQLSRV_FETCH_ASSOC)) {
                        echo "<tr><td>".$tRow['TypeID']."</td><td>".$tRow['TypeName']."</td><td>$".$tRow['BasePrice']."</td></tr>";
                    }
                }
            }
            ?>
        </table>
    </div>

    <div class="card">
        <h3>Step 2: Add a Specific Room</h3>
        <form method="POST" action="rooms.php">
            <div class="form-group">
                <input type="text" name="roomNumber" class="form-control" placeholder="Room Number (e.g. 101)" required>
                <input type="number" name="floor" class="form-control" placeholder="Floor" required>
                <input type="number" name="typeId" class="form-control" placeholder="Room Type ID (From table above)" required>
            </div>
            <div class="form-group">
                <select name="status" class="form-control" required>
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
                <input type="number" name="price" class="form-control" placeholder="Actual Price" required>
                <input type="number" name="capacity" class="form-control" placeholder="Capacity" required>
            </div>
            <button type="submit" name="add_room" class="btn-primary">Save Room</button>
        </form>
    </div>

    <div class="card">
        <h3>Current Room Inventory</h3>
        <table>
            <thead>
                <tr>
                    <th>Room ID</th><th>Number</th><th>Floor</th><th>Type ID</th><th>Status</th><th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn)) {
                    $sql = "SELECT * FROM ROOM ORDER BY RoomNumber ASC";
                    $stmt = sqlsrv_query($conn, $sql);
                    if ($stmt !== false) {
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            echo "<tr><td>" . $row['RoomID'] . "</td><td>" . $row['RoomNumber'] . "</td><td>" . $row['Floor'] . "</td><td>" . $row['TypeID'] . "</td><td>" . $row['Status'] . "</td><td>$" . $row['PricePerNight'] . "</td></tr>";
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; // MAGIC! This closes the page. ?>