<?php
// Start the session
session_start();

// Include database connection file
include('db_connection.php');

// Check if the user is logged in and if they are an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: login.php");
    exit();
}

// Initialize variables for search
$searchName = '';
$searchDate = '';
$searchMonth = '';  // New variable for month filter

// Check if the search form is submitted
if (isset($_POST['searchName'])) {
    $searchName = $_POST['searchName'];
}

if (isset($_POST['searchDate'])) {
    $searchDate = $_POST['searchDate'];
}

if (isset($_POST['searchMonth'])) {  // Capture selected month
    $searchMonth = $_POST['searchMonth'];
}

// Base query to select all orders along with size and price from inventory
$sql = "SELECT o.customer_name, o.order_date, o.product_name, o.quantity, i.size, i.price 
        FROM tb_orders o 
        JOIN tb_inventory i ON o.product_name = i.name";

// Array to hold WHERE conditions
$whereClauses = [];

// If search name is provided, add it to the WHERE clause
if (!empty($searchName)) {
    $whereClauses[] = "o.customer_name LIKE '%" . $conn->real_escape_string($searchName) . "%'";
}

// If search date is provided, add it to the WHERE clause
if (!empty($searchDate)) {
    $whereClauses[] = "DATE(o.order_date) = '" . $conn->real_escape_string($searchDate) . "'";
}

// If search month is provided, filter by the month and year
if (!empty($searchMonth)) {
    $whereClauses[] = "MONTH(o.order_date) = '" . $conn->real_escape_string($searchMonth) . "'";
}

// Apply WHERE conditions if any
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}

// Sort by order date in descending order so the most recent order is first
$sql .= " ORDER BY o.order_date DESC, o.customer_name ASC";

// Execute the query
$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}

// Create an array to hold the customer order data
$customerOrders = [];

// Group orders by customer
while ($row = $result->fetch_assoc()) {
    $customerOrders[$row['customer_name']][] = $row;
}

// Initialize totals
$totalOrders = 0;
$totalQuantity = 0;
$totalAmount = 0;
$monthlyTotals = [];  // Array to hold monthly totals

// Calculate totals and group by month
foreach ($customerOrders as $customerName => $orders) {
    foreach ($orders as $order) {
        $totalOrders++;
        $totalQuantity += $order['quantity'];
        $totalAmount += $order['quantity'] * $order['price'];

        // Extract month and year for monthly totals
        $orderMonthYear = date('Y-m', strtotime($order['order_date']));
        if (!isset($monthlyTotals[$orderMonthYear])) {
            $monthlyTotals[$orderMonthYear] = [
                'quantity' => 0,
                'amount' => 0,
                'orders' => 0
            ];
        }
        $monthlyTotals[$orderMonthYear]['quantity'] += $order['quantity'];
        $monthlyTotals[$orderMonthYear]['amount'] += $order['quantity'] * $order['price'];
        $monthlyTotals[$orderMonthYear]['orders']++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - GSL25 Inventory Management System</title>
    <link rel="icon" href="img/GSL25_transparent 2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      /* General Styles */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    color: #2c3e50;
    background-color: #f9f9f9;
    transition: background-color 0.3s ease;
}

/* Sidebar Styling */
.sidebar {
    width: 260px;
    background: linear-gradient(145deg, #34495e, #2c3e50);
    color: #ecf0f1;
    padding: 30px 20px;
    height: 100vh;
    position: fixed;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease;
}

.sidebarHeader h2 {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
    text-align: center;
    color: #ecf0f1;
}

.sidebarNav ul {
    list-style: none;
    padding: 0;
}

.sidebarNav ul li {
    margin: 1.2rem 0;
}

.sidebarNav ul li a {
    text-decoration: none;
    color: #ecf0f1;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    padding: 0.8rem 1rem;
    border-radius: 8px;
    transition: background 0.3s ease;
}

.sidebarNav ul li a:hover {
    background-color: #2980b9;
}

.sidebarNav ul li a i {
    margin-right: 15px;
}

/* Main Content Styling */
.mainContent {
    margin-left: 280px;
    padding: 30px;
    width: calc(100% - 280px);
    transition: margin-left 0.3s ease;
}

.mainHeader h1 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    text-align: center;
    color: #34495e;
}

/* Totals Section */
.totalsSection {
    display: flex;
    justify-content: space-around;
    margin-bottom: 2rem;
    background: #ecf0f1;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    animation: fadeIn 1s ease-in-out;
}

.totalsSection div {
    text-align: center;
}

.totalsSection div h2 {
    font-size: 1.5rem;
    color: #3498db;
    margin: 0;
}

.totalsSection div span {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
}

/* Orders Section */
.ordersSection {
    margin-top: 2rem;
}

/* Order Details */
.orderDetails {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap; /* Allow wrapping on small screens */
}

.orderDetails h3,
.orderDetails p {
    margin: 0;
    font-size: 1.5rem;
    flex: 1; /* Ensures proper spacing */
}

.orderDetails button {
    margin-left: 20px;
    flex-shrink: 0; /* Prevent button from shrinking */
}

/* Table Styling */
.ordersTable {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
    border-radius: 8px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

.ordersTable th, .ordersTable td {
    padding: 15px;
    border: 1px solid #ddd;
    text-align: center;
}

/* Buttons */
button {
    background-color: #3498db;
    color: #ffffff;
    padding: 10px 20px;
    font-size: 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #2980b9;
}

button:focus {
    outline: none;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .sidebar {
        width: 220px; /* Adjust sidebar size for tablets */
    }

    .mainContent {
        margin-left: 240px; /* Adjust main content position */
    }

    .totalsSection {
        flex-direction: column;
        align-items: center;
    }

    .totalsSection div {
        margin-bottom: 10px;
    }

    .ordersTable th, .ordersTable td {
        padding: 12px;
    }

    button {
        width: 100%;
        padding: 12px;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 200px; /* Adjust sidebar size for smaller screens */
    }

    .mainContent {
        margin-left: 0;
        width: 100%;
    }

    .totalsSection {
        flex-direction: column;
        align-items: center;
        padding: 10px;
    }

    .totalsSection div {
        margin-bottom: 10px;
        width: 100%;
        text-align: left;
    }

    .ordersTable th, .ordersTable td {
        padding: 10px;
        font-size: 0.9rem;
    }

    button {
        width: 100%;
        padding: 12px;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        box-shadow: none;
    }

    .mainContent {
        margin-left: 0;
        padding: 10px;
    }

    .sidebarNav ul li a {
        padding: 1rem;
    }

    .totalsSection {
        padding: 10px;
        flex-direction: column;
        width: 100%;
    }

    .totalsSection div {
        margin-bottom: 5px;
    }

    .ordersTable th, .ordersTable td {
        padding: 8px;
        font-size: 0.8rem;
    }

    button {
        width: 100%;
        padding: 14px;
    }
}
      
        .logout-form {
            margin-top: auto; 
        }

        .logout-button {
            background-color: #e74c3c; 
            color: #ffffff; 
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: background-color 0.3s;
            margin-top: 10px; 
        }

        .logout-button i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        .logout-button:hover {
            background-color: #c0392b;
        }
        /* General Table Styling */
table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px 16px;
  border: 1px solid #ddd;
  text-align: left;
}

th {
  background-color: #f7fafc;
  font-weight: 600;
}

tr:nth-child(even) {
  background-color: #f9f9f9;
}

tr:hover {
  background-color: #f1f1f1;
}

.text-center {
  text-align: center;
}

.text-right {
  text-align: right;
}

/* Printing Media Query */
@media print {
  /* For printing POS receipts */
  @page {
    size: 80mm 80mm;
    margin: 0;
  }

  body {
    font-family: Arial, sans-serif;
    font-size: 10px;
  }

  .receipt-style {
    width: 80mm; /* POS size */
    margin: 0;
    padding: 5mm;
    box-shadow: none;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th, td {
    padding: 5px;
    text-align: left;
    border: 1px solid #000;
  }

  th {
    background-color: #f1f1f1;
    font-weight: bold;
  }

  tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  .text-center {
    text-align: center;
  }

  .text-right {
    text-align: right;
  }

  button {
    display: none; /* Hide the print button when printing */
  }
}

    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebarHeader">
        <h2>GSL25 Dashboard</h2>
    </div>
    <nav class="sidebarNav">
        <ul>
            <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="inventory.php"><i class="fa fa-box"></i> Inventory</a></li>
            <li><a href="orders.php"><i class="fas fa-cash-register"></i> Point of Sale (POS)</a></li>
            <li><a href="reports.php"><i class="fa fa-chart-line"></i> Reports</a></li>
            <li><a href="settings.php"><i class="fa fa-cog"></i> Settings</a></li>
        </ul>
        <!-- Logout Button -->
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-button">
                <i class="fa fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </nav>
</aside>

    <div class="mainContent">
    <header class="mainHeader"></header>

    <!-- Search Bar -->
    <section class="searchSection">
        <form method="POST" action="reports.php" class="flex items-center mb-4">
            <input type="text" name="searchName" placeholder="Search by customer name..." value="<?php echo htmlspecialchars($searchName); ?>" class="p-2 border rounded-lg mr-2">
            <input type="date" name="searchDate" value="<?php echo htmlspecialchars($searchDate); ?>" class="p-2 border rounded-lg mr-2">
            <select name="searchMonth" class="p-2 border rounded-lg mr-2">
                <option value="">Select Month</option>
                <?php for ($month = 1; $month <= 12; $month++) { ?>
                    <option value="<?php echo $month; ?>" <?php echo $searchMonth == $month ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $month, 10)); ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded-lg">Search</button>
        </form>
    </section>

    <!-- Monthly Reports Section -->
    <section class="monthlyReportsSection">
        <h2>Monthly Reports</h2>
        <table class="w-full text-left mb-4">
            <thead>
                <tr class="bg-blue-500 text-white">
                    <th>Month</th>
                    <th>Orders</th>
                    <th>Total Quantity</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthlyTotals as $monthYear => $totals) { ?>
                    <tr class="bg-blue-100">
                        <td><?php echo date('F Y', strtotime($monthYear . '-01')); ?></td>
                        <td><?php echo $totals['orders']; ?></td>
                        <td><?php echo $totals['quantity']; ?></td>
                        <td><?php echo number_format($totals['amount'], 2); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!-- Monthly Graph -->
        <div class="monthlyGraphSection">
            <canvas id="monthlyReportChart" width="500" height="210"></canvas>
        </div>
    </section>

    <section class="ordersSection">
  <table class="ordersTable w-full text-left mb-4">
    <thead>
      <tr class="bg-blue-500 text-white">
        <th>Customer Name</th>
        <th>Order Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($customerOrders as $customerName => $orders) { ?>
        <tr class="bg-blue-100">
          <td><?php echo htmlspecialchars($customerName); ?></td>
          <td><?php echo date("F j, Y", strtotime($orders[0]['order_date'])); ?></td>
          <td>
            <button onclick="toggleReport('<?php echo htmlspecialchars($customerName); ?>')" class="bg-blue-500 text-white p-2 rounded-lg">View Invoice</button>
          </td>
        </tr>

        <tr id="report-<?php echo htmlspecialchars($customerName); ?>" style="display:none;">
  <td colspan="3">
    <div id="invoice-<?php echo htmlspecialchars($customerName); ?>" class="receipt-style bg-white border border-gray-300 rounded-lg p-6 shadow-lg">
      <div class="text-center">
        <h2 class="text-2xl font-bold">GSL25 STEEL TRADING</h2>
        <p class="text-sm">San Nicholas II, Sasmuan Pampanga</p>
        <p class="text-sm">Phone: 09307832574</p>
        <hr class="my-3">
        <h3 class="font-semibold text-xl">Invoice for <?php echo htmlspecialchars($customerName); ?></h3>
      </div>

      <div class="mb-6">
        <p><strong>Date Sold:</strong> <?php echo date("F j, Y", strtotime($orders[0]['order_date'])); ?></p>
        <p><strong>Sold To:</strong> <?php echo htmlspecialchars($customerName); ?></p>
      </div>

      <table class="w-full border-collapse mb-6" style="border: 1px solid #ddd; border-spacing: 0;">
        <thead>
          <tr style="background-color: #f4f4f4; text-align: left; font-weight: bold;">
            <th style="border: 1px solid #ddd; padding: 8px;">Product Name</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Size</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Quantity</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Price</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $totalAmount = 0;
          foreach ($orders as $order) {
            $amount = $order['quantity'] * $order['price'];
            $totalAmount += $amount;
          ?>
          <tr style="text-align: left;">
            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($order['product_name']); ?></td>
            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($order['size']); ?></td>
            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $order['quantity']; ?></td>
            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo number_format($order['price'], 2); ?></td>
            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo number_format($amount, 2); ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>

      <div class="text-right mt-6">
        <p class="text-lg font-semibold"><strong>Total Amount: ₱<?php echo number_format($totalAmount, 2); ?></strong></p>
      </div>

      <div class="text-center mt-6">
        <hr class="my-3">
        <p><strong>Thank you for your purchase!</strong></p>
        <p>No. Ref: <span id="reference-number"><?php echo rand(10000, 99999); ?></span></p>
        <p>Visit us again at GSL25 STEEL TRADING</p>
      </div>

      <div class="mt-6">
        <p style="text-align: right; font-size: 14px;">Seller's Signature: _____________________</p>
      </div>

      <button onclick="printInvoice('<?php echo htmlspecialchars($customerName); ?>')" class="bg-blue-500 text-white px-6 py-3 rounded-lg mt-6">Print Receipt</button>
    </div>
  </td>
</tr>

<?php } ?>
</tbody>
</table>
</section>

<script>
  function toggleReport(customerName) {
    var report = document.getElementById('report-' + customerName);
    report.style.display = (report.style.display === "none" || report.style.display === "") ? "table-row" : "none";
  }

  function printInvoice(customerName) {
    var printContent = document.getElementById('invoice-' + customerName);
    var printWindow = window.open('', '', 'height=600,width=800');
    
    printWindow.document.write('<html><head><title>Invoice</title>');
    
    // Include custom CSS for print styling
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5; margin: 0; padding: 0; width: 4in; height: 6in; }');
    printWindow.document.write('.receipt-style { margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; width: 100%; height: 100%; box-sizing: border-box; }');
    printWindow.document.write('.text-center { text-align: center; }');
    printWindow.document.write('.text-right { text-align: right; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
    printWindow.document.write('th, td { padding: 8px; border: 1px solid #ddd; text-align: left; font-size: 12px; }');
    printWindow.document.write('.text-lg { font-size: 14px; }');
    printWindow.document.write('.font-semibold { font-weight: 600; }');
    printWindow.document.write('.my-3 { margin-top: 1rem; margin-bottom: 1rem; }');
    printWindow.document.write('</style>');

    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent.innerHTML);
    printWindow.document.write('</body></html>');
    
    printWindow.document.close();
    printWindow.print();
  }
</script>

</script>



    <!-- Script to render the graph -->
    <script>
        const monthlyTotals = <?php echo json_encode($monthlyTotals); ?>;
        const months = [];
        const orderCounts = [];
        const totalAmounts = [];

        // Prepare data for the chart
        for (const monthYear in monthlyTotals) {
            months.push(monthYear);
            orderCounts.push(monthlyTotals[monthYear].orders);
            totalAmounts.push(monthlyTotals[monthYear].amount);
        }

        const ctx = document.getElementById('monthlyReportChart').getContext('2d');
        const monthlyReportChart = new Chart(ctx, {
            type: 'line',
            data: {
            labels: months,
            datasets: [{
            label: 'Total Orders',
            data: orderCounts,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true
        }, {
            label: 'Total Amount',
            data: totalAmounts,
            borderColor: 'rgba(153, 102, 255, 1)',
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            fill: true
        }]
    },
    options: {
        responsive: true, // Ensures responsiveness
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Month'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Value'
                }
            }
        }
    }
});

    </script>

    <script>
        // Toggle report visibility
        function toggleReport(customerName) {
            const reportRow = document.getElementById('report-' + customerName);
            reportRow.style.display = reportRow.style.display === 'none' ? 'table-row' : 'none';
        }

        function clearSearch() {
            window.location.href = 'inventory.php';
        }
        
    </script>
</body>
</html>
