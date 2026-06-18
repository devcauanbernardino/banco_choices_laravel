<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

class PaymentStatusController extends Controller
{
    public function show(Request $request)
    {
        $email = trim((string) $request->query('email', ''));
        $pedidos = [];

        if ($email !== '') {
            $pedidos = Pedido::where('email', $email)
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        return view('checkout.verificar-pagamento', [
            'email' => $email,
            'pedidos' => $pedidos,
        ]);
    }
}
