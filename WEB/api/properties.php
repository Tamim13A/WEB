<?php
// api/properties.php - Web API Controller for Property Search
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Property.php';

$search = clean($_GET['search'] ?? '');
$type   = clean($_GET['type'] ?? '');

$properties = Property::all($pdo, $search, $type);
echo json_encode($properties);
