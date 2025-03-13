
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

$page_name="Attendance";
include("include/sidebar.php");
include("include/datatable.php");
?>
<div class="row">
    <div class="col-md-12">
        <div class="well well-custom">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="buttons-container">
                            <div class="col-md-4">
                                <input type="date" id="date" value="<?= $date ?>" class="form-control rounded-0">
                            </div>
                            <button class="btn btn-primary btn-sm btn-menu buttons-right" type="button" id="filter"><i class="glyphicon glyphicon-filter"></i> Filter</button>
                            <button class="btn btn-success btn-sm btn-menu buttons-right" type="button" id="print"><i class="glyphicon glyphicon-print"></i> Print</button>
                            <button class="btn btn-info btn-sm btn-menu buttons-right" type="button" id="exportExcel"><i class="glyphicon glyphicon-download-alt"></i> Excel</button>
                        </div>
                    </div>

                    <?php 
                    $current_date = date('Y-m-d');
                    $current_time = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format('H:i:s');
                    $cutoff_time = '09:30:00';

                    $attendance = getAttendance($user_id, $current_date);
                    $is_checked_in = !empty($attendance['in_time']);
                    $is_checked_out = !empty($attendance['out_time']);

                    // Determine status and break button
                    $status = getAttendanceStatus($attendance, $is_checked_in, $is_checked_out, $current_time);
                    $break_info = getBreakInfo($attendance, $is_checked_in, $is_checked_out);
                    ?>

                    <div class="btn-group btn-group-lg">
                        <?php if (!$is_checked_in && $current_time < $cutoff_time) { ?>
                            <form method="post" action="">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" name="add_punch_in" class="btn btn-success">Check In</button>
                            </form>
                        <?php } ?>

                        <?php if ($is_checked_in && !$is_checked_out) { ?>
                            <form method="post" action="">
                                <input type="hidden" name="aten_id" value="<?= htmlspecialchars($attendance['aten_id'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" name="handle_break" class="btn <?= $break_info['button_class'] ?> btn-xs" onclick="return confirm('Are you sure you want to <?= strtolower($break_info['label']) ?>?');">
                                    <?= $break_info['label'] ?>
                                </button>
                            </form>

                            <div class="clockout-wrapper" style="text-align: right;">
                                <form method="post" action="">
                                    <input type="hidden" name="aten_id" value="<?= htmlspecialchars($attendance['aten_id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" name="add_punch_out" class="btn btn-primary" onclick="return confirm('Are you sure you want to check out?');">Check Out</button>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <center><h3>Manage Attendance</h3></center>
            <div class="gap"></div>
            <div class="table-responsive">
                <table id="printout" class="table table-condensed table-custom">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Name</th>
                            <th>In Time</th>
                            <th>Break Time</th>
                            <th>Out Time</th>
                            <th>Effective Duration</th>
                            <th>Status</th>
                            <?php if ($user_role == 1) { ?>
                                <th>Action</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
    <?php 
    $info = fetchAttendance($user_role, $user_id, $current_date);
    $serial = 1;
    foreach ($info as $row) {
        $is_checked_in = !empty($row['in_time']);
        $is_checked_out = !empty($row['out_time']);
        
        $status = getAttendanceStatus($row, $is_checked_in, $is_checked_out, $current_time);
        
        $total_break_time = calculateBreakTime($row);
        $effective_duration = calculateEffectiveDuration($row, $total_break_time);
        $show_checkin = $show_checkout = $show_break = false;
        determineButtonVisibility($row, $show_checkin, $show_checkout, $show_break, $user_role, $current_date);
        ?>
        <tr>
            <td><?= $serial++ ?></td>
            <td><?= htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $row['in_time'] ? htmlspecialchars($row['in_time'], ENT_QUOTES, 'UTF-8') : 'Not checked in' ?></td>
            <td><?= $total_break_time ?></td>
            <td><?= $row['out_time'] ? htmlspecialchars($row['out_time'], ENT_QUOTES, 'UTF-8') : '---' ?></td>
            <td><?= $effective_duration ?></td>
            <td><?= $status ?></td>
            <?php if ($user_role == 1) { ?>
                <td>
                    <?php if ($show_checkin) { ?>
                        <form method="post" action="">
                            <input type="hidden" name="aten_id" value="<?= $row['aten_id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                            <button type="submit" name="admin_check_in" class="btn btn-primary btn-xs" onclick="return confirm('Are you sure you want to check in this user?');">Check-in</button>
                        </form>
                    <?php } ?>
                    <a title="Delete" href="?delete_attendance=delete_attendance&aten_id=<?= $row['aten_id'] ?>" onclick="return check_delete();">
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
//functions
function getAttendance($user_id, $current_date) {
    global $obj_admin;
    $sql = "SELECT * FROM attendance_info WHERE atn_user_id = $user_id AND DATE(in_time) = '$current_date'";
    return $obj_admin->manage_all_info($sql)->fetch(PDO::FETCH_ASSOC);
    
}

function getAttendanceStatus($attendance, $is_checked_in, $is_checked_out, $current_time) {
    $status = 'Not Checked In';

    if ($is_checked_in) {
        $in_time = new DateTime($attendance['in_time']);
        $in_day = $in_time->format('Y-m-d'); 
        $current_day = date('Y-m-d');
        if ($in_day === $current_day) {
            if (!$is_checked_out) {
                $status = 'Working'; 
            } else {
                $status = 'Completed'; // Checked out on the same day
            }
        } else {
            // For previous days
            if ($is_checked_out) {
                $status = 'Completed'; // Proper check-out for previous day
            } else {
                $status = 'Not Proper Checkout'; // No checkout for a previous day
            }
        }
    }
    

    return $status;
}
function getBreakInfo($attendance, $is_checked_in, $is_checked_out) {
    global $obj_admin;
    $ongoing_break = false;
    if ($is_checked_in && !$is_checked_out) {
        $break_query = $obj_admin->manage_all_info("
            SELECT * FROM break_info 
            WHERE aten_id = {$attendance['aten_id']} 
            AND break_end_time IS NULL
            LIMIT 1
        ");
        $ongoing_break = $break_query->fetch(PDO::FETCH_ASSOC);
    }
    return $ongoing_break ? ['label' => 'Stop Break', 'button_class' => 'btn-danger'] : ['label' => 'Start Break', 'button_class' => 'btn-success'];
}

function fetchAttendance($user_role, $user_id, $current_date) {
    global $obj_admin;
    $sql = $user_role == 1
        ? "SELECT b.user_id, b.fullname, a.in_time, a.out_time, a.total_duration, a.atn_user_id, a.aten_id 
           FROM tbl_admin b 
           LEFT JOIN attendance_info a ON b.user_id = a.atn_user_id 
           AND DATE(a.in_time) = '$current_date' 
           ORDER BY b.user_id"
        : "SELECT a.*, b.fullname 
           FROM attendance_info a 
           LEFT JOIN tbl_admin b ON a.atn_user_id = b.user_id 
           WHERE a.atn_user_id = $user_id 
           ORDER BY a.aten_id DESC";
    return $obj_admin->manage_all_info($sql);
}


function calculateBreakTime($row) {
    global $obj_admin;
    if (empty($row['aten_id'])) {
        return '00:00:00';
    }
    $break_query_total = $obj_admin->manage_all_info("
        SELECT SUM(TIME_TO_SEC(break_duration)) AS total_break_seconds 
        FROM break_info 
        WHERE aten_id = {$row['aten_id']}
    ");

    if ($break_query_total) {
        $break_total = $break_query_total->fetch(PDO::FETCH_ASSOC);
        if ($break_total && $break_total['total_break_seconds']) {
            $hours = floor($break_total['total_break_seconds'] / 3600);
            $minutes = floor(($break_total['total_break_seconds'] % 3600) / 60);
            $seconds = $break_total['total_break_seconds'] % 60;
            return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }
    }
    return '00:00:00';
}


function calculateEffectiveDuration($row, $total_break_time) {
    $timezone = new DateTimeZone('Asia/Kolkata');
        $in_time = !empty($row['in_time']) ? new DateTime($row['in_time'], $timezone) : null;
    $out_time = !empty($row['out_time']) ? new DateTime($row['out_time'], $timezone) : new DateTime('now', $timezone); // Use current time if out_time is NULL
    if ($in_time) {
        $interval = $in_time->diff($out_time);
        $effective_duration = $interval->format('%H:%I:%S');
                if ($total_break_time !== '00:00:00') {
            $effective_duration = adjustDuration($effective_duration, $total_break_time);
        }
        return $effective_duration;
    }
    return '00:00:00';
}


function adjustDuration($duration, $break_time) {
    list($hours, $minutes, $seconds) = explode(':', $duration);
    $durationSeconds = $hours * 3600 + $minutes * 60 + $seconds;

    list($breakHours, $breakMinutes, $breakSeconds) = explode(':', $break_time);
    $breakSecondsTotal = $breakHours * 3600 + $breakMinutes * 60 + $breakSeconds;
    $adjustedSeconds = $durationSeconds - $breakSecondsTotal;
    if ($adjustedSeconds < 0) {
        $adjustedSeconds = 0;  // Set to 0 if subtraction results in negative
    }

    // Convert back to 'H:i:s' format
    $adjustedHours = floor($adjustedSeconds / 3600);
    $adjustedMinutes = floor(($adjustedSeconds % 3600) / 60);
    $adjustedSeconds = $adjustedSeconds % 60;

    return sprintf('%02d:%02d:%02d', $adjustedHours, $adjustedMinutes, $adjustedSeconds);
}
function determineButtonVisibility($row, &$show_checkin, &$show_checkout, &$show_break, $user_role, $current_date) {
    $is_checked_in = !empty($row['in_time']);
    $is_checked_out = !empty($row['out_time']);

    if ($user_role == 1) {
        $show_checkin = !$is_checked_in && date('Y-m-d') === $current_date;
        $show_checkout = $is_checked_in && !$is_checked_out;
    }
}


?>


