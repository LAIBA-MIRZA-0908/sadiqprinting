<?php
include 'db_connection.php';
$action = $_POST['action'] ?? '';

if($action == 'getEmployees'){
  $q = $conn->query("SELECT * FROM employees ORDER BY id DESC");
  $i = 1;
  while($r = $q->fetch_assoc()){
    echo "<tr>
      <td>{$i}</td>
      <td>{$r['full_name']}</td>
      <td>{$r['cnic']}</td>
      <td>{$r['contact_number']}</td>
      <td>{$r['designation']}</td>
      <td>{$r['monthly_salary']}</td>
      <td class='no-print'>
        <button class='btn btn-sm btn-info btnEdit' data-id='{$r['id']}'>Edit</button>
        <button class='btn btn-sm btn-danger btnDelete' data-id='{$r['id']}'>Delete</button>
      </td>
    </tr>";
    $i++;
  }
  exit;
}

if($action == 'saveEmployee'){
  $id = $_POST['id'] ?? '';
  $full_name = $_POST['full_name'];
  $cnic = $_POST['cnic'];
  $contact_number = $_POST['contact_number'];
  $designation = $_POST['designation'];
  $monthly_salary = $_POST['monthly_salary'];

  if($id == ''){
    $stmt = $conn->prepare("INSERT INTO employees (full_name, cnic, contact_number, designation, monthly_salary) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssd", $full_name, $cnic, $contact_number, $designation, $monthly_salary);
    $stmt->execute();
    echo "Employee added successfully!";
  } else {
    $stmt = $conn->prepare("UPDATE employees SET full_name=?, cnic=?, contact_number=?, designation=?, monthly_salary=? WHERE id=?");
    $stmt->bind_param("ssssdi", $full_name, $cnic, $contact_number, $designation, $monthly_salary, $id);
    $stmt->execute();
    echo "Employee updated successfully!";
  }
  exit;
}

if($action == 'getEmployeeById'){
  $id = (int)$_POST['id'];
  $res = $conn->query("SELECT * FROM employees WHERE id=$id")->fetch_assoc();
  echo json_encode($res);
  exit;
}

if($action == 'deleteEmployee'){
  $id = (int)$_POST['id'];
  $conn->query("DELETE FROM employees WHERE id=$id");
  echo "Employee deleted successfully!";
  exit;
}
if($action == 'getAttendance'){
    $month = (int)$_POST['month'];
    $year = (int)$_POST['year'];
    $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    // Get all employees
    $employees = [];
    $res = $conn->query("SELECT id, full_name FROM employees ORDER BY full_name");
    while($r = $res->fetch_assoc()) $employees[] = $r;

    // Get attendance for the month using LEFT JOIN style
    $attendance = [];
    $res2 = $conn->query("
        SELECT e.id AS employee_id, a.attendance_date, a.status
        FROM employees e
        LEFT JOIN attendance a
        ON e.id = a.employee_id 
        AND YEAR(a.attendance_date) = $year 
        AND MONTH(a.attendance_date) = $month
    ");

    while($r = $res2->fetch_assoc()){
        if($r['attendance_date']) {
            $day = (int)date('j', strtotime($r['attendance_date']));
            $attendance[$r['employee_id'].'-'.$day] = $r['status'];
        }
    }

    echo json_encode(['days'=>$days, 'employees'=>$employees, 'attendance'=>$attendance]);
    exit;
}

if($action == 'saveAttendance'){
  $month = (int)$_POST['month'];
  $year = (int)$_POST['year'];
  $data = json_decode($_POST['data'], true);

  foreach($data as $d){
    $emp = (int)$d['emp'];
    $day = (int)$d['day'];
    $val = $conn->real_escape_string($d['val']);

    $date = "$year-$month-".str_pad($day, 2, '0', STR_PAD_LEFT);

$exists = $conn->query("SELECT id FROM attendance WHERE employee_id=$emp AND attendance_date='$date'");
if($exists->num_rows > 0){
    $conn->query("UPDATE attendance SET status='$val' WHERE employee_id=$emp AND attendance_date='$date'");
} else {
    $conn->query("INSERT INTO attendance (employee_id, attendance_date, status) VALUES ($emp, '$date', '$val')");
}
  }
  echo "Attendance saved successfully!";
  exit;
}



if($action == 'getPayroll'){
    $month = (int)$_POST['month'];
    $year = (int)$_POST['year'];
    $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    $sql = "SELECT id, full_name, designation, monthly_salary FROM employees ORDER BY full_name";
    $res = $conn->query($sql);

    $data = [];
    while($r = $res->fetch_assoc()){
        $empId = $r['id'];

        // Check if payroll is already saved
        $payRes = $conn->query("SELECT salary_paid FROM payroll WHERE employee_id=$empId AND month=$month AND year=$year");
        if($payRes->num_rows > 0){
            $payRow = $payRes->fetch_assoc();
            $calc = (float)$payRow['salary_paid']; // use saved value
        } else {
            // Calculate from attendance
            $att = $conn->query("
                SELECT COUNT(*) AS present 
                FROM attendance 
                WHERE employee_id=$empId 
                AND status='Present'
                AND MONTH(attendance_date)=$month 
                AND YEAR(attendance_date)=$year
            ");
            $a = $att->fetch_assoc();
            $present = $a['present'] ?? 0;
            $calc = round(($r['monthly_salary'] / $days) * $present, 0);
        }

        $data[] = [
            'id' => $r['id'],
            'full_name' => $r['full_name'],
            'designation' => $r['designation'],
            'salary_amount' => $r['monthly_salary'],
            'days_present' => $present ?? 0,
            'total_days' => $days,
            'calculated_salary' => $calc
        ];
    }

    echo json_encode($data);
    exit;
}



if($action == 'savePayroll'){
    $month = (int)$_POST['month'];
    $year = (int)$_POST['year'];
    $data = json_decode($_POST['data'], true);

    foreach($data as $d){
        $emp = (int)$d['id'];
        $salary = (float)$d['salary'];

        $exists = $conn->query("SELECT id FROM payroll WHERE employee_id=$emp AND month=$month AND year=$year");
        if($exists->num_rows>0){
            $conn->query("UPDATE payroll SET salary_paid=$salary WHERE employee_id=$emp AND month=$month AND year=$year");
        } else {
            $conn->query("INSERT INTO payroll (employee_id, month, year, salary_paid) VALUES ($emp, $month, $year, $salary)");
        }
    }

    echo "Payroll saved successfully!";
    exit;
}

?>
