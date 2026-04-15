<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #f9fafb; padding: 20px; border-radius: 8px;">

        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #002147, #6a0392); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0 0 5px 0;">Banco de Choices</h1>
        </div>

        {{-- Content --}}
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <p>Hola / Ol&aacute; <strong>{{ $nome }}</strong>,</p>

            <p>
                Se liber&oacute; el acceso a nuevas materias en tu plan: <strong>{{ $planId }}</strong>.<br>
                O acesso a novas mat&eacute;rias foi liberado no seu plano: <strong>{{ $planId }}</strong>.
            </p>

            <center>
                <a href="{{ config('app.url') }}/login"
                   style="display: inline-block; background: linear-gradient(135deg, #002147, #6a0392); color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0;">
                    Iniciar sesi&oacute;n / Fazer login
                </a>
            </center>
        </div>

        {{-- Footer --}}
        <div style="text-align: center; color: #9ca3af; font-size: 12px; margin-top: 20px;">
            <p>&copy; {{ date('Y') }} Banco de Choices.</p>
        </div>
    </div>
</body>
</html>
