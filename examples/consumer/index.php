<html>

<head>
	<title>PHP OpenID Authentication Example</title>
	<link rel="openid.server" href="http://localhost/~msilva/php-openid/examples/server/server.php" />
	<link rel="stylesheet" type="text/css" media="all" href="ideais.css" />
</head>

<body>

<h1>PHP OpenID Authentication Example</h1>

<p>This example consumer uses the <a href="http://www.openidenabled.com/openid/libraries/php/">PHP
OpenID</a> library. It just verifies that the URL that you enter is your identity URL.</p>

<?php if (isset($msg)) { print "<div class=\"alert\">$msg</div>"; } ?>
<?php if (isset($error)) { print "<div class=\"error\">$error</div>"; } ?>
<?php if (isset($success)) { print "<div class=\"success\">$success</div>"; } ?>

<div id="verify-form">
<form method="get" action="try_auth.php">
	Identity URL:
	<input type="hidden" name="action" value="verify" />
	<input type="text" name="openid_url" value="" />
	<input type="submit" value="Verify" />
</form>
</div>

</body>

</html>
