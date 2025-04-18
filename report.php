<?php
    require_once "config.php";
    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    // Default date selection
    $selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

    // Constants from packing.php
    define('MAX_STACK_HEIGHT', 1.6);
    define('STACKING_FACTOR', 0.7);
    define('SPACE_UTILIZATION', 0.85);
    define('TRUCK_CAPACITY', 24);

    // Query for packing data with optimization calculations
    $packingQuery = "SELECT 
        part_no,
        qty_pack,
        l, w, h,
        ROUND(l * w * h / 1000000000, 3) as volume_per_unit
        FROM data_delivery 
        WHERE DATE(date) = ?
        ORDER BY volume_per_unit DESC";

    $stmtPacking = $conn->prepare($packingQuery);
    $stmtPacking->bind_param("s", $selected_date);
    $stmtPacking->execute();
    $packingResult = $stmtPacking->get_result();

    //process packing data with optimization
    $packingData = [];
    $totalOptimizedVolume = 0;
    $totalQty = 0;

    while ($row = $packingResult->fetch_assoc()) {
        //konversi meter
        $length = $row['l'] / 1000;
        $width = $row['w'] / 1000;
        $height = $row['h'] / 1000;
        
        //perhitungan otomatis
        $volumePerUnit = round(($length * $width * $height), 3);
        $possibleStacks = floor(MAX_STACK_HEIGHT / $height);
        if ($possibleStacks < 1) $possibleStacks = 1;
        
        $unitsPerStack = floor(($width * $length) / ($width * $length)) * $possibleStacks;
        if ($unitsPerStack < 1) $unitsPerStack = 1;
        
        $totalStacks = ceil($row['qty_pack'] / $unitsPerStack);
        
        $optimizedVolume = round(
            ($length * $width * MAX_STACK_HEIGHT) *
            $totalStacks *
            STACKING_FACTOR *
            SPACE_UTILIZATION,
            3
        );
        
        $totalOptimizedVolume += $optimizedVolume;
        $totalQty += $row['qty_pack'];

        $packingData[] = [
            'part_no' => $row['part_no'],
            'qty_pack' => $row['qty_pack'],
            'dimensions' => "{$row['l']} x {$row['w']} x {$row['h']}",
            'volume_per_unit' => $volumePerUnit,
            'optimized_volume' => $optimizedVolume
        ];
    }

    //query dari halamanan schedule
    $roundTripQuery = "SELECT 
        ds.*,
        TIME_FORMAT(ds.time, '%H:%i') as formatted_time,
        CASE
            WHEN TIME(ds.time) < '12:00:00' THEN 'Morning'
            ELSE 'Afternoon'
        END as period
        FROM delivery_schedule ds
        WHERE DATE(ds.delivery_date) = ?
        ORDER BY ds.time";

    $stmtRoundTrip = $conn->prepare($roundTripQuery);
    $stmtRoundTrip->bind_param("s", $selected_date);
    $stmtRoundTrip->execute();
    $roundTripResult = $stmtRoundTrip->get_result();

    //process round trip data
    $roundTripData = [];
    while ($row = $roundTripResult->fetch_assoc()) {
        $period = $row['period'];
        if (!isset($roundTripData[$period])) {
            $roundTripData[$period] = [];
        }
        $roundTripData[$period][] = $row;
    }

    // Calculate required trucks based on total volume
    $requiredTrucks = ceil($totalOptimizedVolume / TRUCK_CAPACITY);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrated Logistics Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            color: #A85555;
            padding: 20px;
            text-align: center;
        }

        .report-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .section-title {
            color: #A85555;
            border-bottom: 2px solid #A85555;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .optimization-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .cycle-info {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-left: 4px solid #A85555;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .table thead th {
            background-color: #A85555;
            color: white;
        }

        .period-header {
            background: #A85555;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .date-display {
            font-size: 1rem;  
            color: #666;        
            font-weight: 500;   
            margin: 15px 0; 
        }
    </style>
</head>

<body>
    <!--filter tanggal-->
    <div class="report-container">
        <form method="GET" class="row g-3 align-items-center mb-4">
            <div class="col-auto">
                <label class="form-label">Select Date:</label>
                <input type="date" class="form-control" name="selected_date" value="<?= $selected_date ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary mt-4">Update Report</button>
            </div>
        </form>

        <!--judul-->
        <h1 class="text-center mb-4">Logistics Report</h1>
        <div class="text-center mb-4">
            <p class="mb-2" style="color: #666; font-size: 1.1em;">
                Kawasan Industri Kota Bukit Indah<br>
                Jl. Damar Blok D1 No.1 Purwakarta 41181 Jawa Barat - Indonesia<br>
                Telp: +62264 351 911 Fax: +62264 351 755, +62264 350 488
            </p>
        </div>
        <h4 class="date-display text-center mb-4">Date: <?= date('d/m/Y', strtotime($selected_date)) ?></h4>

        <!--optimasi-->
        <div class="optimization-stats">
            <h3 class="font-size section-title">Optimization Summary</h3>
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Volume</h5>
                            <p class="card-text"><?= number_format($totalOptimizedVolume, 3) ?> m³</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Required Trucks</h5>
                            <p class="card-text"><?= $requiredTrucks ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Parts</h5>
                            <p class="card-text"><?= count($packingData) ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Quantity</h5>
                            <p class="card-text"><?= number_format($totalQty) ?> pack</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--detail data packing-->
        <div class="mb-4">
            <h3 class="section-title">Packing Details</h3>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Part No</th>
                            <th>Qty Pack</th>
                            <th>Dimensions (mm)</th>
                            <th>Volume/Unit (m³)</th>
                            <th>Optimized Volume (m³)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packingData as $item): ?>
                        <tr>
                            <td><?= $item['part_no'] ?></td>
                            <td><?= number_format($item['qty_pack']) ?></td>
                            <td><?= $item['dimensions'] ?></td>
                            <td><?= number_format($item['volume_per_unit'], 3) ?></td>
                            <td><?= number_format($item['optimized_volume'], 3) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delivery Schedule -->
        <div>
            <h3 class="section-title">Delivery Schedule</h3>
            <?php foreach ($roundTripData as $period => $schedules): ?>
            <div class="period-header mb-3">
                <h4 class="mb-0"><?= $period ?> Deliveries</h4>
            </div>
            <div class="table-responsive mb-4">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Cycle</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= $schedule['formatted_time'] ?></td>
                            <td><?= $schedule['cycle'] ?></td>
                            <td>
                                <span class="badge bg-<?= $schedule['status'] === 'Completed' ? 'success' : 'primary' ?>">
                                    <?= $schedule['status'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Print Button -->
    <div class="text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>