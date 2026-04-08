<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<style>
    /* Optional: Basic styling for the PDF */
    body {
        font-family: DejaVu Sans, sans-serif; /* For UTF-8 / Cyrillic etc. */
        font-size: 18px;
        line-height: 22px;
        margin: 0;
        padding: 0;
    }
    p {
        line-height: 22px;
        margin: 0;
        padding: 0;
    }
    table {
        border-collapse: collapse;
    }
    table th, table td {
        padding: 5px;
    }
    h2 {
        margin: 0;
        padding: 0;
    }
</style>
    
<body>
    <header>
        @yield('header')
    </header>
    <div>
        @yield('content')
    </div>
    <footer style="margin-top:100px;">
        @yield('footer')
    </footer>
</body>

</html>