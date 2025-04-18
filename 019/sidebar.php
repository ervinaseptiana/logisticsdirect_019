<style>
    /*footer copyright di bagian bawah*/
    footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        background-color: #fff;
        text-align: center;
        padding: 10px 0;
        font-size: 14px;
    }
</style>

<div id="layoutSidenav">
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion bg_white" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Home</div>
                <a class="nav-link" href="<?= $main_url ?>index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <hr class="mb-0"></hr>
                <div class="sb-sidenav-menu-heading">Menu</div>

                <!--buttom membuka submenu-->
                <a class="nav-link collapsed" href="<?= $main_url ?>index.php" data-bs-toggle="collapse" data-bs-target="#submenuLogistics" aria-expanded="false" aria-controls="submenuLogistics">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-truck-fast"></i></div>
                    Logistics
                    <div class="sb-nav-link-icon ms-auto"><i class="fa-solid fa-chevron-down"></i></div>
                </a>

                <!--submenu collapse-->
                <div class="collapse" id="submenuLogistics">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="<?= $main_url ?>upload_file.php">Upload Data</a>
                        <a class="nav-link" href="<?= $main_url ?>packing.php">Packing Calculation</a>
                    </nav>
                </div>

                <div class="sb-sidenav-menu"></div>
                <a class="nav-link" href="<?= $main_url ?>schedule.php">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-route"></i></div>
                    Schedule
                </a>
                <div class="sb-sidenav-menu"></div>
                <a class="nav-link" href="<?= $main_url ?>report.php">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-file"></i></div>
                    Report
                </a>
            </div>
        </div>
        
        <footer class="py-4 bg-white mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; Your Website Logistics 2024</div>
                </div>
            </div>
        </footer>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </nav>
</div>