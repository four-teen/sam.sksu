  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="index.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Operations</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="index.php">
              <i class="bi bi-circle"></i><span>Manage Request</span>
            </a>
          </li>
          <li>
            <a href="#" onclick="manage_doc_types()">
              <i class="bi bi-circle"></i><span>Manage Document Type</span>
            </a>
          </li>
          <li>
            <a href="released_summary.php">
              <i class="bi bi-circle"></i><span>Released Summary</span>
            </a>
          </li>   
          <li>
            <a href="statistics_summary.php">
              <i class="bi bi-circle"></i><span>Statistics</span>
            </a>
          </li>                   

        </ul>
      </li><!-- End Components Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#components-nav2" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i><span>Management</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav2" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="manage_programs.php">
              <i class="bi bi-circle"></i><span>Manage Degree/Program</span>
            </a>
          </li>
          <li>
            <a href="manage_assigned_faculty.php">
              <i class="bi bi-circle"></i><span>Manage Program Incharge</span>
            </a>
          </li>              

        </ul>
      </li><!-- End Components Nav -->
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-journal-text"></i><span>Reports</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="print_summary.php">
              <i class="bi bi-circle"></i><span>Released Summary</span>
            </a>
          </li>

        </ul>
      </li><!-- End Forms Nav -->


      <li class="nav-heading">Pages</li>
 
      <li class="nav-item">
        <a class="nav-link collapsed" href="../logout.php">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Logout</span>
        </a>
      </li><!-- End Login Page Nav -->


    </ul>

  </aside><!-- End Sidebar-->