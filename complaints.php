<?php
// --- DATABASE CONNECTION & LOGIC ---
include 'db.php';
include 'header.php'; 

$message = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_complaint'])) {
    
    $customer_id = $_POST['customer_id'];
    $room_id = $_POST['room_id']; 
    $description = $_POST['description'];
    $status = 'Pending';
    
    // --- GRAB TODAY'S DATE ---
    $logged_date = date('Y-m-d'); 

    // --- MySQLi PREPARED STATEMENT ---
    $insertSql = "INSERT INTO COMPLAINT (CustomerID, RoomID, Description, Status, LoggedDate) VALUES (?, ?, ?, ?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertSql);
    
    // "iisss" means: Int, Int, String, String, String
    mysqli_stmt_bind_param($insertStmt, "iisss", $customer_id, $room_id, $description, $status, $logged_date);

    if (!mysqli_stmt_execute($insertStmt)) {
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>MySQL Error: " . mysqli_error($conn) . "</div>";
    } else {
        $message = "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Maintenance ticket logged successfully!</div>";
    }
    mysqli_stmt_close($insertStmt);
}
?>

<header class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-brand-orange">Maintenance & Complaints</h1>
    <p class="text-sm text-gray-400">Log guest issues and track maintenance resolution tickets</p>
</header>

<?php echo $message; ?>

<section class="mb-8 rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
    <h3 class="mb-4 border-b border-navy-700 pb-2 text-lg font-bold text-brand-orange">Log a New Issue</h3>
    
    <form method="POST" action="complaints.php" class="space-y-4">
        <div class="flex flex-col gap-4 sm:flex-row">
            <select name="customer_id" id="guestDropdown" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange" onchange="autoFillRoom()" required>
                <option value="" data-roomid="" data-roomnum="" disabled selected>-- Select Checked-In Guest --</option>
                <?php
                if(isset($conn)) {
                    $custSql = "SELECT b.CustomerID, b.RoomID, c.Name, r.RoomNumber 
                                FROM BOOKING b
                                JOIN CUSTOMER c ON b.CustomerID = c.CustomerID
                                JOIN ROOM r ON b.RoomID = r.RoomID
                                WHERE b.Status IN ('Confirmed', 'Active', 'Paid', 'Completed')"; 
                                
                    $custResult = mysqli_query($conn, $custSql);
                    
                    if ($custResult) {
                        while ($cRow = mysqli_fetch_assoc($custResult)) {
                            echo "<option value='".$cRow['CustomerID']."' data-roomid='".$cRow['RoomID']."' data-roomnum='".htmlspecialchars($cRow['RoomNumber'])."'>ID #".$cRow['CustomerID']." - ".htmlspecialchars($cRow['Name'])."</option>";
                        }
                    }
                }
                ?>
            </select>

            <input type="hidden" name="room_id" id="hiddenRoomId" required>
            <input type="text" id="displayRoomNumber" class="w-full rounded-md border border-navy-700 bg-navy-900 px-3 py-2 text-sm text-gray-500 cursor-not-allowed" placeholder="Room Number (Auto-fills)" readonly required>
        </div>
        
        <div>
            <input type="text" name="description" class="w-full rounded-md border border-navy-700 bg-navy-800 px-3 py-2 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange" placeholder="Describe the issue (e.g., AC not working, Leaking sink)" required>
        </div>
        
        <div class="pt-2">
            <button type="submit" name="submit_complaint" class="rounded-md bg-brand-orange px-6 py-2.5 text-sm font-bold text-navy-900 shadow-sm transition hover:bg-yellow-500">Generate Maintenance Ticket</button>
        </div>
    </form>
</section>

<section class="rounded-lg border border-navy-700 bg-navy-900 shadow-sm overflow-hidden">
    <div class="border-b border-navy-700 px-6 py-4">
        <h3 class="text-lg font-bold text-brand-orange">Active Maintenance Tickets</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-brand-orange text-navy-900 text-sm font-bold tracking-wide">
                <tr>
                    <th class="px-6 py-3">Ticket ID</th>
                    <th class="px-6 py-3">Guest Name</th>
                    <th class="px-6 py-3">Room Number</th>
                    <th class="px-6 py-3">Issue Description</th>
                    <th class="px-6 py-3">Date Logged</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-700 text-sm bg-navy-800">
                <?php
                if(isset($conn) && $conn) {
                    $sql = "SELECT cp.ComplaintID, cp.Description, cp.Status, cp.LoggedDate, c.Name, r.RoomNumber 
                            FROM COMPLAINT cp
                            LEFT JOIN CUSTOMER c ON cp.CustomerID = c.CustomerID
                            LEFT JOIN ROOM r ON cp.RoomID = r.RoomID
                            ORDER BY cp.ComplaintID DESC";
                            
                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Format the MySQL string date
                            $logDate = $row['LoggedDate'] ? date('M d, Y', strtotime($row['LoggedDate'])) : 'Unknown';

                            // Dynamic Badge Colors
                            $statusClass = 'bg-gray-800 text-gray-300';
                            if ($row['Status'] == 'Pending') $statusClass = 'bg-red-900/50 text-red-400 border border-red-800/50';
                            if ($row['Status'] == 'Resolved') $statusClass = 'bg-emerald-900/50 text-emerald-400 border border-emerald-800/50';

                            echo "<tr class='hover:bg-navy-700/50 transition'>";
                            echo "<td class='whitespace-nowrap px-6 py-4 font-bold text-white'>#" . htmlspecialchars($row['ComplaintID']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . (htmlspecialchars($row['Name']) ? htmlspecialchars($row['Name']) : 'Unknown Guest') . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>Room " . (htmlspecialchars($row['RoomNumber']) ? htmlspecialchars($row['RoomNumber']) : 'N/A') . "</td>";
                            echo "<td class='px-6 py-4 text-gray-300'>" . htmlspecialchars($row['Description']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . $logDate . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4'><span class='inline-flex items-center rounded px-2.5 py-1 text-xs font-semibold " . $statusClass . "'>" . htmlspecialchars($row['Status']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='px-6 py-10 text-center text-sm text-gray-500 font-medium'>No complaints found.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script>
function autoFillRoom() {
    var dropdown = document.getElementById("guestDropdown");
    var hiddenRoomId = document.getElementById("hiddenRoomId");
    var displayRoom = document.getElementById("displayRoomNumber");
    
    var selectedOption = dropdown.options[dropdown.selectedIndex];
    
    var roomId = selectedOption.getAttribute("data-roomid");
    var roomNum = selectedOption.getAttribute("data-roomnum");
    
    if(roomId && roomNum) {
        hiddenRoomId.value = roomId;
        displayRoom.value = "Room " + roomNum;
    }
}
</script>

<?php include 'footer.php'; ?>