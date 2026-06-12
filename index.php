<?php
include 'db.php';
include 'header.php'; 

$notification = "";

// --- 1. HANDLE CUSTOMER REGISTRATION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO CUSTOMER (Name, Phone, Email, CNIC) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $_POST['cName'], $_POST['cPhone'], $_POST['cEmail'], $_POST['cCNIC']);
    
    if (mysqli_stmt_execute($stmt)) {
        $notification = "<div class='mb-6 rounded-lg border border-emerald-500 bg-emerald-500/10 p-4 text-center font-bold text-emerald-500'>✅ Customer Registered Successfully!</div>";
    } else {
        $notification = "<div class='mb-6 rounded-lg border border-red-500 bg-red-500/10 p-4 text-center font-bold text-red-500'>❌ Error: " . mysqli_error($conn) . "</div>";
    }
    mysqli_stmt_close($stmt);
}

// --- 2. FETCH ALL BOOKINGS FOR THE DASHBOARD ---
$bookingsArray = array();

if(isset($conn)) {
    $sql = "SELECT b.BookingID, c.Name, c.Email, r.RoomNumber, b.CheckInDate, b.CheckOutDate, b.TotalAmount, b.Status 
            FROM BOOKING b 
            JOIN CUSTOMER c ON b.CustomerID = c.CustomerID 
            JOIN ROOM r ON b.RoomID = r.RoomID 
            ORDER BY b.BookingID DESC";
            
    $result = mysqli_query($conn, $sql); 
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) { 
            $checkIn = $row['CheckInDate'] ?: 'N/A';
            $checkOut = $row['CheckOutDate'] ?: 'N/A';
            
            $bookingsArray[] = array(
                'id' => $row['BookingID'],
                'customer_name' => $row['Name'],
                'email' => $row['Email'] ?: 'No Email Provided',
                'room_details' => 'Room ' . $row['RoomNumber'],
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'amount' => $row['TotalAmount'] ?: 0,
                'status' => $row['Status']
            );
        }
    }
}
?>

<header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-brand-orange">System Overview</h1>
        <p class="text-sm text-gray-400">Manage real-time bookings and room statuses seamlessly.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <div class="relative hidden sm:block w-64">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.604 10.604z" />
                </svg>
            </span>
            <input type="text" id="searchInput" class="w-full rounded-md bg-navy-900 border border-navy-700 py-2 pl-9 pr-4 text-sm text-white outline-none transition focus:border-brand-orange focus:ring-1 focus:ring-brand-orange placeholder-gray-500" placeholder="Search guests or rooms...">
        </div>
        
        <button id="openCustomerModalBtn" class="whitespace-nowrap rounded-md border border-brand-orange bg-navy-800 px-4 py-2 text-sm font-bold text-brand-orange hover:bg-navy-700 transition shadow-sm">
            + Register Customer
        </button>

        <button id="openModalBtn" class="whitespace-nowrap rounded-md bg-brand-orange px-4 py-2 text-sm font-bold text-navy-900 hover:bg-yellow-500 transition shadow-sm">
            + New Booking
        </button>
    </div>
</header>

<?php echo $notification; ?>

<section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-navy-700 bg-navy-900 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Bookings</p>
        <h3 id="statTotalBookings" class="mt-2 text-3xl font-bold text-brand-orange">0</h3>
    </div>
    <div class="rounded-lg border border-navy-700 bg-navy-900 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Active Stays</p>
        <h3 id="statActiveStays" class="mt-2 text-3xl font-bold text-brand-orange">0</h3>
    </div>
    <div class="rounded-lg border border-navy-700 bg-navy-900 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Revenue</p>
        <h3 id="statTotalRevenue" class="mt-2 text-3xl font-bold text-brand-orange">Rs 0</h3>
    </div>
    <div class="rounded-lg border border-navy-700 bg-navy-900 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Completed Stays</p>
        <h3 id="statCompleted" class="mt-2 text-3xl font-bold text-brand-orange">0</h3>
    </div>
</section>

<section class="rounded-lg border border-navy-700 bg-navy-900 shadow-sm overflow-hidden">
    <div class="border-b border-navy-700 px-6 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="font-bold text-brand-orange">Recent Transactions</h2>
        <div id="filterTabs" class="inline-flex rounded-lg bg-navy-800 p-1 border border-navy-700">
            <button data-filter="All" class="rounded bg-navy-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition">All Time</button>
            <button data-filter="Confirmed" class="rounded px-3 py-1.5 text-xs font-semibold text-gray-400 hover:text-white transition">Confirmed</button>
            <button data-filter="Active" class="rounded px-3 py-1.5 text-xs font-semibold text-gray-400 hover:text-white transition">Active</button>
            <button data-filter="Completed" class="rounded px-3 py-1.5 text-xs font-semibold text-gray-400 hover:text-white transition">Completed</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-brand-orange text-navy-900 text-sm font-bold tracking-wide">
                <tr>
                    <th class="px-6 py-3">Guest Details</th>
                    <th class="px-6 py-3">Room Information</th>
                    <th class="px-6 py-3">Stay Timeline</th>
                    <th class="px-6 py-3">Amount Charged</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody id="bookingsTableBody" class="divide-y divide-navy-700 text-sm bg-navy-800">
            </tbody>
        </table>
    </div>
</section>

<div id="bookingModal" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-lg bg-navy-800 border border-navy-700 text-left shadow-2xl transition-all sm:my-8">
                <div class="border-b border-navy-700 bg-navy-900 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-brand-orange">Add New Booking Reservation</h3>
                    <button id="closeModalCross" class="text-gray-400 hover:text-white transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="bookings.php" method="GET" class="px-6 py-4 space-y-4">
                    <div class="text-sm text-gray-400 mb-4 bg-navy-900 p-3 rounded border border-navy-700">
                        <span class="font-bold text-brand-orange">Note:</span> Please process reservations through the main Bookings engine.
                    </div>
                    <div class="border-t border-navy-700 pt-4 flex items-center justify-end gap-3">
                        <button type="button" id="closeModalBtn" class="rounded px-4 py-2 text-sm font-semibold text-gray-400 hover:text-white transition">Cancel</button>
                        <button type="submit" class="rounded bg-brand-blue px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-500 transition">Go to Bookings Hub</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="customerModal" class="relative z-50 hidden" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-navy-800 border border-navy-700 rounded-lg p-6 shadow-2xl">
            <h3 class="text-brand-orange font-bold text-xl mb-4">Register New Customer</h3>
            <form method="POST" action="index.php" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Full Name</label>
                    <input type="text" name="cName" class="w-full bg-navy-900 border border-navy-700 p-2 text-sm rounded text-white outline-none focus:border-brand-orange" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Phone Number</label>
                    <input type="text" name="cPhone" class="w-full bg-navy-900 border border-navy-700 p-2 text-sm rounded text-white outline-none focus:border-brand-orange" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Email Address</label>
                    <input type="email" name="cEmail" class="w-full bg-navy-900 border border-navy-700 p-2 text-sm rounded text-white outline-none focus:border-brand-orange">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">CNIC Number</label>
                    <input type="text" name="cCNIC" class="w-full bg-navy-900 border border-navy-700 p-2 text-sm rounded text-white outline-none focus:border-brand-orange" required>
                </div>
                <div class="flex gap-3 pt-4 border-t border-navy-700">
                    <button type="button" id="closeCustomerModal" class="flex-1 bg-navy-700 p-2 rounded text-white font-bold hover:bg-navy-600 transition">Cancel</button>
                    <button type="submit" name="add_customer" class="flex-1 bg-brand-orange p-2 rounded text-navy-900 font-bold hover:bg-yellow-500 transition">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let bookingsData = <?php echo json_encode($bookingsArray); ?>;
        let activeFilter = 'All';
        let searchQuery = '';
        
        // Modal Selectors
        const openModalBtn = document.getElementById('openModalBtn');
        const bookingModal = document.getElementById('bookingModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const closeModalCross = document.getElementById('closeModalCross');
        
        // Customer Modal Selectors
        const customerModal = document.getElementById('customerModal');
        const openCustomerModalBtn = document.getElementById('openCustomerModalBtn');
        const closeCustomerModal = document.getElementById('closeCustomerModal');

        // Table & Dashboard Selectors
        const bookingsTableBody = document.getElementById('bookingsTableBody');
        const searchInput = document.getElementById('searchInput');
        const filterTabs = document.getElementById('filterTabs').querySelectorAll('button');
        const statTotalBookings = document.getElementById('statTotalBookings');
        const statActiveStays = document.getElementById('statActiveStays');
        const statTotalRevenue = document.getElementById('statTotalRevenue');
        const statCompleted = document.getElementById('statCompleted');

        // Modal Event Listeners
        if (openModalBtn) openModalBtn.addEventListener('click', () => bookingModal.classList.remove('hidden'));
        if (closeModalBtn) closeModalBtn.addEventListener('click', () => bookingModal.classList.add('hidden'));
        if (closeModalCross) closeModalCross.addEventListener('click', () => bookingModal.classList.add('hidden'));
        
        // Customer Modal Event Listeners
        if (openCustomerModalBtn) openCustomerModalBtn.addEventListener('click', () => customerModal.classList.remove('hidden'));
        if (closeCustomerModal) closeCustomerModal.addEventListener('click', () => customerModal.classList.add('hidden'));

        // Rendering Logic
        const renderDashboard = () => {
            const filteredData = bookingsData.filter(item => {
                const matchesFilter = (activeFilter === 'All' || item.status === activeFilter);
                const matchesSearch = item.customer_name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                      item.email.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                      item.room_details.toLowerCase().includes(searchQuery.toLowerCase());
                return matchesFilter && matchesSearch;
            });
            statTotalBookings.innerText = bookingsData.length;
            statActiveStays.innerText = bookingsData.filter(b => b.status === 'Active').length;
            statCompleted.innerText = bookingsData.filter(b => b.status === 'Completed').length;
            let totalRevenue = bookingsData.reduce((sum, b) => sum + parseFloat(b.amount), 0);
            statTotalRevenue.innerText = 'Rs ' + totalRevenue.toLocaleString();
            bookingsTableBody.innerHTML = '';
            
            if (filteredData.length === 0) {
                bookingsTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 font-medium">No reservations match specified queries.</td></tr>`;
                return;
            }
            
            filteredData.forEach(booking => {
                let badgeStyles = 'bg-gray-800 text-gray-300';
                if (booking.status === 'Confirmed') badgeStyles = 'bg-amber-900/50 text-amber-400 border border-amber-800/50';
                if (booking.status === 'Active') badgeStyles = 'bg-blue-900/50 text-blue-400 border border-blue-800/50';
                if (booking.status === 'Completed') badgeStyles = 'bg-emerald-900/50 text-emerald-400 border border-emerald-800/50';
                const row = `
                    <tr class="hover:bg-navy-700/50 transition">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="font-bold text-white">${booking.customer_name}</div>
                            <div class="text-xs text-gray-400">${booking.email}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-300">${booking.room_details}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-gray-300">${booking.check_in} to ${booking.check_out}</td>
                        <td class="whitespace-nowrap px-6 py-4 font-bold text-brand-orange">Rs ${parseFloat(booking.amount).toLocaleString()}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex items-center rounded px-2.5 py-1 text-xs font-semibold ${badgeStyles}">${booking.status}</span>
                        </td>
                    </tr>`;
                bookingsTableBody.insertAdjacentHTML('beforeend', row);
            });
        };
        
        if (searchInput) searchInput.addEventListener('input', (e) => { searchQuery = e.target.value; renderDashboard(); });
        
        filterTabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                filterTabs.forEach(t => t.className = 'rounded px-3 py-1.5 text-xs font-semibold text-gray-400 hover:text-white transition');
                e.target.className = 'rounded bg-navy-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition';
                activeFilter = e.target.getAttribute('data-filter');
                renderDashboard();
            });
        });
        renderDashboard();
    });
</script>

<?php include 'footer.php'; ?>