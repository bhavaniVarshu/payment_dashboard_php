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
    $obj_admin->add_new_task($_POST, $user_role);
}

if (isset($_POST['start_task'])) {
  $task_id = $_POST['task_id'];
  $obj_admin->start_task($task_id);
}

if (isset($_POST['pause_task'])) {
  $task_id = $_POST['task_id'];
  $obj_admin->pause_task($task_id);
}

if (isset($_POST['resume_task'])) {
  $task_id = $_POST['task_id'];
  $obj_admin->resume_task($task_id);
}

if (isset($_POST['end_task'])) {
  $task_id = $_POST['task_id'];
  $obj_admin->end_task($task_id);
}

$page_name="Task_Info";
include("include/sidebar.php");
include("include/datatable.php");

?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog add-category-modal">
    
      <!-- Modal content-->
      <div class="modal-content rounded-0">
        <div class="modal-header rounded-0">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h2 class="modal-title text-center">Assign New Task</h2>
        </div>
        <div class="modal-body rounded-0">
          <div class="row">
            <div class="col-md-12">
              <form role="form" action="" method="post" autocomplete="off">
                <div class="form-horizontal">
                  <div class="form-group">
                    <label class="control-label text-p-reset">Task Title</label>
                    <div class="">
                      <input type="text" placeholder="Task Title" id="task_title" name="task_title" list="expense" class="form-control rounded-0" id="default" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label text-p-reset">Task Description</label>
                    <div class="">
                      <textarea name="task_description" id="task_description" placeholder="Text Deskcription" class="form-control rounded-0" rows="5" cols="5"></textarea>
                    </div>
                  </div>
                
                 
                  <div class="form-group">
                    <label class="control-label text-p-reset">Assign To</label>
                    <div class="">
                      <?php 
                        $sql = "SELECT user_id, fullname FROM tbl_admin WHERE user_role = 2";
                        $info = $obj_admin->manage_all_info($sql);   
                      ?>
                      <select class="form-control rounded-0" name="assign_to" id="aassign_to" required>
                        <option value="">Select Employee...</option>

                        <?php while($row = $info->fetch(PDO::FETCH_ASSOC)){ ?>
                        <option value="<?php echo $row['user_id']; ?>"><?php echo $row['fullname']; ?></option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
              
                  <div class="form-group">
                  </div>
                  <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-3">
                      <button type="submit" name="add_task_post" class="btn btn-primary rounded-0 btn-sm">Assign Task</button>
                    </div>
                    <div class="col-sm-3">
                      <button type="submit" class="btn btn-default rounded-0 btn-sm" data-dismiss="modal">Cancel</button>
                    </div>
                  </div>
                </div>
              </form> 
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>





    <div class="row">
      <div class="col-md-12">
        <div class="well well-custom rounded-0">
	  <div class="gap"></div>
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
          <div class="row">
            <div class="col-md-8">
              <div class="btn-group">
                <div class="btn-group">
                  <button class="btn btn-info btn-menu" data-toggle="modal" data-target="#myModal">Assign New Task</button>
                </div>

              </div>

            </div>

            
          </div>
          <center ><h3>Task Management Section</h3></center>
          <div class="gap"></div>

          <div class="gap"></div>

          <div class="table-responsive">
            <table id="printout" class="table table-codensed table-custom">
              <thead>
                <tr>
                  <th>S.no</th>
                  <th>Task Title</th>
                  <th>Assigned To</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                  <th>Taken Time</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>

              <?php 
                if($user_role == 1){
                  $sql = "SELECT a.*, b.fullname 
                        FROM task_info a
                        INNER JOIN tbl_admin b ON(a.t_user_id = b.user_id)
                        ORDER BY a.task_id DESC";
                }else{
                  $sql = "SELECT a.*, b.fullname 
                  FROM task_info a
                  INNER JOIN tbl_admin b ON(a.t_user_id = b.user_id)
                  WHERE a.t_user_id = $user_id
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
                        echo '<small class="label label-default border px-3">Completed <span class="glyphicon glyphicon-remove" ></small>';
                    } ?>
                    
                  </td>
                  <td>
    <?php if($row['status'] != 2 && $row['status'] != 1){ // Task is not completed ?>
        <form method="post" action="" style="display:inline;">
            <input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
            <button type="submit" name="start_task" class="btn btn-success btn-xs">Start</button>
        </form>
    <?php } ?>

    <?php if($row['status'] == 1){ // Task is in progress ?>
        <form action="" method="post" style="display:inline;">
            <input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
            <button type="submit" name="pause_task" class="btn btn-warning btn-xs">Pause</button>
        </form>
    <?php } elseif($row['status'] == 3) { // Task is paused ?>
        <form action="" method="post" style="display:inline;">
            <input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
            <button type="submit" name="resume_task" class="btn btn-info btn-xs">Resume</button>
        </form>
    <?php } ?>

    <?php if($row['status'] != 2){ // Task is not completed ?>
        <form method="post" action="" style="display:inline;">
            <input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
            <button type="submit" name="end_task" class="btn btn-danger btn-xs">End</button>
        </form>
    <?php } ?>

    <?php if($row['status'] != 2) { // Task is not completed ?>
        <a title="Update Task" href="edit-task.php?task_id=<?php echo $row['task_id']; ?>"><span class="glyphicon glyphicon-edit"></span></a>
    <?php } ?>
    <a title="View" href="task-details.php?task_id=<?php echo $row['task_id']; ?>"><span class="glyphicon glyphicon-folder-open"></span></a>
    <?php if($user_role == 1) { // Allow delete only if task is not completed ?>
        <a title="Delete" href="?delete_task=delete_task&task_id=<?php echo $row['task_id']; ?>" onclick=" return check_delete();"><span class="glyphicon glyphicon-trash"></span></a>
    <?php } ?>
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

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script type="text/javascript">
  flatpickr('#t_start_time', {
    enableTime: true
  });

  flatpickr('#t_end_time', {
    enableTime: true
  });

</script>


