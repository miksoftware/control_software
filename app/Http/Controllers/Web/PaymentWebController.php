<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with('client');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->betweenDates($request->input('from'), $request->input('to'));
        }

        $payments = $query->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $paymentMethods = PaymentMethod::cases();
        $clients = Client::orderBy('name')->get();

        return view('payments.index', compact('payments', 'paymentMethods', 'clients'));
    }

    public function create(Request $request): View
    {
        $paymentMethods = PaymentMethod::cases();
        $clientId = $request->input('client_id');
        $category = $request->input('category', 'global');
        
        $clients = Client::orderBy('name')->get();
        $selectedClient = $clientId ? Client::find($clientId) : null;

        return view('payments.create', compact('paymentMethods', 'clients', 'selectedClient', 'category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id'      => ['required', 'exists:clients,id'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'category'       => ['required', 'string', 'in:global,projects,features'],
            'payment_method' => ['required', 'string'],
            'paid_at'        => ['required', 'date'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string'],
        ]);

        $client = Client::findOrFail($validated['client_id']);
        
        // Validar que el abono no supere la deuda pendiente
        $pending = ($validated['category'] === 'projects') 
            ? $client->total_custom_projects_debt - $client->payments()->where('category', 'projects')->sum('amount')
            : (($validated['category'] === 'features') 
                ? $client->total_features_debt - $client->payments()->where('category', 'features')->sum('amount')
                : $client->global_pending_balance);

        if ($validated['amount'] > $pending && $validated['category'] !== 'global') {
            return back()->withInput()->with('error', "El monto excede la deuda de la categoría seleccionada ($" . number_format($pending, 2) . ").");
        }

        $client->payments()->create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Abono registrado exitosamente en la cuenta corriente del cliente.');
    }

    public function show(Payment $payment): View
    {
        $payment->load('client');
        return view('payments.show', compact('payment'));
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $client = $payment->client;
        $payment->delete();

        return redirect()->route('clients.show', $client)
            ->with('success', 'Registro de pago eliminado.');
    }
}
