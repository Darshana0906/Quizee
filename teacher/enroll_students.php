<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id <= 0) {
    die("Invalid Quiz ID");
}

$check = $conn->prepare("SELECT title FROM quiz WHERE quiz_id = ? AND teacher_id = ?");
$check->bind_param("ii", $quiz_id, $teacher_id);
$check->execute();
$quiz = $check->get_result()->fetch_assoc();
$check->close();

if (!$quiz) {
    die("Invalid quiz, or you don't have permission to manage this quiz.");
}

$error = "";
$success = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $students = isset($_POST['students']) ? $_POST['students'] : [];

    if (empty($students)) {
        $error = "Select at least one student.";
    } else {
        
        // $del = $conn->prepare("DELETE FROM quiz_enrollments WHERE quiz_id = ?");
        // $del->bind_param("i", $quiz_id);
        // $del->execute();
        // $del->close();

        
        $ins = $conn->prepare("INSERT INTO quiz_enrollments (quiz_id, student_id) VALUES (?, ?)");
        foreach ($students as $student_id) {
            $student_id = (int)$student_id;
            $ins->bind_param("ii", $quiz_id, $student_id);
            $ins->execute();
        }
        $ins->close();

        header("Location: enroll_students.php?quiz_id=$quiz_id&saved=1");
        exit;
    }
}

if (isset($_GET['saved'])) {
    $success = "Enrollment saved successfully!";
}


$enrolled_ids = [];
$enr_stmt = $conn->prepare("SELECT student_id FROM quiz_enrollments WHERE quiz_id = ?");
$enr_stmt->bind_param("i", $quiz_id);
$enr_stmt->execute();
$enr_result = $enr_stmt->get_result();
while ($row = $enr_result->fetch_assoc()) {
    $enrolled_ids[] = (int)$row['student_id'];
}
$enr_stmt->close();


$statement = $conn->prepare("SELECT student_id, name, MIS, branch, division, email, university FROM student ORDER BY branch");
$statement->execute();
$result = $statement->get_result();


$branches = $conn->query("SELECT DISTINCT branch FROM student ORDER BY branch");
$divisions = $conn->query("SELECT DISTINCT division FROM student ORDER BY division");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Enroll Students</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f4f4f4; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 4px; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 4px; }
    .filter-box { background: #f9f9f9; padding: 12px; border-radius: 6px; margin-bottom: 15px; }
</style>
</head>
<body>

<h2>Enroll Students — <?php echo htmlspecialchars($quiz['title']); ?></h2>

<?php if (!empty($success)) { ?><p class="success"><?php echo htmlspecialchars($success); ?></p><?php } ?>
<?php if (!empty($error)) { ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php } ?>

<div class="filter-box">
    <label>Branch:</label>
    <select id="filterBranch">
        <option value="">-- Any --</option>
        <?php while ($b = $branches->fetch_assoc()) { ?>
            <option value="<?php echo htmlspecialchars($b['branch']); ?>"><?php echo htmlspecialchars($b['branch']); ?></option>
        <?php } ?>
    </select>

    <label>Division:</label>
    <select id="filterDivision">
        <option value="">-- Any --</option>
        <?php while ($d = $divisions->fetch_assoc()) { ?>
            <option value="<?php echo htmlspecialchars($d['division']); ?>"><?php echo htmlspecialchars($d['division']); ?></option>
        <?php } ?>
    </select>

    <button type="button" onclick="selectByFilter()">Select Matching</button>
    <button type="button" onclick="clearAll()">Clear All</button>
</div>

<form method="POST">
    <table>
        <tr>
            <th>Select</th>
            <th>MIS</th>
            <th>Name</th>
            <th>Branch</th>
            <th>Division</th>
            <th>Email</th>
            <th>University</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { 
            $is_checked = in_array($row['student_id'], $enrolled_ids) ? 'checked' : '';
        ?>
        <tr>
            <td>
                <input type="checkbox"
                       class="student-checkbox"
                       name="students[]"
                       value="<?php echo $row['student_id']; ?>"
                       data-branch="<?php echo htmlspecialchars($row['branch']); ?>"
                       data-division="<?php echo htmlspecialchars($row['division']); ?>"
                       <?php echo $is_checked; ?>>
            </td>
            <td><?php echo htmlspecialchars($row['MIS']); ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['branch']); ?></td>
            <td><?php echo htmlspecialchars($row['division']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['university']); ?></td>
        </tr>
        <?php } ?>
    </table>

    <br>
    <button type="submit">Save Enrollment</button>
</form>

<br>
<a href="dashboard.php">Back to Dashboard</a>

<script>
function selectByFilter() {
    const branch = document.getElementById('filterBranch').value;
    const division = document.getElementById('filterDivision').value;
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        const matchBranch = (branch === "" || cb.dataset.branch === branch);
        const matchDivision = (division === "" || cb.dataset.division === division);
        if (matchBranch && matchDivision) {
            cb.checked = true;
        }
    });
}
function clearAll() {
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
}
</script>

</body>
</html>