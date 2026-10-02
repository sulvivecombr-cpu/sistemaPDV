<?php
// Optional credentials supplied securely through the environment.
// Obtain the access token from the Mercado Pago developer dashboard.
define('MP_ACCESS_TOKEN', getenv('MP_ACCESS_TOKEN') ?: 'YOUR_ACCESS_TOKEN_HERE');
define('MP_DEVICE_ID', getenv('MP_DEVICE_ID') ?: 'YOUR_DEVICE_ID_HERE');
?>
