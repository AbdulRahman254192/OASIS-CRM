<?php
session_start(); // CRITICAL: This must be here to grab the logged-in Staff ID!
include 'db.php';
include_once 'pricing_engine.php';
include 'header.php'; 

$message = "";

// Security Check: Ensure a staff member is logged in before allowing bookings
$staff_id = isset($_SESSION['staff_id']) ? $_SESSION['staff_id'] : null;

// --- 1. HANDLE FORM SUBMISSION (CREATE BOOKING) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_booking'])) {
    
    if ($staff_id === null) {
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Access Denied: You must be logged in as Staff to create a booking!</div>";
    } else {
        $customerId = $_POST['customerId'];
        $roomId = $_POST['roomId'];
        $checkIn = $_POST['checkIn'];
        $checkOut = $_POST['checkOut'];
        $status = 'Confirmed'; 

        // --- THE DOUBLE-BOOKING SECURITY CHECK (MySQLi) ---
        $overlapSql = "SELECT BookingID FROM BOOKING 
                       WHERE RoomID = ? 
                       AND Status IN ('Confirmed', 'Active') 
                       AND (CheckInDate < ?) 
                       AND (CheckOutDate > ?)";
        
        $overlapStmt = mysqli_prepare($conn, $overlapSql);
        mysqli_stmt_bind_param($overlapStmt, "iss", $roomId, $checkOut, $checkIn);
        mysqli_stmt_execute($overlapStmt);
        mysqli_stmt_store_result($overlapStmt); 

        if (mysqli_stmt_num_rows($overlapStmt) > 0) {
            // 🚨 OVERLAP DETECTED!
            $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Booking Failed: That room is already reserved during those dates!</div>";
        } else {
            // ✅ CALENDAR IS CLEAR! 
            
            // Calculate the exact price dynamically
            $priceData = calculateFinalPrice($conn, $customerId, $roomId, $checkIn, $checkOut);
            $calculatedTotal = $priceData['FinalTotal']; 

            // Save the booking with the verified Staff ID
            $insertSql = "INSERT INTO BOOKING (CustomerID, RoomID, CheckInDate, CheckOutDate, Status, TotalAmount, StaffID) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertSql);
            
            // "iisssdi" means: Int, Int, String, String, String, Double, Int
            mysqli_stmt_bind_param($insertStmt, "iisssdi", $customerId, $roomId, $checkIn, $checkOut, $status, $calculatedTotal, $staff_id);
            
            if (!mysqli_stmt_execute($insertStmt)) { 
                $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>Error creating booking: " . mysqli_error($conn) . "</div>"; 
            } else { 
                $message = "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Booking reserved! Final Price: $" . number_format($calculatedTotal, 2) . "</div>"; 
            }
            mysqli_stmt_close($insertStmt);
        }
        mysqli_stmt_close($overlapStmt);
    }
}
?>

<header class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-brand-orange">Reservation Desk</h1>
    <p class="text-sm text-gray-400">Create and manage customer room bookings</p>
</header>

<?php echo $message; ?>

<section class="mb-8 rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
    <h3 class="mb-4 border-b border-navy-700 pb-2 text-lg font-bold text-brand-orange">Create New Booking</h3>
    
    <form method="POST" action="bookings.php" class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row">
            <select name="customerId" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange" required>
                <option value="" disabled selected>-- Select Customer --</option>
                <?php
                if(isset($conn)) {
                    $custSql = "SELECT CustomerID, Name, CNIC FROM CUSTOMER ORDER BY Name ASC";
                    $custResult = mysqli_query($conn, $custSql);
                    if ($custResult) {
                        while ($cRow = mysqli_fetch_assoc($custResult)) {
                            echo "<option value='".$cRow['CustomerID']."'>".$cRow['Name']." (CNIC: ".$cRow['CNIC'].")</option>";
                        }
                    }
                }
                ?>
            </select>

            <select name="roomId" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange" required>
                <option value="" disabled selected>-- Select Room --</option>
                <?php
                if(isset($conn)) {
                    $roomSql = "SELECT RoomID, RoomNumber, PricePerNight FROM ROOM WHERE Status != 'Maintenance' ORDER BY RoomNumber ASC";
                    $roomResult = mysqli_query($conn, $roomSql);
                    if ($roomResult) {
                        while ($rRow = mysqli_fetch_assoc($roomResult)) {
                            echo "<option value='".$rRow['RoomID']."'>Room ".$rRow['RoomNumber']." - $".$rRow['PricePerNight']."/night</option>";
                        }
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="flex flex-col gap-4 sm:flex-row">
            <div class="w-full">
                <label class="mb-1 block text-xs font-semibold text-gray-400">Check-In Date</label>
                <input type="date" name="checkIn" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange" required>
            </div>
            <div class="w-full">
                <label class="mb-1 block text-xs font-semibold text-gray-400">Check-Out Date</label>
                <input type="date" name="checkOut" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange" required>
            </div>
        </div>
        
        <div class="pt-2">
            <button type="submit" name="add_booking" class="rounded-md bg-brand-orange px-6 py-2.5 text-sm font-bold text-navy-900 shadow-sm transition hover:bg-yellow-500">Confirm Booking</button>
        </div>
    </form>
</section>

<section class="rounded-lg border border-navy-700 bg-navy-900 shadow-sm overflow-hidden">
    <div class="border-b border-navy-700 px-6 py-4">
        <h3 class="text-lg font-bold text-brand-orange">Active Bookings</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-brand-orange text-navy-900 text-sm font-bold tracking-wide">
                <tr>
                    <th class="px-6 py-3">Booking ID</th>
                    <th class="px-6 py-3">Customer Name</th>
                    <th class="px-6 py-3">Room #</th>
                    <th class="px-6 py-3">Check-In</th>
                    <th class="px-6 py-3">Check-Out</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-700 text-sm bg-navy-800">
                <?php
                if(isset($conn)) {
                    $sql = "SELECT b.BookingID, c.Name, r.RoomNumber, b.CheckInDate, b.CheckOutDate, b.Status 
                            FROM BOOKING b
                            INNER JOIN CUSTOMER c ON b.CustomerID = c.CustomerID
                            INNER JOIN ROOM r ON b.RoomID = r.RoomID
                            ORDER BY b.BookingID DESC";
                            
                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $checkInDate = $row['CheckInDate'] ? date('Y-m-d', strtotime($row['CheckInDate'])) : 'N/A';
                            $checkOutDate = $row['CheckOutDate'] ? date('Y-m-d', strtotime($row['CheckOutDate'])) : 'N/A';

                            // Dynamic Badge Colors
                            $badgeStyles = 'bg-gray-800 text-gray-300';
                            if ($row['Status'] === 'Confirmed') $badgeStyles = 'bg-amber-900/50 text-amber-400 border border-amber-800/50';
                            if ($row['Status'] === 'Active') $badgeStyles = 'bg-blue-900/50 text-blue-400 border border-blue-800/50';
                            if ($row['Status'] === 'Completed') $badgeStyles = 'bg-emerald-900/50 text-emerald-400 border border-emerald-800/50';

                            echo "<tr class='hover:bg-navy-700/50 transition'>";
                            echo "<td class='whitespace-nowrap px-6 py-4 font-bold text-white'>#" . $row['BookingID'] . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . htmlspecialchars($row['RoomNumber']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . $checkInDate . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . $checkOutDate . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4'><span class='inline-flex items-center rounded px-2.5 py-1 text-xs font-semibold " . $badgeStyles . "'>" . htmlspecialchars($row['Status']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='px-6 py-10 text-center text-sm text-gray-500 font-medium'>No active bookings found.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<?php include 'footer.php'; ?>