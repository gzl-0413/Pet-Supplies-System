<?php
require_once '../config/helper.php';

if (!isset($_GET['table'])) {
    echo json_encode(['error' => 'Table parameter is required']);
    echo "<script>
    alert('You are not allow to access this page with this way.');
    window.location.href = 'adminDashboard.php';
</script>";
exit;
}

$table = $_GET['table'];

$conn = getDbConnection();

$sql = "SELECT * FROM " . $conn->real_escape_string($table);
$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo json_encode(['error' => 'No results found']);
    exit;
}
$conn->close();

echo json_encode($data);
?>