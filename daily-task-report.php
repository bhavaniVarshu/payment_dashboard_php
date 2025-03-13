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

    <div class="row">
      <div class="col-md-12">
        <div class="well well-custom rounded-0">
	  <div class="gap"></div>
 <!-- <div class="row">
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

          </div> -->
          <center ><h3>Daily Task Report</h3></center>
          <div class="gap"></div>

          <div class="gap"></div>

          <div class="table-responsive" >
            <table id="printout" class="table table-codensed table-custom">
              <thead>
                <tr>
                  <th>S.No</th>
                  <th>Task Title</th>
                  <th>Assigned To</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                  <th>Taken Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>

              <?php 
                if($user_role == 1){
                  $sql = "SELECT a.*, b.fullname FROM task_info a INNER JOIN tbl_admin b ON(a.t_user_id = b.user_id) WHERE status!='0' ORDER BY a.task_id DESC";
                }else{
                  $sql = "SELECT a.*, b.fullname 
                  FROM task_info a
                  INNER JOIN tbl_admin b ON(a.t_user_id = b.user_id)
                  WHERE a.t_user_id = $user_id and ('{$date}' BETWEEN date(a.t_start_time) and date(a.t_end_time))
                  ORDER BY a.task_id DESC";
                } 
                
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
                  <td><?php echo $row['t_title']; ?></td>
                  <td><?php echo $row['fullname']; ?></td>
                  <td><?php echo $row['t_start_time']; ?></td>
                  <td><?php echo $row['t_end_time']; ?></td>
                  <td><?php echo $row['t_total_time']; ?></td>

                  <td>
                    <?php  if($row['status'] == 1){
                        // echo "In Progress <span style='color:#5bcad9;' class=' glyphicon glyphicon-refresh' >";
                        echo '<small class="label label-warning px-3">In Progress <span class="glyphicon glyphicon-refresh" ></small>';
                    }elseif($row['status'] == 2){
                        echo '<small class="label label-success px-3">Completed <span class="glyphicon glyphicon-ok" ></small>';
                        // echo "Completed <span style='color:#00af16;' class=' glyphicon glyphicon-ok' >";
                    }else{
                        echo '<small class="label label-default border px-3">In Completed <span class="glyphicon glyphicon-remove" ></small>';
                    } ?>
                    
                  </td>
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
