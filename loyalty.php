<?php
include 'db.php';
include 'header.php';
$message = "";

// --- LOGIC: MANAGER APPROVES LOYALTY ACCOUNT ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_loyalty'])) {
    $loyaltyId = $_POST['loyaltyId'];
    $managerId = $_POST['managerId'];

    // Update the account to add the Manager's ID who approved it
    $sql = "UPDATE LOYALTY_ACCOUNT SET StaffApprovedBy = ? WHERE LoyaltyID = ?";
    $stmt = sqlsrv_query($conn, $sql, array($managerId, $loyaltyId));

    if ($stmt === false) {
        $message = "<div style='color:#ef4444; background:#1e293b; border:1px solid #ef4444; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>❌ Error approving account!</div>";
    } else {
        header("Location: loyalty.php?status=approved");
        exit();
    }
}
?>

<div class="container">
    <div class="section-title" style="margin-top: 20px;">
        <h2>Loyalty & Rewards Hub</h2>
        <p>Manage VIP tiers, approve accounts, and track discount records</p>
    </div>

    <?php 
    if (isset($_GET['status']) && $_GET['status'] == 'approved') {
        echo "<div style='color:#10b981; background:#1e293b; border:1px solid #10b981; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>✅ VIP Account Approved by Manager!</div>";
    }
    echo $message; 
    ?>

    <div class="card" style="border-left: 4px solid #f59e0b;">
        <h3 style="color: #f59e0b;">Pending Approvals (Requires Manager Sign-off)</h3>
        <table style="width:100%; text-align:left;">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Auto-Calculated Points</th>
                    <th>Base Tier</th>
                    <th>Manager Approval</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn)) {
                    // Fetch accounts where StaffApprovedBy is NULL
                    $pSql = "SELECT l.LoyaltyID, c.Name, l.TotalPoints, l.Tier 
                             FROM LOYALTY_ACCOUNT l
                             JOIN CUSTOMER c ON l.CustomerID = c.CustomerID
                             WHERE l.StaffApprovedBy IS NULL";
                             
                    $pStmt = sqlsrv_query($conn, $pSql);

                    if ($pStmt !== false && sqlsrv_has_rows($pStmt)) {
                        while ($row = sqlsrv_fetch_array($pStmt, SQLSRV_FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td><strong>" . htmlspecialchars($row['TotalPoints']) . " pts</strong></td>";
                            echo "<td>" . htmlspecialchars($row['Tier']) . "</td>";
                            echo "<td>
                                    <form method='POST' action='loyalty.php' style='display:flex; gap:10px;'>
                                        <input type='hidden' name='loyaltyId' value='".$row['LoyaltyID']."'>
                                        <select name='managerId' required style='padding:5px; border-radius:4px; border:none; background:#0b1120; color:white;'>
                                            <option value='' disabled selected>-- Select Manager --</option>";
                                            
                                            // Fetch only Managers to sign off
                                            $mSql = "SELECT StaffID, Name FROM STAFF WHERE Role = 'Manager'";
                                            $mStmt = sqlsrv_query($conn, $mSql);
                                            while ($mRow = sqlsrv_fetch_array($mStmt, SQLSRV_FETCH_ASSOC)) {
                                                echo "<option value='".$mRow['StaffID']."'>".$mRow['Name']."</option>";
                                            }
                                            
                            echo "      </select>
                                        <button type='submit' name='approve_loyalty' style='background:#f59e0b; color:black; border:none; padding:5px 15px; border-radius:4px; cursor:pointer; font-weight:bold;'>Sign Off</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; color:#94a3b8;'>No pending accounts waiting for approval.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top: 20px; border-left: 4px solid #10b981;">
        <h3 style="color: #10b981;">Official VIP Directory</h3>
        <table style="width:100%; text-align:left;">
            <thead>
                <tr>
                    <th>VIP ID</th>
                    <th>Customer Name</th>
                    <th>Points Available</th>
                    <th>Current Tier</th>
                    <th>Discount Rate</th>
                    <th>Approved By</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn)) {
                    // Fetch accounts where StaffApprovedBy is NOT NULL
                    $aSql = "SELECT l.LoyaltyID, c.Name, l.TotalPoints, l.Tier, l.DiscountRate, s.Name AS ManagerName
                             FROM LOYALTY_ACCOUNT l
                             JOIN CUSTOMER c ON l.CustomerID = c.CustomerID
                             JOIN STAFF s ON l.StaffApprovedBy = s.StaffID
                             ORDER BY l.TotalPoints DESC";
                             
                    $aStmt = sqlsrv_query($conn, $aSql);

                    if ($aStmt !== false && sqlsrv_has_rows($aStmt)) {
                        while ($row = sqlsrv_fetch_array($aStmt, SQLSRV_FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>#" . htmlspecialchars($row['LoyaltyID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td style='color:#3498db; font-weight:bold;'>" . htmlspecialchars($row['TotalPoints']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Tier']) . "</td>";
                            echo "<td>" . (htmlspecialchars($row['DiscountRate']) * 100) . "%</td>";
                            echo "<td><span style='color:#94a3b8; font-size:12px;'>Mgr. " . htmlspecialchars($row['ManagerName']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; color:#94a3b8;'>No approved VIP accounts yet.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>