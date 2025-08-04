<?php
// =================================================================
// FILE: view_department_reports.php
// UPDATED - This page now shows graphs and data tables.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$allowed_roles = ['Super Admin', 'Admin','Manager'.'Operator','Stores','Purchasing','Observer'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

// --- Fetch data for filters ---
$departments_result = $conn->query("SELECT dept_id, dept_name FROM departments ORDER BY dept_name");
$needles_result = $conn->query("SELECT needle_type_id, needle_sku, needle_size FROM needle_types ORDER BY needle_sku");

// --- Handle Filtering ---
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
$dept_id = isset($_GET['dept_id']) && !empty($_GET['dept_id']) ? $_GET['dept_id'] : null;
$needle_id = isset($_GET['needle_type_id']) && !empty($_GET['needle_type_id']) ? $_GET['needle_type_id'] : null;

$conditions = [];
$params = [];
$types = "";

if ($start_date) {
    $conditions[] = "DATE(nr.issued_at) >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if ($end_date) {
    $conditions[] = "DATE(nr.issued_at) <= ?";
    $params[] = $end_date;
    $types .= "s";
}
if ($dept_id) {
    $conditions[] = "u.dept_id_fk = ?";
    $params[] = $dept_id;
    $types .= "i";
}
if ($needle_id) {
    $conditions[] = "nr.needle_type_id_fk = ?";
    $params[] = $needle_id;
    $types .= "i";
}

$where_clause = "";
if (!empty($conditions)) {
    $where_clause = " AND " . implode(" AND ", $conditions);
}

// --- Fetch data for Needle Usage by Needle Number (SKU) ---
$needle_usage_sql = "SELECT 
                        nt.needle_sku, 
                        nt.needle_size, 
                        SUM(CASE WHEN nr.change_reason_type = 'Broken' THEN nr.quantity_requested ELSE 0 END) AS broken_count,
                        SUM(CASE WHEN nr.change_reason_type = 'Blunted' THEN nr.quantity_requested ELSE 0 END) AS blunted_count
                     FROM needle_requests nr
                     JOIN needle_types nt ON nr.needle_type_id_fk = nt.needle_type_id
                     JOIN users u ON nr.requesting_user_id_fk = u.user_id
                     WHERE nr.request_status = 'Issued'" . $where_clause .
                    " GROUP BY nt.needle_type_id
                     ORDER BY nt.needle_sku";

$stmt_needle = $conn->prepare($needle_usage_sql);
if(!empty($params)) {
    $stmt_needle->bind_param($types, ...$params);
}
$stmt_needle->execute();
$needle_usage_result = $stmt_needle->get_result();
$needle_usage_data = [];
while($row = $needle_usage_result->fetch_assoc()) {
    $needle_usage_data[] = $row;
}
$needle_usage_json = json_encode($needle_usage_data);
$stmt_needle->close();


// --- Fetch data for Needles Used by Machine Model ---
$model_usage_sql = "SELECT 
                        m.model_name, 
                        SUM(CASE WHEN nr.change_reason_type = 'Broken' THEN nr.quantity_requested ELSE 0 END) AS broken_count,
                        SUM(CASE WHEN nr.change_reason_type = 'Blunted' THEN nr.quantity_requested ELSE 0 END) AS blunted_count
                    FROM needle_requests nr
                    JOIN machines m ON nr.machine_model_id_fk = m.model_id
                    JOIN users u ON nr.requesting_user_id_fk = u.user_id
                    WHERE nr.request_status = 'Issued'" . $where_clause .
                   " GROUP BY m.model_name
                    ORDER BY m.model_name";

$stmt_model = $conn->prepare($model_usage_sql);
if(!empty($params)) {
    $stmt_model->bind_param($types, ...$params);
}
$stmt_model->execute();
$model_usage_result = $stmt_model->get_result();
$model_usage_data = [];
while($row = $model_usage_result->fetch_assoc()) {
    $model_usage_data[] = $row;
}
$model_usage_json = json_encode($model_usage_data);
$stmt_model->close();


// --- Fetch data for Needles Used by Machine Serial ---
$serial_usage_sql = "SELECT 
                        m.serial_number, 
                        m.model_name,
                        SUM(CASE WHEN nr.change_reason_type = 'Broken' THEN nr.quantity_requested ELSE 0 END) AS broken_count,
                        SUM(CASE WHEN nr.change_reason_type = 'Blunted' THEN nr.quantity_requested ELSE 0 END) AS blunted_count
                     FROM needle_requests nr
                     JOIN machines m ON nr.machine_model_id_fk = m.model_id
                     JOIN users u ON nr.requesting_user_id_fk = u.user_id
                     WHERE nr.request_status = 'Issued'" . $where_clause .
                    " GROUP BY m.model_id
                     ORDER BY m.serial_number";

$stmt_serial = $conn->prepare($serial_usage_sql);
if(!empty($params)) {
    $stmt_serial->bind_param($types, ...$params);
}
$stmt_serial->execute();
$serial_usage_result = $stmt_serial->get_result();
$serial_usage_data = [];
while($row = $serial_usage_result->fetch_assoc()) {
    $serial_usage_data[] = $row;
}
$serial_usage_json = json_encode($serial_usage_data);
$stmt_serial->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Reports - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Department & Machine Reports</h1></div>
            <div class="content">

                <!-- Filter Form -->
                <div class="content-box">
                    <h2>Filter Reports</h2>
                    <form action="view_department_reports.php" method="GET">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="plant">Choose Plant</label>
                                <select id="plant" name="plant">
                                    <option value="">All Plants</option>
                                    <option value="1">Plant 1</option>
                                    <option value="2">Plant 2</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dept_id">Choose Department</label>
                                <select id="dept_id" name="dept_id">
                                    <option value="">All Departments</option>
                                    <?php while($dept = $departments_result->fetch_assoc()): ?>
                                        <option value="<?php echo $dept['dept_id']; ?>" <?php if($dept_id == $dept['dept_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($dept['dept_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                             <div class="form-group">
                                <label for="needle_type_id">Choose Needle</label>
                                <select id="needle_type_id" name="needle_type_id">
                                    <option value="">All Needles</option>
                                     <?php while($needle = $needles_result->fetch_assoc()): ?>
                                        <option value="<?php echo $needle['needle_type_id']; ?>" <?php if($needle_id == $needle['needle_type_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($needle['needle_sku'] . ' / ' . $needle['needle_size']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Generate</button>
                            <a href="view_department_reports.php" class="btn">Clear Filter</a>
                            <button type="button" id="downloadPdfBtn" class="btn btn-success">Download as PDF</button>
                        </div>
                    </form>
                </div>

                <div id="report-content">
                    <!-- Report by Needle Number -->
                    <div class="content-box">
                        <h2>Needle Broken/Blunted based on Needle Number</h2>
                        <div class="chart-container" style="position: relative; height:50vh; width:100%">
                            <canvas id="needleChart"></canvas>
                        </div>
                        <button type="button" class="btn" onclick="exportChart('needleChart', 'needle-usage-by-number.png')">Export as Image</button>
                    </div>

                    <!-- Report by Machine Model -->
                    <div class="content-box">
                        <h2>Needle Broken/Blunted based on Machine Models</h2>
                        <div class="chart-container" style="position: relative; height:50vh; width:100%">
                            <canvas id="modelChart"></canvas>
                        </div>
                        <button type="button" class="btn" onclick="exportChart('modelChart', 'needle-usage-by-model.png')">Export as Image</button>
                    </div>
                    
                    <!-- Report by Machine Serial -->
                    <div class="content-box">
                        <h2>Needle Broken/Blunted based on Machine Serial Number</h2>
                        <div class="chart-container" style="position: relative; height:50vh; width:100%">
                            <canvas id="serialChart"></canvas>
                        </div>
                        <button type="button" class="btn" onclick="exportChart('serialChart', 'needle-usage-by-serial.png')">Export as Image</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const needleData = <?php echo $needle_usage_json; ?>;
    const modelData = <?php echo $model_usage_json; ?>;
    const serialData = <?php echo $serial_usage_json; ?>;

    function createStackedBarChart(canvasId, chartData, labelKey, title) {
        const labels = chartData.map(item => item[labelKey]);
        const brokenData = chartData.map(item => item.broken_count);
        const bluntedData = chartData.map(item => item.blunted_count);

        const ctx = document.getElementById(canvasId).getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Blunt',
                        data: bluntedData,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)', // Blue
                    },
                    {
                        label: 'Broken',
                        data: brokenData,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)', // Red
                    }
                ]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: title
                    },
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    createStackedBarChart('needleChart', needleData.map(d => ({...d, label: `${d.needle_sku} / ${d.needle_size}`})), 'label', 'Needle Usage by Needle Number');
    createStackedBarChart('modelChart', modelData, 'model_name', 'Needle Usage by Machine Model');
    createStackedBarChart('serialChart', serialData.map(d => ({...d, label: `${d.model_name} (${d.serial_number})`})), 'label', 'Needle Usage by Machine Serial');

    // --- PDF Download Functionality ---
    const { jsPDF } = window.jspdf;
    const downloadBtn = document.getElementById('downloadPdfBtn');

    downloadBtn.addEventListener('click', function() {
        const doc = new jsPDF('p', 'mm', 'a4');
        const reportContent = document.getElementById('report-content');
        
        doc.text("Needle Consumption Report", 14, 16);
        
        html2canvas(reportContent, { scale: 2 }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const imgProps= doc.getImageProperties(imgData);
            const pdfWidth = doc.internal.pageSize.getWidth() - 20; // with margin
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
            doc.addImage(imgData, 'PNG', 10, 25, pdfWidth, pdfHeight);
            doc.save('needle-usage-report.pdf');
        });
    });
});

function exportChart(chartId, filename) {
    const canvas = document.getElementById(chartId);
    const image = canvas.toDataURL('image/png', 1.0);
    const link = document.createElement('a');
    link.href = image;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

</body>
</html>
