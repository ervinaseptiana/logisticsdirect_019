<?php
    require_once "config.php";
    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
    
        //perbaiki query dengan menghapus koma setelah kolom "status"
        $query = "SELECT delivery_date, time, cycle, status FROM delivery_schedule WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(['error' => 'Schedule not found']);
        }
    
        $stmt->close();
    }