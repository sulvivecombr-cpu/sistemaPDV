<?php
// Healthcheck: confirms the login page renders AND the database is reachable.
$r = @file_get_contents('http://127.0.0.1:3000/login.php', false, stream_context_create(['http' => ['timeout' => 3]]));
if ($r === false || strpos($r, 'Sistema PDV') === false) {
    fwrite(STDERR, "Login page unavailable\n");
    exit(1);
}
require '/app/config/db.php';
$pdo->query('SELECT 1 FROM usuarios LIMIT 1');
