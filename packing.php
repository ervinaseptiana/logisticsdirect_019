<?php
    require_once "config.php";
    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    //default tanggal
    $selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');

    $truckCapacity = 24; // kapasitas truk dalam m³
    $requiredTrucks = 0; // nilai default
    $lastTruckRemainingCapacity = 0; // nilai default

   //query untuk mengambil data berdasarkan tanggal
    //$query = "SELECT part_no, qty_pack, l, w, h FROM data_order_inventory WHERE DATE(date) = ?";
    $query = "SELECT part_no, qty_pack, l, w, h FROM data_delivery WHERE DATE(date) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Konstanta untuk optimalisasi
    define('MAX_STACK_HEIGHT', 1.6); // Tinggi maksimum tumpukan dalam meter
    define('STACKING_FACTOR', 0.7); // Faktor pengurangan volume karena penumpukan
    define('SPACE_UTILIZATION', 0.85); // Faktor utilisasi ruang

    // perhitungan total volume
    $grandTotalVolume = 0;
    $data = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Konversi dimensi ke meter
            $length = $row['l'] / 1000;
            $width = $row['w'] / 1000;
            $height = $row['h'] / 1000;
            
            // Hitung volume dasar per unit
            $volumePerUnit = round(($length * $width * $height), 3);
            
            // Hitung berapa tingkat yang bisa ditumpuk
            $possibleStacks = floor(MAX_STACK_HEIGHT / $height);
            if ($possibleStacks < 1) $possibleStacks = 1;
            
            // Hitung jumlah unit per tumpukan
            $unitsPerStack = floor(($width * $length) / ($width * $length)) * $possibleStacks;
            if ($unitsPerStack < 1) $unitsPerStack = 1;
            
            // Hitung total tumpukan yang dibutuhkan
            $totalStacks = ceil($row['qty_pack'] / $unitsPerStack);
            
            // Hitung volume aktual dengan mempertimbangkan penumpukan
            $optimizedVolume = (
                ($length * $width * MAX_STACK_HEIGHT) * // Volume dasar tumpukan
                $totalStacks * // Jumlah tumpukan yang dibutuhkan
                STACKING_FACTOR * // Faktor efisiensi penumpukan
                SPACE_UTILIZATION // Faktor utilisasi ruang
            );
            
            // Pembulatan ke 3 desimal
            $optimizedVolume = round($optimizedVolume, 3);
            
            // Update total volume
            $grandTotalVolume += $optimizedVolume;
            
            $data[] = [
                'part_no' => $row['part_no'],
                'qty_pack' => $row['qty_pack'],
                'l' => $row['l'],
                'w' => $row['w'],
                'h' => $row['h'],
                'volume_per_unit' => $volumePerUnit,
                'total_volume' => $optimizedVolume,
            ];
        }

        // Implementasi algoritma greedy (mengurutkan berdasarkan volume per unit)
        usort($data, function ($a, $b) {
            return $b['volume_per_unit'] <=> $a['volume_per_unit'];
        });

    // Calculate the number of trucks and remaining capacity
    if ($grandTotalVolume > 0) {
        $requiredTrucks = ceil($grandTotalVolume / $truckCapacity);
        $lastTruckRemainingCapacity = ($requiredTrucks * $truckCapacity) - $grandTotalVolume;
    } else {
        $requiredTrucks = 0;
        $lastTruckRemainingCapacity = $truckCapacity;
    }

    // Save the calculation results to the database
    $query = "INSERT INTO packing_calculation (calculation_date, total_volume, required_trucks, last_truck_remaining_capacity) 
          VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sddd", $selected_date, $grandTotalVolume, $requiredTrucks, $lastTruckRemainingCapacity);
    $stmt->execute();

        // Perhitungan penggunaan kapasitas truk terakhir
        $lastTruckUsage = 0;
        if ($requiredTrucks > 0) {
            $lastTruckUsage = (($truckCapacity - $lastTruckRemainingCapacity) / $truckCapacity) * 100;
        }
    }

    if (isset($_SESSION['grandTotalVolume']) && isset($_SESSION['requiredTrucks']) && isset($_SESSION['lastTruckRemainingCapacity'])) {
        echo "<div class='alert alert-info'>Total Volume: " . number_format($_SESSION['grandTotalVolume'], 3) . " m³</div>";
        echo "<div class='alert alert-info'>Required Trucks: " . $_SESSION['requiredTrucks'] . "</div>";
        echo "<div class='alert alert-info'>Remaining Capacity of Last Truck: " . number_format($_SESSION['lastTruckRemainingCapacity'], 3) . " m³</div>";
        unset($_SESSION['grandTotalVolume']);
        unset($_SESSION['requiredTrucks']);
        unset($_SESSION['lastTruckRemainingCapacity']);
    }

    function calculateTripDistribution($data, $truckCapacity) {
        $trips = array();
        $currentTrip = 0;
        $currentVolume = 0;
    
        foreach ($data as $item) {
            if ($currentVolume + $item['total_volume'] > $truckCapacity) {
                $trips[$currentTrip]['remaining'] = $truckCapacity - $currentVolume;
                $currentTrip++;
                $currentVolume = 0;
            }
            $trips[$currentTrip]['items'][] = $item;
            $currentVolume += $item['total_volume'];
        }
    
        if ($currentVolume > 0) {
            $trips[$currentTrip]['remaining'] = $truckCapacity - $currentVolume;
        }
    
        return $trips;
    }
    
    $title = "Packing";
    require_once "019/header.php";
    require_once "019/navbar.php";
    require_once "019/sidebar.php";
?>

<!--script data tables-->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
    #layoutSidenav_content {
        background-color: #A85555;
        padding: 20px;
        margin-left: 250px;
        min-height: 100vh;
        position: fixed; 
        top: 0;
        right: 0; 
        bottom: 0; 
        overflow-y: auto;
    }
    
    #layoutSidenav {
        height: 100vh;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        height: 100vh;
        overflow: hidden;
    }

    #layoutSidenav #layoutSidenav_nav {
        background-color: #ffffff;
    }

    .breadcrumb {
        color: white;
        font-size: 18px;
    }

    .volume-info {
        background-color: #f4f4f4;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }

    .progress {
        height: 30px;
    }

    .cycle-volumes {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-radius: 8px;
    }

    .cycle-bar .progress {
        background-color: rgba(255, 255, 255, 0.2);
    }

    .cycle-bar .progress-bar {
        transition: width 0.6s ease;
        font-weight: bold;
    }
</style>

<div id="layoutSidenav_content">
    <main class="container-fluid px-4">
        <h1 class="mt-4">
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="<?= $main_url ?>index.php">Dashboard</a> / <span>Packing Calculation</span></li>
            </ol>
        </h1>

        <!--memilih tanggal-->
        <form method="GET" class="row mb-4">
            <div class="col-md-4">
                <input type="date" name="selected_date" class="form-control" value="<?= $selected_date ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>

        <!--tabel-->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-truck me-1"></i> Data Packing Tanggal <?= date('d/m/Y', strtotime($selected_date)) ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="packingTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Part No</th>
                                <th class="text-center">Qty Pack/Hari</th>
                                <th class="text-center">Dimensi (L x W x H)</th>
                                <th class="text-center">Volume/Unit</th>
                                <th class="text-center">Total Volume</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($result->num_rows > 0) {
                                    $no = 1;
                                    foreach ($data as $item) {
                                        echo "<tr>";
                                        echo "<td class='text-center'>" . $no . "</td>";
                                        echo "<td class='text-center'>" . htmlspecialchars($item['part_no']) . "</td>";
                                        echo "<td class='text-center'>" . htmlspecialchars($item['qty_pack']) . "</td>";
                                        echo "<td class='text-center'>" . htmlspecialchars($item['l']) . " x " . htmlspecialchars($item['w']) . " x " . htmlspecialchars($item['h']) . " mm</td>";
                                        echo "<td class='text-center'>" . number_format($item['volume_per_unit'], 3) . " m³</td>";
                                        echo "<td class='text-center'>" . number_format($item['total_volume'], 3) . " m³</td>";
                                        echo "</tr>";
                                        $no++;
                                    }
                                    } else {
                                    echo "<tr><td colspan='6' class='text-center'>Tidak ada data untuk tanggal ini</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--volume&kapasitas-->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Total Volume</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= number_format($grandTotalVolume, 3) ?> m³</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Capacity Truck</div>
                    <div class="card-body">
                        <h5 class="card-title">24 m³</h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Cycle</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $requiredTrucks ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Remaining Capacity</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= number_format($lastTruckRemainingCapacity, 3) ?> m³</h5>
                    </div>
                </div>
            </div>
        </div>

        <!--visualisasi penggunaan kapasitas truk terakhir-->
        <div class="cycle-volumes mt-4">
            <h4 class="text-white mb-3">Volume Distribution per Cycle (Max: 24m³/cycle)</h4>
            <?php
                //inisialisasi array untuk menyimpan volume per cycle
                $cycleVolumes = array_fill(0, $requiredTrucks, 0);
                $remainingVolume = $grandTotalVolume;
    
                //distribusi volume dari atas ke bawah
                for ($i = 0; $i < $requiredTrucks; $i++) {
                    if ($remainingVolume > $truckCapacity) {
                        $cycleVolumes[$i] = $truckCapacity;
                        $remainingVolume -= $truckCapacity;
                    } else {
                        $cycleVolumes[$i] = $remainingVolume;
                        $remainingVolume = 0;
                    }
                }
    
                for ($i = 0; $i < $requiredTrucks; $i++): 
                $cycleVolume = $cycleVolumes[$i];
                $volumePercentage = ($cycleVolume / $truckCapacity) * 100;
            ?>

            <div class="cycle-bar mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-white">Cycle <?= $i + 1 ?></span>
                    <span class="text-white"><?= number_format($cycleVolume, 3) ?> m³ / 24 m³</span>
                </div>
            
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-info" 
                        role="progressbar" 
                        style="width: <?= min(100, $volumePercentage) ?>%"
                        aria-valuenow="<?= $volumePercentage ?>" 
                        aria-valuemin="0" 
                        aria-valuemax="100">
                        <?= number_format($volumePercentage, 1) ?>%
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </main>
</div>

<script>
    $(document).ready(function () {
        $('#packingTable').DataTable({
            paging: true,
            scrollX: true,
            responsive: true,
            autoWidth: false,
            order: [[4, 'desc']], // Sort by Volume/Unit column
            columns: [
                { orderable: true }, // Prioritas
                { orderable: true }, // Part No
                { orderable: true }, // Qty
                { orderable: false }, // Dimensi
                { orderable: true }, // Volume/Unit
                { orderable: true }  // Total Volume
            ],
            language: {
                paginate: {
                    previous: "Previous",
                    next: "Next"
                },
                search: "Search :",
                lengthMenu: "Show _MENU_ data",
                info: "Displaying _START_ to _END_ of _TOTAL_ data",
                zeroRecords: "No data found",
                infoEmpty: "No data available",
                infoFiltered: "(filtered from _MAX_ total data)"
            }
        });
    });
</script>