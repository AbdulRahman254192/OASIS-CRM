<?php
// --- DATABASE CONNECTION & LOGIC ---
include 'db.php';

$message = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $cnic = $_POST['cnic'];
    $nationality = $_POST['nationality'];

    $insertSql = "INSERT INTO CUSTOMER (Name, Email, Phone, Address, CNIC, Nationality) VALUES (?, ?, ?, ?, ?, ?)";
    $params = array($name, $email, $phone, $address, $cnic, $nationality);
    
    $insertStmt = sqlsrv_query($conn, $insertSql, $params);

    if ($insertStmt === false) {
        $message = "<div style='color:#ef4444; text-align:center; margin-bottom:20px; font-weight:bold;'>Error adding customer!</div>";
    } else {
        $message = "<div style='color:#10b981; text-align:center; margin-bottom:20px; font-weight:bold;'>Customer added successfully!</div>";
    }
}

// Bring in your master navigation here!
include 'header.php'; 
?>

<style>
/* INDEX-SPECIFIC STYLES (Kept exactly as you designed them) */
.hero {
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    text-align:center;
    padding:120px 20px;
    background:
    linear-gradient(rgba(11,17,32,0.8),rgba(11,17,32,0.9)),
    url('https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1600&auto=format&fit=crop');
    background-size:cover;
    background-position:center;
}

.hero-content { max-width:900px; }
.hero-content h1 { font-size:70px; line-height:1.1; margin-bottom:20px; }
.hero-content span { color:#f59e0b; }
.hero-content p { color:#cbd5e1; line-height:1.8; font-size:18px; margin-bottom:40px; }
.hero-buttons { display:flex; justify-content:center; gap:20px; flex-wrap:wrap; }

.btn {
    padding:15px 35px;
    border-radius:6px;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.btn-primary { background:#f59e0b; color:black; }
.btn-primary:hover { background:#ffbe3b; }
.btn-secondary { border:2px solid #f59e0b; color:#f59e0b; background: transparent; }
.btn-secondary:hover { background:#f59e0b; color:black; }

/* STATS */
.stats {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:25px;
    padding:60px;
    background:#111827;
}

.stat-box {
    background:#1e293b;
    padding:35px;
    text-align:center;
    border-radius:10px;
    border:1px solid rgba(245,158,11,0.2);
    transition:0.3s;
}

.stat-box:hover { transform:translateY(-8px); border-color:#f59e0b; }
.stat-box h2 { font-size:45px; color:#f59e0b; margin-bottom:10px; }
.stat-box p { color:#cbd5e1; }

/* SECTION */
section { padding:100px 60px; }
.section-title { text-align:center; margin-bottom:60px; }
.section-title h2 { font-size:50px; color:#f59e0b; margin-bottom:15px; }
.section-title p { color:#94a3b8; }

/* CARDS */
.grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:30px; }
.card { background:#1e293b; padding:30px; border-radius:12px; transition:0.4s; border:1px solid transparent; }
.card:hover { transform:translateY(-10px); border:1px solid #f59e0b; }
.card h3 { color:#f59e0b; margin-bottom:15px; font-size:24px; }
.card p { color:#cbd5e1; line-height:1.8; }

/* FORMS */
.form-group { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
.form-control { flex: 1; padding: 15px; border: 1px solid rgba(245,158,11,0.2); border-radius: 6px; background: #0b1120; color: white; outline: none; font-size: 15px; min-width: 200px; }
.form-control:focus { border-color: #f59e0b; }
.form-control::placeholder { color: #64748b; }

/* TABLES */
.table-container { overflow-x:auto; }
table { width:100%; border-collapse:collapse; background:#1e293b; border-radius:10px; overflow:hidden; }
table th { background:#f59e0b; color:black; padding:18px; text-align: left; }
table td { padding:18px; border-bottom:1px solid rgba(255,255,255,0.08); color:#cbd5e1; }
table tr:hover { background:#334155; }

/* ERD */
.erd-box { text-align:center; }
.erd-box img { width:100%; max-width:1200px; border-radius:12px; border:4px solid #f59e0b; box-shadow:0 0 30px rgba(245,158,11,0.2); }

/* TIMELINE */
.timeline { display:grid; gap:25px; }
.timeline-item { background:#1e293b; padding:25px; border-left:5px solid #f59e0b; border-radius:8px; }
.timeline-item h3 { color:#f59e0b; margin-bottom:10px; }

/* RESPONSIVE */
@media(max-width:768px){
    .hero-content h1 { font-size:45px; }
    section { padding:80px 20px; }
    .section-title h2 { font-size:36px; }
}
</style>

<div class="hero">
    <div class="hero-content">
        <h1>Dynamic <span>Hospitality</span><br>Management System</h1>
        <p>A complete Hotel Management Database System designed using ERD concepts. The system manages rooms, bookings, loyalty programs, pricing rules, maintenance complaints, room suggestions, customers, staff, and payments.</p>
        <div class="hero-buttons">
            <a href="#dashboard" class="btn btn-primary">Go to Dashboard</a>
            <a href="#erd" class="btn btn-secondary">View ERD</a>
        </div>
    </div>
</div>

<div class="stats">
    <div class="stat-box">
        <h2>12+</h2>
        <p>Database Tables</p>
    </div>
    <div class="stat-box">
        <h2>4</h2>
        <p>Advanced Features</p>
    </div>
    <div class="stat-box">
        <h2>100%</h2>
        <p>Database Normalized</p>
    </div>
    <div class="stat-box">
        <h2>24/7</h2>
        <p>Hotel Operations</p>
    </div>
</div>

<section id="about">
    <div class="section-title">
        <h2>About the System</h2>
        <p>Modern Hotel Database Management Solution</p>
    </div>
    <div class="grid">
        <div class="card">
            <h3>Customer Management</h3>
            <p>Store customer information including email, phone number, nationality, address, and booking history.</p>
        </div>
        <div class="card">
            <h3>Booking Management</h3>
            <p>Handle reservations, room availability, check-in/check-out operations, and booking status.</p>
        </div>
        <div class="card">
            <h3>Payment System</h3>
            <p>Track payments, methods, invoices, and transaction status securely.</p>
        </div>
        <div class="card">
            <h3>Room Management</h3>
            <p>Manage room categories, pricing, capacity, availability, and amenities.</p>
        </div>
    </div>
</section>

<section id="features">
    <div class="section-title">
        <h2>Advanced Features</h2>
        <p>Features implemented from your ERD Diagram</p>
    </div>
    <div class="grid">
        <div class="card">
            <h3>Surge Pricing</h3>
            <p>Pricing rules dynamically change room prices based on occupancy rate and hotel demand.</p>
        </div>
        <div class="card">
            <h3>Loyalty Accounts</h3>
            <p>Guests collect points and receive discounts based on booking history.</p>
        </div>
        <div class="card">
            <h3>Maintenance Tickets</h3>
            <p>Complaints generate tickets assigned to maintenance staff for resolution.</p>
        </div>
        <div class="card">
            <h3>Room Suggestion Engine</h3>
            <p>Suggest rooms according to capacity, price range, and customer requirements.</p>
        </div>
        <div class="card">
            <h3>Demand Logging</h3>
            <p>Track room demand and occupancy rates to improve business decisions.</p>
        </div>
        <div class="card">
            <h3>Staff Management</h3>
            <p>Store staff details including salary, role, shift timing, and hiring dates.</p>
        </div>
    </div>
</section>

<section id="dashboard" style="background-color: #111827;">
    <div class="section-title">
        <h2>Live Database Dashboard</h2>
        <p>Direct connection to SQL Server (OasisHMS Database)</p>
    </div>

    <?php echo $message; ?>

    <div class="grid">
        <div class="card" style="grid-column: 1 / -1;">
            <h3>Register New Customer</h3>
            <form method="POST" action="index.php#dashboard">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    <input type="email" name="email" class="form-control" placeholder="Email Address">
                    <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                </div>
                <div class="form-group">
                    <input type="text" name="address" class="form-control" placeholder="Home Address">
                    <input type="text" name="cnic" class="form-control" placeholder="CNIC Number" required>
                    <input type="text" name="nationality" class="form-control" placeholder="Nationality" value="Pakistani">
                </div>
                <button type="submit" name="add_customer" class="btn btn-primary" style="width: 100%;">Save to Database</button>
            </form>
        </div>
    </div>

    <div class="table-container" style="margin-top: 40px;">
        <h3 style="color:#f59e0b; margin-bottom:15px; font-size:24px;">Customer Directory</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>CNIC</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn) && $conn) {
                    $sql = "SELECT * FROM CUSTOMER ORDER BY CustomerID DESC";
                    $stmt = sqlsrv_query($conn, $sql);

                    if ($stmt !== false) {
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['CustomerID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['CNIC']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No data found or query failed.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<section id="tables">
    <div class="section-title">
        <h2>Database Tables</h2>
        <p>Main entities from your ERD diagram</p>
    </div>
    <div class="table-container">
        <table>
            <tr>
                <th>Table Name</th>
                <th>Purpose</th>
            </tr>
            <tr><td>CUSTOMER</td><td>Stores customer information</td></tr>
            <tr><td>ROOM</td><td>Stores room details and availability</td></tr>
            <tr><td>ROOM_TYPE</td><td>Stores room category and pricing</td></tr>
            <tr><td>BOOKING</td><td>Stores reservation records</td></tr>
            <tr><td>PAYMENT</td><td>Stores payment transactions</td></tr>
            <tr><td>STAFF</td><td>Stores hotel employee data</td></tr>
            <tr><td>LOYALTY_ACCOUNT</td><td>Stores customer loyalty points</td></tr>
            <tr><td>DISCOUNT_RECORD</td><td>Stores discount information</td></tr>
            <tr><td>PRICING_RULE</td><td>Stores surge pricing rules</td></tr>
            <tr><td>DEMAND_LOG</td><td>Stores occupancy demand data</td></tr>
            <tr><td>COMPLAINT</td><td>Stores customer complaints</td></tr>
            <tr><td>MAINTENANCE_TICKET</td><td>Stores maintenance issue records</td></tr>
            <tr><td>ROOM_SUGGESTION_LOG</td><td>Stores suggested room records</td></tr>
        </table>
    </div>
</section>

<section id="timeline">
    <div class="section-title">
        <h2>Project Timeline</h2>
        <p>Database development phases</p>
    </div>
    <div class="timeline">
        <div class="timeline-item">
            <h3>Week 1 - Requirement Analysis</h3>
            <p>Gather hotel management requirements and identify entities.</p>
        </div>
        <div class="timeline-item">
            <h3>Week 2 - ER Diagram Design</h3>
            <p>Create relationships and normalize database structure.</p>
        </div>
        <div class="timeline-item">
            <h3>Week 3 - Table Creation</h3>
            <p>Create database tables and define constraints.</p>
        </div>
        <div class="timeline-item">
            <h3>Week 4 - SQL Development</h3>
            <p>Develop SQL queries, procedures, and triggers.</p>
        </div>
        <div class="timeline-item">
            <h3>Week 5 - Testing</h3>
            <p>Validate bookings, payments, and maintenance workflow.</p>
        </div>
        <div class="timeline-item">
            <h3>Week 6 - Final Documentation</h3>
            <p>Prepare reports and final project presentation.</p>
        </div>
    </div>
</section>

<section id="erd">
    <div class="section-title">
        <h2>ERD Diagram</h2>
        <p>Entity Relationship Diagram of the Hotel System</p>
    </div>
    <div class="erd-box">
        <img src="erd.png" alt="ERD Diagram">
    </div>
</section>

<?php include 'footer.php'; ?>