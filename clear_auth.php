<?php
// Clears only the auth session (used by chooser when returning via back button)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/auth.php';
// only clear if there's an active auth flag
if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
    do_logout();
    echo json_encode(['cleared' => true]);
    exit;
}
echo json_encode(['cleared' => false]);
exit;
