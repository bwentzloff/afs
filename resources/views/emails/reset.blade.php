<!DOCTYPE html>
    <html lang="en-US">
    	<head>
    		<meta charset="utf-8">
    	</head>
    	<body>
    		<h2>Click this link to reset your password</h2>
    		<p><a href="https://altfantasysports.com/password/reset/{{ $token }}?email={{ urlencode($email) }}">https://altfantasysports.com/password/reset/{{ $token }}?email={{ urlencode($email) }}</a></p>
    	</body>
    </html>