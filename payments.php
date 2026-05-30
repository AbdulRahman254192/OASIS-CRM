<?php
include 'db.php';
include 'header.php';
$message = "";

// --- HANDLE PAYMENT & CHECKOUT LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    $bookingId = $_POST['bookingId'];
    $amount = $_POST['amount'];
    $method = $_POST['method'];
    $paymentDate = date('Y-m-d'); // Automatically grabs today's date

    // 1. Insert into PAYMENT table
    $insertSql = "INSERT INTO PAYMENT (BookingID, Amount, PaymentDate, PaymentMethod) VALUES (?, ?, ?, ?)";
    $stmt = sqlsrv_query($conn, $insertSql, array($bookingId, $amount, $paymentDate, $method));

    if ($stmt === false) {
        $message = "<div style='color:#ef4444; text-align:center; margin-bottom:20px; font-weight:bold;'>Error processing payment!</div>";
    } else {
        // 2. Update BOOKING status to Completed
        $updateBooking = "UPDATE BOOKING SET Status = 'Completed' WHERE BookingID = ?";
        sqlsrv_query($conn, $updateBooking, array($bookingId));

        // 3. Find which Room they were in, and set it back to Available
        $findRoomSql = "SELECT RoomID FROM BOOKING WHERE BookingID = ?";
        $roomStmt = sqlsrv_query($conn, $findRoomSql, array($bookingId));
        
        if ($roomRow = sqlsrv_fetch_array($roomStmt, SQLSRV_FETCH_ASSOC)) {
            $roomId = $roomRow['RoomID'];
            $updateRoom = "UPDATE ROOM SET Status = 'Available' WHERE RoomID = ?";
            sqlsrv_query($conn, $updateRoom, array($roomId));
        }

        $message = "<div style='color:#10b981; text-align:center; margin-bottom:20px; font-weight:bold;'>Payment Successful! Booking is completed and the room is now available for new guests.</div>";
    }
}
?>

<div class="container">
    <div class="section-title" style="margin-top: 20px;">
        <h2>Checkout & Payments</h2>
        <p>Process final payments and release rooms</p>
    </div>

    <?php echo $message; ?>

    <div class="card">
        <h3>Process a Payment</h3>
        <form method="POST" action="payments.php">
            <div class="form-group">
                <select name="bookingId" class="form-control" required>
                    <option value="" disabled selected>-- Select Active Booking to Checkout --</option>
                    <?php
                    if(isset($conn)) {
                        // Only show bookings that haven't been completed yet
                        $bookSql = "SELECT b.BookingID, c.Name, r.RoomNumber 
                                    FROM BOOKING b
                                    JOIN CUSTOMER c ON b.CustomerID = c.CustomerID
                                    JOIN ROOM r ON b.RoomID = r.RoomID
                                    WHERE b.Status = 'Confirmed'";
                        $bookStmt = sqlsrv_query($conn, $bookSql);
                        if ($bookStmt !== false) {
                            while ($bRow = sqlsrv_fetch_array($bookStmt, SQLSRV_FETCH_ASSOC)) {
                                echo "<option value='".$bRow['BookingID']."'>Booking #".$bRow['BookingID']." - ".$bRow['Name']." (Room ".$bRow['RoomNumber'].")</option>";
                            }
                        }
                    }
                    ?>
                </select>

                <input type="number" name="amount" class="form-control" placeholder="Total Amount to Charge ($)" required>
                
                <select name="method" class="form-control" required>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Cash">Cash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>
            
            <button type="submit" name="process_payment" class="btn-primary">Process Checkout</button>
        </form>
    </div>

    <div class="card">
        <h3>Recent Transactions</h3>
        <table>
            <thead>
                <tr>
                    <th>Receipt ID</th>
                    <th>Booking Ref</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn)) {
                    // Fetch payment history
                    $sql = "SELECT p.PaymentID, p.BookingID, p.PaymentDate, p.PaymentMethod, p.Amount, c.Name 
                            FROM PAYMENT p
                            JOIN BOOKING b ON p.BookingID = b.BookingID
                            JOIN CUSTOMER c ON b.CustomerID = c.CustomerID
                            ORDER BY p.PaymentID DESC";
                            
                    $stmt = sqlsrv_query($conn, $sql);

                    if ($stmt !== false) {
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            $dateStr = $row['PaymentDate'] ? $row['PaymentDate']->format('Y-m-d') : 'N/A';

                            echo "<tr>";
                            echo "<td>#" . htmlspecialchars($row['PaymentID']) . "</td>";
                            echo "<td>#" . htmlspecialchars($row['BookingID']) . " (" . htmlspecialchars($row['Name']) . ")</td>";
                            echo "<td>" . $dateStr . "</td>";
                            echo "<td>" . htmlspecialchars($row['PaymentMethod']) . "</td>";
                            echo "<td style='color:#10b981; font-weight:bold;'>$" . htmlspecialchars($row['Amount']) . "</td>";
                            echo "</tr>";
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>