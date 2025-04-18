<?php
    require_once "config.php";
    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
    
    $selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date('Y-m-d');
    
    define('MAX_STACK_HEIGHT', 1.6); //tinggi tumpukan maksimum dalam meteran
    define('STACKING_FACTOR', 0.7); //faktor pengurangan volume karena penumpukan
    define('SPACE_UTILIZATION', 0.85); //faktor pemanfaatan ruang
    define('TRUCK_CAPACITY', 24); //kapasitas truk dalam m3

    $detailQuery = "SELECT part_no, qty_pack, l, w, h FROM data_delivery WHERE DATE(date) = ?";
    $stmt = $conn->prepare($detailQuery);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $grandTotalVolume = 0;
    $data = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $length = $row['l'] / 1000;
            $width = $row['w'] / 1000;
            $height = $row['h'] / 1000;
            $volumePerUnit = round(($length * $width * $height), 3);

            $possibleStacks = floor(MAX_STACK_HEIGHT / $height);
            if ($possibleStacks < 1) $possibleStacks = 1;
            
            $unitsPerStack = floor(($width * $length) / ($width * $length)) * $possibleStacks;
            if ($unitsPerStack < 1) $unitsPerStack = 1;
            
            $totalStacks = ceil($row['qty_pack'] / $unitsPerStack);
            
            $optimizedVolume = ($length * $width * MAX_STACK_HEIGHT) * $totalStacks * STACKING_FACTOR * SPACE_UTILIZATION;
            $optimizedVolume = round($optimizedVolume, 3);
            $grandTotalVolume += $optimizedVolume;
        }
    }
    $requiredCycles = $grandTotalVolume > 0 ? ceil($grandTotalVolume / TRUCK_CAPACITY) : 0;
    $timeSlots = [ '08:00:00' => 'Morning 1', '09:30:00' => 'Morning 2', '11:00:00' => 'Morning 3', '13:00:00' => 'Afternoon 1', '14:30:00' => 'Afternoon 2', '16:00:00' => 'Afternoon 3' ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_schedule'])) {
            // Menambahkan Jadwal Baru
            $delivery_date = $conn->real_escape_string($_POST['delivery_date']);
            $time = $conn->real_escape_string($_POST['time']);
            $cycle = intval($_POST['cycle']);
            $status = 'Scheduled';
    
            // Validasi Persamaan Waktu
            $checkSlot = "SELECT COUNT(*) as slot_count FROM delivery_schedule WHERE delivery_date = ? AND time = ?";
            $checkStmt = $conn->prepare($checkSlot);
            $checkStmt->bind_param("ss", $delivery_date, $time);
            $checkStmt->execute();
            $slotResult = $checkStmt->get_result()->fetch_assoc();
        
            if ($slotResult['slot_count'] > 0) {
                $_SESSION['error'] = "Time slot already booked. Please choose another time.";
                $checkStmt->close();
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
    
            // Tambahkan jadwal baru
            $insertQuery = "INSERT INTO delivery_schedule (delivery_date, time, cycle, status) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssis", $delivery_date, $time, $cycle, $status);
    
            if ($stmt->execute()) {
                $_SESSION['success'] = "Jadwal pengiriman berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Error: " . $stmt->error;
            }
    
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    
        if (isset($_POST['edit_schedule'])) {
            // Mencoba Edit
            $schedule_id = intval($_POST['schedule_id']);
            $delivery_date = $conn->real_escape_string($_POST['delivery_date']);
            $time = $conn->real_escape_string($_POST['time']);
            $cycle = intval($_POST['cycle']);
    
            // Validasi data
            if ($schedule_id <= 0 || empty($delivery_date) || empty($time) || $cycle <= 0) {
                $_SESSION['error'] = "Invalid input data.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
    
            // Query untuk update data
            $query = "UPDATE delivery_schedule SET delivery_date = ?, time = ?, cycle = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssii", $delivery_date, $time, $cycle, $schedule_id);
    
            if ($stmt->execute()) {
                $_SESSION['success'] = "Jadwal pengiriman berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui jadwal: " . $stmt->error;
            }
    
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    //hapus data jadwal
    if (isset($_GET['delete'])) {
        $id = $conn->real_escape_string($_GET['delete']);

        $deleteQuery = "DELETE FROM delivery_schedule WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Jadwal pengiriman berhasil dihapus";
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    //semua jadwal
    $schedulesQuery = "SELECT id, delivery_date, time, cycle, status FROM delivery_schedule WHERE delivery_date = '$selected_date' ORDER BY delivery_date, time";
    $schedules = $conn->query($schedulesQuery);

    $title = "Round Trip - Logistics Direct Supplier";
    require_once "019/header.php";
    require_once "019/navbar.php";
    require_once "019/sidebar.php";
?>

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

    .navbar {
        background-color: #ffffff;
    }

    .table thead {
        background-color: #f8f9fa;
        margin-bottom: 10px;
    }

    .breadcrumb {
        color: white;
        font-size: 18px;
        margin-bottom: 20px;
    }

    .add-schedule-btn {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 10px;
    }

    /* Tambahkan di bagian style */
    .hover-highlight {
        background-color: rgba(0,0,0,0.05) !important;
        transition: background-color 0.3s ease;
    }

    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }
</style>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item">
                        <a href="<?= $main_url ?>index.php">Dashboard</a> / Schedule<span></span>
                    </li>
                </ol>
            </h1>

            <!--notifikasi-->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!--informasi cycle-->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-truck me-1"></i>
                    Data Cycle <?= date('d/m/Y', strtotime($selected_date)) ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Total Required Cycles: <?= $requiredCycles ?></h5>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" class="row">
                                <div class="col-md-8">
                                    <input type="date" name="selected_date" class="form-control" value="<?= $selected_date ?>">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Update Date</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!--tambah jadwal-->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-plus me-1"></i>
                    Add Delivery Schedule
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="delivery_date" class="form-label">Delivery Date</label>
                                <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                                       value="<?= $selected_date ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="time" class="form-label">Time</label>
                                <select class="form-control" id="time" name="time" required>
                                    <option value="">Select Time</option>
                                    <?php foreach ($timeSlots as $time => $slot): ?>
                                        <option value="<?= $time ?>"><?= $slot ?> (<?= date('H:i', strtotime($time)) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="cycle" class="form-label">Cycle Number</label>
                                <input type="number" class="form-control" id="cycle" name="cycle" 
                                       min="1" max="<?= $requiredCycles ?>" required>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <button type="submit" name="add_schedule" class="btn btn-primary">
                                    Add Schedule
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!--daftar jadwal-->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Delivery Schedule List
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Cycle</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $schedules->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($row['delivery_date'])) ?></td>
                                    <td>
                                        <?= array_key_exists($row['time'], $timeSlots) 
                                            ? $timeSlots[$row['time']] 
                                            : date('H:i', strtotime($row['time'])) ?>
                                    </td>
                                    <td><?= $row['cycle'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] === 'Completed' ? 'success' : 'primary' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" onclick="editSchedule(<?= $row['id'] ?>)"><i class="fas fa-edit"></i> Edit</button>
                                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="deleteSchedule(<?= $row['id'] ?>)"><i class="fas fa-trash"></i> Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Edit Schedule -->
            <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Delivery Schedule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                                <div class="mb-3">
                                    <label for="edit_delivery_date" class="form-label">Delivery Date</label>
                                    <input type="date" class="form-control" id="edit_delivery_date" name="delivery_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_time" class="form-label">Delivery Time</label>
                                    <select class="form-control" id="edit_time" name="time" required>
                                        <option value="08:00:00">Morning 1 (08:00)</option>
                                        <option value="09:30:00">Morning 2 (09:30)</option>
                                        <option value="11:00:00">Morning 3 (11:00)</option>
                                        <option value="13:00:00">Afternoon 1 (13:00)</option>
                                        <option value="14:30:00">Afternoon 2 (14:30)</option>
                                        <option value="16:00:00">Afternoon 3 (16:00)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_cycle" class="form-label">cycle (m³)</label>
                                    <input type="number" step="0.001" class="form-control" id="edit_cycle" name="cycle" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">cycle (m³)</label>
                                    <input type="text" step="0.001" class="form-control" id="edit_status" name="status" readonly>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="edit_schedule" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script>
    //validasi form dan pembaruan dinamis
    document.addEventListener('DOMContentLoaded', function() {
        const cycleInput = document.getElementById('cycle');
        const maxCycles = <?= $requiredCycles ?>;
        
        cycleInput.addEventListener('input', function() {
            if (this.value > maxCycles) {
                alert(`Maximum allowed cycles for this date is ${maxCycles}`);
                this.value = maxCycles;
            }
        });
    });
    function editSchedule(id) {
        fetch(`get_schedule.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('edit_schedule_id').value = id;
                    document.getElementById('edit_delivery_date').value = data.delivery_date;
                    document.getElementById('edit_time').value = data.time;
                    document.getElementById('edit_cycle').value = data.cycle;
                    document.getElementById('edit_status').value = data.status;

                    // Menampilkan modal
                    var editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
                    editModal.show();
                }
            })
            .catch(error => console.error('Error:', error));
    }

    
    function deleteSchedule(id) {
        if (confirm('Are you sure you want to delete this schedule?')) {
            $('#delete_schedule_id').val(id);
            $('#deleteScheduleForm').submit();
        }
    }
</script>