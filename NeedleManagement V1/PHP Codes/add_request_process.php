<?php
// =================================================================
// FILE: add_request_process.php
// UPDATED - Now handles the new "change_reason_type" field.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Get Form Data ---
    $requesting_user_id = $_SESSION['user_id'];
    $machine_id = $_POST['machine_id'];
    $needle_type_id = $_POST['needle_type_id'];
    $quantity = $_POST['quantity'];
    $change_reason_type = $_POST['change_reason_type']; // New field
    $change_reason = trim($_POST['change_reason']); // Optional details
    $droidcam_ip = trim($_POST['droidcam_ip']);
    $droidcam_port = trim($_POST['droidcam_port']);
    $captured_image_data = $_POST['captured_image_data'];
    $image_file = $_FILES['broken_needle_image'];
    $image_path = null;

    // Remember the IP and Port for the next session
    $_SESSION['droidcam_ip'] = $droidcam_ip;
    $_SESSION['droidcam_port'] = $droidcam_port;

    // --- Validation ---
    if (empty($machine_id) || empty($needle_type_id) || empty($quantity) || empty($change_reason_type)) {
        header("Location: new_request.php?status=error&message=" . urlencode("Please fill in all required fields."));
        exit();
    }
    
    // --- Image Handling ---
    $upload_dir = '/var/www/html/uploads/';

    // Prioritize captured base64 data
    if (!empty($captured_image_data)) {
        $img_data = str_replace('data:image/jpeg;base64,', '', $captured_image_data);
        $img_data = str_replace(' ', '+', $img_data);
        $decoded_image = base64_decode($img_data);
        
        $new_file_name = uniqid('', true) . '.jpg';
        $destination = $upload_dir . $new_file_name;

        if (file_put_contents($destination, $decoded_image)) {
            $image_path = 'uploads/' . $new_file_name;
        } else {
            header("Location: new_request.php?status=error&message=" . urlencode("Failed to save captured image. Check folder permissions."));
            exit();
        }
    }
    // Fallback to Droidcam IP if no base64 data was sent (e.g., JS disabled)
    elseif (!empty($droidcam_ip) && !empty($droidcam_port)) {
        $droidcam_url = "http://" . $droidcam_ip . ":" . $droidcam_port . "/photo.jpg";
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $image_data = @file_get_contents($droidcam_url, false, $context);

        if ($image_data !== false) {
            $new_file_name = uniqid('', true) . '.jpg';
            $destination = $upload_dir . $new_file_name;
            if (file_put_contents($destination, $image_data)) {
                $image_path = 'uploads/' . $new_file_name;
            } else {
                 header("Location: new_request.php?status=error&message=" . urlencode("Failed to save Droidcam image. Check folder permissions."));
                 exit();
            }
        } else {
            header("Location: new_request.php?status=error&message=" . urlencode("Could not connect to Droidcam for final capture."));
            exit();
        }
    } 
    // Fallback to file upload
    elseif (isset($image_file) && $image_file['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $image_file['tmp_name'];
        $file_name = $image_file['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid('', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $destination)) {
                $image_path = 'uploads/' . $new_file_name;
            } else {
                header("Location: new_request.php?status=error&message=" . urlencode("Failed to move uploaded file. Check permissions."));
                exit();
            }
        } else {
            header("Location: new_request.php?status=error&message=" . urlencode("Invalid file type."));
            exit();
        }
    } else {
        header("Location: new_request.php?status=error&message=" . urlencode("You must provide an image via Droidcam or file upload."));
        exit();
    }

    // --- Insert into Database ---
    $sql = "INSERT INTO needle_requests (requesting_user_id_fk, machine_model_id_fk, needle_type_id_fk, quantity_requested, change_reason_type, change_reason, broken_needle_image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissss", $requesting_user_id, $machine_id, $needle_type_id, $quantity, $change_reason_type, $change_reason, $image_path);

    if ($stmt->execute()) {
        header("Location: new_request.php?status=success&message=" . urlencode("Request submitted successfully."));
    } else {
        header("Location: new_request.php?status=error&message=" . urlencode("Failed to submit request: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: new_request.php");
    exit();
}
?>