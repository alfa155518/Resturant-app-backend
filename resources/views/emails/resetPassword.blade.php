<!DOCTYPE html>
<html>
<head>
    <title>Gourmet Haven Restaurant</title>
</head>
<body>
    <h1>{{ $mailData['title'] }}</h1>
    <p>{{ $mailData['body'] }}</p>

    @if(isset($mailData['link']))
    <p>
        <a href="{{$mailData['link']}}" class="button">click her</a>
    </p>
@endif

    <p>Thank you</p>
</body>
</html>
