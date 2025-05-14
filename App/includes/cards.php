
<?php include('counts.php'); ?>

<style>

/**
    BAR GRAPH
*/
.chart-bar {
    width: 100%;
    max-width: 100%;
    overflow-x: auto; 
}

canvas {
    display: block;
    width: 100% !important;
    height: auto !important;
    max-height: 500px; 
}

/**
    DOUGHNUT
*/

.chart-pie {
    width: 100%;
    max-width: 400px; 
    margin: auto;
    display: flex;
    justify-content: center;
}

canvas {
    width: 100% !important;
    height: auto !important;
    max-height: 300px;
}


</style>

<!-- Employee Count Card -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-dark shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                        Employees
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalEmployees ?></div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                </div>
            </div>
            <div class="text-center">
                <a href="?page=allEmployees">View</a>
            </div>
        </div>
    </div>
</div>

<!-- Schools Card -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-dark shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                        Schools
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalSchools ?></div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-school fa-2x text-gray-300"></i>
                </div>
            </div>
            <div class="text-center">
                <a href="?page=schools">View</a>
            </div>
        </div>
    </div>
</div>

<!-- Position Card -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-dark shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                        Positions
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPositions ?></div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                </div>
            </div>
            <div class="text-center">
                <a href="?page=positions">View</a>
            </div>
        </div>
    </div>
</div>

<!-- Teaching Subjects Card -->
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-dark shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                        Teaching Subjects
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalSubjects ?></div>
                </div>
                <div class="col-auto">
                    <i class="fas fa-book fa-2x text-gray-300"></i>
                </div>
            </div>
            <div class="text-center">
                <a href="?page=subjects">View</a>
            </div>
        </div>
    </div>
</div>


                       <!-- Bar Chart -->
                    <div class="card card-shadow mb-4" style="background-color: transparent; width: 100%;">
                        <div class="card-body">
                            <div class="chart-bar" style="width: 100%; overflow-x: auto;">
                                <canvas id="myBarChart"></canvas>
                            </div>
                        </div>
                    </div>


                              <!-- Bar Chart -->
                              <div class="card card-shadow mb-4 mx-2" style="background-color: transparent;">
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                </div>
                            </div>

            <!-- Gender Distribution Doughnut Chart -->
            <div class="card card-shadow mb-4 mx-2" style="background-color: transparent;">
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="genderDoughnutChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Age Distribution Bar Chart -->
<div class="card card-shadow mb-4 mx-2" style="background-color: transparent;">
    <div class="card-body">
        <div class="chart-bar">
            <canvas id="ageDistributionChart"></canvas>
        </div>
    </div>
</div>
                 

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php
require_once '../../Config/Config.php';

$config = new \App\Config\Config();
$conn = $config->DB_CONNECTION;

if (!$conn) {
    die(json_encode(["error" => "DB con failed"]));
}

$query = "SELECT p.empPosition, COUNT(e.empPosition_id) as count 
          FROM employees e 
          JOIN positions p ON e.empPosition_id = p.id 
          GROUP BY e.empPosition_id";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$positions = [];
$employeeCounts = [];

if ($result) {
    foreach ($result as $row) {
        $positions[] = htmlspecialchars($row['empPosition']); 
        $employeeCounts[] = (int) $row['count'];
    }
}

$statusQuery = "SELECT LOWER(TRIM(COALESCE(NULLIF(empStatus, ''), 'Inactive'))) AS empStatus, COUNT(*) as count 
                FROM employees 
                GROUP BY LOWER(TRIM(COALESCE(NULLIF(empStatus, ''), 'Inactive')))";

$statusStmt = $conn->prepare($statusQuery);
$statusStmt->execute();
$statusResult = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

$activeCount = 0;
$inactiveCount = 0;

if ($statusResult) {
    foreach ($statusResult as $row) {
        if ($row['empStatus'] === 'active') {
            $activeCount = (int) $row['count'];
        } elseif ($row['empStatus'] === 'inactive') {
            $inactiveCount = (int) $row['count'];
        }
    }
}

?>




<script>
const positions = <?php echo json_encode($positions); ?>;
const employeeCounts = <?php echo json_encode($employeeCounts); ?>;
const activeCount = <?php echo $activeCount; ?>;
const inactiveCount = <?php echo $inactiveCount; ?>;

const barCanvas = document.getElementById("myBarChart");
const barCtx = barCanvas.getContext("2d");

const generateColors = (count) => {
    const colors = [];
    for (let i = 0; i < count; i++) {
        colors.push(`hsl(${Math.random() * 360}, 70%, 50%)`);
    }
    return colors;
};

new Chart(barCtx, {
    type: "bar",
    data: {
        labels: positions,
        datasets: [{
            label: "Number of Employees By Position",
            backgroundColor: generateColors(positions.length),
            borderColor: "#4e73df",
            borderWidth: 1,
            data: employeeCounts,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, 
        scales: {
            y: {
                beginAtZero: true,
            },
        },
    },
});

function resizeChart() {
    barCanvas.style.height = (window.innerHeight * 0.5) + "px"; 
}
window.addEventListener("resize", resizeChart);
resizeChart(); 


// == Doughnut
const pieCanvas = document.getElementById("myPieChart");
const pieCtx = pieCanvas.getContext("2d");

new Chart(pieCtx, {
  type: "doughnut",
  data: {
    labels: ["Employee Active", "Employee Inactive"],
    datasets: [{
      data: [activeCount, inactiveCount],
      backgroundColor: ["blue", "#e74a3b"],
      hoverBackgroundColor: ["#4B0082", "#be2617"],
    }],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false, 
    cutout: "60%",
  },
});

function resizePieChart() {
    pieCanvas.style.height = (window.innerHeight * 0.4) + "px"; 
}
window.addEventListener("resize", resizePieChart);
resizePieChart(); 



// == Gender Distribution Doughnut Chart
const genderCanvas = document.getElementById("genderDoughnutChart");
const genderCtx = genderCanvas.getContext("2d");

new Chart(genderCtx, {
    type: "doughnut",
    data: {
        labels: ["Male", "Female"],
        datasets: [{
            data: [<?php echo $maleCount; ?>, <?php echo $femaleCount; ?>],
            backgroundColor: ["#36a2eb", "#ff6384"], // Blue for Male, Pink for Female
            hoverBackgroundColor: ["#2c88c3", "#e64a6e"],
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "60%",
        plugins: {
            legend: {
                position: "bottom",
            },
        },
    },
});

function resizeGenderChart() {
    genderCanvas.style.height = (window.innerHeight * 0.4) + "px";
}
window.addEventListener("resize", resizeGenderChart);
resizeGenderChart();


// == Age Distribution Bar Chart
const ageCanvas = document.getElementById("ageDistributionChart");
const ageCtx = ageCanvas.getContext("2d");

new Chart(ageCtx, {
    type: "bar",
    data: {
        labels: <?php echo json_encode($ageRanges); ?>,
        datasets: [{
            label: "Number of Employees by Age Range",
            backgroundColor: generateColors(<?php echo count($ageRanges); ?>),
            borderColor: "#4e73df",
            borderWidth: 1,
            data: <?php echo json_encode($ageCounts); ?>,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
            },
        },
    },
});

function resizeAgeChart() {
    ageCanvas.style.height = (window.innerHeight * 0.4) + "px";
}
window.addEventListener("resize", resizeAgeChart);
resizeAgeChart();




</script>
