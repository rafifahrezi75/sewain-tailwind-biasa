<?php
include '../config.php';
$q = mysqli_query($conn, "SHOW COLUMNS FROM alat");
while($r = mysqli_fetch_assoc($q)) {
    echo $r['Field'] . "\n";
}
