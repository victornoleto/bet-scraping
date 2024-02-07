<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bet Scraping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=JetBrains+Mono">
    <style>

        body {
            font-family: 'JetBrains Mono', monospace;
            --bs-body-font-size: 13px;
        }

        .table-responsive {
            max-height: calc(100vh - 3rem);
        }

        th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #fff !important;
            box-shadow: inset 0 -2px 0 var(--bs-border-color) !important;
            border-top: none;
            border-bottom: none;
            padding: 1rem 0.5rem !important;
        }

    </style>
</head>
<body>

	<main class="p-4">

        @yield('content')

	</main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    
</body>
</html>