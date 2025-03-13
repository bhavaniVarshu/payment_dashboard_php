<?php 
    if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
$base_url = $protocol . "://".$_SERVER['SERVER_NAME'].'/' .(explode('/',$_SERVER['PHP_SELF'])[1]).'/';
?>
<?php
require 'authentication.php'; // admin authentication check 

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

// check admin
$user_role = $_SESSION['user_role'];


if(isset($_GET['delete_task'])){
  $action_id = $_GET['task_id'];
  
  $sql = "DELETE FROM task_info WHERE task_id = :id";
  $sent_po = "task-info.php";
  $obj_admin->delete_data_by_this_method($sql,$action_id,$sent_po);
}

if(isset($_POST['add_task_post'])){
    $obj_admin->add_new_task($_POST);
}

$page_name="Task_Info";
include("include/sidebar.php");
include("include/datatable.php");

// include('ems_header.php');


?>
<?php $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <div class="row">
      <div class="col-md-12">
        <div class="well well-custom rounded-0">
          <div class="gap"></div>
          <!-- <div class="row">
            <div class="col-md-4">
                <input type="date" id="date" value="<?= $date ?>" class="form-control rounded-0">
            </div>
            <div class="col-md-4">
                  <button class="btn btn-primary btn-sm btn-menu" type="button" id="filter"><i class="glyphicon glyphicon-filter"></i> Filter</button>
                  <button class="btn btn-success btn-sm btn-menu" type="button" id="print"><i class="glyphicon glyphicon-print"></i> Print</button>
                  <button class="btn btn-info btn-sm btn-menu" type="button" id="exportExcel">
        <i class="glyphicon glyphicon-download-alt"></i> Excel
    </button>

                </div>

            
          </div> -->
          <center ><h3>Daily Attendance Report</h3></center>
          <div class="gap"></div>

          <div class="gap"></div>
          <div class="table-responsive">
          <table class="table table-codensed table-custom" id="printout">
              <thead>
                <tr>
                  <th>S.N.</th>
                  <th>Name</th>
                  <th>In Time</th>
                  <th>Out Time</th>
                  <th>Break Time</th>
                  <th>Total Duration</th>
                  <th>Location</th>
                </tr>
              </thead>
              <tbody>

              <?php 
                  $sql = "SELECT a.aten_id, a.atn_user_id, a.in_time, a.out_time, a.ip_address, a.total_duration, b.fullname, COALESCE(SEC_TO_TIME(SUM(TIME_TO_SEC(c.break_duration))), '00:00:00') AS total_break_duration FROM attendance_info a LEFT JOIN tbl_admin b ON a.atn_user_id = b.user_id LEFT JOIN break_info c ON a.aten_id = c.aten_id GROUP BY a.aten_id, a.in_time, a.out_time, a.ip_address, a.total_duration, b.fullname ORDER BY a.aten_id DESC";
                  $info = $obj_admin->manage_all_info($sql);
                  $serial  = 1;
                  $num_row = $info->rowCount();
                  if($num_row==0){
                    echo '<tr><td colspan="7">No Data found</td></tr>';
                  }
                      while( $row = $info->fetch(PDO::FETCH_ASSOC) ){
              ?>
                <tr>
                  <td><?php echo $serial; $serial++; ?></td>
                  <td><?php echo $row['fullname']; ?></td>
                  <td><?php echo $row['in_time']; ?></td>
                  <td><?php echo $row['out_time']; ?></td>
                  <td><?php echo $row['total_break_duration']; ?></td>
                  <td>
                  <?php
  // Convert in_time and out_time to DateTime objects
  $dteStart = new DateTime($row['in_time']);
  
  if ($row['out_time'] == null) {
      // If out_time is NULL, use the current time
      $date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
      $dteEnd = $date;
  } else {
      $dteEnd = new DateTime($row['out_time']);
  }

  // Calculate total worked duration
  $dteDiff = $dteStart->diff($dteEnd);
  $worked_seconds = ($dteDiff->h * 3600) + ($dteDiff->i * 60) + $dteDiff->s; // Convert to seconds

  // Convert break_duration (stored as HH:MM:SS) to seconds
  $break_seconds = 0;
  if (!empty($row['total_break_duration'])) {
      list($hours, $minutes, $seconds) = explode(":", $row['total_break_duration']);
      $break_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
  }

  // Subtract break time from worked time
  $effective_seconds = max(0, $worked_seconds - $break_seconds);

  // Convert back to HH:MM:SS format
  $hours = floor($effective_seconds / 3600);
  $minutes = floor(($effective_seconds % 3600) / 60);
  $seconds = $effective_seconds % 60;

  echo sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
  ?> 

                
                  </td>
                                    <td><?php echo $row['ip_address']; ?></td>

                  </tr>
                  <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>


<?php

include("include/footer.php");



?>

