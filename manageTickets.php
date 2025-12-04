<?php
require_once __DIR__ . '/config.php';

// Staff authorization
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'staff') {
    header("Location: login.php");
    exit;
}

// Note: This page is similar to staff_tickets.php already created
// Redirecting to the proper staff panel page
header("Location: staff_tickets.php");
exit;
?>
<html>
<head>
    <title>Manage Tickets - Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Reuse your home/main CSS -->
    <link rel="stylesheet" type="text/css" href="css/home.css"> 
    
    <style>
        .dashboard-container {
            max-width: 900px;
            margin: 100px auto;
            padding: 20px;
        }
        .ticket-row {
            background-color: #1F1F1F;
            border-left: 5px solid #FFD700;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ticket-details h4 { margin: 0 0 5px 0; color: #EDE6D6; text-transform: uppercase;}
        .ticket-details p { margin: 0; color: #A0A0A0; font-size: 14px; }
        .user-badge { background: #333; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-right: 10px;}
        
        .delete-btn {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }
        .delete-btn:hover { background-color: #ff4c4c; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #FFD700; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body style="background-color: #121212;">

    <header class="header">
        <div class="header-left">
            <a href="EmployeeDashboard.jsp" class="logo-img">
                <img src="images/themouse.png" alt="Logo" height="40">
            </a>
        </div>
        <div class="header-right">
            <span class="account-info" style="color: #FFD700;">TICKET MANAGER</span>
        </div>
    </header>

    <div class="dashboard-container">
        <a href="EmployeeDashboard.jsp" class="back-link">&larr; Back to Dashboard</a>
        <h2 style="color: white; border-bottom: 1px solid #333; padding-bottom: 10px;">All Active Bookings</h2>

        <%
            List<String> allTickets = (List<String>) request.getAttribute("allTickets");
            
            if (allTickets == null || allTickets.isEmpty()) {
        %>
            <div style="text-align: center; color: #666; margin-top: 50px;">
                <h3>No tickets in the system.</h3>
            </div>
        <%
            } else {
                for (String ticketLine : allTickets) {
                    // Parse line: username|movie_date_time|seats
                    String[] parts = ticketLine.split("\\|");
                    if(parts.length < 3) continue; // Skip broken lines

                    String username = parts[0];
                    String sessionID = parts[1];
                    String seats = parts[2];
                    
                    // Format Session ID (avengers_today_10:00AM) -> Nice text
                    String[] sParts = sessionID.split("_");
                    String movie = (sParts.length > 0) ? sParts[0] : "Unknown";
                    String date = (sParts.length > 1) ? sParts[1] : "";
                    String time = (sParts.length > 2) ? sParts[2] : "";
        %>
            <div class="ticket-row">
                <div class="ticket-details">
                    <h4><%= movie %> <span style="font-size: 12px; color: #666; font-weight: normal;">(<%= sessionID %>)</span></h4>
                    <p>
                        <span class="user-badge"><i class="fas fa-user"></i> <%= username %></span>
                        <i class="far fa-calendar-alt"></i> <%= date %> | <i class="far fa-clock"></i> <%= time %>
                    </p>
                    <p style="margin-top: 5px; color: #FFD700;">Seats: <%= seats.replace(",", ", ") %></p>
                </div>
                
                <form action="manageTickets" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                    <!-- Pass the exact line content to identify which ticket to delete -->
                    <input type="hidden" name="ticketLine" value="<%= ticketLine %>">
                    <button type="submit" class="delete-btn"><i class="fas fa-trash"></i> Delete</button>
                </form>
            </div>
        <%
                }
            }
        %>
    </div>

</body>
</html>