<?php
    require_once "config.php";
    require 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\IOFactory;

    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $fileName = $_FILES['file']['tmp_name'];
        $spreadsheet = IOFactory::load($fileName);
        $sheet = $spreadsheet->getActiveSheet()->toArray();
    
        // Loop through the rows and insert into the database
        foreach ($sheet as $index => $row) {
            if ($index === 0) continue; // Skip the header row
    
            // Escape special characters to prevent SQL injection
            $partNo = $conn->real_escape_string($row[0]);
            $minor = $conn->real_escape_string($row[1]);
            $partName = $conn->real_escape_string($row[2]);
            $supplier = $conn->real_escape_string($row[3]);
            $deliveryType = $conn->real_escape_string($row[4]);
            $destination = $conn->real_escape_string($row[5]);
            $packingType = $conn->real_escape_string($row[6]);
            $qtyPack = (int)$row[7];
            $length = (float)$row[8];
            $width = (float)$row[9];
            $height = (float)$row[10];
    
            // Fix for date parsing
            $uploadDate = !empty($row[11]) ? date("Y-m-d H:i:s", strtotime($row[11])) : date("Y-m-d H:i:s");
    
            // Validate data before inserting
            if (!empty($partNo) && $qtyPack > 0 && $length > 0 && $width > 0 && $height > 0) {
                // Insert into data_delivery table
                $query = "INSERT INTO data_delivery (part_no, minor, part_name, supplier, delivery_type, destination, packing_type, qty_pack, l, w, h, date) 
                          VALUES ('$partNo', '$minor', '$partName', '$supplier', '$deliveryType', '$destination', '$packingType', $qtyPack, $length, $width, $height, '$uploadDate')";
                $conn->query($query);
            }
        }
    
        // Redirect with success status
        header("Location: ".$_SERVER['PHP_SELF']."?status=success");
        exit();
    }

    // Mengambil total qty untuk semua pesanan
    $totalQtyPack = 0;
    $queryTotalQtyPack = "SELECT SUM(qty_pack) AS total_qty_pack FROM data_delivery";

    $resultTotalQtyPack = $conn->query($queryTotalQtyPack);
    if ($resultTotalQtyPack && $resultTotalQtyPack->num_rows > 0) {
        $rowTotalQtyPack = $resultTotalQtyPack->fetch_assoc();
        $totalQtyPack = $rowTotalQtyPack['total_qty_pack'] ?? 0;
    }

    $title = "Upload Data - Logistics Delivery Supplier";
    require_once "019/header.php";
    require_once "019/navbar.php";
    require_once "019/sidebar.php";
?>

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

    #layoutSidenav #layoutSidenav_nav {
        background-color: #ffffff;
    }

    .form-upload {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-upload .form-control {
        flex: 1;
    }

    .form-upload button {
        flex: 0;
    }

    .card-body h3 {
        margin-bottom: 10px;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        height: 100vh;
        overflow: hidden;
    }

    .breadcrumb {
        color: white;
        font-size: 18px;
    }

    .breadcrumb a {
        color: #007bff;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .breadcrumb span {
        color: white;
    }

    .dataTables_scroll {
        margin-bottom: 15px;  /* Memberikan jarak bawah antara search bar dan tabel */
        margin-top: 15px;  /* Memberikan jarak bawah antara search bar dan tabel */
    }


    table.table {
        border-top: 2px solid #dee2e6; /* Sesuaikan warna garis */
    }

    table.table th,
    table.table td {
        padding: 10px;
        text-align: center;
        vertical-align: middle;
    }
    .dataTables_filter {
        margin-bottom: 15px;  /* Memberikan jarak bawah antara search bar dan tabel */
    }

</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="<?= $main_url ?>index.php">Dashboard</a> / <span>Upload</span></li>
                </ol>
            </h1>

            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="text-black">Orders</h3>
                    <p><strong>Total Quantity Pack:</strong> <?php echo $totalQtyPack; ?> items</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="text-black">Upload File</h3>
                </div>
                <div class="card-body">
                    <?php
                    if (isset($_GET['status'])) {
                        $status = $_GET['status'];
                        $message = isset($_GET['message']) ? urldecode($_GET['message']) : '';

                        if ($status == 'success') {
                            echo "<div class='alert alert-success'>File berhasil diupload dan data berhasil disimpan.</div>";
                        } elseif ($status == 'error') {
                            echo "<div class='alert alert-danger'>Error: $message</div>";
                        }
                    }
                    ?>

                    <form action="" method="POST" enctype="multipart/form-data" class="form-upload">
                        <input type="file" name="file" id="file" class="form-control" required>
                        <button type="submit" name="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="text-black">Data Delivery</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Part No</th>
                                    <th>Minor</th>
                                    <th>Part Name</th>
                                    <th>Supplier</th>
                                    <th>Delivery Type</th>
                                    <th>Destination</th>
                                    <th>packing Type</th>
                                    <th>QTY/PACK</th>
                                    <th>Length (L)</th>
                                    <th>Width (W)</th>
                                    <th>Height (H)</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // Modified query to properly handle date and destination
                                    $query = "SELECT 
                                        part_no, 
                                        minor, 
                                        part_name, 
                                        supplier, 
                                        delivery_type, 
                                        COALESCE(destination, '') as destination, 
                                        packing_type, 
                                        qty_pack, 
                                        l, 
                                        w, 
                                        h, 
                                        DATE_FORMAT(date, '%Y-%m-%d %H:%i:%s') as date 
                                    FROM data_delivery";
                                    
                                    $result = $conn->query($query);

                                    if ($result->num_rows > 0) {
                                        $no = 1;
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>
                                                <td>{$no}</td>
                                                <td>{$row['part_no']}</td>
                                                <td>{$row['minor']}</td>
                                                <td>{$row['part_name']}</td>
                                                <td>{$row['supplier']}</td>
                                                <td>{$row['delivery_type']}</td>
                                                <td>" . htmlspecialchars($row['destination']) . "</td>
                                                <td>{$row['packing_type']}</td>
                                                <td>{$row['qty_pack']}</td>
                                                <td>{$row['l']}</td>
                                                <td>{$row['w']}</td>
                                                <td>{$row['h']}</td>
                                                <td>{$row['date']}</td>
                                            </tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='13' class='text-center'>Tidak ada data</td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                $(document).ready(function () {
                    $('#dataTable').DataTable({
                        paging: true,
                        scrollX: true,
                        responsive: true,
                        autoWidth: false,
                        lengthMenu: [10, 25, 50, 100],
                        pageLength: 10,
                        language: {
                        paginate: {
                            previous: "Previously",
                            next: "Next"
                        },
                        search: "Search:",
                        infoEmpty: "No data available",
                        }
                    });
                });
            </script>
        </div>
    </main>
</div>