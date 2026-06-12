<?php
session_start();
include 'db.php';

$error = "";

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $staff_id = $_POST['staff_id'];
    $password = $_POST['password'];

    // --- MySQLi PREPARED STATEMENT ---
    $sql = "SELECT StaffID, Name FROM STAFF WHERE StaffID = ? AND Password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    // "is" means: Integer (StaffID), String (Password)
    mysqli_stmt_bind_param($stmt, "is", $staff_id, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // SUCCESS: Create a session (The "VIP Pass")
        $_SESSION['staff_id'] = $row['StaffID'];
        $_SESSION['staff_name'] = $row['Name'];
        
        // Redirect to Dashboard
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid Staff ID or Password.";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OASIS | Secure Login</title>
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
<body class="flex h-screen items-center justify-center bg-gradient-to-br from-navy-900 to-navy-800 p-4 font-sans text-white antialiased">

    <div class="w-full max-w-md rounded-2xl border border-navy-700 bg-navy-900 p-8 text-center shadow-2xl">
        <h2 class="mb-1 text-3xl font-black tracking-wider text-brand-orange">OASIS HMS</h2>
        <p class="mb-8 text-sm font-semibold tracking-widest text-gray-400 uppercase">Authorized Personnel Only</p>

        <?php if($error != ""): ?>
            <div class="mb-6 rounded-lg border border-red-500 bg-red-500/10 p-3 text-sm font-bold text-red-500">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5 text-left">
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase tracking-wider">Staff ID Number</label>
                <input type="number" name="staff_id" class="w-full rounded-lg border border-navy-700 bg-navy-800 px-4 py-3 text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange placeholder-gray-600" placeholder="e.g. 1" required>
            </div>
            
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase tracking-wider">Password</label>
                <input type="password" name="password" class="w-full rounded-lg border border-navy-700 bg-navy-800 px-4 py-3 text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange placeholder-gray-600" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="login" class="mt-4 w-full rounded-lg bg-brand-orange px-4 py-3 font-bold text-navy-900 shadow-lg transition hover:-translate-y-0.5 hover:bg-yellow-500 hover:shadow-brand-orange/20">
                Access Command Center
            </button>
        </form>
    </div>

</body>
</html>