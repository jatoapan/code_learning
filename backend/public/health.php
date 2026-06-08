<?php
// Healthcheck ultra rápido y aislado para Railway
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'message' => 'Nginx/PHP-FPM is healthy, bypassing Laravel boot']);
exit;
