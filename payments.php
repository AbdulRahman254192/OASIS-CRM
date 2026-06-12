<?php
include 'db.php';
include_once 'pricing_engine.php';

$message = "";

// --- HANDLE PAYMENT & CHECK-IN LOGIC ---
// NOTE: This must happen BEFORE header.php is included so the redirect works!
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    $bookingId = $_POST['bookingId'];
    $amount = $_POST['amount'];
    $method = $_POST['method'];
    $paymentDate = date('Y-m-d'); // Automatically grabs today's date

    // 1. Insert into PAYMENT table
    $insertSql = "INSERT INTO PAYMENT (BookingID, Amount, PaymentDate, PaymentMethod) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertSql);
    mysqli_stmt_bind_param($stmt, "idss", $bookingId, $amount, $paymentDate, $method);

    if (!mysqli_stmt_execute($stmt)) {
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>Error processing payment: " . mysqli_error($conn) . "</div>";
    } else {
        // 2. Update BOOKING status to Active
        $updateBooking = "UPDATE BOOKING SET Status = 'Active' WHERE BookingID = ?";
        $bookStmt = mysqli_prepare($conn, $updateBooking);
        mysqli_stmt_bind_param($bookStmt, "i", $bookingId);
        mysqli_stmt_execute($bookStmt);

        // 3. Find Room and mark as Occupied
        $findRoomSql = "SELECT RoomID FROM BOOKING WHERE BookingID = ?";
        $roomStmt = mysqli_prepare($conn, $findRoomSql);
        mysqli_stmt_bind_param($roomStmt, "i", $bookingId);
        mysqli_stmt_execute($roomStmt);
        $roomResult = mysqli_stmt_get_result($roomStmt);
        
        if ($roomRow = mysqli_fetch_assoc($roomResult)) {
            $roomId = $roomRow['RoomID'];
            $updateRoom = "UPDATE ROOM SET Status = 'Occupied' WHERE RoomID = ?";
            $upRoomStmt = mysqli_prepare($conn, $updateRoom);
            mysqli_stmt_bind_param($upRoomStmt, "i", $roomId);
            mysqli_stmt_execute($upRoomStmt);
        }

        // --- NEW INVOICE REDIRECT LOGIC (Using LIMIT 1 for MySQL) ---
        $getIdSql = "SELECT PaymentID FROM PAYMENT WHERE BookingID = ? ORDER BY PaymentID DESC LIMIT 1";
        $idStmt = mysqli_prepare($conn, $getIdSql);
        mysqli_stmt_bind_param($idStmt, "i", $bookingId);
        mysqli_stmt_execute($idStmt);
        $idResult = mysqli_stmt_get_result($idStmt);
        
        if ($idRow = mysqli_fetch_assoc($idResult)) {
            $newPaymentId = $idRow['PaymentID'];
            // Since we haven't loaded HTML yet, this redirect will work perfectly!
            header("Location: invoice.php?id=" . $newPaymentId);
            exit();
        }
    }
}

// NOW we can load the visual UI, after all redirects are handled!
include 'header.php';
?>

<header class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-brand-orange">Payments & Check-In</h1>
    <p class="text-sm text-gray-400">Process payments and activate guest rooms</p>
</header>

<?php echo $message; ?>

<section class="mb-8 rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
    <h3 class="mb-4 border-b border-navy-700 pb-2 text-lg font-bold text-brand-orange">Process a Payment</h3>
    
    <form method="POST" action="payments.php" class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row">
            
            <select name="bookingId" id="bookingDropdown" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange" onchange="autoFillPrice()" required>
                <option value="" data-price="" disabled selected>-- Select Confirmed Booking to Process --</option>
                <?php
                if(isset($conn)) {
                    $bookSql = "SELECT b.BookingID, b.CustomerID, b.RoomID, b.CheckInDate, b.CheckOutDate, b.TotalAmount, c.Name, r.RoomNumber 
                                FROM BOOKING b
                                JOIN CUSTOMER c ON b.CustomerID = c.CustomerID
                                JOIN ROOM r ON b.RoomID = r.RoomID
                                WHERE b.Status = 'Confirmed'";
                    
                    $bookResult = mysqli_query($conn, $bookSql);
                    if ($bookResult) {
                        while ($bRow = mysqli_fetch_assoc($bookResult)) {
                            
                            // SMART CHECK: Use DB amount, or calculate via Engine
                            if (!empty($bRow['TotalAmount'])) {
                                $final_price = $bRow['TotalAmount'];
                            } else {
                                $check_in = date('Y-m-d', strtotime($bRow['CheckInDate']));
                                $check_out = date('Y-m-d', strtotime($bRow['CheckOutDate']));
                                $priceData = calculateFinalPrice($conn, $bRow['CustomerID'], $bRow['RoomID'], $check_in, $check_out);
                                $final_price = $priceData['FinalTotal'];
                            }

                            echo "<option value='".$bRow['BookingID']."' data-price='".htmlspecialchars($final_price)."'>Booking #".$bRow['BookingID']." - ".$bRow['Name']." (Room ".$bRow['RoomNumber'].")</option>";
                        }
                    }
                }
                ?>
            </select>

            <input type="number" step="0.01" name="amount" id="autoAmountBox" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-brand-orange font-bold outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange placeholder-gray-600 cursor-not-allowed" placeholder="Total Amount ($)" readonly required>
            
            <select name="method" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange" required>
                <option value="Credit Card">Credit Card</option>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
        </div>
        
        <div class="pt-2">
            <button type="submit" name="process_payment" class="rounded-md bg-brand-orange px-6 py-2.5 text-sm font-bold text-navy-900 shadow-sm transition hover:bg-yellow-500">Process Payment & Check-In</button>
        </div>
    </form>
</section>

<section class="rounded-lg border border-navy-700 bg-navy-900 shadow-sm overflow-hidden">
    <div class="border-b border-navy-700 px-6 py-4">
        <h3 class="text-lg font-bold text-brand-orange">Recent Transactions</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-brand-orange text-navy-900 text-sm font-bold tracking-wide">
                <tr>
                    <th class="px-6 py-3">Receipt ID</th>
                    <th class="px-6 py-3">Booking Ref</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Method</th>
                    <th class="px-6 py-3">Amount Paid</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-700 text-sm bg-navy-800">
                <?php
                if(isset($conn)) {
                    $sql = "SELECT p.PaymentID, p.BookingID, p.PaymentDate, p.PaymentMethod, p.Amount, c.Name 
                            FROM PAYMENT p
                            JOIN BOOKING b ON p.BookingID = b.BookingID
                            JOIN CUSTOMER c ON b.CustomerID = c.CustomerID
                            ORDER BY p.PaymentID DESC";
                            
                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $dateStr = $row['PaymentDate'] ? date('M d, Y', strtotime($row['PaymentDate'])) : 'N/A';

                            echo "<tr class='hover:bg-navy-700/50 transition'>";
                            echo "<td class='whitespace-nowrap px-6 py-4 font-bold text-white'>#" . htmlspecialchars($row['PaymentID']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>#" . htmlspecialchars($row['BookingID']) . " (" . htmlspecialchars($row['Name']) . ")</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . $dateStr . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . htmlspecialchars($row['PaymentMethod']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-emerald-500 font-bold'>$" . number_format($row['Amount'], 2) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='px-6 py-10 text-center text-sm text-gray-500 font-medium'>No transactions found.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function autoFillPrice() {
    var dropdown = document.getElementById("bookingDropdown");
    var priceBox = document.getElementById("autoAmountBox");
    
    var selectedOption = dropdown.options[dropdown.selectedIndex];
    var calculatedPrice = selectedOption.getAttribute("data-price");
    
    if(calculatedPrice) {
        priceBox.value = parseFloat(calculatedPrice).toFixed(2);
    } else {
        priceBox.value = "";
    }
}
</script>

<?php include 'footer.php'; ?>