<?php
include 'db.php';
$message = "";

// --- LOGIC: ADD NEW STAFF ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_staff'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO STAFF (Name, Role, Phone, Salary, ShiftTiming, HireDate) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssiss", $_POST['name'], $_POST['role'], $_POST['phone'], $_POST['salary'], $_POST['shiftTiming'], date('Y-m-d'));
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: staff.php?status=staff_added");
        exit();
    } else {
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Error hiring staff: " . mysqli_error($conn) . "</div>";
    }
    mysqli_stmt_close($stmt);
}

// --- LOGIC: CREATE MAINTENANCE TICKET ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_ticket'])) {
    $sql = "INSERT INTO MAINTENANCE_TICKET (ComplaintID, AssignedStaffID, VerifiedByManager, Status) VALUES (?, ?, ?, 'Pending')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $_POST['complaintId'], $_POST['assignedStaff'], $_POST['managerId']);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: staff.php?status=ticket_added");
        exit();
    } else {
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Error creating ticket.</div>";
    }
    mysqli_stmt_close($stmt);
}

// --- LOGIC: RESOLVE TICKET ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resolve_ticket'])) {
    // 1. Update Ticket
    $stmt1 = mysqli_prepare($conn, "UPDATE MAINTENANCE_TICKET SET Status = 'Resolved', Resolution = ?, ClosedDate = ? WHERE TicketID = ?");
    $stmt1_date = date('Y-m-d');
    mysqli_stmt_bind_param($stmt1, "ssi", $_POST['resolution'], $stmt1_date, $_POST['ticketId']);
    mysqli_stmt_execute($stmt1);
    
    // 2. Update Complaint
    $stmt2 = mysqli_prepare($conn, "UPDATE COMPLAINT SET Status = 'Resolved' WHERE ComplaintID = ?");
    mysqli_stmt_bind_param($stmt2, "i", $_POST['complaintId']);
    mysqli_stmt_execute($stmt2);
    
    header("Location: staff.php?status=resolved");
    exit();
}

// NOW we load the header so the HTML doesn't block the redirects above
include 'header.php';
?>

<header class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-brand-orange">Staff & Maintenance Hub</h1>
    <p class="text-sm text-gray-400">Manage hotel employees and track maintenance tickets</p>
</header>

<?php 
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'staff_added') echo "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Staff member hired successfully!</div>";
    if ($_GET['status'] == 'ticket_added') echo "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Maintenance ticket assigned!</div>";
    if ($_GET['status'] == 'resolved') echo "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Ticket resolved successfully!</div>";
}
echo $message; 
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <section class="rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
        <h3 class="mb-4 font-bold text-brand-orange border-b border-navy-700 pb-2">Hire New Staff</h3>
        <form method="POST" class="space-y-4">
            <input type="text" name="name" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Full Name" required>
            <select name="role" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white">
                <option value="Manager">Manager</option>
                <option value="Receptionist">Receptionist</option>
                <option value="Housekeeping">Housekeeping</option>
                <option value="Plumber">Plumber</option>
                <option value="Electrician">Electrician</option>
            </select>
            <input type="text" name="phone" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Phone Number" required>
            <input type="number" name="salary" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Salary" required>
            <select name="shiftTiming" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white">
                <option value="08:00 AM - 04:00 PM">Morning Shift</option>
                <option value="04:00 PM - 12:00 AM">Evening Shift</option>
                <option value="12:00 AM - 08:00 AM">Night Shift</option>
            </select>
            <button type="submit" name="add_staff" class="w-full bg-brand-orange text-navy-900 font-bold py-2 rounded">Hire Employee</button>
        </form>
    </section>

    <section class="rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
        <h3 class="mb-4 font-bold text-brand-orange border-b border-navy-700 pb-2">Assign Ticket</h3>
        <form method="POST" class="space-y-4">
            <select name="complaintId" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
                <option value="" disabled selected>-- Select Pending Complaint --</option>
                <?php
                $res = mysqli_query($conn, "SELECT c.ComplaintID, c.Description, r.RoomNumber FROM COMPLAINT c JOIN ROOM r ON c.RoomID = r.RoomID WHERE c.Status = 'Pending'");
                while ($c = mysqli_fetch_assoc($res)) {
                    // Truncate the description to 35 characters so the dropdown stays clean
                    $desc = strlen($c['Description']) > 35 ? substr($c['Description'], 0, 35) . "..." : $c['Description'];
                    
                    // Display both Room Number and the truncated Description snippet
                    echo "<option value='{$c['ComplaintID']}'>#{$c['ComplaintID']} - Room {$c['RoomNumber']} ({$desc})</option>";
                }
                ?>
            </select>
            <select name="assignedStaff" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
                <option value="" disabled selected>-- Assign Worker --</option>
                <?php
                $res = mysqli_query($conn, "SELECT StaffID, Name, Role FROM STAFF WHERE Role NOT IN ('Manager', 'Receptionist')");
                while ($s = mysqli_fetch_assoc($res)) echo "<option value='{$s['StaffID']}'>{$s['Name']} ({$s['Role']})</option>";
                ?>
            </select>
            <select name="managerId" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
                <option value="" disabled selected>-- Approved By --</option>
                <?php
                $res = mysqli_query($conn, "SELECT StaffID, Name FROM STAFF WHERE Role = 'Manager'");
                while ($m = mysqli_fetch_assoc($res)) echo "<option value='{$m['StaffID']}'>Mgr: {$m['Name']}</option>";
                ?>
            </select>
            <button type="submit" name="add_ticket" class="w-full bg-brand-blue text-white font-bold py-2 rounded">Create Ticket</button>
        </form>
    </section>
</div>

<section id="ticketTable" class="mb-8 rounded-lg border border-navy-700 bg-navy-900 overflow-hidden shadow-sm">
    <div class="bg-navy-800 p-4 border-b border-navy-700 font-bold text-brand-orange">Active Maintenance Tickets</div>
    <table class="w-full text-left text-sm text-gray-300">
        <thead class="bg-navy-900 text-xs uppercase">
            <tr><th class="p-3">Ticket ID</th><th class="p-3">Worker</th><th class="p-3">Status</th><th class="p-3">Action</th></tr>
        </thead>
        <tbody class="divide-y divide-navy-700">
            <?php
            $sql = "SELECT t.TicketID, t.ComplaintID, t.Status, w.Name AS WorkerName FROM MAINTENANCE_TICKET t JOIN STAFF w ON t.AssignedStaffID = w.StaffID ORDER BY t.TicketID DESC";
            $res = mysqli_query($conn, $sql);
            if(mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<tr class='hover:bg-navy-800'>";
                    echo "<td class='p-3'>#{$row['TicketID']}</td><td class='p-3'>{$row['WorkerName']}</td>";
                    echo "<td class='p-3 ".($row['Status'] == 'Resolved' ? 'text-emerald-500' : 'text-amber-500')."'>{$row['Status']}</td>";
                    echo "<td class='p-3'>";
                    if ($row['Status'] != 'Resolved') {
                        echo "<form method='POST' class='flex gap-2'>
                                <input type='hidden' name='ticketId' value='{$row['TicketID']}'>
                                <input type='hidden' name='complaintId' value='{$row['ComplaintID']}'>
                                <input type='text' name='resolution' placeholder='Fix details...' class='bg-navy-800 border-navy-700 p-1 rounded text-xs text-white' required>
                                <button type='submit' name='resolve_ticket' class='bg-emerald-600 px-2 py-1 rounded text-xs text-white'>Resolve</button>
                              </form>";
                    } else {
                        echo "<span class='text-gray-500 italic text-xs'>Completed</span>";
                    }
                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='p-6 text-center text-gray-500'>No active tickets.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<section class="rounded-lg border border-navy-700 bg-navy-900 overflow-hidden shadow-sm">
    <div class="bg-navy-800 p-4 border-b border-navy-700 font-bold text-brand-orange">Staff Directory</div>
    <table class="w-full text-left text-sm text-gray-300">
        <thead class="bg-navy-900 text-xs uppercase">
            <tr><th class="p-3">Staff ID</th><th class="p-3">Staff Name</th><th class="p-3">Role</th><th class="p-3">Salary</th><th class="p-3">Shift</th></tr>
        </thead>
        <tbody class="divide-y divide-navy-700">
            <?php
            $res = mysqli_query($conn, "SELECT StaffID, Name, Role, Salary, ShiftTiming FROM STAFF");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<tr class='hover:bg-navy-800'>";
                echo "<td class='p-3 text-brand-blue font-bold'>#" . htmlspecialchars($row['StaffID']) . "</td>";
                echo "<td class='p-3 font-bold text-white'>{$row['Name']}</td>";
                echo "<td class='p-3'>{$row['Role']}</td>";
                echo "<td class='p-3'>Rs " . number_format($row['Salary']) . "</td>"; 
                echo "<td class='p-3'>{$row['ShiftTiming']}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<script>
    <?php if (isset($_GET['status'])): ?>
        window.onload = () => document.getElementById('ticketTable').scrollIntoView({ behavior: 'smooth' });
    <?php endif; ?>
</script>

<?php include 'footer.php'; ?>