<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> CONTROL MANUAL </title>
    <style>
        .column {
            float: left;
            width: 30%;
            padding: 10px;
        }

        .button {
            display: block;
            margin-bottom: 10px;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .text {
            display: block;
            margin-bottom: 10px;
            width: 100%;
        }

        .slider {
            display: block;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="column">
    @foreach ($buttons as $key => $button)
        <button class="button" onclick="location.href='{{ route($button['route']) }}'">{{ $button['name'] }}</button>
    @endforeach
</div>

<div class="column">
    @foreach ($buttons as $text => $button)
        <span class="text">{{ $button['message'] }}</span>
    @endforeach
</div>
</body>