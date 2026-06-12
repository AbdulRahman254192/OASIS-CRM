<?php
include 'db.php';
$message = "";

// --- LOGIC 1: ADD PRICING RULE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_rule'])) {
    $ruleName = $_POST['ruleName'];
    $condition = $_POST['condition'];
    $multiplier = $_POST['multiplier'];
    $managerId = $_POST['managerId'];
    
    // MySQLi Prepared Statement
    $sql = "INSERT INTO PRICING_RULE (RuleName, TriggerCondition, SurgeMultiplier, ApprovedByManager, Status) VALUES (?, ?, ?, ?, 'Active')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssdi", $ruleName, $condition, $multiplier, $managerId);

    if (!mysqli_stmt_execute($stmt)) {
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Error creating pricing rule!</div>";
    } else {
        header("Location: smart_hub.php?status=rule_added");
        exit();
    }
    mysqli_stmt_close($stmt);
}

// --- LOGIC 2: SIMULATE & LOG AI ROOM SUGGESTION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['run_ai'])) {
    $customerId = $_POST['customerId'];
    $guests = $_POST['guests'];
    $budget = $_POST['budget'];
    $nights = $_POST['nights'];
    $staffId = $_POST['staffId']; 
    $suggestionDate = date('Y-m-d');
    
    $maxPerNight = $budget / $nights;
    
    // FETCH: Grab the RoomNumber so we can display it!
    $findRoomSql = "SELECT RoomID, RoomNumber FROM ROOM WHERE Status = 'Available' AND PricePerNight <= ? ORDER BY PricePerNight DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $findRoomSql);
    mysqli_stmt_bind_param($stmt, "d", $maxPerNight);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $suggestedRoomId = $row['RoomID'];
        $suggestedRoomNum = $row['RoomNumber']; // Grab the actual room number
        
        $logSql = "INSERT INTO ROOM_SUGGESTION_LOG (CustomerID, SuggestedRoomID, ConfirmedByStaff, Guests, PriceRange, SuggestionDate, Outcome) VALUES (?, ?, ?, ?, ?, ?, 'Suggested')";
        $logStmt = mysqli_prepare($conn, $logSql);
        mysqli_stmt_bind_param($logStmt, "iiidds", $customerId, $suggestedRoomId, $staffId, $guests, $budget, $suggestionDate);
        
        if (mysqli_stmt_execute($logStmt)) {
            // REDIRECT: Pass the room number into the URL!
            header("Location: smart_hub.php?status=ai_logged&room=" . $suggestedRoomNum);
            exit();
        } else {
            $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Error logging AI suggestion.</div>";
        }
        mysqli_stmt_close($logStmt);
    } else {
         $message = "<div class='mb-6 rounded-lg border border-amber-500 bg-amber-500/10 p-4 text-center font-bold text-amber-500'>⚠️ AI Engine Error: No available rooms fit that budget for " . htmlspecialchars($nights) . " nights! ($" . round($maxPerNight, 2) . "/night max)</div>";
    }
}

// NOW we load the header
include 'header.php';
?>

<header class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-brand-orange">Smart Hotel Engine</h1>
    <p class="text-sm text-gray-400">Manage dynamic pricing algorithms and AI room suggestions</p>
</header>

<?php 
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'rule_added') {
        echo "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Pricing Rule Activated Successfully!</div>";
    } elseif ($_GET['status'] == 'ai_logged' && isset($_GET['room'])) {
        // DISPLAY: Show the specific room number that matched!
        $rNum = htmlspecialchars($_GET['room']);
        echo "<div class='mb-6 rounded-lg border border-brand-orange bg-brand-orange/10 p-4 text-center font-bold text-brand-orange'>🤖 AI Match Generated! Best fit is Room " . $rNum . "</div>";
    } elseif ($_GET['status'] == 'ai_logged') {
        // Fallback just in case the room number is missing from URL
        echo "<div class='mb-6 rounded-lg border border-brand-orange bg-brand-orange/10 p-4 text-center font-bold text-brand-orange'>🤖 AI Room Match Generated & Logged!</div>";
    }
}
echo $message; 
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <section class="col-span-1 rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
        <h3 class="mb-4 font-bold text-brand-orange border-b border-navy-700 pb-2">Create Dynamic Pricing</h3>
        <form method="POST" action="smart_hub.php" class="space-y-4">
            <input type="text" name="ruleName" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Rule Name" required>
            <input type="text" name="condition" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Condition (e.g. Day = Sat)" required>
            <input type="number" step="0.01" name="multiplier" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Multiplier (1.20)" required>
            <select name="managerId" class="w-full rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
                <option value="" disabled selected>-- Approved By --</option>
                <?php
                $mRes = mysqli_query($conn, "SELECT StaffID, Name FROM STAFF WHERE Role = 'Manager'");
                while ($m = mysqli_fetch_assoc($mRes)) echo "<option value='{$m['StaffID']}'>{$m['Name']}</option>";
                ?>
            </select>
            <button type="submit" name="add_rule" class="w-full bg-brand-orange text-navy-900 font-bold py-2 rounded">Activate Rule</button>
        </form>
    </section>

    <section class="col-span-2 rounded-lg border border-navy-700 bg-navy-900 overflow-hidden shadow-sm">
        <div class="bg-navy-800 p-4 border-b border-navy-700 font-bold text-brand-orange">Active Pricing Rules</div>
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-navy-900 text-xs uppercase">
                <tr><th class="p-3">Rule</th><th class="p-3">Condition</th><th class="p-3">Multiplier</th><th class="p-3">Status</th></tr>
            </thead>
            <tbody class="divide-y divide-navy-700">
                <?php
                $rRes = mysqli_query($conn, "SELECT RuleName, TriggerCondition, SurgeMultiplier, Status FROM PRICING_RULE ORDER BY RuleID DESC");
                while ($row = mysqli_fetch_assoc($rRes)) {
                    echo "<tr class='hover:bg-navy-800'><td class='p-3 font-bold text-white'>{$row['RuleName']}</td><td class='p-3'>{$row['TriggerCondition']}</td><td class='p-3 text-red-500 font-bold'>x{$row['SurgeMultiplier']}</td><td class='p-3 text-emerald-500'>{$row['Status']}</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>
</div>

<section class="mt-8 rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
    <h3 class="mb-4 font-bold text-brand-orange border-b border-navy-700 pb-2">AI Room Matcher</h3>
    <form method="POST" action="smart_hub.php" class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <select name="customerId" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
            <option value="" disabled selected>Customer</option>
            <?php 
            $cRes = mysqli_query($conn, "SELECT CustomerID, Name FROM CUSTOMER");
            while($c = mysqli_fetch_assoc($cRes)) echo "<option value='{$c['CustomerID']}'>{$c['Name']}</option>";
            ?>
        </select>
        
        <select name="staffId" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
            <option value="" disabled selected>Staff</option>
            <?php 
            // Only selects staff who have the role of Manager or Receptionist
            $sRes = mysqli_query($conn, "SELECT StaffID, Name FROM STAFF WHERE Role IN ('Manager', 'Receptionist')");
            while($s = mysqli_fetch_assoc($sRes)) echo "<option value='{$s['StaffID']}'>{$s['Name']}</option>";
            ?>
        </select>
        
        <input type="number" name="guests" placeholder="Guests" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
        <input type="number" name="nights" placeholder="Nights" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
        <input type="number" name="budget" placeholder="Budget" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
        <button type="submit" name="run_ai" class="bg-brand-blue text-white font-bold py-2 rounded">Match</button>
    </form>
</section>

<?php include 'footer.php'; ?>