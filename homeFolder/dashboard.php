<?php
require_once '../phpFolder/client.php';

$clientObj = new Client();

if (isset($_GET['id'])) {
    $clientId = $_GET['id'];
} else {
    echo "No client ID provided!";
    exit;
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    $user = $clientObj->getClientById($userId);

    if (!$user) {
        die("Error: User not found!");
    }

    $paymentHistory = $clientObj->getPaymentHistory($userId);
    $lastPaymentDate = !empty($paymentHistory) ? end($paymentHistory)['ph_date'] : 'No payment history';

    $waterUsage = $clientObj->getWaterUsage($userId);

    $paymentDetails = $clientObj->getClientPaymentDetails($userId);

} else {
    die("Error: User ID not provided!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Extra styling here */
        .transaction-history .time {
          color: #777;
          margin-top: 5px;
      }

      .transaction-history {
        height: 200px;
        max-height: 200px;
        padding: 20px;
      }
    </style>

    <script src="dashboard.js" defer></script>
</head>
<body>
    <div class="container">
        <div class="navigation">
            <div class="container1">
                <div class="title-container">
                    <h2 class="pricing-title">WaterWay Pricing Per Cubic Meter</h2>
                </div>
                <div class="card-container">
                    <!-- Card 1 -->
                    <div class="card1 playing">
                      <div class="content">
                        <div class="title">Basic Plan</div>
                            <div class="price">₱250</div>
                            <div class="description">
                                Ideal for small households with minimal water consumption.
                            </div>
                      </div>
                    </div>
                  
                    <!-- Card 2 -->
                    <div class="card1 playing">
                      <div class="content">
                        <div class="title">Standard Plan</div>
                            <div class="price">₱500</div>
                            <div class="description">
                                Suitable for medium-sized households or small businesses.
                            </div>
                      </div>
                    </div>
                  
                    <!-- Card 3 -->
                    <div class="card1 playing">
                      <div class="content">
                        <div class="title">Premium Plan</div>
                            <div class="price">₱1000</div>
                            <div class="description">
                                Best for large families or businesses with higher water needs.
                            </div>
                      </div>
                    </div>
                  
                    <!-- Card 4 -->
                    <div class="card1 playing">
                      <div class="content">
                        <div class="title">Custom Plan</div>
                            <div class="price1">Contact for Pricing</div>
                            <div class="description">
                                Tailored pricing for businesses or large-scale operations.
                            </div>
                      </div>
                    </div>
                </div>
                <!-- Message to Admin -->
                <div class="contact-container">
                    <center>
                    <p class="pricing-subtitle">Choose the best plan based on your water consumption</p>
                    <button class="cta">
                        <span><a href="#" onclick="directMessage()" style="color: inherit; text-decoration: none;">Contact Us &nbsp;</a></span>
                        <svg viewBox="0 0 13 10" height="10px" width="15px">
                        <path d="M1,5 L11,5"></path>
                        <polyline points="8 1 12 5 8 9"></polyline>
                        </svg>
                    </button>
                    </center>
                </div>  
                
                <script>
                    function directMessage() {
                        window.location.href = "https://wa.me/639760998892"
                    }
                </script>
            </div>
        </div>
        
        <!-- Dashboard Section -->
        <div class="dashboard-container">
            <header class="header">
                <h1>Dashboard</h1>
                <button class="Btn" onclick="handleLogout()">
                <div class="sign"><svg viewBox="0 0 512 512"><path d="M217.9 105.9L340.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L217.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1L32 320c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM352 416l64 0c17.7 0 32-14.3 32-32l0-256c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32l64 0c53 0 96 43 96 96l0 256c0 53-43 96-96 96l-64 0c-17.7 0-32-14.3-32-32s14.3-32 32-32z"></path></svg></div>
                <div class="text">Logout</div>
                </button>
            </header>

            <!-- Current Balance -->
            <div class="dashboard">
                <div class="card current-balance">
                    <table>
                        <tr>
                            <th colspan="2">Current Balance</th>
                        </tr>
                        <tr>
                            <td><?php echo number_format($user['remaining_water'], 2); ?> m³</td>
                            <td style="color: red;">₱<?php echo number_format($user['remaining_water_peso'], 2); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Welcome -->
                <div class="card welcome">
                    <table>
                        <tr>
                            <th>Manage your water, your way.</th>
                        </tr>
                        <!--<th>WELCOME hello!</th>-->
                        <tr>
                            <td>Welcome to your dashboard, <?= htmlspecialchars($user['fname']) ?> !</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Water Usage -->
            <div class="dashboard">
                <div class="card water-usage">
                    <table>
                        <tr>
                            <th>Water Usage</th>
                            <th>Timestamp</th>
                            <th>Percentage</th>
                        </tr>
                        <tbody>
                            <?php foreach ($waterUsage as $usage): ?>
                                <?php if ($usage['water_used'] > 0): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($usage['water_used']) ?> m³</td>
                                        <td><?= htmlspecialchars($usage['usage_date']) ?></td>
                                        <td><?= htmlspecialchars($usage['waterused_percentage']) ?>%</td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>

                <!-- Basic Account Info -->
                <div class="card account-info">
                    <table>
                        <tr>
                            <th colspan="2">Basic Account Info</th>
                        </tr>
                        <tr>
                            <td>Account ID:</td>
                            <td><?php echo $user['client_id']; ?></td>
                        </tr>
                        <tr>
                            <td>Name:</td>
                            <td><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td><?php echo htmlspecialchars($user['account_status']); ?></td>
                        </tr>
                        <tr>
                            <td>Prepaid Limit:</td>
                            <td><?php echo number_format($user['prepaid_limit'], 2); ?> m³</td>
                        </tr>
                        <tr>
                            <td>Last Payment:</td>
                            <td><?php echo htmlspecialchars($lastPaymentDate); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

                <!-- Transaction History -->
                <div class="card transaction-history">
                    <table>
                        <tr>
                            <th>Amount (m³)</th>
                            <th>Amount (₱)</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                        <tbody>
                            <?php $paymentDetails = $clientObj->getClientPaymentDetails($userId);?>
                            <?php foreach ($paymentDetails as $payment): ?>
                                        <?php if ($payment['amount_paid_cubic'] > 0): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($payment['amount_paid_cubic']) ?> m³</td>
                                                <td style="color: green;">₱<?= htmlspecialchars($payment['amount_paid']) ?></td>
                                                <td><?= htmlspecialchars($payment['ph_time']) ?></td>
                                                <td><?= htmlspecialchars($payment['ph_status']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
</body>
</html>
