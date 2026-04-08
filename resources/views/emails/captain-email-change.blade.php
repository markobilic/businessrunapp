<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{__('Captain Email Change Request')}}</title>
</head>
<body>
    <h1>{{__('Email Change Request')}}</h1>
    <p>
        {{__('A user wants to change their captain email from')}} <strong>{{ $oldEmail }}</strong>
        {{__('to')}} <strong>{{ $newEmail }}</strong>.
    </p>
    <p>
        {{__('Please review this request and take any necessary action.')}}
    </p>
</body>
</html>
