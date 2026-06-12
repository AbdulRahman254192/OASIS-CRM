<?php
session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
include_once 'pricing_engine.php';

if (!isset($_GET['id'])) {
    echo "<div class='p-10 text-center font-bold text-red-500'>No Invoice ID provided.</div>";
    exit();
}

$payment_id = $_GET['id'];

// --- MySQLi PREPARED STATEMENT ---
$sql = "SELECT p.PaymentID, p.PaymentDate, p.PaymentMethod, p.Amount, 
               b.BookingID, b.CheckInDate, b.CheckOutDate, 
               c.CustomerID, c.Name, c.Phone, c.Email, 
               r.RoomID, r.RoomNumber
        FROM PAYMENT p
        JOIN BOOKING b ON p.BookingID = b.BookingID
        JOIN CUSTOMER c ON b.CustomerID = c.CustomerID
        JOIN ROOM r ON b.RoomID = r.RoomID
        WHERE p.PaymentID = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $payment_id); // "i" = integer
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "<div class='p-10 text-center font-bold text-red-500'>Invoice not found.</div>";
    exit();
}

// --- MYSQL DATE FORMATTING ---
// MySQL returns strings, so we wrap them in strtotime() before formatting
$check_in = date('Y-m-d', strtotime($row['CheckInDate']));
$check_out = date('Y-m-d', strtotime($row['CheckOutDate']));
$payment_date = date('F j, Y', strtotime($row['PaymentDate']));

// Run the pricing engine to get the exact breakdown
$priceData = calculateFinalPrice($conn, $row['CustomerID'], $row['RoomID'], $check_in, $check_out);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo htmlspecialchars($row['PaymentID']); ?> - OASIS HMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 900: '#0b1120', 800: '#131c31', 700: '#1e293b' },
                        brand: { orange: '#f59e0b', blue: '#3ea0e5', red: '#ef4444' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-navy-800 text-white font-sans antialiased min-h-screen flex items-center justify-center p-4 print:bg-white print:p-0">

    <div class="w-full max-w-3xl rounded-xl border border-navy-700 bg-navy-900 p-8 shadow-2xl print:border-none print:bg-white print:shadow-none print:p-0">
        
        <div class="mb-8 flex items-start justify-between border-b border-navy-700 pb-6 print:border-gray-300">
            <div class="text-3xl font-black text-brand-orange print:text-black">OASIS HMS</div>
            <div class="text-right">
                <h1 class="text-2xl font-bold tracking-widest text-white uppercase print:text-black">Guest Invoice</h1>
                <p class="text-sm text-gray-400 print:text-gray-600">Receipt #: <?php echo htmlspecialchars($row['PaymentID']); ?></p>
                <p class="text-sm text-gray-400 print:text-gray-600">Date: <?php echo $payment_date; ?></p>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-8 sm:grid-cols-2">
            <div>
                <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-brand-orange print:text-gray-500">Billed To:</h3>
                <p class="font-bold text-white print:text-black"><?php echo htmlspecialchars($row['Name']); ?></p>
                <p class="text-gray-400 print:text-gray-700">Phone: <?php echo htmlspecialchars($row['Phone']); ?></p>
                <p class="text-gray-400 print:text-gray-700">Email: <?php echo htmlspecialchars($row['Email']); ?></p>
            </div>
            <div>
                <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-brand-orange print:text-gray-500">Stay Details:</h3>
                <p class="text-gray-400 print:text-gray-700">Booking Ref: <span class="text-white print:text-black">#<?php echo htmlspecialchars($row['BookingID']); ?></span></p>
                <p class="text-gray-400 print:text-gray-700">Room: <span class="text-white print:text-black"><?php echo htmlspecialchars($row['RoomNumber']); ?></span></p>
                <p class="text-gray-400 print:text-gray-700">Check-In: <span class="text-white print:text-black"><?php echo $check_in; ?></span></p>
                <p class="text-gray-400 print:text-gray-700">Check-Out: <span class="text-white print:text-black"><?php echo $check_out; ?></span></p>
            </div>
        </div>

        <table class="w-full border-collapse mb-8">
            <thead>
                <tr>
                    <th class="bg-brand-orange/10 px-4 py-3 text-left text-sm font-bold uppercase tracking-wider text-brand-orange border-b-2 border-brand-orange/20 print:bg-gray-100 print:text-gray-600 print:border-gray-300">Description</th>
                    <th class="bg-brand-orange/10 px-4 py-3 text-right text-sm font-bold uppercase tracking-wider text-brand-orange border-b-2 border-brand-orange/20 print:bg-gray-100 print:text-gray-600 print:border-gray-300">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border-b border-navy-700 px-4 py-4 text-gray-300 print:border-gray-200 print:text-gray-800">
                        Base Room Rate (<?php echo htmlspecialchars($priceData['TotalDays']); ?> Nights @ $<?php echo number_format($priceData['BasePrice'], 2); ?>)
                    </td>
                    <td class="border-b border-navy-700 px-4 py-4 text-right text-gray-300 print:border-gray-200 print:text-gray-800">
                        $<?php echo number_format($priceData['BasePrice'] * $priceData['TotalDays'], 2); ?>
                    </td>
                </tr>
                <?php if($priceData['RawTotal'] > ($priceData['BasePrice'] * $priceData['TotalDays'])): ?>
                <tr>
                    <td class="border-b border-navy-700 px-4 py-4 text-gray-300 print:border-gray-200 print:text-gray-800">
                        Weekend Surge Pricing (Applied to Fri/Sat nights)
                    </td>
                    <td class="border-b border-navy-700 px-4 py-4 text-right text-gray-300 print:border-gray-200 print:text-gray-800">
                        $<?php echo number_format($priceData['RawTotal'] - ($priceData['BasePrice'] * $priceData['TotalDays']), 2); ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="ml-auto w-full max-w-xs space-y-3">
            <div class="flex justify-between text-gray-300 print:text-gray-800">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($priceData['RawTotal'], 2); ?></span>
            </div>
            
            <?php if($priceData['DiscountAmount'] > 0): ?>
            <div class="flex justify-between text-brand-orange print:text-gray-600 font-semibold">
                <span>Loyalty Discount (VIP):</span>
                <span>-$<?php echo number_format($priceData['DiscountAmount'], 2); ?></span>
            </div>
            <?php endif; ?>

            <div class="flex justify-between border-t-2 border-brand-orange/20 pt-4 text-xl font-bold text-emerald-500 print:border-gray-300 print:text-black">
                <span>Total Paid (<?php echo htmlspecialchars($row['PaymentMethod']); ?>):</span>
                <span>$<?php echo number_format($row['Amount'], 2); ?></span>
            </div>
        </div>

        <div class="mt-10 flex justify-center gap-4 print:hidden">
            <button onclick="window.print()" class="rounded-md bg-brand-blue px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-500">Print Receipt</button>
            <a href="payments.php" class="rounded-md bg-navy-700 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-navy-600">Return to Payments</a>
        </div>

    </div>
</body>
</html>