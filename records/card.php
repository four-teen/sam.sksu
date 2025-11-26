
<div class="row g-2">

  <!-- Manage Request -->
  <div class="col-lg-2 col-md-6">
    <div class="card info-card border-0 shadow-sm" style="--start-color:#007bff;--end-color:#17a2b8;"  onclick="card_one()">
      <div class="card-body">
        <h5 class="card-title">Received <span class="text-muted">| records</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class="bx bx-file"></i>
          </div>
          <div>
            <h3 id="load_new_received_count" class="mb-0">0</h3>
            <small class="text-muted">document</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- OUTGOING -->
  <div class="col-lg-2 col-md-6">
    <div class="card info-card border-0 shadow-sm" style="--start-color:#198754;--end-color:#20c997;" onclick="card_two()">
      <div class="card-body">
        <h5 class="card-title">Outgoing <span class="text-muted">| Documents</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class='bx bx-archive-out'></i>
          </div>
          <div>
            <h3 id="load_outgoing_count" class="mb-0">0</h3>
            <small class="text-muted">Need Actions</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Returned -->
<div class="col-lg-2 col-md-6">
  <div class="card info-card border-0 shadow-sm" style="--start-color:#dc3545;--end-color:#ff4136;" onclick="card_three()">
    <div class="card-body">
      <h5 class="card-title">Returned <span class="text-muted">| Documents</span></h5>
      <div class="d-flex align-items-center">
        <div class="card-icon">
          <i class="bi bi-arrow-90deg-down"></i>
        </div>
        <div>
          <h3 id="load_returned_count" class="mb-0">0</h3>
          <small class="text-muted">records</small>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- ACTED -->
  <div class="col-lg-2 col-md-6">
    <div class="card info-card border-0 shadow-sm" style="--start-color:#ffc107;--end-color:#ffb347;" onclick="card_four()">
      <div class="card-body">
        <h5 class="card-title">Acted <span class="text-muted">| Records</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class="bi bi-folder-check"></i>
          </div>
          <div>
            <h3 id="load_acted_count" class="mb-0">0</h3>
            <small class="text-muted">documents</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-6">
    <div class="card info-card border-0 shadow-sm" style="--start-color:#007bff;--end-color:#17a2b8;"  onclick="card_five()">
      <div class="card-body">
        <h5 class="card-title">Delivered <span class="text-muted">| records</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class="bi bi-person-walking"></i>
          </div>
          <div>
            <h3 id="load_delivered_count" class="mb-0">0</h3>
            <small class="text-muted">document</small>
          </div>
        </div>
      </div>
    </div>
  </div> 


  <!-- ALL RECORDS -->
  <div class="col-lg-2 col-md-6">
    <div class="card info-card border-0 shadow-sm" style="--start-color:#198754;--end-color:#20c997;" onclick="card_six()">
      <div class="card-body">
        <h5 class="card-title">All <span class="text-muted">| Timeline</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon">
            <i class="bi bi-list-columns"></i>
          </div>
          <div>
            <h3 id="load_doc_count" class="mb-0">0</h3>
            <small class="text-muted">Documents</small>
          </div>
        </div>
      </div>
    </div>
  </div>   

</div>