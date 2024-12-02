<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        
</head>
<body>
    <?php
    $con = new mysqli('localhost', 'root', '', 'db_inventory_system'); // Specify your database name here
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // Replace 'transaction_date' if necessary with your actual date column name in transactions table
    $query = $con->query("
    SELECT
        MONTHNAME(transaction_date) AS monthname,
        SUM(amount) AS amount 
    FROM transactions 
    GROUP BY monthname");

    $month = [];
    $amount = [];
    
    foreach($query as $data) {
        $month[] = $data['monthname']; // Access the month name
        $amount[] = $data['amount']; // Access the total amount
    }
    ?>

    <div>
        <canvas id="myChart"></canvas>
    </div>
    <script>
        const labels = <?php echo json_encode($month); ?>; // Encode PHP array to JavaScript
        const data = {
            labels: labels,
            datasets: [{
                label: 'Monthly Sales',
                data: <?php echo json_encode($amount); ?>, // Encode PHP array to JavaScript
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(255, 205, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(201, 203, 207, 0.2)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(255, 159, 64)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(54, 162, 235)',
                    'rgb(153, 102, 255)',
                    'rgb(201, 203, 207)'
                ],
                borderWidth: 1
            }]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
        };

        var myChart = new Chart(
            document.getElementById("myChart"),
            config
        );
    </script>
</body>
</html>
