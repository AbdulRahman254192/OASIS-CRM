<?php
// ==========================================
// OASIS HMS - DYNAMIC PRICING & LOYALTY ENGINE
// ==========================================

function calculateFinalPrice($conn, $customer_id, $room_id, $check_in, $check_out) {
    // 1. Get Base Room Price (MySQLi Prepared Statement)
    $room_sql = "SELECT PricePerNight FROM ROOM WHERE RoomID = ?";
    $stmt1 = mysqli_prepare($conn, $room_sql);
    mysqli_stmt_bind_param($stmt1, "i", $room_id);
    mysqli_stmt_execute($stmt1);
    $result1 = mysqli_stmt_get_result($stmt1);
    
    $base_price = 0;
    if ($row = mysqli_fetch_assoc($result1)) {
        $base_price = $row['PricePerNight'];
    }
    mysqli_stmt_close($stmt1);

    // 2. Calculate Total Days and check for Weekends
    $start = new DateTime($check_in);
    $end = new DateTime($check_out);
    $interval = $start->diff($end);
    $total_days = $interval->days;
    
    if ($total_days == 0) $total_days = 1; // Minimum 1 night stay

    $raw_total = 0;
    $current_date = clone $start;

    // Loop through each day of the stay
    for ($i = 0; $i < $total_days; $i++) {
        $day_of_week = $current_date->format('N'); // 1 = Monday, 7 = Sunday
        
        // SURGE PRICING: If it's Friday (5) or Saturday (6), add 20%
        if ($day_of_week == 5 || $day_of_week == 6) {
            $raw_total += ($base_price * 1.20); 
        } else {
            $raw_total += $base_price;
        }
        $current_date->modify('+1 day');
    }

    // 3. LOYALTY CHECK: How many past bookings does this customer have?
    $loyalty_sql = "SELECT COUNT(*) as PastStays FROM BOOKING WHERE CustomerID = ? AND Status = 'Completed'";
    $stmt2 = mysqli_prepare($conn, $loyalty_sql);
    mysqli_stmt_bind_param($stmt2, "i", $customer_id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    
    $past_stays = 0;
    if ($row = mysqli_fetch_assoc($result2)) {
        $past_stays = $row['PastStays'];
    }
    mysqli_stmt_close($stmt2);

    $discount_applied = 0;
    $final_total = $raw_total;

    // If they have 3 or more past stays, apply 10% Gold Member discount
    if ($past_stays >= 3) {
        $discount_applied = $raw_total * 0.10;
        $final_total = $raw_total - $discount_applied;
    }

    // 4. Return the data as an array
    return array(
        'BasePrice' => $base_price,
        'TotalDays' => $total_days,
        'RawTotal' => $raw_total,
        'PastStays' => $past_stays,
        'DiscountAmount' => $discount_applied,
        'FinalTotal' => $final_total
    );
}
?>