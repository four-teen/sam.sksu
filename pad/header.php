<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

  <div class="d-flex align-items-center justify-content-between">
    <a href="index.html" class="logo d-flex align-items-center">
      <img src="../assets/img/logo.png" alt="">
      <span class="d-none d-lg-block"><?php echo $rowconfig['systemname'] ?>: PAD</span>
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

      <!-- 🔍 Mobile Search Icon -->
      <li class="nav-item d-block d-lg-none">
        <a class="nav-link nav-icon search-bar-toggle" href="#">
          <i class="bi bi-search"></i>
        </a>
      </li>

      <!-- 🔔 Notification Bell -->
      <li class="nav-item dropdown me-3">
        <a class="nav-link position-relative" href="#" id="notifBell" onclick="openNotifications()">
          <i class="bi bi-bell-fill text-warning fs-5" id="notifIcon"></i>
          <span id="notifCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
        </a>
      </li>

      <!-- End Notification Bell -->

      <!-- 👤 Profile -->
      <li class="nav-item dropdown pe-3">
        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <img src="../assets/img/profile.png" alt="Profile" class="rounded-circle">
          <span class="d-none d-md-block ps-2">
            <?php echo '(OFFICE ID: '.$_SESSION['officeid'].') '.ucfirst($_SESSION['fullname']); ?>
          </span>
        </a>
      </li><!-- End Profile Nav -->

    </ul>
  </nav><!-- End Icons Navigation -->

</header><!-- End Header -->
