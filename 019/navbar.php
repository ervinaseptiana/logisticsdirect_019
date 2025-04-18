<style>
    /*navbar tetap di atas*/
    .navbar {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1030; /*pastikan navbar di atas elemen lain*/
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }

    /*tambahkan margin pada konten utama agar tidak tertutup navbar*/
    body {
        padding-top: 56px;
    }
    
    /*toggle sidebar*/
    .sb-sidenav-toggled #layoutSidenav_nav {
        transform: translateX(-250px);
        transition: transform 0.3s ease;
    }
</style>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar bg-white">
        <a class="navbar-brand ps-3" href="<?= $main_url?>index.php" style="font-weight: bold; color : #A85555; font-size: 0.8rem;">
            <img src="asset/image/logohino.png" alt="Logo" style="height: 25px; width: auto; margin-right: 2px;">
            Logistics Direct Supplier
        </a>
        
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <span class="text-capitalize"><?= "Admin"?></span>
        </form>
        <!--navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-dark" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item text-dark" href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</body>