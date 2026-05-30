<?php
include 'db.php';
include 'header.php'; 
$message = "";

// --- 1. HANDLE FORM SUBMISSION (CREATE BOOKING) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_booking'])) {
    $customerId = $_POST['customerId'];
    $roomId = $_POST['roomId'];
    $checkIn = $_POST['checkIn'];
    $checkOut = $_POST['checkOut'];
    $status = 'Confirmed'; 

    // Insert the new booking
    $insertSql = "INSERT INTO BOOKING (CustomerID, RoomID, CheckInDate, CheckOutDate, Status) VALUES (?, ?, ?, ?, ?)";
    $stmt = sqlsrv_query($conn, $insertSql, array($customerId, $roomId, $checkIn, $checkOut, $status));

    if ($stmt === false) { 
        $message = "<div style='color:#ef4444; text-align:center; margin-bottom:20px; font-weight:bold;'>Error creating booking! (Check table constraints)</div>"; 
    } else { 
        // Bonus: Automatically update the room status to 'Occupied'
        $updateRoom = "UPDATE ROOM SET Status = 'Occupied' WHERE RoomID = ?";
        sqlsrv_query($conn, $updateRoom, array($roomId));

        $message = "<div style='color:#10b981; text-align:center; margin-bottom:20px; font-weight:bold;'>Booking created successfully! Room is now Occupied.</div>"; 
    }
}
?>

<div class="container">
    <div class="section-title" style="margin-top: 20px;">
        <h2>Reservation Desk</h2>
        <p>Create and manage customer room bookings</p>
    </div>

    <?php echo $message; ?>

    <div class="card">
        <h3>Create New Booking</h3>
        <form method="POST" action="bookings.php">
            <div class="form-group">
                <select name="customerId" class="form-control" required>
                    <option value="" disabled selected>-- Select Customer --</option>
                    <?php
                    if(isset($conn)) {
                        $custSql = "SELECT CustomerID, Name, CNIC FROM CUSTOMER ORDER BY Name ASC";
                        $custStmt = sqlsrv_query($conn, $custSql);
                        if ($custStmt !== false) {
                            while ($cRow = sqlsrv_fetch_array($custStmt, SQLSRV_FETCH_ASSOC)) {
                                echo "<option value='".$cRow['CustomerID']."'>".$cRow['Name']." (CNIC: ".$cRow['CNIC'].")</option>";
                            }
                        }
                    }
                    ?>
                </select>

                <select name="roomId" class="form-control" required>
                    <option value="" disabled selected>-- Select Available Room --</option>
                    <?php
                    if(isset($conn)) {
                        // Only show rooms that are currently Available
                        $roomSql = "SELECT RoomID, RoomNumber, PricePerNight FROM ROOM WHERE Status = 'Available' ORDER BY RoomNumber ASC";
                        $roomStmt = sqlsrv_query($conn, $roomSql);
                        if ($roomStmt !== false) {
                            while ($rRow = sqlsrv_fetch_array($roomStmt, SQLSRV_FETCH_ASSOC)) {
                                echo "<option value='".$rRow['RoomID']."'>Room ".$rRow['RoomNumber']." - $".$rRow['PricePerNight']."/night</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <input type="date" name="checkIn" class="form-control" required title="Check-In Date">
                <input type="date" name="checkOut" class="form-control" required title="Check-Out Date">
            </div>
            
            <button type="submit" name="add_booking" class="btn-primary">Confirm Booking</button>
        </form>
    </div>

    <div class="card">
        <h3>Active Bookings</h3>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Room #</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn)) {
                    // This query uses INNER JOIN to get the actual Name and Room Number instead of just IDs!
                    $sql = "SELECT b.BookingID, c.Name, r.RoomNumber, b.CheckInDate, b.CheckOutDate, b.Status 
                            FROM BOOKING b
                            INNER JOIN CUSTOMER c ON b.CustomerID = c.CustomerID
                            INNER JOIN ROOM r ON b.RoomID = r.RoomID
                            ORDER BY b.BookingID DESC";
                            
                    $stmt = sqlsrv_query($conn, $sql);

                    if ($stmt !== false) {
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            // Format the SQL dates nicely for the web
                            $checkInDate = $row['CheckInDate'] ? $row['CheckInDate']->format('Y-m-d') : 'N/A';
                            $checkOutDate = $row['CheckOutDate'] ? $row['CheckOutDate']->format('Y-m-d') : 'N/A';

                            echo "<tr>";
                            echo "<td>" . $row['BookingID'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['RoomNumber']) . "</td>";
                            echo "<td>" . $checkInDate . "</td>";
                            echo "<td>" . $checkOutDate . "</td>";
                            echo "<td><span style='background:#f59e0b; color:black; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:bold;'>" . htmlspecialchars($row['Status']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No active bookings found.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>