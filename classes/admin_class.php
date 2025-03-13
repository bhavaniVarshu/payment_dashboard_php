<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Admin_Class
{	

/* -------------------------set_database_connection_using_PDO---------------------- */
private $db;

public function __construct() { 
	$host_name = 'localhost';
	$user_name = 'root';
	$password = '';
	$db_name = 'etms_db';

	try {
		$connection = new PDO("mysql:host={$host_name};dbname={$db_name}", $user_name, $password);
		$this->db = $connection; // connection established
	} catch (PDOException $message) {
		echo $message->getMessage();
	}
}

/* ---------------------- test_form_input_data ----------------------------------- */
	
	public function test_form_input_data($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
	return $data;
	}

 
/* ---------------------- Admin Login Check ----------------------------------- */

    public function admin_login_check($data) {
        
        $upass = $this->test_form_input_data(md5($data['admin_password']));
		$username = $this->test_form_input_data($data['username']);
        try
       {
          $stmt = $this->db->prepare("SELECT * FROM tbl_admin WHERE username=:uname AND password=:upass LIMIT 1");
          $stmt->execute(array(':uname'=>$username, ':upass'=>$upass));
          $userRow=$stmt->fetch(PDO::FETCH_ASSOC);
          if($stmt->rowCount() > 0)
          {
          		session_start();
	            $_SESSION['admin_id'] = $userRow['user_id'];
	            $_SESSION['name'] = $userRow['fullname'];
	            $_SESSION['security_key'] = 'rewsgf@%^&*nmghjjkh';
	            $_SESSION['user_role'] = $userRow['user_role'];
	            $_SESSION['temp_password'] = $userRow['temp_password'];

          		if($userRow['temp_password'] == null){
	                header('Location: attendance-info.php');
          		}else{
          			header('Location: changePasswordForEmployee.php');
          		}
                
             
          }else{
			  $message = 'Invalid user name or Password';
              return $message;
		  }
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }	
		
    }



    public function change_password_for_employee($data){
    	$password  = $this->test_form_input_data($data['password']);
		$re_password = $this->test_form_input_data($data['re_password']);

		$user_id = $this->test_form_input_data($data['user_id']);
		$final_password = md5($password);
		$temp_password = '';

		if($password == $re_password){
			try{
				$update_user = $this->db->prepare("UPDATE tbl_admin SET password = :x, temp_password = :y WHERE user_id = :id ");

				$update_user->bindparam(':x', $final_password);
				$update_user->bindparam(':y', $temp_password);
				$update_user->bindparam(':id', $user_id);
				$update_user->execute();



				$stmt = $this->db->prepare("SELECT * FROM tbl_admin WHERE user_id=:id LIMIT 1");
		          $stmt->execute(array(':id'=>$user_id));
		          $userRow=$stmt->fetch(PDO::FETCH_ASSOC);

		          if($stmt->rowCount() > 0){
			          		session_start();
				            $_SESSION['admin_id'] = $userRow['user_id'];
				            $_SESSION['name'] = $userRow['fullname'];
				            $_SESSION['security_key'] = 'rewsgf@%^&*nmghjjkh';
				            $_SESSION['user_role'] = $userRow['user_role'];
				            $_SESSION['temp_password'] = $userRow['temp_password'];

				            header('Location: attendance-info.php');
			          }

			}catch (PDOException $e) {
				echo $e->getMessage();
			}

		}else{
			$message = 'Sorry !! Password Can not match';
            return $message;
		}

		
    }


/* -------------------- Admin Logout ----------------------------------- */

    public function admin_logout() {
        
        session_start();
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['security_key']);
        unset($_SESSION['user_role']);
        header('Location: index.php');
    }

/*----------- add_new_user--------------*/

	public function add_new_user($data){
		$user_fullname  = $this->test_form_input_data($data['em_fullname']);
		$user_username = $this->test_form_input_data($data['em_username']);
		$user_email = $this->test_form_input_data($data['em_email']);
		$temp_password = rand(000000001,10000000);
		$user_password = $this->test_form_input_data(md5($temp_password));
		$user_role = 2;
		try{
			$sqlEmail = "SELECT email FROM tbl_admin WHERE email = '$user_email' ";
			$query_result_for_email = $this->manage_all_info($sqlEmail);
			$total_email = $query_result_for_email->rowCount();

			$sqlUsername = "SELECT username FROM tbl_admin WHERE username = '$user_username' ";
			$query_result_for_username = $this->manage_all_info($sqlUsername);
			$total_username = $query_result_for_username->rowCount();

			if($total_email != 0 && $total_username != 0){
				$message = "Email and Password both are already taken";
            	return $message;

			}elseif($total_username != 0){
				$message = "Username Already Taken";
            	return $message;

			}elseif($total_email != 0){
				$message = "Email Already Taken";
            	return $message;

			}else{
				$add_user = $this->db->prepare("INSERT INTO tbl_admin (fullname, username, email, password, temp_password, user_role) VALUES (:x, :y, :z, :a, :b, :c) ");

				$add_user->bindparam(':x', $user_fullname);
				$add_user->bindparam(':y', $user_username);
				$add_user->bindparam(':z', $user_email);
				$add_user->bindparam(':a', $user_password);
				$add_user->bindparam(':b', $temp_password);
				$add_user->bindparam(':c', $user_role);

				$add_user->execute();
			}


		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


/* ---------update_user_data----------*/

	public function update_user_data($data, $id){
		$user_fullname  = $this->test_form_input_data($data['em_fullname']);
		$user_username = $this->test_form_input_data($data['em_username']);
		$user_email = $this->test_form_input_data($data['em_email']);
		try{
			$update_user = $this->db->prepare("UPDATE tbl_admin SET fullname = :x, username = :y, email = :z WHERE user_id = :id ");

			$update_user->bindparam(':x', $user_fullname);
			$update_user->bindparam(':y', $user_username);
			$update_user->bindparam(':z', $user_email);
			$update_user->bindparam(':id', $id);
			
			$update_user->execute();

			$_SESSION['update_user'] = 'update_user';

			header('Location: admin-manage-user.php');
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


/* ------------update_admin_data-------------------- */

	public function update_admin_data($data, $id){
		$user_fullname  = $this->test_form_input_data($data['em_fullname']);
		$user_username = $this->test_form_input_data($data['em_username']);
		$user_email = $this->test_form_input_data($data['em_email']);

		try{
			$update_user = $this->db->prepare("UPDATE tbl_admin SET fullname = :x, username = :y, email = :z WHERE user_id = :id ");

			$update_user->bindparam(':x', $user_fullname);
			$update_user->bindparam(':y', $user_username);
			$update_user->bindparam(':z', $user_email);
			$update_user->bindparam(':id', $id);
			
			$update_user->execute();

			header('Location: manage-admin.php');
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


/* ------update_user_password------------------*/
	
	public function update_user_password($data, $id){
		$employee_password  = $this->test_form_input_data(md5($data['employee_password']));
		
		try{
			$update_user_password = $this->db->prepare("UPDATE tbl_admin SET password = :x WHERE user_id = :id ");

			$update_user_password->bindparam(':x', $employee_password);
			$update_user_password->bindparam(':id', $id);
			
			$update_user_password->execute();

			$_SESSION['update_user_pass'] = 'update_user_pass';

			header('Location: admin-manage-user.php');
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}




/* -------------admin_password_change------------*/

	public function admin_password_change($data, $id){
		$admin_old_password  = $this->test_form_input_data(md5($data['admin_old_password']));
		$admin_new_password  = $this->test_form_input_data(md5($data['admin_new_password']));
		$admin_cnew_password  = $this->test_form_input_data(md5($data['admin_cnew_password']));
		$admin_raw_password = $this->test_form_input_data($data['admin_new_password']);
		
		try{

			// old password matching check 

			$sql = "SELECT * FROM tbl_admin WHERE user_id = '$id' AND password = '$admin_old_password' ";

			$query_result = $this->manage_all_info($sql);

			$total_row = $query_result->rowCount();
			$all_error = '';
			if($total_row == 0){
				$all_error = "Invalid old password";
			}
			

			if($admin_new_password != $admin_cnew_password ){
				$all_error .= '<br>'."New and Confirm New password do not match";
			}

			$password_length = strlen($admin_raw_password);

			if($password_length < 6){
				$all_error .= '<br>'."Password length must be more then 6 character";
			}

			if(empty($all_error)){
				$update_admin_password = $this->db->prepare("UPDATE tbl_admin SET password = :x WHERE user_id = :id ");

				$update_admin_password->bindparam(':x', $admin_new_password);
				$update_admin_password->bindparam(':id', $id);
				
				$update_admin_password->execute();

				$_SESSION['update_user_pass'] = 'update_user_pass';

				header('Location: admin-manage-user.php');

			}else{
				return $all_error;
			}

			
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}




	/* =================Task Related===================== */

	public function add_new_task($data, $user_role, $current_user_id){
		// Data insert   
		$task_title = $this->test_form_input_data($data['task_title']);
		$task_description = $this->test_form_input_data($data['task_description']);
		$assign_to = $this->test_form_input_data($data['assign_to']);
		try {
		$add_task = $this->db->prepare("INSERT INTO task_info (t_title, t_description, t_user_id, assigned_by) VALUES (:x, :y, :b, :c)");
			$add_task->bindparam(':x', $task_title);
			$add_task->bindparam(':y', $task_description);
			$add_task->bindparam(':b', $assign_to);
			$add_task->bindparam(':c', $current_user_id); // Bind the current user's ID
		
			$add_task->execute();
		
			echo "<script type='text/javascript'>alert('Task added successfully');</script>";
			header('Location: task-info.php');
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	


	public function update_task_info($data, $task_id, $user_role){
		$task_title  = $this->test_form_input_data($data['task_title']);
		$task_description = $this->test_form_input_data($data['task_description']);
		$status = $this->test_form_input_data($data['status']);
	
		if($user_role == 1){
			$assign_to = $this->test_form_input_data($data['assign_to']);
		} else {
			$sql = "SELECT * FROM task_info WHERE task_id='$task_id' ";
			$info = $this->manage_all_info($sql);
			$row = $info->fetch(PDO::FETCH_ASSOC);
			$assign_to = $row['t_user_id'];
		}
	
		try {
			$update_task = $this->db->prepare("UPDATE task_info SET t_title = :x, t_description = :y, t_user_id = :b, status = :c WHERE task_id = :id ");
			$update_task->bindparam(':x', $task_title);
			$update_task->bindparam(':y', $task_description);
			$update_task->bindparam(':b', $assign_to);
			$update_task->bindparam(':c', $status);
			$update_task->bindparam(':id', $task_id);
	
			$update_task->execute();
	
			echo "<script type='text/javascript'>alert('Task updated successfully');</script>";
			header('Location: task-info.php');
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function start_task($task_id) {
		$date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$start_time = $date->format('Y-m-d H:i:s');
	
		try {
			$update_task = $this->db->prepare("UPDATE task_info SET t_start_time = :start_time, t_pause_duration = '00:00:00', status = 1 WHERE task_id = :task_id");
			$update_task->bindParam(':start_time', $start_time);
			$update_task->bindParam(':task_id', $task_id);
			$update_task->execute();
	
			header('Location: task-info.php');
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public function pause_task($task_id) {
		$date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$pause_time = $date->format('Y-m-d H:i:s');
	
		try {
			// Fetch relevant task data
			$query = $this->db->prepare("
				SELECT t_start_time, COALESCE(t_pause_duration, '00:00:00') AS t_pause_duration
				FROM task_info 
				WHERE task_id = :task_id
			");
			$query->bindParam(':task_id', $task_id);
			$query->execute();
			$result = $query->fetch(PDO::FETCH_ASSOC);
	
			if (!$result) {
				throw new Exception("Task not found.");
			}
	
			// Calculate new pause duration
			$start_time = new DateTime($result['t_start_time']);
			$pause_time_obj = new DateTime($pause_time);
			$new_pause_duration_seconds = $pause_time_obj->getTimestamp() - $start_time->getTimestamp();
	
			// Parse existing pause duration
			sscanf($result['t_pause_duration'], "%d:%d:%d", $pause_hours, $pause_minutes, $pause_seconds);
			$existing_pause_seconds = ($pause_hours * 3600) + ($pause_minutes * 60) + $pause_seconds;
	
			// Total pause duration in seconds
			$total_pause_seconds = $existing_pause_seconds + $new_pause_duration_seconds;
	
			// Convert total pause duration back to HH:MM:SS
			$t_pause_duration = gmdate('H:i:s', $total_pause_seconds);
	
			// Update task with the pause time and total pause duration
			$update_task = $this->db->prepare("
				UPDATE task_info 
				SET t_pause_time = :pause_time, 
					t_pause_duration = :t_pause_duration, 
					status = 3 
				WHERE task_id = :task_id
			");
			$update_task->bindParam(':pause_time', $pause_time);
			$update_task->bindParam(':t_pause_duration', $t_pause_duration);
			$update_task->bindParam(':task_id', $task_id);
			$update_task->execute();
	
			header('Location: task-info.php');
			exit;
		} catch (Exception $e) {
			error_log("Error in pause_task: " . $e->getMessage());
			echo "<script>alert('An error occurred while pausing the task. Please try again.');</script>";
		}
	}
	
	public function resume_task($task_id) {
		$date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$resume_time = $date->format('Y-m-d H:i:s');
	
		try {
			$update_task = $this->db->prepare("UPDATE task_info SET t_resume_time = :resume_time, status = 1 WHERE task_id = :task_id");
			$update_task->bindParam(':resume_time', $resume_time);
			$update_task->bindParam(':task_id', $task_id);
			$update_task->execute();
	
			header('Location: task-info.php');
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
/*	
	public function end_task($task_id) {
		$date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$end_time = $date->format('Y-m-d H:i:s');
	
		try {
			// Retrieve start time, total time, and pause duration
			$query = $this->db->prepare("SELECT t_start_time, t_total_time, t_pause_duration FROM task_info WHERE task_id = :task_id");
			$query->bindParam(':task_id', $task_id);
			$query->execute();
			$result = $query->fetch(PDO::FETCH_ASSOC);
	
			$start_time = new DateTime($result['t_start_time']);
			$end_time_obj = new DateTime($end_time);
	
			// Calculate task duration from start/resume to end
			$task_duration = $start_time->diff($end_time_obj);
	
			// Split the pause duration into hours, minutes, and seconds
			list($pause_hours, $pause_minutes, $pause_seconds) = explode(':', $result['t_pause_duration']);
	
			// Create a DateInterval object for the pause duration
			$pause_interval = new DateInterval("PT{$pause_hours}H{$pause_minutes}M{$pause_seconds}S");
	
			// Subtract pause duration from task duration
			$task_duration_seconds = ($task_duration->h * 3600 + $task_duration->i * 60 + $task_duration->s) - 
									  ($pause_hours * 3600 + $pause_minutes * 60 + $pause_seconds);
			
			$active_duration = gmdate('H:i:s', $task_duration_seconds);
	
			// Add active duration to existing total time
			$existing_total_time_seconds = strtotime($result['t_total_time']) - strtotime('TODAY');
			$total_time_seconds = $existing_total_time_seconds + $task_duration_seconds;
			$total_time = gmdate('H:i:s', $total_time_seconds);
	
			// Update the task with the end time and total time
			$update_task = $this->db->prepare("UPDATE task_info SET t_end_time = :end_time, t_total_time = :total_time, status = 2 WHERE task_id = :task_id");
			$update_task->bindParam(':end_time', $end_time);
			$update_task->bindParam(':total_time', $total_time);
			$update_task->bindParam(':task_id', $task_id);
			$update_task->execute();
	
			header('Location: task-info.php');
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	
	
 */
public function end_task($task_id) {
    $date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $end_time = $date->format('Y-m-d H:i:s');

    try {
        // Fetch relevant task data
        $query = $this->db->prepare("
            SELECT t_start_time, t_total_time, COALESCE(t_pause_duration, '00:00:00') AS t_pause_duration
            FROM task_info
            WHERE task_id = :task_id
        ");
        $query->bindParam(':task_id', $task_id);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Task not found.");
        }

        // Parse task times
        $start_time = new DateTime($result['t_start_time']);
        $end_time_obj = new DateTime($end_time);

        // Calculate task duration
        $task_duration_seconds = $end_time_obj->getTimestamp() - $start_time->getTimestamp();

        // Parse pause duration to seconds
        sscanf($result['t_pause_duration'], "%d:%d:%d", $pause_hours, $pause_minutes, $pause_seconds);
        $pause_duration_seconds = ($pause_hours * 3600) + ($pause_minutes * 60) + $pause_seconds;

        // Calculate active duration
        $active_duration_seconds = max(0, $task_duration_seconds - $pause_duration_seconds);
        $active_duration = gmdate('H:i:s', $active_duration_seconds);

        // Parse existing total time to seconds
        sscanf($result['t_total_time'], "%d:%d:%d", $total_hours, $total_minutes, $total_seconds);
        $existing_total_time_seconds = ($total_hours * 3600) + ($total_minutes * 60) + $total_seconds;

        // Update total time
        $total_time_seconds = $existing_total_time_seconds + $active_duration_seconds;
        $total_time = gmdate('H:i:s', $total_time_seconds);

        // Update database
        $update_task = $this->db->prepare("
            UPDATE task_info 
            SET t_end_time = :end_time,
                t_total_time = :total_time,
                status = 2
            WHERE task_id = :task_id
        ");
        $update_task->bindParam(':end_time', $end_time);
        $update_task->bindParam(':total_time', $total_time);
        $update_task->bindParam(':task_id', $task_id);
        $update_task->execute();

        header('Location: task-info.php');
        exit;
    } catch (Exception $e) {
        error_log("Error in end_task: " . $e->getMessage());
        echo "<script>alert('An error occurred while ending the task. Please try again.');</script>";
    }
}




	
		
		/* =================Attendance Related===================== */

		public function adminCheckIn($aten_id, $user_id) {
			// Set the current check-in time
			$date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$new_check_in_time = $date->format('Y-m-d H:i:s');
		
			try {
				// Update the check-in time in the database for this attendance ID
				$updateCheckIn = $this->db->prepare("
					UPDATE attendance_info 
					SET in_time = :new_check_in_time 
					WHERE aten_id = :aten_id
				");
				$updateCheckIn->bindParam(':new_check_in_time', $new_check_in_time);
				$updateCheckIn->bindParam(':aten_id', $aten_id);
				$updateCheckIn->execute();
				var_dump($updateCheckIn->rowCount()); 
				echo "<script>alert('Check-In time updated successfully.');</script>";
				header('Location: attendance-info.php'); // Redirect to refresh the page
				exit;
			} catch (PDOException $e) {
				echo "Error: " . $e->getMessage();
			}
		}
		
		public function add_punch_in($data) {
			// Set timezone
			$date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
			$user_id = $this->test_form_input_data($data['user_id']);
			$user_role = $_SESSION['user_role']; // Fetch the user role
			$punch_in_time = $date->format('Y-m-d H:i:s');
			$current_date = $date->format('Y-m-d');
		
			// Get the IP address
			$ip_address = $this->getUserIP();
		
			// Extract hour and minute from punch-in time
			$punchInHour = (int) $date->format('H');
			$punchInMinute = (int) $date->format('i');
		
			// Define cutoff hour and minute for check-in
			$cutoffHour = 9;
			$cutoffMinute = 30;
		
			// Skip the cutoff check if the user is an admin
			if ($user_role != 1 && ($punchInHour > $cutoffHour || ($punchInHour === $cutoffHour && $punchInMinute > $cutoffMinute))) {
				echo "<script>alert('Check In is only allowed before 9:30 AM.');</script>";
				return;
			}
		
			// Check if the user already checked in today
			$query = $this->db->prepare("
				SELECT * FROM attendance_info 
				WHERE atn_user_id = :user_id 
				AND DATE(in_time) = :current_date
			");
			$query->bindParam(':user_id', $user_id);
			$query->bindParam(':current_date', $current_date);
			$query->execute();
		
			if ($query->rowCount() > 0) {
				echo "<script>alert('You have already Checked In today.');</script>";
				return;
			}
		
			try {
				// Insert a new attendance record with IP address
				$add_attendance = $this->db->prepare("
					INSERT INTO attendance_info (atn_user_id, in_time, ip_address) 
					VALUES (:user_id, :punch_in_time, :ip_address)
				");
				$add_attendance->bindParam(':user_id', $user_id);
				$add_attendance->bindParam(':punch_in_time', $punch_in_time);
				$add_attendance->bindParam(':ip_address', $ip_address);
				$add_attendance->execute();
		
				header('Location: attendance-info.php');
				exit;
			} catch (PDOException $e) {
				// Log the error instead of displaying it to the user
				error_log("Error in add_punch_in: " . $e->getMessage());
				echo "<script>alert('An error occurred while Checking in. Please try again later.');</script>";
			}
		}
		
		// Function to get the real user IP address
		private function getUserIP() {
			// Check for valid IP from HTTP headers
			if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
				return $_SERVER['HTTP_CLIENT_IP'];
			} else
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				// Get the first valid IP from the list (if multiple proxies)
				$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($ips as $ip) {
					$ip = trim($ip); // Remove spaces
					if (filter_var($ip, FILTER_VALIDATE_IP)) {
						return $ip;
					}
				}
			}
			if (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
				return $_SERVER['REMOTE_ADDR'];
			}
			return null;
		}

		

		public function add_punch_out($data) {
			$aten_id = $data['aten_id'];
		
			// Fetch punch-in time for the given attendance record
			$query = $this->db->prepare("SELECT in_time FROM attendance_info WHERE aten_id = :aten_id");
			$query->bindParam(':aten_id', $aten_id);
			$query->execute();
			$result = $query->fetch(PDO::FETCH_ASSOC);
		
			if ($result) {
				$punch_in_time = new DateTime($result['in_time']);
				$currentTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		
				// Calculate the total working duration
				$workedDuration = $punch_in_time->diff($currentTime);
				$totalWorkedSeconds = ($workedDuration->h * 3600) + ($workedDuration->i * 60) + $workedDuration->s;
		
				// Fetch total break duration from the break_info table
				$break_query = $this->db->prepare("
					SELECT SUM(TIME_TO_SEC(break_duration)) AS total_break_seconds 
					FROM break_info 
					WHERE aten_id = :aten_id AND break_end_time IS NOT NULL
				");
				$break_query->bindParam(':aten_id', $aten_id);
				$break_query->execute();
				$break_result = $break_query->fetch(PDO::FETCH_ASSOC);
		
				if ($break_result && $break_result['total_break_seconds']) {
					$break_duration_in_seconds = $break_result['total_break_seconds'];
				} else {
					$break_duration_in_seconds = 0;
				}
		
				// Calculate effective working time by subtracting break duration
				$effective_worked_seconds = $totalWorkedSeconds - $break_duration_in_seconds;
		
				// Check if worked hours are 9 or more
				$nine_hours_in_seconds = 9 * 3600;
		
				if ($effective_worked_seconds < $nine_hours_in_seconds) {
					// If worked hours are less than 9, show an alert but still allow checkout
					echo "<script>alert('Minimum working condition not satisfied. Check out anyway.');</script>";
				} else {
					// If worked hours are 9 or more, show the success alert
					echo "<script>alert('Check Out successful.');</script>";
				}
		
				// Proceed with punch-out logic regardless of the hours worked
				try {
					// Update the out_time of the record
					$punch_out_time = $currentTime->format('Y-m-d H:i:s');
					$update_out_time = $this->db->prepare("
						UPDATE attendance_info 
						SET out_time = :out_time 
						WHERE aten_id = :aten_id
					");
					$update_out_time->bindParam(':out_time', $punch_out_time);
					$update_out_time->bindParam(':aten_id', $aten_id);
					$update_out_time->execute();
		
					header('Location: attendance-info.php');
					exit;
				} catch (PDOException $e) {
					echo "Error: " . $e->getMessage();
				}
			} else {
				echo "<script>alert('Invalid attendance record.');</script>";
			}
		}
		
		

		public function update_duration() {
			// Fetch users who haven't checked out (where out_time is NULL)
			$query = $this->db->prepare("
				SELECT aten_id, in_time 
				FROM attendance_info 
				WHERE out_time IS NULL
			");
			$query->execute();
			$results = $query->fetchAll(PDO::FETCH_ASSOC);
		
			foreach ($results as $row) {
				$aten_id = $row['aten_id'];
				$punch_in_time = new DateTime($row['in_time']);
				$currentTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		
				// Calculate total working duration
				$workedDuration = $punch_in_time->diff($currentTime);
				$totalWorkedSeconds = ($workedDuration->h * 3600) + ($workedDuration->i * 60) + $workedDuration->s;
		
				// Fetch total break duration for the user
				$break_query = $this->db->prepare("
					SELECT SUM(TIME_TO_SEC(break_duration)) AS total_break_seconds 
					FROM break_info 
					WHERE aten_id = :aten_id AND break_end_time IS NOT NULL
				");
				$break_query->bindParam(':aten_id', $aten_id);
				$break_query->execute();
				$break_result = $break_query->fetch(PDO::FETCH_ASSOC);
		
				if ($break_result && $break_result['total_break_seconds']) {
					$break_duration_in_seconds = $break_result['total_break_seconds'];
				} else {
					$break_duration_in_seconds = 0;
				}
		
				// Calculate effective working time by subtracting break duration
				$effective_worked_seconds = $totalWorkedSeconds - $break_duration_in_seconds;
		
				// Convert back to HH:MM:SS
				$hours = floor($effective_worked_seconds / 3600);
				$minutes = floor(($effective_worked_seconds % 3600) / 60);
				$seconds = $effective_worked_seconds % 60;
				$effective_duration = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
		
				// Update the total duration in the attendance_info table
				$update_duration = $this->db->prepare("
					UPDATE attendance_info 
					SET total_duration = :total_duration 
					WHERE aten_id = :aten_id
				");
				$update_duration->bindParam(':total_duration', $effective_duration);
				$update_duration->bindParam(':aten_id', $aten_id);
				$update_duration->execute();
			}
		}
		
		
		public function start_break($aten_id) {
			try {
				// Check if there's already an ongoing break (redundant but safe)
				$check = $this->db->prepare("
					SELECT * FROM break_info 
					WHERE aten_id = :aten_id 
					  AND break_end_time IS NULL
				");
				$check->bindParam(':aten_id', $aten_id);
				$check->execute();
				
				if ($check->rowCount() > 0) {
					echo "<script>alert('You are already on a break.');</script>";
					return;
				}
				
				// Insert a new break record
				$current_time = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d H:i:s');
				$insert = $this->db->prepare("
					INSERT INTO break_info (aten_id, break_start_time) 
					VALUES (:aten_id, :break_start_time)
				");
				$insert->bindParam(':aten_id', $aten_id);
				$insert->bindParam(':break_start_time', $current_time);
				$insert->execute();
				
				echo "<script>alert('Break started successfully.');</script>";
				header('Location: attendance-info.php');
				exit;
			} catch (PDOException $e) {
				error_log("Error in start_break: " . $e->getMessage());
				echo "<script>alert('An error occurred while starting your break. Please try again later.');</script>";
			}
		}
		
		public function stop_break($aten_id) {
			try {
				// Fetch the latest ongoing break
				$query = $this->db->prepare("
					SELECT break_id, break_start_time 
					FROM break_info 
					WHERE aten_id = :aten_id 
					  AND break_end_time IS NULL
					ORDER BY break_id DESC 
					LIMIT 1
				");
				$query->bindParam(':aten_id', $aten_id);
				$query->execute();
				$current_break = $query->fetch(PDO::FETCH_ASSOC);
				
				if ($current_break) {
					$break_id = $current_break['break_id'];
					$break_start_time = new DateTime($current_break['break_start_time'], new DateTimeZone('Asia/Kolkata'));
					$current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
					$break_duration = $break_start_time->diff($current_time);
					
					// Format break duration as H:i:s
					$break_duration_formatted = $break_duration->format('%H:%I:%S');
					
					// Update the break record with end time and duration
					$update = $this->db->prepare("
						UPDATE break_info 
						SET break_end_time = :break_end_time, 
							break_duration = :break_duration 
						WHERE break_id = :break_id
					");
					$current_time_formatted = $current_time->format('Y-m-d H:i:s');
					$update->bindParam(':break_end_time', $current_time_formatted);
					$update->bindParam(':break_duration', $break_duration_formatted);
					$update->bindParam(':break_id', $break_id);
					$update->execute();
					
					echo "<script>alert('Break stopped successfully.');</script>";
					header('Location: attendance-info.php');
					exit;
				} else {
					echo "<script>alert('No active break found.');</script>";
				}
			} catch (PDOException $e) {
				error_log("Error in stop_break: " . $e->getMessage());
				echo "<script>alert('An error occurred while stopping your break. Please try again later.');</script>";
			}
		}
		

		public function handle_break($data) {
			// Retrieve and sanitize the attendance ID
			if (!is_array($data) || !isset($data['aten_id'])) {
				throw new Exception("Invalid input data. 'aten_id' is missing.");
			}
			$aten_id = $this->test_form_input_data($data['aten_id']);
			
			try {
				// Check if there's an ongoing break for this attendance record
				$query = $this->db->prepare("
					SELECT * FROM break_info 
					WHERE aten_id = :aten_id 
					  AND break_end_time IS NULL
					ORDER BY break_id DESC 
					LIMIT 1
				");
				$query->bindParam(':aten_id', $aten_id);
				$query->execute();
				$current_break = $query->fetch(PDO::FETCH_ASSOC);
				
				if ($current_break) {
					// If there's an ongoing break, stop it
					$this->stop_break($aten_id);
				} else {
					// If there's no ongoing break, start a new one
					$this->start_break($aten_id);
				}
			} catch (PDOException $e) {
				// Log the error for debugging (do not expose raw errors to users)
				error_log("Error in handle_break: " . $e->getMessage());
				echo "<script>alert('An error occurred while processing your request. Please try again later.');</script>";
			}
		}
		
				

	/* --------------------delete_data_by_this_method--------------*/

	public function delete_data_by_this_method($sql,$action_id,$sent_po){
		try{
			$delete_data = $this->db->prepare($sql);

			$delete_data->bindparam(':id', $action_id);

			$delete_data->execute();

			header('Location: '.$sent_po);
		}catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

/* ----------------------manage_all_info--------------------- */

	public function manage_all_info($sql) {
		try{
			$info = $this->db->prepare($sql);
			$info->execute();
			return $info;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}





}
?>
