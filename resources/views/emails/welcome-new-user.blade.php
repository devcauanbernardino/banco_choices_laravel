<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #f9fafb; padding: 20px; border-radius: 8px;">

        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #002147, #6a0392); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
            <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" width="180" height="44" style="display:block;margin:0 auto 12px auto;max-width:100%;height:auto;">
            <p style="margin: 0;">&iexcl;Bienvenido! / Bem-vindo!</p>
        </div>

        {{-- Content --}}
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <p>Hola / Ol&aacute; <strong>{{ $nome }}</strong>,</p>

            <p>
                Tu compra fue procesada. Aqu&iacute; est&aacute;n tus credenciales:<br>
                Sua compra foi processada. Aqui est&atilde;o suas credenciais:
            </p>

            {{-- Credentials box --}}
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #6a0392;">
                <p style="margin: 10px 0;"><strong style="color: #6a0392;">Email:</strong> {{ $email }}</p>
                <p style="margin: 10px 0;"><strong style="color: #6a0392;">Contrase&ntilde;a / Senha:</strong> {{ $password }}</p>
                <p style="margin: 10px 0;"><strong style="color: #6a0392;">Plan / Plano:</strong> {{ $planId }}</p>
                <p style="margin: 10px 0;"><strong style="color: #6a0392;">Total:</strong> {{ $totalPrice }}</p>
            </div>

            <center>
                <a href="{{ config('app.url') }}/login"
                   style="display: inline-block; background: linear-gradient(135deg, #002147, #6a0392); color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0;">
                    Ir al Login / Ir para o Login
                </a>
            </center>
        </div>

        {{-- Footer --}}
        <div style="text-align: center; color: #9ca3af; font-size: 12px; margin-top: 20px;">
            <p>&copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
