<?php
include 'db.php';
include 'header.php';
$message = "";

// --- LOGIC: ADD NEW STAFF ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_staff'])) {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $salary = $_POST['salary'];
    $shiftTiming = $_POST['shiftTiming'];
    $hireDate = date('Y-m-d'); 

    $sql = "INSERT INTO STAFF (Name, Role, Phone, Salary, ShiftTiming, HireDate) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = sqlsrv_query($conn, $sql, array($name, $role, $phone, $salary, $shiftTiming, $hireDate));

    if ($stmt === false) {
        $message = "<div style='color:#ef4444; background:#1e293b; border:1px solid #ef4444; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>❌ Error hiring staff. Check database constraints!</div>";
    } else {
        header("Location: staff.php?status=staff_added");
        exit();
    }
}

// --- LOGIC: CREATE MAINTENANCE TICKET ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_ticket'])) {
    $complaintId = $_POST['complaintId'];
    $assignedStaff = $_POST['assignedStaff'];
    $managerId = $_POST['managerId'];
    
    // Status defaults to Pending, Resolution and ClosedDate are NULL until resolved
    $sql = "INSERT INTO MAINTENANCE_TICKET (ComplaintID, AssignedStaffID, VerifiedByManager, Status) VALUES (?, ?, ?, 'Pending')";
    $stmt = sqlsrv_query($conn, $sql, array($complaintId, $assignedStaff, $managerId));

    if ($stmt === false) {
        $message = "<div style='color:#ef4444; background:#1e293b; border:1px solid #ef4444; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>❌ Error creating ticket! Ensure the Complaint ID exists.</div>";
    } else {
        header("Location: staff.php?status=ticket_added");
        exit();
    }
}

// --- LOGIC: RESOLVE TICKET ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resolve_ticket'])) {
    $ticketId = $_POST['ticketId'];
    $resolution = $_POST['resolution'];
    $closedDate = date('Y-m-d');

    $sql = "UPDATE MAINTENANCE_TICKET SET Status = 'Resolved', Resolution = ?, ClosedDate = ? WHERE TicketID = ?";
    $stmt = sqlsrv_query($conn, $sql, array($resolution, $closedDate, $ticketId));
    
    header("Location: staff.php?status=resolved");
    exit();
}
?>

<div class="container">
    <div class="section-title" style="margin-top: 20px;">
        <h2>Staff & Maintenance Hub</h2>
        <p>Manage hotel employees and track maintenance tickets</p>
    </div>

    <?php 
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'staff_added') echo "<div style='color:#10b981; background:#1e293b; border:1px solid #10b981; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>✅ Staff member hired successfully!</div>";
        if ($_GET['status'] == 'ticket_added') echo "<div style='color:#10b981; background:#1e293b; border:1px solid #10b981; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>✅ Maintenance ticket assigned!</div>";
        if ($_GET['status'] == 'resolved') echo "<div style='color:#10b981; background:#1e293b; border:1px solid #10b981; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>✅ Ticket resolved successfully!</div>";
    }
    echo $message; 
    ?>

    <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start;">
        
        <div class="card" style="flex: 1; min-width: 300px;">
            <h3>Hire New Staff</h3>
            <form method="POST" action="staff.php">
                <div class="form-group"><input type="text" name="name" class="form-control" placeholder="Full Name" required></div>
                
                <div class="form-group">
                    <select name="role" class="form-control" required>
                        <option value="" disabled selected>-- Select Role --</option>
                        <option value="Manager">Manager</option>
                        <option value="Receptionist">Receptionist</option>
                        <option value="Housekeeping">Housekeeping</option>
                        <option value="Plumber">Plumber</option>
                        <option value="Electrician">Electrician</option>
                    </select>
                </div>

                <div class="form-group"><input type="text" name="phone" class="form-control" placeholder="Phone Number" required></div>
                <div class="form-group"><input type="number" name="salary" class="form-control" placeholder="Salary (PKR/USD)" step="0.01" required></div>
                
                <div class="form-group">
                    <select name="shiftTiming" class="form-control" required>
                        <option value="" disabled selected>-- Select Shift Timing --</option>
                        <option value="08:00 AM - 04:00 PM">08:00 AM - 04:00 PM (Morning)</option>
                        <option value="04:00 PM - 12:00 AM">04:00 PM - 12:00 AM (Evening)</option>
                        <option value="12:00 AM - 08:00 AM">12:00 AM - 08:00 AM (Night)</option>
                    </select>
                </div>
                
                <button type="submit" name="add_staff" class="btn-primary" style="width: 100%;">Hire Employee</button>
            </form>
        </div>

        <div class="card" style="flex: 1; min-width: 300px;">
            <h3>Assign Ticket</h3>
            <form method="POST" action="staff.php">
                <div class="form-group">
                    <select name="complaintId" class="form-control" required>
                        <option value="" disabled selected>-- Select Active Complaint --</option>
                        <?php
                        // Upgraded dropdown linking COMPLAINT and ROOM
                        if(isset($conn)) {
                            $cSql = "SELECT c.ComplaintID, c.Description, r.RoomNumber 
                                     FROM COMPLAINT c 
                                     JOIN ROOM r ON c.RoomID = r.RoomID
                                     WHERE c.Status = 'Pending' OR c.Status IS NULL"; 
                                     
                            $cStmt = sqlsrv_query($conn, $cSql);
                            
                            if ($cStmt !== false) {
                                while ($cRow = sqlsrv_fetch_array($cStmt, SQLSRV_FETCH_ASSOC)) {
                                    $shortDesc = htmlspecialchars(substr($cRow['Description'], 0, 30)) . "...";
                                    echo "<option value='".$cRow['ComplaintID']."'>#" . $cRow['ComplaintID'] . " - Room " . $cRow['RoomNumber'] . " (" . $shortDesc . ")</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="assignedStaff" class="form-control" required>
                        <option value="" disabled selected>-- Assign To (Maintenance Staff) --</option>
                        <?php
                        if(isset($conn)) {
                            $sSql = "SELECT StaffID, Name, Role FROM STAFF WHERE Role NOT IN ('Manager', 'Receptionist')";
                            $sStmt = sqlsrv_query($conn, $sSql);
                            if ($sStmt !== false) {
                                while ($sRow = sqlsrv_fetch_array($sStmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<option value='".$sRow['StaffID']."'>".$sRow['Name']." (".$sRow['Role'].")</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="managerId" class="form-control" required>
                        <option value="" disabled selected>-- Verified By (Manager) --</option>
                        <?php
                        if(isset($conn)) {
                            $mSql = "SELECT StaffID, Name FROM STAFF WHERE Role = 'Manager'";
                            $mStmt = sqlsrv_query($conn, $mSql);
                            if ($mStmt !== false) {
                                while ($mRow = sqlsrv_fetch_array($mStmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<option value='".$mRow['StaffID']."'>Manager: ".$mRow['Name']."</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <button type="submit" name="add_ticket" class="btn-secondary" style="width: 100%;">Create Ticket</button>
            </form>
        </div>
    </div>

    <div class="card" id="ticketTable">
        <h3>Active Maintenance Tickets</h3>
        <table style="width:100%; text-align:left;">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Complaint ID</th>
                    <th>Assigned To</th>
                    <th>Verified By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn)) {
                    $tSql = "SELECT t.TicketID, t.ComplaintID, t.Status, t.Resolution,
                                    w.Name AS WorkerName, 
                                    m.Name AS ManagerName
                             FROM MAINTENANCE_TICKET t
                             JOIN STAFF w ON t.AssignedStaffID = w.StaffID
                             JOIN STAFF m ON t.VerifiedByManager = m.StaffID
                             ORDER BY t.TicketID DESC";
                             
                    $tStmt = sqlsrv_query($conn, $tSql);

                    if ($tStmt !== false) {
                        while ($row = sqlsrv_fetch_array($tStmt, SQLSRV_FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>#" . htmlspecialchars($row['TicketID']) . "</td>";
                            echo "<td>#" . htmlspecialchars($row['ComplaintID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['WorkerName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ManagerName']) . "</td>";
                            
                            if ($row['Status'] == 'Resolved') {
                                echo "<td style='color:#10b981; font-weight:bold;'>Resolved</td>";
                                echo "<td><span style='color:#94a3b8; font-size: 12px;'>Note: " . htmlspecialchars($row['Resolution']) . "</span></td>";
                            } else {
                                echo "<td style='color:#f59e0b; font-weight:bold;'>Pending</td>";
                                echo "<td>
                                        <form method='POST' action='staff.php' style='display:flex; gap:10px;'>
                                            <input type='hidden' name='ticketId' value='".$row['TicketID']."'>
                                            <input type='text' name='resolution' placeholder='Enter fix details...' required style='padding:5px; border-radius:4px; border:none; background:#0b1120; color:white;'>
                                            <button type='submit' name='resolve_ticket' style='background:#10b981; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;'>Resolve</button>
                                        </form>
                                      </td>";
                            }
                            echo "</tr>";
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    <?php if (isset($_GET['status']) || !empty($message)): ?>
        window.onload = function() {
            setTimeout(function() {
                document.getElementById('ticketTable').scrollIntoView({ behavior: 'smooth' });
            }, 300);
        };
    <?php endif; ?>
</script>

<?php include 'footer.php'; ?>