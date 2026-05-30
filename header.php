<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OASIS Hotel Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; scroll-behavior:smooth; font-family:'Poppins',sans-serif; }
        body { background:#0b1120; color:white; padding-top: 100px; padding-bottom: 50px; overflow-x:hidden; }
        
        /* NAVBAR */
        nav { width:100%; position:fixed; top:0; left:0; z-index:1000; background:rgba(15,23,42,0.95); backdrop-filter:blur(10px); display:flex; justify-content:space-between; align-items:center; padding:20px 60px; border-bottom:1px solid rgba(245,158,11,0.3); }
        .logo { font-size:30px; font-weight:700; color:#f59e0b; }
        nav ul { display:flex; list-style:none; gap:30px; }
        nav ul li a { text-decoration:none; color:white; transition:0.3s; font-size:15px; }
        nav ul li a:hover { color:#f59e0b; }
        
        /* COMMON STYLES */
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .card { background:#1e293b; padding:30px; border-radius:12px; margin-bottom: 30px; border: 1px solid rgba(245,158,11,0.2); }
        h3 { color:#f59e0b; margin-bottom:20px; font-size:24px; border-bottom: 1px solid rgba(245,158,11,0.2); padding-bottom: 10px;}
        .form-group { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .form-control { flex: 1; padding: 15px; border: 1px solid rgba(245,158,11,0.2); border-radius: 6px; background: #0b1120; color: white; min-width: 150px; outline: none;}
        .form-control:focus { border-color: #f59e0b; }
        .btn-primary { background:#f59e0b; color:black; padding:15px 30px; border:none; border-radius:6px; cursor:pointer; font-weight: bold;}
        .btn-secondary { background:#3498db; color:white; padding:15px 30px; border:none; border-radius:6px; cursor:pointer; font-weight: bold;}
        table { width:100%; border-collapse:collapse; background:#0b1120; border-radius:10px; overflow:hidden; margin-top: 15px;}
        th { background:#f59e0b; color:black; padding:15px; text-align: left; }
        td { padding:15px; border-bottom:1px solid rgba(255,255,255,0.08); color:#cbd5e1; }
    </style>
</head>
<body>

<nav>
    <div class="logo">OASIS HMS</div>
   <ul>
    <li><a href="index.php">Dashboard</a></li>
    <li><a href="rooms.php">Rooms</a></li>
    <li><a href="bookings.php">Bookings</a></li>
    <li><a href="payments.php">Payments</a></li>
    <li><a href="staff.php">Staff</a></li>
    <li><a href="loyalty.php">Loyalty</a></li>
    <li><a href="smart_hub.php">Smart Hub</a></li> </ul>
</ul>
</nav>