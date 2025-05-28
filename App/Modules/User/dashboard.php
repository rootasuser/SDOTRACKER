<?php include('user.php'); ?>
<?php include('../Names/names.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="<?php echo $programmer[0]; ?>">

    <title><?php echo $dashboard[1]; ?></title>
    <?php include('rels.php'); ?>


</head>

<body id="page-top" style="font-family: Arial, Helvetica, sans-serif;">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color: #083c5d;">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="?page=dashboard">
                <div class="sidebar-brand-icon rotate-n-15">
                    <img src="../../assets/images/favicon1.png" alt="..." width="40" height="40" class="rounded-circle">
                </div>
                <div class="sidebar-brand-text mx-2"><?php echo $dashboard[0]; ?></div>
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
                    <span>Update Employee</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?page=allEmployees">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Employee Records</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?page=records">
                    <i class="fas fa-fw fa-filter"></i>
                    <span>Employee Filter Search</span></a>
            </li>


             <!-- Divider -->
             <hr class="sidebar-divider">
                   <!-- Heading -->
            <div class="sidebar-heading">
                Training Management
            </div>

            <!-- Nav Item - Training -->
            <li class="nav-item">
                <a class="nav-link" href="?page=trainings">
                    <i class="fas fa-fw fa-chalkboard-teacher"></i>
                    <span>Training Attended</span></a>
            </li>

             <!-- Divider -->
             <hr class="sidebar-divider">
                   <!-- Heading -->
            <div class="sidebar-heading">
                Research Management
            </div>

             <!-- Nav Item - Research Title -->
             <li class="nav-item">
                <a class="nav-link" href="?page=research">
                    <i class="fas fa-fw fa-microscope"></i>
                    <span>Research Titles</span></a>
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
                <a class="nav-link" href="?page=create_account">
                    <i class="fas fa-fw fa-user-plus"></i>
                    <span>Create Account</span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="?page=backup">
                    <i class="fas fa-fw fa-database"></i>
                    <span>Backup Database</span></a>
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

                        
                    <marquee style="width: 400px; color: #000;" behavior="scroll" direction="left">
    If you'd like to change your profile information, simply click on the profile image in the top-right corner.
</marquee>

                       
                        <div class="topbar-divider d-none d-sm-block"></div>

                       <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($user['username']); ?></span>
                                <img class="img-profile rounded-circle"
                                    src="../../assets/images/user.png">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="?page=account">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
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
                            <!--- Switch Pages -->
                        <?php include('switch.php'); ?>
                      
                    </div>

                    <div class="row">

                      <!--- EMPTY ROW --->


                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
         
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
    <?php include('modal.php'); ?>

   
   <?php include('src.php'); ?>


</body>

</html>