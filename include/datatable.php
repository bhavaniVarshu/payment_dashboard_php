<?php 
    if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
$base_url = $protocol . "://".$_SERVER['SERVER_NAME'].'/' .(explode('/',$_SERVER['PHP_SELF'])[1]).'/';
?>
<!-- datatable.php -->
<!-- DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- XLSX JS for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- Buttons extension for DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.7/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.3.7/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.7/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.7/js/buttons.print.min.js"></script>

<!-- Custom CSS for Button Alignment -->
<style>
 .buttons-container {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;

  }
  .buttons-right {
    margin-left: 10px;
  }
</style>

<!-- DataTables Initialization and Custom Buttons -->

<?php $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d') ?>
<noscript>
    <div>
        <style>
            body{
                background-image:none !important;
            }
            .mb-0{
                margin:0px;
            }
        </style>
        <div style="line-height:1em">
        <h4 class="mb-0 text-center"><b>Employee Task Managament System</b></h4>
        <h4 class="mb-0 text-center"><b>Daily Task Report</b></h4>
        <div class="mb-0 text-center"><b>as of</b></div>
        <div class="mb-0 text-center"><b><?= date("F d, Y", strtotime($date)) ?></b></div>
        </div>
        <hr>
    </div>
</noscript>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>



<link href="./assets/report_css/jquery.dataTables.min.css" rel="stylesheet">
<link href="./assets/report_css/buttons.dataTables.min.css" rel="stylesheet">
<script src='./assets/report_js/jquery-1.12.3.js'></script>
<script src='./assets/report_js/jquery.dataTables.min.js'></script>
<script src='./assets/report_js/dataTables.buttons.min.js'></script>
<script src='./assets/report_js/buttons.flash.min.js'></script>
<script src='./assets/report_js/jszip.min.js'></script>
<script src='./assets/report_js/pdfmake.min.js'></script>
<script src='./assets/report_js/vfs_fonts.js'></script>
<script src='./assets/report_js/buttons.html5.min.js'></script>
<script src='./assets/report_js/buttons.print.min.js'></script>
<script src='../assets/report_js/jquery-ui.min.js'></script>
<link href="../assets/report_css/jquery-ui.css" rel="stylesheet">
<link href="../assets/report_css/jquery-ui.theme.css" rel="stylesheet">

<script>

$(document).ready(function () {
        // Initialize DataTable for #paymentTable
        var table = $('#printout').DataTable({
            dom: 'Bfrtip',
            buttons: ['csv', 'excel'],
            order: [], // Disable initial sorting
            searching: true,
            language: {
                search: "Search:"
            }
        });
    });
        </script>
