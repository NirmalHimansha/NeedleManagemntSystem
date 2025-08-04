<?php
// =================================================================
// FILE: new_request.php
// UPDATED - Switched from Droidcam IP to direct Webcam access.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// --- Fetch Data for Form Dropdowns ---
$machines_result = $conn->query("SELECT model_id, model_name, serial_number FROM machines ORDER BY model_name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Request - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>New Needle Request</h1></div>
            <div class="content">

                <?php 
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="content-box">
                    <h2>Create a New Request</h2>
                    <form action="add_request_process.php" method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="machine_id">Select Machine</label>
                                <select id="machine_id" name="machine_id" required>
                                    <option value="">Select a machine...</option>
                                    <?php while($machine = $machines_result->fetch_assoc()): ?>
                                        <option value="<?php echo $machine['model_id']; ?>">
                                            <?php echo htmlspecialchars($machine['model_name'] . ' (' . $machine['serial_number'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="needle_type_id">Select Needle Type</label>
                                <select id="needle_type_id" name="needle_type_id" required disabled>
                                    <option value="">Select a machine first</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                            </div>
                             <div class="form-group">
                                <label for="change_reason_type">Reason for Change</label>
                                <select id="change_reason_type" name="change_reason_type" required>
                                    <option value="">Select a reason...</option>
                                    <option value="Broken">Broken</option>
                                    <option value="Blunted">Blunted</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Broken Needle Image</label>
                                <!-- Webcam Section -->
                                <div id="webcam-section">
                                    <div class="webcam-container">
                                        <div class="webcam-preview">
                                            <video id="webcam-feed" autoplay playsinline></video>
                                        </div>
                                        <div class="webcam-controls">
                                            <button type="button" id="start-camera-btn" class="btn">Start Camera</button>
                                            <button type="button" id="capture-btn" class="btn" disabled>Capture Image</button>
                                        </div>
                                        <input type="hidden" name="captured_image_data" id="captured_image_data">
                                    </div>
                                    <div class="toggle-upload">
                                        <a id="show-upload-btn">Can't use Webcam? Switch to File Upload.</a>
                                    </div>
                                </div>
                                <!-- File Upload Section (hidden by default) -->
                                <div id="upload-section" style="display:none;">
                                    <input type="file" id="broken_needle_image" name="broken_needle_image" accept="image/*">
                                    <div class="toggle-upload">
                                        <a id="show-webcam-btn">Switch to Webcam Capture.</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 20px;">
                            <label for="change_reason">Reason Details (Optional)</label>
                            <textarea id="change_reason" name="change_reason" rows="4"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Dynamic Needle Loading ---
        const machineSelect = document.getElementById('machine_id');
        const needleSelect = document.getElementById('needle_type_id');
        
        machineSelect.addEventListener('change', function() {
            const machineId = this.value;
            needleSelect.innerHTML = '<option value="">Loading...</option>';
            needleSelect.disabled = true;

            if (machineId) {
                fetch('get_compatible_needles.php?machine_id=' + machineId)
                    .then(response => response.json())
                    .then(data => {
                        needleSelect.innerHTML = '<option value="">Select a needle...</option>';
                        if (data.length > 0) {
                            data.forEach(needle => {
                                const option = document.createElement('option');
                                option.value = needle.id;
                                option.textContent = needle.name;
                                needleSelect.appendChild(option);
                            });
                            needleSelect.disabled = false;
                        } else {
                            needleSelect.innerHTML = '<option value="">No compatible needles found</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching needles:', error);
                        needleSelect.innerHTML = '<option value="">Error loading needles</option>';
                    });
            } else {
                needleSelect.innerHTML = '<option value="">Select a machine first</option>';
            }
        });

        // --- Webcam Functionality ---
        const webcamSection = document.getElementById('webcam-section');
        const uploadSection = document.getElementById('upload-section');
        const showUploadBtn = document.getElementById('show-upload-btn');
        const showWebcamBtn = document.getElementById('show-webcam-btn');
        
        const videoElement = document.getElementById('webcam-feed');
        const startCameraBtn = document.getElementById('start-camera-btn');
        const captureBtn = document.getElementById('capture-btn');
        const fileInput = document.getElementById('broken_needle_image');
        const hiddenImageData = document.getElementById('captured_image_data');
        let stream;

        startCameraBtn.addEventListener('click', async function() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                videoElement.srcObject = stream;
                captureBtn.disabled = false;
                startCameraBtn.disabled = true;
            } catch (err) {
                console.error("Error accessing webcam: ", err);
                alert("Could not access the webcam. Please ensure you have a webcam connected and have granted permission.");
            }
        });

        captureBtn.addEventListener('click', function() {
            const canvas = document.createElement('canvas');
            canvas.width = videoElement.videoWidth;
            canvas.height = videoElement.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
            
            hiddenImageData.value = canvas.toDataURL('image/jpeg');
            videoElement.style.border = '3px solid #28a745'; // Green border to show it's captured
            alert('Image captured!');
            
            // Stop the camera stream after capture
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            captureBtn.disabled = true;
            startCameraBtn.disabled = false;
        });

        function switchToUpload() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            webcamSection.style.display = 'none';
            uploadSection.style.display = 'block';
            fileInput.required = true;
            hiddenImageData.value = ''; // Clear captured data
            captureBtn.disabled = true;
            startCameraBtn.disabled = false;
        }

        function switchToWebcam() {
            webcamSection.style.display = 'block';
            uploadSection.style.display = 'none';
            fileInput.required = false;
            fileInput.value = ''; // Clear file input
        }

        showUploadBtn.addEventListener('click', switchToUpload);
        showWebcamBtn.addEventListener('click', switchToWebcam);

        // Set initial state
        switchToWebcam();
    });
    </script>

</body>
</html>
