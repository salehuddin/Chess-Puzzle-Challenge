<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, sans-serif; color: #262626; line-height: 1.6;">
    <h1>{{ $title }}</h1>
    <p>{!! nl2br(e($body)) !!}</p>
    @if(filled($buttonText))
        <p><a href="{{ $url }}" style="display: inline-block; padding: 10px 16px; background: #f59e0b; color: #ffffff; text-decoration: none; border-radius: 6px;">{{ $buttonText }}</a></p>
    @endif
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
