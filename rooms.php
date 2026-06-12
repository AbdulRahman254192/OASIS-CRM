<?php
include 'db.php';
include 'header.php';
$message = "";

// 1. Logic to Add a Room Type
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_type'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO ROOM_TYPE (TypeName, Description, Amenities, BasePrice) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssd", $_POST['typeName'], $_POST['description'], $_POST['amenities'], $_POST['basePrice']);
    
    if (mysqli_stmt_execute($stmt)) { 
        $message = "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Room Type added successfully!</div>"; 
    } else { 
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Error: " . mysqli_error($conn) . "</div>"; 
    }
    mysqli_stmt_close($stmt);
}

// 2. Logic to Add a Specific Room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO ROOM (RoomNumber, Floor, TypeID, Status, PricePerNight, Capacity) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "siisdi", $_POST['roomNumber'], $_POST['floor'], $_POST['typeId'], $_POST['status'], $_POST['price'], $_POST['capacity']);
    
    if (mysqli_stmt_execute($stmt)) { 
        $message = "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Room added successfully!</div>"; 
    } else { 
        $message = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Error: " . mysqli_error($conn) . "</div>"; 
    }
    mysqli_stmt_close($stmt);
}
?>

<header class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-brand-orange">Room Management</h1>
    <p class="text-sm text-gray-400">Manage categories and room inventory</p>
</header>

<?php echo $message; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <section class="rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
        <h3 class="mb-4 font-bold text-brand-orange border-b border-navy-700 pb-2">Step 1: Add Room Category</h3>
        <form method="POST" action="rooms.php" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="typeName" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Name (e.g. Deluxe Suite)" required>
                <input type="text" name="description" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Description" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="amenities" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Amenities" required>
                <input type="number" name="basePrice" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" placeholder="Base Price" required>
            </div>
            <button type="submit" name="add_type" class="w-full rounded-md bg-brand-blue px-4 py-2 font-bold text-white hover:bg-blue-500 transition">Save Category</button>
        </form>
    </section>

    <section class="rounded-lg border border-navy-700 bg-navy-900 p-6 shadow-sm">
        <h3 class="mb-4 font-bold text-brand-orange border-b border-navy-700 pb-2">Step 2: Add Specific Room</h3>
        <form method="POST" action="rooms.php" class="space-y-4">
            <div class="grid grid-cols-3 gap-2">
                <input type="text" name="roomNumber" placeholder="Number" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
                <input type="number" name="floor" placeholder="Floor" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
                <input type="number" name="typeId" placeholder="TypeID" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <select name="status" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white">
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
                <input type="number" name="price" placeholder="Price" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
                <input type="number" name="capacity" placeholder="Capacity" class="rounded-md border border-navy-700 bg-navy-800 p-2 text-white" required>
            </div>
            <button type="submit" name="add_room" class="w-full rounded-md bg-brand-orange px-4 py-2 font-bold text-navy-900 hover:bg-yellow-500 transition">Save Room</button>
        </form>
    </section>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <section class="lg:col-span-1 rounded-lg border border-navy-700 bg-navy-900 overflow-hidden shadow-sm">
        <div class="bg-navy-800 p-4 border-b border-navy-700 font-bold text-brand-orange">Room Categories</div>
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-navy-900 text-xs uppercase">
                <tr><th class="p-3">ID</th><th class="p-3">Name</th><th class="p-3">Price</th></tr>
            </thead>
            <tbody class="divide-y divide-navy-700">
                <?php
                $res = mysqli_query($conn, "SELECT TypeID, TypeName, BasePrice FROM ROOM_TYPE");
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<tr><td class='p-3'>{$row['TypeID']}</td><td class='p-3'>{$row['TypeName']}</td><td class='p-3'>\${$row['BasePrice']}</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <section class="lg:col-span-2 rounded-lg border border-navy-700 bg-navy-900 overflow-hidden shadow-sm">
        <div class="bg-navy-800 p-4 border-b border-navy-700 font-bold text-brand-orange">Current Room Inventory</div>
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-navy-900 text-xs uppercase">
                <tr><th class="p-3">ID</th><th class="p-3">Number</th><th class="p-3">Floor</th><th class="p-3">Type</th><th class="p-3">Status</th><th class="p-3">Price</th></tr>
            </thead>
            <tbody class="divide-y divide-navy-700">
                <?php
                $sql = "SELECT * FROM ROOM ORDER BY RoomNumber ASC";
                $res = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<tr class='hover:bg-navy-800'>
                            <td class='p-3'>{$row['RoomID']}</td>
                            <td class='p-3 font-bold text-white'>{$row['RoomNumber']}</td>
                            <td class='p-3'>{$row['Floor']}</td>
                            <td class='p-3'>{$row['TypeID']}</td>
                            <td class='p-3'>{$row['Status']}</td>
                            <td class='p-3'>\${$row['PricePerNight']}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </section>
</div>

<?php include 'footer.php'; ?>