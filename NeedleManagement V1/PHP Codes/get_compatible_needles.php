<?php
// =================================================================
// FILE: get_compatible_needles.php
// RE-CREATED - This file is needed for the dynamic dropdown.
// =================================================================

require_once 'db_connect.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Get the machine ID from the request
$machine_id = isset($_GET['machine_id']) ? intval($_GET['machine_id']) : 0;

if ($machine_id > 0) {
    // Prepare a statement to fetch compatible needles
    $sql = "SELECT nt.needle_type_id, nt.needle_sku, nt.needle_size 
            FROM needle_types nt
            JOIN machine_needle_compatibility mnc ON nt.needle_type_id = mnc.needle_type_id_fk
            WHERE mnc.machine_id_fk = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $machine_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $needles = [];
    while ($row = $result->fetch_assoc()) {
        $needles[] = [
            'id' => $row['needle_type_id'],
            'name' => $row['needle_sku'] . ' / ' . $row['needle_size']
        ];
    }

    // Output the needles as a JSON array
    echo json_encode($needles);

    $stmt->close();
} else {
    // If no machine ID is provided, return an empty array
    echo json_encode([]);
}

$conn->close();
?>