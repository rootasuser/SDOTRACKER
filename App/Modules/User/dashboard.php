<?php

use App\Config\Config;

session_start();

if (session_id() == '' || !isset($_SESSION['user'])) {
    session_regenerate_id(true);
}

if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit;
}

$timeoutDuration = 900;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutDuration)) {
    $username = $_SESSION['user']['username']; 
    session_unset();
    session_destroy();

    require '../../Config/Config.php';  

    $config = new Config(); 
    $conn = $config->DB_CONNECTION; 

    $stmt = $conn->prepare("INSERT INTO logs (username, action) VALUES (?, ?)");
    $action = $username . " automatically logged out due to inactivity";
    $stmt->bindParam(1, $username);
    $stmt->bindParam(2, $action);
    $stmt->execute();
    
    $stmt = null; 
    $conn = null; 

    header("Location: ../../../index.php");
    exit;
}

$_SESSION['last_activity'] = time();
$user = $_SESSION['user'];


?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Windel Navales">

    <title>SDO Bayawan Teacher Tracker - Dashboard </title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.jpg">

    <!-- Custom fonts for this template-->
    <link href="../../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
   

    <!-- Custom styles for this template-->
    <link href="../../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../../assets/css/form-control.css" rel="stylesheet">
    
    <link href="../../assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/js/data-tables/datatables.css">
    <link rel="stylesheet" href="../../assets/js/data-tables/datatables.min.css">
    


</head>

<body id="page-top" style="font-family: Arial, Helvetica, sans-serif;">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color: #20263e;">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="?page=dashboard">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="../../assets/images/logo.jpg" alt="..." width="40" height="40" class="rounded-circle">
                </div>
                <div class="sidebar-brand-text mx-2">ADMIN PANEL</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="?page=dashboard">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Employee Management
            </div>

    
            <!-- Nav Item - Employees -->
            <li class="nav-item">
                <a class="nav-link" href="?page=addEmployee">
                    <i class="fas fa-fw fa-plus"></i>
                    <span>Add Employee</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?page=searchEmployee">
                    <i class="fas fa-fw fa-search"></i>
                    <span>Search / Update Employee</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?page=allEmployees">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Employee List</span></a>
            </li>

               <!-- Divider -->
               <hr class="sidebar-divider">
                   <!-- Heading -->
            <div class="sidebar-heading">
                Data Entry
            </div>

            <!-- Nav Item - Data Entries -->
            <li class="nav-item">
                <a class="nav-link" href="?page=manage">
                    <i class="fas fa-fw fa-plus"></i>
                    <span>Add Category</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?page=records">
                    <i class="fas fa-fw fa-filter"></i>
                    <span>Employees Category Filter </span></a>
            </li>

          

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Heading -->
            <div class="sidebar-heading">
                System 
            </div>

            <li class="nav-item">
                <a class="nav-link" href="?page=history-logs">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Activity Log</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?page=about">
                    <i class="fas fa-fw fa-info-circle"></i>
                    <span>About Us</span></a>
            </li>

            <!-- Sidebar Toggler (Sidebar) -->
            <!-- <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div> -->

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        
                        

                       
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($user['username']); ?></span>
                                <img class="img-profile rounded-circle"
                                    src="../../assets/images/adminProfile.jpeg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="?page=account">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                      <!--- LEAVE EMPTY -->
                    </div>

                    <div class="row">


                        <?php
                            if (isset($_GET['page'])) {
                                $page = $_GET['page'];
                                switch ($page) {
                                    case 'dashboard':
                                        include '../../includes/cards.php';
                                        break;
                                    case 'addEmployee':
                                        include '../../includes/addEmployee.php';
                                        break;
                                    case 'manage':
                                        include '../../includes/manage.php';
                                        break;

                                    case 'records':
                                        include '../../includes/records.php';
                                        break;
                               
                                    case 'history-logs':
                                        include '../../includes/history-logs.php';
                                        break;

                                    case 'searchEmployee':
                                        include '../../includes/search.php';
                                        break;

                                    case 'allEmployees':
                                        include '../../includes/allEmployees.php';
                                        break;
                                        
                                    case 'about':
                                        include '../../includes/about.php';
                                        break;

                                        case 'account':
                                            include '../../includes/account.php';
                                            break;

                                    default:
                                        include '../../includes/cards.php';
                                        break;
                                }
                            } else {
                                include '../../includes/cards.php';
                            }

                        ?>
                    </div>

                    <div class="row">

                      <!--- EMPTY ROW --->


                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white" id="footerControl">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>&copy; Deped Bayawan City Division 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="../../Modules/Auth/logout.php?action=logout">Logout</a>
                </div>
            </div>
        </div>
    </div>

   
    <!-- Bootstrap core JavaScript-->
    <script src="../../../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../assets/js/sb-admin-2.js"></script>
    <script src="../../assets/js/sb-admin-2.min.js"></script>

     <!-- Page level plugins -->
     <script src="../../assets/vendor/datatables/jquery.dataTables.js"></script>
     <script src="../../assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../../assets/js/data-tables/datatables.js"></script>
    <script src="../../assets/js/data-tables/datatables.min.js"></script>
   

    <!-- Page level custom scripts -->
    <script src="../../assets/js/demo/datatables-demo.js"></script>


</body>

</html>