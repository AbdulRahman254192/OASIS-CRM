<?php
include 'db.php';

// --- THE LOYALTY ENGINE ---
function calculateLoyaltyTier($points) {
    if ($points >= 300) {
        return array('Tier' => 'Gold', 'Discount' => 0.15);
    } elseif ($points >= 100) {
        return array('Tier' => 'Silver', 'Discount' => 0.10);
    } else {
        return array('Tier' => 'Bronze', 'Discount' => 0.05);
    }
}

$message = "";

// --- LOGIC: MANAGER APPROVES LOYALTY ACCOUNT ---
// NOTE: This must happen BEFORE header.php is included so the redirect works!
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_loyalty'])) {
    $loyaltyId = $_POST['loyaltyId'];
    $managerId = $_POST['managerId'];

    // 1. Fetch current points (MySQLi Prepared Statement)
    $ptSql = "SELECT TotalPoints FROM LOYALTY_ACCOUNT WHERE LoyaltyID = ?";
    $ptStmt = mysqli_prepare($conn, $ptSql);
    mysqli_stmt_bind_param($ptStmt, "i", $loyaltyId);
    mysqli_stmt_execute($ptStmt);
    $ptResult = mysqli_stmt_get_result($ptStmt);
    
    $points = 0;
    if ($ptRow = mysqli_fetch_assoc($ptResult)) {
        $points = $ptRow['TotalPoints'];
    }
    mysqli_stmt_close($ptStmt);

    // 2. Run the points through our logic engine
    $tierData = calculateLoyaltyTier($points);
    $calculated_tier = $tierData['Tier'];
    $calculated_discount = $tierData['Discount'];

    // 3. Update the account (MySQLi Prepared Statement)
    $sql = "UPDATE LOYALTY_ACCOUNT SET StaffApprovedBy = ?, Tier = ?, DiscountRate = ? WHERE LoyaltyID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    // "isdi" means: Int (ManagerID), String (Tier), Double (DiscountRate), Int (LoyaltyID)
    mysqli_stmt_bind_param($stmt, "isdi", $managerId, $calculated_tier, $calculated_discount, $loyaltyId);

    if (!mysqli_stmt_execute($stmt)) {
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Error approving account: " . mysqli_error($conn) . "</div>";
    } else {
        // Since we haven't loaded HTML yet, this redirect will work perfectly!
        header("Location: loyalty.php?status=approved");
        exit();
    }
    mysqli_stmt_close($stmt);
}

// NOW we can load the visual UI
include 'header.php';
?>

<header class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-brand-orange">Loyalty & Rewards Hub</h1>
    <p class="text-sm text-gray-400">Manage VIP tiers, approve accounts, and track discount records</p>
</header>

<?php 
if (isset($_GET['status']) && $_GET['status'] == 'approved') {
    echo "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ VIP Account Approved & Tier Calculated!</div>";
}
echo $message; 
?>

<section class="mb-8 rounded-lg border-l-4 border-l-brand-orange border-y border-r border-y-navy-700 border-r-navy-700 bg-navy-900 shadow-sm overflow-hidden">
    <div class="border-b border-navy-700 px-6 py-4">
        <h3 class="text-lg font-bold text-brand-orange">Pending Approvals (Requires Manager Sign-off)</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-navy-800 text-gray-400 text-sm font-bold tracking-wide">
                <tr>
                    <th class="px-6 py-3">Customer Name</th>
                    <th class="px-6 py-3">Auto-Calculated Points</th>
                    <th class="px-6 py-3">Base Tier</th>
                    <th class="px-6 py-3">Manager Approval</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-700 text-sm bg-navy-900">
                <?php
                if(isset($conn)) {
                    $pSql = "SELECT l.LoyaltyID, c.Name, l.TotalPoints, l.Tier 
                             FROM LOYALTY_ACCOUNT l
                             JOIN CUSTOMER c ON l.CustomerID = c.CustomerID
                             WHERE l.StaffApprovedBy IS NULL";
                             
                    $pResult = mysqli_query($conn, $pSql);

                    if ($pResult && mysqli_num_rows($pResult) > 0) {
                        while ($row = mysqli_fetch_assoc($pResult)) {
                            echo "<tr class='hover:bg-navy-800 transition'>";
                            echo "<td class='whitespace-nowrap px-6 py-4 font-bold text-white'>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-brand-blue font-bold'>" . htmlspecialchars($row['TotalPoints']) . " pts</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . htmlspecialchars($row['Tier']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4'>
                                    <form method='POST' action='loyalty.php' class='flex items-center gap-3'>
                                        <input type='hidden' name='loyaltyId' value='".$row['LoyaltyID']."'>
                                        <select name='managerId' class='rounded-md border border-navy-700 bg-navy-800 px-3 py-1.5 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange' required>
                                            <option value='' disabled selected>-- Select Manager --</option>";
                                            
                                            // Fetch only Managers
                                            $mSql = "SELECT StaffID, Name FROM STAFF WHERE Role = 'Manager'";
                                            $mResult = mysqli_query($conn, $mSql);
                                            if ($mResult) {
                                                while ($mRow = mysqli_fetch_assoc($mResult)) {
                                                    echo "<option value='".$mRow['StaffID']."'>".$mRow['Name']."</option>";
                                                }
                                            }
                                            
                            echo "      </select>
                                        <button type='submit' name='approve_loyalty' class='rounded-md bg-brand-orange px-4 py-1.5 text-sm font-bold text-navy-900 shadow-sm transition hover:bg-yellow-500'>Sign Off</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='px-6 py-10 text-center text-sm text-gray-500 font-medium'>No pending accounts waiting for approval.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<section class="rounded-lg border-l-4 border-l-emerald-500 border-y border-r border-y-navy-700 border-r-navy-700 bg-navy-900 shadow-sm overflow-hidden">
    <div class="border-b border-navy-700 px-6 py-4">
        <h3 class="text-lg font-bold text-emerald-500">Official VIP Directory</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-navy-800 text-gray-400 text-sm font-bold tracking-wide">
                <tr>
                    <th class="px-6 py-3">VIP ID</th>
                    <th class="px-6 py-3">Customer Name</th>
                    <th class="px-6 py-3">Points Available</th>
                    <th class="px-6 py-3">Current Tier</th>
                    <th class="px-6 py-3">Discount Rate</th>
                    <th class="px-6 py-3">Approved By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-700 text-sm bg-navy-900">
                <?php
                if(isset($conn)) {
                    $aSql = "SELECT l.LoyaltyID, c.Name, l.TotalPoints, l.Tier, l.DiscountRate, s.Name AS ManagerName
                             FROM LOYALTY_ACCOUNT l
                             JOIN CUSTOMER c ON l.CustomerID = c.CustomerID
                             JOIN STAFF s ON l.StaffApprovedBy = s.StaffID
                             ORDER BY l.TotalPoints DESC";
                             
                    $aResult = mysqli_query($conn, $aSql);

                    if ($aResult && mysqli_num_rows($aResult) > 0) {
                        while ($row = mysqli_fetch_assoc($aResult)) {
                            echo "<tr class='hover:bg-navy-800 transition'>";
                            echo "<td class='whitespace-nowrap px-6 py-4 font-bold text-white'>#" . htmlspecialchars($row['LoyaltyID']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-brand-blue font-bold'>" . htmlspecialchars($row['TotalPoints']) . " pts</td>";
                            
                            // Highlight Gold and Silver Members visually
                            if ($row['Tier'] == 'Gold') {
                                echo "<td class='whitespace-nowrap px-6 py-4 text-brand-orange font-bold'>⭐ " . htmlspecialchars($row['Tier']) . "</td>";
                            } elseif ($row['Tier'] == 'Silver') {
                                echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300 font-bold'>⚪ " . htmlspecialchars($row['Tier']) . "</td>";
                            } else {
                                echo "<td class='whitespace-nowrap px-6 py-4 text-amber-700 font-bold'>🟤 " . htmlspecialchars($row['Tier']) . "</td>";
                            }
                            
                            echo "<td class='whitespace-nowrap px-6 py-4 text-gray-300'>" . (htmlspecialchars($row['DiscountRate']) * 100) . "%</td>";
                            echo "<td class='whitespace-nowrap px-6 py-4 text-xs text-gray-500 uppercase tracking-wider'>Mgr. " . htmlspecialchars($row['ManagerName']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='px-6 py-10 text-center text-sm text-gray-500 font-medium'>No approved VIP accounts yet.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<?php include 'footer.php'; ?>