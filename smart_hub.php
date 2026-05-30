<?php
include 'db.php';
include 'header.php';
$message = "";

// --- LOGIC: ADD PRICING RULE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_rule'])) {
    $ruleName = $_POST['ruleName'];
    $condition = $_POST['condition'];
    $multiplier = $_POST['multiplier'];
    $managerId = $_POST['managerId'];
    
    $sql = "INSERT INTO PRICING_RULE (RuleName, TriggerCondition, SurgeMultiplier, ApprovedByManager, Status) 
            VALUES (?, ?, ?, ?, 'Active')";
    $stmt = sqlsrv_query($conn, $sql, array($ruleName, $condition, $multiplier, $managerId));

    if ($stmt === false) {
        $message = "<div style='color:#ef4444; background:#1e293b; border:1px solid #ef4444; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>❌ Error creating pricing rule!</div>";
    } else {
        header("Location: smart_hub.php?status=rule_added");
        exit();
    }
}
?>

<div class="container">
    <div class="section-title" style="margin-top: 20px;">
        <h2>Smart Hotel Engine</h2>
        <p>Manage dynamic pricing algorithms and AI room suggestions</p>
    </div>

    <?php 
    if (isset($_GET['status']) && $_GET['status'] == 'rule_added') {
        echo "<div style='color:#10b981; background:#1e293b; border:1px solid #10b981; padding:15px; border-radius:6px; text-align:center; margin-bottom:20px; font-weight:bold;'>✅ Pricing Rule Activated Successfully!</div>";
    }
    echo $message; 
    ?>

    <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start;">
        
        <div class="card" style="flex: 1; min-width: 300px; border-left: 4px solid #9b59b6;">
            <h3 style="color: #9b59b6;">Create Dynamic Pricing Rule</h3>
            <form method="POST" action="smart_hub.php">
                <div class="form-group">
                    <input type="text" name="ruleName" class="form-control" placeholder="Rule Name (e.g., Weekend Surge)" required>
                </div>
                <div class="form-group">
                    <input type="text" name="condition" class="form-control" placeholder="Trigger Condition (e.g., Day = Saturday)" required>
                </div>
                <div class="form-group">
                    <input type="number" name="multiplier" class="form-control" placeholder="Surge Multiplier (e.g., 1.20 for 20% increase)" step="0.01" required>
                </div>
                <div class="form-group">
                    <select name="managerId" class="form-control" required>
                        <option value="" disabled selected>-- Approved By (Manager) --</option>
                        <?php
                        if(isset($conn)) {
                            $mSql = "SELECT StaffID, Name FROM STAFF WHERE Role = 'Manager'";
                            $mStmt = sqlsrv_query($conn, $mSql);
                            if ($mStmt !== false) {
                                while ($mRow = sqlsrv_fetch_array($mStmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<option value='".$mRow['StaffID']."'>".$mRow['Name']."</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="add_rule" class="btn-primary" style="background:#9b59b6; color:white; width: 100%;">Activate Rule</button>
            </form>
        </div>

        <div class="card" style="flex: 2; min-width: 400px;">
            <h3>Active Pricing Rules</h3>
            <table style="width:100%; text-align:left;">
                <thead>
                    <tr>
                        <th>Rule Name</th>
                        <th>Condition</th>
                        <th>Multiplier</th>
                        <th>Approved By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(isset($conn)) {
                        $rSql = "SELECT p.RuleName, p.TriggerCondition, p.SurgeMultiplier, p.Status, s.Name AS ManagerName 
                                 FROM PRICING_RULE p
                                 JOIN STAFF s ON p.ApprovedByManager = s.StaffID
                                 ORDER BY p.RuleID DESC";
                        $rStmt = sqlsrv_query($conn, $rSql);

                        if ($rStmt !== false && sqlsrv_has_rows($rStmt)) {
                            while ($row = sqlsrv_fetch_array($rStmt, SQLSRV_FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($row['RuleName']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row['TriggerCondition']) . "</td>";
                                echo "<td style='color:#ef4444; font-weight:bold;'>x" . htmlspecialchars($row['SurgeMultiplier']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ManagerName']) . "</td>";
                                echo "<td style='color:#10b981; font-weight:bold;'>" . htmlspecialchars($row['Status']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; color:#94a3b8;'>No pricing rules active. Base prices apply.</td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3>AI Room Suggestion Log</h3>
        <table style="width:100%; text-align:left;">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Suggested Room</th>
                    <th>Guests / Price Range</th>
                    <th>Staff Confirmed</th>
                    <th>Outcome</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn)) {
                    $sSql = "SELECT l.SuggestionID, l.SuggestionDate, c.Name AS CustomerName, r.RoomNumber, 
                                    l.Guests, l.PriceRange, l.Outcome, s.Name AS StaffName
                             FROM ROOM_SUGGESTION_LOG l
                             JOIN CUSTOMER c ON l.CustomerID = c.CustomerID
                             JOIN ROOM r ON l.SuggestedRoomID = r.RoomID
                             JOIN STAFF s ON l.ConfirmedByStaff = s.StaffID
                             ORDER BY l.SuggestionID DESC";
                    $sStmt = sqlsrv_query($conn, $sSql);

                    if ($sStmt !== false && sqlsrv_has_rows($sStmt)) {
                        while ($row = sqlsrv_fetch_array($sStmt, SQLSRV_FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>#" . htmlspecialchars($row['SuggestionID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['SuggestionDate']->format('Y-m-d')) . "</td>";
                            echo "<td>" . htmlspecialchars($row['CustomerName']) . "</td>";
                            echo "<td>Room " . htmlspecialchars($row['RoomNumber']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Guests']) . " Guests / $" . htmlspecialchars($row['PriceRange']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['StaffName']) . "</td>";
                            
                            $outcomeColor = ($row['Outcome'] == 'Booked') ? '#10b981' : '#f59e0b';
                            echo "<td style='color:".$outcomeColor."; font-weight:bold;'>" . htmlspecialchars($row['Outcome']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; color:#94a3b8;'>No AI suggestions logged yet.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>