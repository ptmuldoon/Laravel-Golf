<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background: #f4f4f7; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f4f4f7; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, {{ $themeSettings['primary'] }} 0%, {{ $themeSettings['secondary'] }} 100%); background-color: {{ $themeSettings['primary'] }}; color: white; padding: 25px 20px; text-align: center; border-radius: 12px 12px 0 0;">
                            <h1 style="margin: 0; font-size: 24px;">{{ $league->name }}</h1>
                        </td>
                    </tr>

                    {{-- Message --}}
                    <tr>
                        <td style="background: white; padding: 30px 25px;">
                            <h2 style="color: {{ $themeSettings['primary'] }}; font-size: 20px; margin: 0 0 15px 0;">{{ $messageSubject }}</h2>
                            <div style="line-height: 1.6; color: #333; font-size: 15px;">
                                {!! nl2br(e($messageBody)) !!}
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background: {{ $themeSettings['primary_light'] }}; padding: 15px 20px; text-align: center; color: #999; font-size: 12px; border-radius: 0 0 12px 12px; border-top: 1px solid #eee;">
                            {{ $league->name }} &bull; {{ $league->season }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
