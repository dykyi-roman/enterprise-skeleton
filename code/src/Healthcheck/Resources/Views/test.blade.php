<!DOCTYPE html>
<html>
<head>
    <title>Health Check Test</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h1>Health Check Status</h1>
        
        @if(isset($status))
            <div class="status">
                <p>Current Status: {{ $status }}</p>
            </div>
        @endif

        <div class="timestamp">
            <p>Last Checked: {{ date('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
