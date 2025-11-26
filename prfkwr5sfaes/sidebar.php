<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link" href="index.php">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#operations-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i><span>Operations</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="operations-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li><a href="index.php"><i class="bi bi-circle"></i><span>Manage Request</span></a></li>
        <li><a href="#" onclick="manage_doc_types()"><i class="bi bi-circle"></i><span>Manage Document Type</span></a></li>
        <li><a href="released_summary.php"><i class="bi bi-circle"></i><span>Released Summary</span></a></li>
        <li><a href="statistics_summary.php"><i class="bi bi-circle"></i><span>Statistics</span></a></li>
      </ul>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#management-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-people"></i><span>Management</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="management-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li><a href="manage_user_account.php"><i class="bi bi-person-gear"></i><span>Manage User Accounts</span></a></li>
      </ul>
    </li>

    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-journal-text"></i><span>Reports</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="reports-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li><a href="#"><i class="bi bi-circle"></i><span>Individual Summary</span></a></li>
      </ul>
    </li>

    <li class="nav-heading">System</li>
    <li class="nav-item">
      <a class="nav-link collapsed" href="../logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
      </a>
    </li>
  </ul>
</aside>
<!-- End Sidebar -->
