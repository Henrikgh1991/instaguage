<?php

session_start();

$user = null;
if(isset($_SESSION["user"])) {
	$user = $_SESSION["user"];
}

if(!isset($_SESSION["csrfToken"])) {
	$_SESSION["csrfToken"] = bin2hex(openssl_random_pseudo_bytes(16));
}

$base = dirname($_SERVER["SCRIPT_NAME"]);

?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="robots" content="noindex">
		<base href="<?php echo $base; ?>/">
		<link rel="icon" type="image/png" href="favicon.png" />
		<script>
			document.csrfToken = <?php echo json_encode($_SESSION["csrfToken"]); ?>;
			document.base = <?php echo json_encode($base); ?>;
			document.user = <?php echo json_encode($user); ?>;
		</script>
		<title></title>
	</head>

	<body>
		<div id="app"></div>
	</body>
</html>
