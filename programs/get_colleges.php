<?php
require_once '../classes/programClass.php';

$program = new Program();
$colleges = $program->fetchColleges();
echo json_encode($colleges);
?> 