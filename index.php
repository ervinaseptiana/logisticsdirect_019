<?php
    //memanggil setiap halaman
    require_once "config.php";
    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    //mengambil volume truk
    $queryTruckVolume = "SELECT volume FROM truck_volume WHERE id = 1";
    $resultTruckVolume = $conn->query($queryTruckVolume);
    $truckVolume = 0;

    if ($resultTruckVolume->num_rows > 0) {
        $rowTruckVolume = $resultTruckVolume->fetch_assoc();
        $truckVolume = $rowTruckVolume['volume']; //nilai volume
    }

    //proses update volume truk
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_volume'])) {
        $newVolume = $_POST['truck_volume'];
        $newVolume = $conn->real_escape_string($newVolume);

        $updateQuery = "UPDATE truck_volume SET volume = '$newVolume' WHERE id = 1";
    if ($conn->query($updateQuery)) {
        $truckVolume = $newVolume; //update nilai volume truk
        $updateSuccess = "Volume truk berhasil diperbarui!";
    } else {
        $updateError = "Gagal memperbarui volume truk: " . $conn->error;
    }
    }

    //menghitung jumlah total pesanan unik berdasarkan id_up (Primary Key)
    $totalOrders = 0;
    $queryOrdersCount = "SELECT COUNT(DISTINCT id_up) AS total_orders FROM data_delivery";

    try {
        $resultOrdersCount = $conn->query($queryOrdersCount);
        if ($resultOrdersCount && $resultOrdersCount->num_rows > 0) {
            $rowOrdersCount = $resultOrdersCount->fetch_assoc();
            $totalOrders = $rowOrdersCount['total_orders'] ?? 0;
        }
    } catch (mysqli_sql_exception $e) {
        die("Error fetching total orders: " . $e->getMessage());
    }

    //mengambil tanggal 7 hari ke belakang dari hari ini
    $today = date('Y-m-d');
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

    //mengambil data Route Activity
    // $queryRouteActivity = "SELECT route, distance, time, status FROM route_activity  WHERE DATE(time) BETWEEN '$sevenDaysAgo' AND '$today' ORDER BY time DESC";
    // $resultRouteActivity = $conn->query($queryRouteActivity);
    // if (!$resultRouteActivity) {
    //     die("Error executing query: " . $conn->error);
    // }

    //ambil data pengiriman dari tabel delivery_schedule
    $query = "SELECT * FROM delivery_schedule";
    $result = $conn->query($query);

    //menyimpan data dalam array
    $schedules = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }
    }
    
    // Query untuk mengambil informasi delivery dari tabel delivery_schedule
    $today = date('Y-m-d');
    $query = "SELECT 
                ds.delivery_date,
                ds.time,
                ds.cycle,
                ds.status,
                COUNT(dd.part_no) as total_parts,
                SUM(dd.qty_pack) as total_quantity
                FROM delivery_schedule ds
                LEFT JOIN data_delivery dd ON DATE(dd.date) = ds.delivery_date
                -- WHERE ds.delivery_date >= CURDATE()
                GROUP BY ds.delivery_date, ds.time, ds.cycle, ds.status
                ORDER BY ds.delivery_date ASC, ds.time ASC
                LIMIT 10";
              
    $result = $conn->query($query);
    $deliverySchedules = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $deliverySchedules[] = $row;
        }
    }

    $title = "Dashboard - Logistics Delivery Supplier";
    require_once "019/header.php";
    require_once "019/navbar.php";
    require_once "019/sidebar.php";
?>

<style>
    /*background bagian tengah*/
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

    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    height: 100vh;
    overflow: hidden;
    }

    #layoutSidenav {
        height: 100vh;
    }

    /* Mengatur container utama */
    .container-fluid {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 30px;
    }

    /*sidebar*/
    #layoutSidenav #layoutSidenav_nav {
        background-color: #ffffff;
    }

    .navbar {
        background-color: #ffffff;
    }

    .breadcrumb {
        color: white;
        font-size: 18px;
    }

    /* Mengatur card dan tabel */
    .card {
        margin: 20px 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        padding: 15px 20px;
    }

    .card-body {
        padding: 20px;
    }

    /* Mengatur tabel */
    .table-responsive {
        margin: 0 auto;
        padding: 0 15px;
    }

    .table {
        width: 95%;
        margin: 0 auto;
    }

    table.table th,
    table.table td {
        vertical-align: middle;
        padding: 12px 15px;
    }

    table.table th {
        text-align: center;
        background-color: #f8f9fa;
    }

    table.table td {
        text-align: center;
    }

    /* Mengatur DataTables */
    .dataTables_wrapper {
        padding: 20px;
    }

    .dataTables_length,
    .dataTables_filter {
        margin-bottom: 15px;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .container-fluid {
            padding: 0 15px;
        }
    
        .table {
            width: 100%;
        }
    }
</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item">Dashboard</a></li>
                </ol>
            </h1>

            <!--notifikasi-->
            <?php if (isset($updateSuccess)): ?>
                <div class="alert alert-success"><?php echo $updateSuccess; ?></div>
            <?php elseif (isset($updateError)): ?>
                <div class="alert alert-danger"><?php echo $updateError; ?></div>
            <?php endif; ?>

            <!--komponen-->
            <div class="row justify-content-center">
                <div class="col-12 col-md-6">
                    <div class="card bg-white text-black fw-bold mb-4">
                        <div class="card-body" class="sb-nav-link-icon">
                            <i class="fa-solid fa-truck-moving"></i> Volume Truck
                        </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-black stretched-link" href="#" data-bs-toggle="modal" data-bs-target="#volumeModal"><?php echo $truckVolume; ?> m³</a>
                            <div class="small text-white">
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!--all orders-->
                <div class="col-12 col-md-6">
                    <div class="card bg-white text-black fw-bold mb-4">
                        <div class="card-body sb-nav-link-icon">
                            <i class="fa-solid fa-border-all"></i> All Orders
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-black stretched-link" href="upload_file.php"><?php echo $totalOrders; ?> orders</a>
                            <div class="small text-white">
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--tabel informasi delivery-->
            <div class="bg-white text-black rounded-3">
                <div class="card-header">
                    <i class="fas fa-truck-loading me-1"></i>
                    Delivery Schedule Information
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover delivery-table">
                            <thead>
                                <tr>
                                    <th>Delivery Date</th>
                                    <th>Time Slot</th>
                                    <th>Cycle</th>
                                    <th>Total Parts</th>
                                    <th>Total Quantity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($deliverySchedules)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No scheduled deliveries found</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($deliverySchedules as $schedule): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($schedule['delivery_date'])) ?></td>
                                        <td class="time-slot">
                                            <?= date('H:i', strtotime($schedule['time'])) ?>
                                        </td>
                                        <td class="cycle-number">
                                            Cycle <?= htmlspecialchars($schedule['cycle']) ?>
                                        </td>
                                        <td><?= number_format($schedule['total_parts']) ?></td>
                                        <td><?= number_format($schedule['total_quantity']) ?></td>
                                        <td>
                                            <span class="delivery-status status-<?= strtolower($schedule['status']) ?>">
                                                <?= htmlspecialchars($schedule['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!--update volume-->
            <div class="modal fade" id="volumeModal" tabindex="-1" aria-labelledby="volumeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="volumeModalLabel">Update Volume Truck</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="">
                            <div class="modal-body">
                                <div class="mb-3">
                                <label for="truck_volume" class="form-label">Volume (m³)</label>
                                    <input type="text" pattern="^\d+(\.\d+)?$" class="form-control" id="truck_volume" name="truck_volume" value="<?php echo rtrim(rtrim($truckVolume, '0'), '.'); ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="update_volume" class="btn btn-primary">Update</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </main>
</div>

<script>
    $(document).ready(function() {
        $('.delivery-table').DataTable({
            responsive: true,
            order: [[0, 'asc'], [1, 'asc']],
            pageLength: 10,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries available",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching records found"
            }
        });
    });
</script>