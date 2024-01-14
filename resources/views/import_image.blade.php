<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body class="antialiased">

<form action="{{ route('importImages') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="d-flex align-items-start flex-column">
        <div class="p-2">
            <label>Image Set:</label>
            <input type="file" name="batch_photos[]" accept="image/*" multiple required>
        </div>
        <div>
            <div class="p-2">
                <button class="btn btn-primary" type="submit">Create</button>
            </div>
        </div>
    </div>
</form>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
