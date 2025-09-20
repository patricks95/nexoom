<?php
echo "PHP is working! Current time: " . date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Test</title>
</head>
<body>
    <h1>PHP Test Page</h1>
    <p>If you see this message, PHP is working correctly.</p>
    <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
    <p>Random number: <?php echo rand(1, 100); ?></p>
</body>
</html>
