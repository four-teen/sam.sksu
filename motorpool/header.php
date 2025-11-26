  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center">
        <img src="../assets/img/logo.png" alt="">
        <span class="d-none d-lg-block"><?php echo $rowconfig['systemname'] ?>: MOTORPOOL</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

<!-- Add New Record Button -->
<li class="nav-item d-none d-lg-block me-2">
    <button class="btn btn-primary shadow-sm" onclick="add_new_travel()">
        <i class="bi bi-plus-circle"></i> Add New Request
    </button>
</li>

<!-- Mobile Floating Add Button -->
<li class="nav-item d-lg-none me-2">
    <button class="btn btn-primary shadow-sm rounded-circle d-flex 
                   justify-content-center align-items-center"
            style="width: 40px; height: 40px;"
            onclick="add_new_travel()">
        <i class="bi bi-plus fs-5"></i>
    </button>
</li>


        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->



        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="../assets/img/profile.png" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block ps-2"><?php echo '(OFFICE ID: '.$_SESSION['officeid'].') '.ucfirst($_SESSION['fullname']) ?></span>
          </a><!-- End Profile Iamge Icon -->

        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->