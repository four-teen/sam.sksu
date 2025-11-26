<?php
session_start();
ob_start();
include '../db.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header('location:../logout.php');
    exit;
}

$getsystemconfig = "SELECT * FROM `tblconfig`";
$runsystemconfig = mysqli_query($conn, $getsystemconfig);
$rowconfig = mysqli_fetch_assoc($runsystemconfig);
$_SESSION['systemname'] = $rowconfig['systemname'];
$_SESSION['systemcopyright'] = $rowconfig['systemcopyright'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo $rowconfig['systemname']; ?> | Presidential Dashboard</title>

  <!-- Favicons -->
  <link href="../assets/img/logo.png" rel="icon">
  <link href="../assets/img/logo.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">

  <!-- Vendor CSS -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="mycss.css" rel="stylesheet">



</head>

<body onload="get_req();get_acted_counter()">
  <?php include 'header.php'; ?>


  <main id="main" class="main">


    <section class="section dashboard">
      <div class="container-fluid">
        <div class="row g-3 justify-content-center">

      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-arrow-left-square-fill text-danger"></i> Back</a></li>
          <li class="breadcrumb-item active">PAD</li>
        </ol>
      </nav>

          <!-- 🟦 Pending Actions -->
          <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="card info-card border-0 shadow-sm" style="--start-color:#007bff;--end-color:#17a2b8;" onclick="show_pending_actions()">
              <div class="card-body">
                <h5 class="card-title">Pending Actions <span class="text-muted">| Awaiting Review</span></h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon"><i class="bx bx-time"></i></div>
                  <div>
                    <h3 id="load_pending_actions" class="mb-0">0</h3>
                    <small class="text-muted">documents for action / approval</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

              <?php 

                // Color palette (start → end)
                $colors = [
                  1 => ['#0d6efd', '#0dcaf0'], // ACTIVITY DESIGN
                  2 => ['#6610f2', '#d63384'], // EMAIL
                  3 => ['#198754', '#20c997'], // HAND CARRY
                  4 => ['#fd7e14', '#ffc107'], // LOCAL COMMUNICATION
                  5 => ['#dc3545', '#fd7e14'], // OUTGOING COMMUNICATION
                  6 => ['#6f42c1', '#0d6efd'], // PROJECT PROPOSAL
                  7 => ['#20c997', '#198754'], // TRAVEL ORDER
                ];

                // Icons
                $icons = [
                  1 => "bi bi-file-earmark-text",
                  2 => "bi bi-envelope-paper",
                  3 => "bi bi-handbag",
                  4 => "bi bi-chat-left-text",
                  5 => "bi bi-send-check",
                  6 => "bi bi-journal-bookmark",
                  7 => "bi bi-airplane-engines",
                ];

                $typeofdocuments = "SELECT * FROM `tbltypeofdocuments`";
                $runtypedoc = mysqli_query($conn, $typeofdocuments);

                while($row = mysqli_fetch_assoc($runtypedoc)){

                  $id = $row['docid'];
                  $desc = $row['doctype_desc'];

                  // 🔵 COUNT RECORDS PER DOCUMENT TYPE
                  $count_sql = "SELECT COUNT(*) AS total 
                                FROM tbl_documents_registry 
                                WHERE type_of_documents = '$id'";
                  $count_run = mysqli_query($conn, $count_sql);
                  $count_row = mysqli_fetch_assoc($count_run);
                  $total = $count_row['total'];

                  // fallback colors & icons if none provided
                  $start_color = $colors[$id][0] ?? '#6c757d';
                  $end_color   = $colors[$id][1] ?? '#adb5bd';
                  $icon        = $icons[$id] ?? "bi bi-file-earmark";

                  echo '
                    <div class="col-xl-3 col-md-6 col-sm-12">
                      <div class="card info-card border-0 shadow-sm" 
                           style="--start-color:'.$start_color.';--end-color:'.$end_color.';" 
                          onclick="openDocumentType('.$id.')">

                        <div class="card-body">
                          <h5 class="card-title">'.$desc.'</h5>

                          <div class="d-flex align-items-center">
                            <div class="card-icon"><i class="'.$icon.'"></i></div>

                            <div>
                              <h3 id="doc_count_'.$id.'" class="mb-0">'.$total.'</h3>
                              <small class="text-muted">Transactions</small>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>
                  ';
                }

              ?>



          <!-- 🟩 Endorsed Documents -->
          <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="card info-card border-0 shadow-sm" style="--start-color:#198754;--end-color:#20c997;" onclick="show_endorsed_documents()">
              <div class="card-body">
                <h5 class="card-title">Acted Online <span class="text-muted">| Documents</span></h5>
                <div class="d-flex align-items-center">
                  <div class="card-icon"><i class="bi bi-chat-left-quote-fill"></i></div>
                  <div>
                    <h3 id="load_endorsed_documents" class="mb-0">0</h3>
                    <small class="text-muted">total signed documents</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>



      </div>
    </section>

  <br>
  <br>
  <br>
  <br>
  <br>
</main>

  <!-- Footer -->
  <footer id="footer" class="footer text-center" 
    style="
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      width: 100%;
      background: #ffffff;
      border-top: 1px solid rgba(0, 0, 0, 0.1);
      text-align: center;
      padding: 10px 0;
      font-size: 0.9rem;
      color: #555;
      margin: 0;
      z-index: 999;
      box-sizing: border-box;
    ">
    <div class="copyright" style="margin-bottom: 4px; margin-left: auto; margin-right: auto;">
      &copy; <strong><span><?php echo $rowconfig['systemname']; ?></span></strong> All Rights Reserved
    </div>
    <div class="credits" style="margin-left: auto; margin-right: auto;"> 
      <a href="#" style="color:#007bff; text-decoration:none;"><?php echo $rowconfig['systemcopyright']; ?></a> <br> 
      Managed by 
      <a href="https://www.facebook.com/breeve.antonio/" style="color:#007bff; text-decoration:none;">EOA</a>
    </div>
  </footer>

<!-- 🟦 Pending Actions Modal -->
<div class="modal fade" id="modalPendingActions" tabindex="-1" aria-labelledby="modalPendingActionsLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalPendingActionsLabel">
          <i class="bx bx-time"></i> Pending Actions
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="pending_list_container" style="max-height:70vh; overflow-y:auto; padding:5px;">
          <!-- cards load here -->
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
      </div>

    </div>
  </div>
</div>

<!-- 📄 Document Image Viewer Modal -->
<div class="modal fade" id="modalViewImage" tabindex="-1" aria-labelledby="modalViewImageLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalViewImageLabel"><i class="bi bi-image"></i> Document Attachment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i></button>
      </div>
      <div class="modal-body text-center" id="document_image_container">
        <div class="text-muted">Loading document image...</div>
      </div>
    </div>
  </div>
</div>

<!-- 🖋️ Image Annotation Modal -->
<div class="modal fade" id="annotateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-brush"></i> Annotate Image</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i>
      </div>
      <div class="modal-body text-center">
        <canvas id="annotateCanvas" style="max-width:100%; border:1px solid #ddd; cursor:crosshair;"></canvas>
        <div class="mt-3">
          <div class="d-flex justify-content-center gap-2 flex-wrap mb-2">
            <button class="btn btn-sm btn-outline-warning" id="clearCanvasBtn"><i class="bi bi-eraser"></i> Clear</button>
            <button class="btn btn-sm btn-success" id="saveAnnotatedBtn"><i class="bi bi-save"></i> Save</button>
          </div>

          <!-- 🗒️ Fixed Annotation Textbox with Add Button -->
          <div class="d-flex justify-content-center align-items-start gap-2 flex-wrap" style="max-width:650px; margin:auto;">
            <textarea id="bottomAnnotationBox" class="form-control form-control-sm flex-grow-1"
              placeholder="Type your remarks here..."
              style="resize:none; height:80px; background:rgba(255,255,255,0.9); border:1px solid #ccc; border-radius:6px;"></textarea>
            <button class="btn btn-sm btn-primary mt-2 mt-md-0" id="addAnnotationBtn">
              <i class="bi bi-plus-circle"></i> Add Annotation
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- 🟩 Acted Online Modal -->
<div class="modal fade" id="modalActedOnline" tabindex="-1" aria-labelledby="modalActedOnlineLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalActedOnlineLabel">
          <i class="bi bi-chat-left-quote-fill"></i> Acted Online
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="acted_list_container" style="max-height:70vh; overflow-y:auto; padding:5px;">
          <!-- acted docs load here -->
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
      </div>

    </div>
  </div>
</div>


<!-- 🟩 View Acted Document Modal -->
<div class="modal fade" id="modalViewActedImage" tabindex="-1" aria-labelledby="modalViewActedImageLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalViewActedImageLabel">
          <i class="bi bi-file-earmark-text"></i> View Acted Document
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center" id="acted_image_container">
        <div class="text-muted py-4">Loading document image...</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>


<!-- DOCUMENT TYPE MODAL -->
<div class="modal fade" id="docTypeModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="modal_title">Documents</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <div id="doc_types">Loading documents</div>
      </div>
      
    </div>
  </div>
</div>




  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../assets/sweetalert2.js"></script>
  <script src="../assets/js/main.js"></script>

  <script>

function openDocumentType(doc_id) {

  // Temporarily show loading
  $('#modal_title').text("Loading...");
  $('#docTypeModal').modal('show');

  // Load the document list
  $.ajax({
    url: "query_document_by_type.php",
    type: "POST",
    data: { 
        get_doc_type: 1,
        doc_id: doc_id
    },
    success: function (response) {
      $("#doc_types").html(response);
    }
  });

  // Load and update the modal title
  $.ajax({
    url: "query_document_by_type.php",
    type: "POST",
    data: { 
        get_doc_type_title: 1,
        doc_id: doc_id
    },
    success: function (title) {
      $('#modal_title').html(title);
    }
  });

}





// ====================================================================

// 🟩 View-only modal for Acted Online documents (NO annotate)
function viewActedImage(docId) {
  // ✅ Hide the current modal first to prevent stacking
  $('.modal').modal('hide');

  setTimeout(() => {
    $('#modalViewActedImage').modal('show');
    $('#acted_image_container').html('<div class="text-muted py-4">Loading document image...</div>');

    $.ajax({
      url: "query_president.php",
      type: "POST",
      data: { get_document_image_acted: 1, doc_id: docId },
      success: function (response) {
        // 🟢 Display image only — no edit options
        let content = `
          <div class="mb-3">${response}</div>
          <p class="text-muted small"><i class="bi bi-info-circle"></i> This document has already been acted upon and is view-only.</p>
        `;
        $('#acted_image_container').html(content);
      },
      error: function () {
        $('#acted_image_container').html('<div class="text-danger py-4">Error loading image.</div>');
      }
    });
  }, 400);
}

//acted call list of 15 documents
// 🟩 Show Acted Online Documents
function show_endorsed_documents() {
  $('#modalActedOnline').modal('show');

  $("#acted_list_container").html('<div class="text-center text-secondary py-3">Loading...</div>');

  $.ajax({
    url: "query_president.php",
    type: "POST",
    data: { load_acted_documents: 1 },
    success: function (response) {
      $("#acted_list_container").html(response);
    },
    error: function () {
      $("#acted_list_container").html('<div class="text-center text-danger py-3">Error loading data.</div>');
    }
  });
}


// ================= PRESIDENTIAL ACTION FUNCTIONS =================
// 🟩 PRESIDENT: Mark Document as Acted (with Remarks)
function markAsActed(docId) {
  // ✅ Temporarily remove focus trap from Bootstrap modal
  $(".modal").modal("hide");

  // ✅ Slight delay to allow Bootstrap modal backdrop to close fully
  setTimeout(() => {
    Swal.fire({
      title: "Confirm Action",
      html: `
        <div class="text-start">
          <p class="mb-2">You are about to mark this document as <strong>Acted</strong>.</p>
          <label for="actedRemarks" class="form-label small text-muted mb-1">Action Remarks:</label>
          <textarea id="actedRemarks" class="form-control" placeholder="Type your additional information here..." rows="4" style="border-radius:6px; resize:none;"></textarea>
        </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: "Submit Action",
      cancelButtonText: "Cancel",
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#6c757d",
      didOpen: () => {
        // ✅ Automatically focus the textarea when alert opens
        document.getElementById("actedRemarks").focus();
      },
      preConfirm: () => {
        const remarks = document.getElementById("actedRemarks").value.trim();
        if (!remarks) {
          Swal.showValidationMessage("Please provide remarks or action details.");
        }
        return remarks;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("query_president.php", {
          mark_acted: 1,
          doc_id: docId,
          remarks: result.value
        }, function (res) {
          get_req();
          Swal.fire({
            title: "Action Recorded!",
            text: res || "This document has been marked as acted.",
            icon: "success",
            confirmButtonColor: "#198754"
          });
          // ✅ Reopen the Pending Actions modal after action
          $("#modalPendingActions").modal("show");
          show_pending_actions();
        });
      } else {
        // ✅ If canceled, reopen modal too
        $("#modalPendingActions").modal("show");
      }
    });
  }, 400);
}



// ==============IMAGE ANNOTATION (Optimized Version) ===============================

let canvas, ctx, imgObj;
let currentImgPath = '';
let currentImgId = 0;

function openAnnotateModal(imgPath, imgId) {
  currentImgPath = imgPath;
  currentImgId = imgId;

  $('#annotateModal').modal('show');

  setTimeout(() => {
    canvas = document.getElementById('annotateCanvas');
    ctx = canvas.getContext('2d');
    imgObj = new Image();

    imgObj.onload = function () {
      canvas.width = imgObj.width;
      canvas.height = imgObj.height;
      ctx.drawImage(imgObj, 0, 0);
    };
    imgObj.src = imgPath;

    /* ===================== ANNOTATION FUNCTION ===================== */
    function renderBottomText() {
      const text = $("#bottomAnnotationBox").val().trim();
      if (text === "") return;

      const padding = 10;
      const lineHeight = 20;
      const maxWidth = canvas.width - 2 * padding;

      // 🔹 Split text into lines and wrap if necessary
      const paragraphs = text.split(/\r?\n/);
      ctx.font = "italic 15px Poppins, Arial";
      let lines = [];

      paragraphs.forEach((para) => {
        const words = para.split(" ");
        let currentLine = words[0];
        for (let i = 1; i < words.length; i++) {
          const testLine = currentLine + " " + words[i];
          if (ctx.measureText(testLine).width > maxWidth && i > 0) {
            lines.push(currentLine);
            currentLine = words[i];
          } else {
            currentLine = testLine;
          }
        }
        lines.push(currentLine);
      });

      // 🔹 Calculate box height based on total lines
      const boxHeight = lines.length * lineHeight + padding * 2;
      const y = canvas.height - boxHeight - 10;

      // 🔹 Draw semi-transparent box
      ctx.fillStyle = "rgba(200,200,200,0.8)";
      ctx.fillRect(padding, y, canvas.width - 2 * padding, boxHeight);
      ctx.strokeStyle = "rgba(100,100,100,0.4)";
      ctx.strokeRect(padding, y, canvas.width - 2 * padding, boxHeight);

      // 🔹 Render wrapped multi-line italic red text
      ctx.fillStyle = "red";
      ctx.textBaseline = "top";
      let textY = y + padding;
      lines.forEach((line) => {
        ctx.fillText(line, padding + 10, textY);
        textY += lineHeight;
      });
    }

    // ✅ Add annotation when clicking the “Add Annotation” button
    $("#addAnnotationBtn").off("click").on("click", function () {
      renderBottomText();
      $("#bottomAnnotationBox").val(""); // clear after adding
    });

  }, 300);
}

/* ===================== TOOLBAR BUTTONS ===================== */
$("#clearCanvasBtn").click(() => {
  if (imgObj) ctx.drawImage(imgObj, 0, 0);
});
$("#saveAnnotatedBtn").click(() => saveAnnotatedImage());

/* ===================== SAVE ANNOTATED IMAGE ===================== */
function saveAnnotatedImage() {
  const dataURL = canvas.toDataURL("image/png");
  $.ajax({
    url: "query_president.php",
    type: "POST",
    data: { image: dataURL, img_id: currentImgId },
    success: function (res) {
      Swal.fire({
        title: "Saved!",
        text: res,
        icon: "success",
        timer: 1500,
        showConfirmButton: false,
      });
      $("#annotateModal").modal("hide");
    },
    error: function (xhr) {
      Swal.fire("Error", xhr.responseText || "Unable to save image", "error");
    },
  });
}

//========== END OF IMAGE ANNOTATION ===============================================



// 🖼️ Load image related to clicked document
function viewDocumentImage(docId) {
  $('#modalViewImage').modal('show');
  $('#document_image_container').html('<div class="text-muted py-4">Loading document image...</div>');

  $.ajax({
    url: "query_president.php",
    type: "POST",
    data: { get_document_image: 1, doc_id: docId },
    success: function (response) {
      $('#document_image_container').html(response);
    },
    error: function () {
      $('#document_image_container').html('<div class="text-danger py-4">Error loading image.</div>');
    }
  });
}

function show_pending_actions() {
  $('#modalPendingActions').modal('show');

  $("#pending_list_container").html('<div class="text-center text-secondary py-3">Loading...</div>');

  $.ajax({
    url: "query_president.php",
    type: "POST",
    data: { load_pending_actions: 1 },
    success: function (response) {
      $("#pending_list_container").html(response);
    },
    error: function () {
      $("#pending_list_container").html('<div class="text-center text-danger py-3">Error loading data.</div>');
    }
  });
}




    
  function animateValue(id, start, end, duration) {
    const el = document.getElementById(id);
    if (!el) return;
    let startTime = null;
    function frame(timestamp) {
      if (!startTime) startTime = timestamp;
      const progress = Math.min((timestamp - startTime) / duration, 1);
      el.textContent = Math.floor(progress * (end - start) + start);
      if (progress < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

// 🔹 Load the count for the Pending Actions card
function get_req() {
  $.ajax({
    url: "query_president.php",
    type: "POST",
    data: { get_received_counter: 1 },
    success: function (response) {
      $("#load_pending_actions").text(response || 0);
    },
    error: function () {
      $("#load_pending_actions").text("0");
    }
  });
}

function get_acted_counter() {
  $.ajax({
    url: "query_president.php",
    type: "POST",
    data: { get_acted_counter: 1 },
    success: function (response) {
      $("#load_endorsed_documents").text(response || 0);
    },
    error: function () {
      $("#load_endorsed_documents").text("0");
    }
  });
}

  </script>
</body>
</html>
