<?php
date_default_timezone_set('Asia/Kolkata');
require 'authentication.php'; // admin authentication check 

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
$user_role = $_SESSION['user_role'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}


if (isset($_POST['admin_check_in'])) {
    $aten_id = $_POST['aten_id'];
    $user_id = $_POST['user_id'];
    
    // If the attendance record doesn't exist, create it
    if (empty($aten_id)) {
        $obj_admin->add_punch_in(['user_id' => $user_id]);
    } else {
        $obj_admin->adminCheckIn($aten_id, $user_id);
    }
}

if(isset($_GET['delete_attendance'])){
  $action_id = $_GET['aten_id'];
  
  $sql = "DELETE FROM attendance_info WHERE aten_id = :id";
  $sent_po = "attendance-info.php";
  $obj_admin->delete_data_by_this_method($sql,$action_id,$sent_po);
}


if(isset($_POST['add_punch_in'])){
   $info = $obj_admin->add_punch_in($_POST);
}

if(isset($_POST['add_punch_out'])){
  $obj_admin->add_punch_out($_POST);
}

if(isset($_POST['handle_break'])){
  $obj_admin->handle_break($_POST);
}
$current_date = date('Y-m-d');
$current_time_obj = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$current_time = $current_time_obj->format('H:i:s');
$cutoff_time = '09:30:00';

$page_name="Attendance";
include("include/sidebar.php");
include("include/datatable.php");

//$info = "Hello World";
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">



    <div class="row">
      <div class="col-md-12">
        <div class="well well-custom">
          <div class="row">
	    <div class="col-md-8 ">
<div class="row">
            <div class="buttons-container">
            <div class="col-md-4">
                <input type="date" id="date" value="<?= $date ?>" class="form-control rounded-0">
            </div>
                  <button class="btn btn-primary btn-sm btn-menu buttons-right" type="button" id="filter"><i class="glyphicon glyphicon-filter"></i> Filter</button>
                  <button class="btn btn-success btn-sm btn-menu buttons-right" type="button" id="print"><i class="glyphicon glyphicon-print"></i> Print</button>
                  <button class="btn btn-info btn-sm btn-menu buttons-right" type="button" id="exportExcel">
        <i class="glyphicon glyphicon-download-alt"></i> Excel
    </button>
                </div>


          </div>
              <div class="btn-group">
                <?php 
               
               // Fetch attendance for the current day
              $current_date = date('Y-m-d');
              $current_time_obj = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
              $current_time = $current_time_obj->format('H:i:s');
              $cutoff_time = '09:30:00';
              $current_day = $current_time_obj->format('Y-m-d');


              $sql = "SELECT * FROM attendance_info 
                      WHERE atn_user_id = $user_id 
                      AND DATE(in_time) = '$current_date'";
              $info = $obj_admin->manage_all_info($sql);
              $attendance = $info->fetch(PDO::FETCH_ASSOC);

              $is_checked_in = !empty($attendance['in_time']);
              $is_checked_out = !empty($attendance['out_time']);
              if ($is_checked_in) {
                $in_time = new DateTime($attendance['in_time']);
                $in_day = $in_time->format('Y-m-d');
                if ($in_day === $current_day && !$is_checked_out) {
                    $status = 'Working';
                } elseif ($is_checked_out) {
                    $status = 'Completed';
                } else {
                    $status = 'Completed';
                }
            } else {
                $status = 'Not Checked In';
            }          
               // Fetch ongoing break status
              $ongoing_break = false;
              if ($is_checked_in && !$is_checked_out) {
                $break_query = $obj_admin->manage_all_info("
                    SELECT * FROM break_info 
                    WHERE aten_id = {$attendance['aten_id']} 
                      AND break_end_time IS NULL
                    ORDER BY break_id DESC 
                    LIMIT 1
                ");
                $current_break = $break_query->fetch(PDO::FETCH_ASSOC);
                if ($current_break) {
                    $ongoing_break = true;
                }
            }

             // Determine break button label
             if ($ongoing_break) {
              $break_button_label = 'Stop Break';
              $button_class = 'btn-danger';
          } else if ($is_checked_in && !$is_checked_out) {
              $break_button_label = 'Start Break';
              $button_class = 'btn-success'; 
          }
?>
<div class="btn-group btn-group-lg">
 <?php if (!$is_checked_in && $current_time < $cutoff_time) { ?>
                                <form method="post" action="">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" name="add_punch_in" class="btn btn-success">Check In</button>
                                </form>
                            <?php } ?>
   <!-- Break Button -->
   <?php if ($is_checked_in && !$is_checked_out) { ?>
                                <form method="post" action="">
                                    <input type="hidden" name="aten_id" value="<?php echo htmlspecialchars($attendance['aten_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" name="handle_break" class="btn <?php echo $button_class; ?> btn-xs" onclick="return confirm('Are you sure you want to <?php echo strtolower($break_button_label); ?>?');">
                                        <?php echo $break_button_label; ?>
                                    </button>
                                </form>
                            <?php } ?>

                            <!-- Clock Out Button -->
                            <?php if ($is_checked_in && !$is_checked_out) { ?>
                                <div class="clockout-wrapper" style="text-align: right;">
                                <form method="post" action="">
                                    <input type="hidden" name="aten_id" value="<?php echo htmlspecialchars($attendance['aten_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" name="add_punch_out" class="btn btn-primary" onclick="return confirm('Are you sure you want to check out?');">Check Out</button>
                                </form>
                                </div>
                            <?php } ?>
                            </div>
                         
                        </div>
                    </div>
                </div>

          <center><h3>Manage Atendance</h3>  </center>
          <div class="gap"></div>
          <div class="table-responsive">
            <table id="printout" class="table table-codensed table-custom">
              <thead>
                <tr>
                  <th>S.No</th>
                  <th>Name</th>
                  <th>In Time</th>
                  <th>Break Time</th>
                  <th>Out Time</th>
                  <th>Effective Duration</th>
                  <th>Status</th>
                  <?php if($user_role == 1){ ?>
                  <th>Action</th>
                  <?php } ?>
                </tr>
              </thead>
              <tbody>
                <?php 
                $current_date = date('Y-m-d');
                if ($user_role == 1) {
                  // Admin: Fetch all users' attendance for today
                  $sql = "SELECT b.user_id, b.fullname, a.in_time, a.out_time, a.total_duration, a.atn_user_id, a.aten_id 
                        FROM tbl_admin b 
                        LEFT JOIN attendance_info a ON b.user_id = a.atn_user_id 
                        AND DATE(a.in_time) = '$current_date' 
                        ORDER BY b.user_id";
              } else {
                  // User: Fetch user's attendance records
                  $sql = "SELECT a.*, b.fullname 
                          FROM attendance_info a 
                          LEFT JOIN tbl_admin b ON a.atn_user_id = b.user_id 
                          WHERE a.atn_user_id = $user_id 
                          ORDER BY a.aten_id DESC";
              }

              $info = $obj_admin->manage_all_info($sql);

                $serial = 1;
                $num_row = $info->rowCount();
                $colspan = ($user_role == 1) ? 8 : 7;
                
                if ($num_row == 0) {
                    echo '<tr><td colspan="' . $colspan . '">No Data found</td></tr>';
                }

                while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                    $in_time_date = ($row['in_time']) ? (new DateTime($row['in_time']))->format('Y-m-d') : null;
                    $is_today = ($in_time_date === $current_date);
                    $show_checkin = false;
                    $show_checkout = false;
                    $show_break = false;
                    $break_button_label = 'Start Break'; 

                    if ($user_role == 1) {
                        if (!$is_today || !$row['in_time']) {
                            $show_checkin = true;
                        }
                    } else {
                        if ($is_today) {
                            if (empty($row['out_time'])) {
                                $show_checkout = true;
                            }
                        }

                        if (!$is_today && $current_time < $cutoff_time) {
                            $query_today = $obj_admin->manage_all_info("SELECT * FROM attendance_info WHERE atn_user_id = $user_id AND DATE(in_time) = '$current_date'");
                            if ($query_today->rowCount() == 0) {
                                $show_checkin = true;
                            }
                        }

                        // Determine break button label based on break status
                        if ($row['in_time'] && !$row['out_time']) {
                            // Fetch the latest break status
                            $aten_id = $row['aten_id'];
                            $break_query = $obj_admin->manage_all_info("
                                SELECT * FROM break_info 
                                WHERE aten_id = $aten_id 
                                  AND break_end_time IS NULL
                                ORDER BY break_id DESC 
                                LIMIT 1
                            ");
                            $current_break = $break_query->fetch(PDO::FETCH_ASSOC);
                            
                            if ($current_break) {
                                $break_button_label = 'Stop Break';
                            } else {
                                $break_button_label = 'Start Break';
                            }
                            
                            $show_break = true;
                        }
                    }

                    // Calculate total break time
                    if ($user_role != 1 && $row['in_time']) {
                        $break_query_total = $obj_admin->manage_all_info("
                            SELECT SUM(TIME_TO_SEC(break_duration)) AS total_break_seconds 
                            FROM break_info 
                            WHERE aten_id = {$row['aten_id']}
                        ");
                        $break_total = $break_query_total->fetch(PDO::FETCH_ASSOC);
                        
                        if ($break_total && $break_total['total_break_seconds']) {
                            $total_break_seconds = $break_total['total_break_seconds'];
                            $hours = floor($total_break_seconds / 3600);
                            $minutes = floor(($total_break_seconds % 3600) / 60);
                            $seconds = $total_break_seconds % 60;
                            $total_break_time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                        } else {
                            $total_break_time = '---';
                        }
                    } else {
                        $total_break_time = '---';
                    }

                   // Calculate total duration minus break time
                   if ($row['total_duration'] == null && $row['in_time']) {
                    $dteStart = new DateTime($row['in_time']);
                    $dteEnd = ($row['out_time']) ? new DateTime($row['out_time']) : new DateTime('now', new DateTimeZone('Asia/Kolkata'));
                
                    // Make sure $dteEnd is after $dteStart to prevent negative durations
                    if ($dteEnd < $dteStart) {
                        $effective_duration = '00:00:00';  // Handle this case with default duration
                    } else {
                        // Calculate the total duration between in_time and out_time/current time
                        $dteDiff = $dteStart->diff($dteEnd);
                
                        // Convert total duration to seconds
                        $total_duration_in_seconds = ($dteDiff->h * 3600) + ($dteDiff->i * 60) + $dteDiff->s;
                
                        // Convert total break time to seconds
                        if ($total_break_time !== '---') {
                            list($bh, $bm, $bs) = explode(':', $total_break_time);
                            $break_duration_in_seconds = ($bh * 3600) + ($bm * 60) + $bs;
                        } else {
                            $break_duration_in_seconds = 0;
                        }
                
                        // Subtract break duration from total duration
                        $effective_duration_in_seconds = $total_duration_in_seconds - $break_duration_in_seconds;
                
                        // Prevent negative durations by ensuring minimum value is 0
                        $effective_duration_in_seconds = max($effective_duration_in_seconds, 0);
                
                        // Convert effective duration back to HH:MM:SS
                        $hours = floor($effective_duration_in_seconds / 3600);
                        $minutes = floor(($effective_duration_in_seconds % 3600) / 60);
                        $seconds = $effective_duration_in_seconds % 60;
                
                        $effective_duration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                    }
                } else {
                    $effective_duration = $row['total_duration'] ?: '---';
                }
                
                ?>
                <tr>
                    <td><?php echo $serial++; ?></td>
                    <td><?php echo htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $row['in_time'] ? htmlspecialchars($row['in_time'], ENT_QUOTES, 'UTF-8') : 'Not checked in'; ?></td>
                    <td><?php echo $total_break_time; ?></td>
                    <td><?php echo $row['out_time'] ? htmlspecialchars($row['out_time'], ENT_QUOTES, 'UTF-8') : '---'; ?></td>
                    <td><?php echo $effective_duration; ?></td>
                    <td><?php echo $status; ?></td>

                    <?php if($user_role == 1){ ?>
                        <td>
                            <?php
                                if ($show_checkin) {
                            ?>
                                <!-- Check-in button for admin -->
                                <form method="post" action="">
                                    <input type="hidden" name="aten_id" value="<?php echo $row['aten_id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                    <button type="submit" name="admin_check_in" class="btn btn-primary btn-xs" onclick="return confirm('Are you sure you want to check in this user?');">Check-in</button>
                                </form>
                            <?php 
                                }
                            ?>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="aten_id" value="<?php echo $row['aten_id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $row['atn_user_id']; ?>">
                            </form>
                            <a title="Delete" href="?delete_attendance=delete_attendance&aten_id=<?php echo $row['aten_id']; ?>" onclick="return check_delete();">
                                <span class="glyphicon glyphicon-trash"></span>
                            </a>
                        </td>
                    <?php } ?>
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

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!--
<script>
$(document).ready(function() {
    $('#printout').DataTable({
        "pageLength": 10, // Display 10 rows per page
        "lengthMenu": [ [10, 25, 50, 100], [10, 25, 50, 100] ], // Page length options
        "ordering": true, // Enable column sorting
        "searching": true, // Enable search
        "info": true, // Show table info
        "paging": true // Enable pagination
    });
});
</script>
-->
