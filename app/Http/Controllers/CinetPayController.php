<?php



namespace App\Http\Controllers;


use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CinetPayController extends Controller
{
    public function index()
    {
        return view('cinetpay');
    }

    public function Payment(Request $request)
{
    // Validation des données d'entrée
    $request->validate([
        'amount' => 'required|numeric|min:1',
        'currency' => 'required|string|max:3',
    ]);

    $transaction_id = date("YmdHis"); // Générer votre identifiant de transaction
    $transaction_id_full = "SénAgrie-" . $transaction_id;

    $cinetpay_data = [
        "amount" => $request->input('amount'),
        "currency" => $request->input('currency'),
        "apikey" => env("APIKEY"),
        "site_id" => env("SITE_ID"),
        "secret_key" => env("SECRET"),
        "transaction_id" => $transaction_id_full,
        "description" => "TEST-Laravel",
        "return_url" => route('return_url'),
        "notify_url" => route('notify_url'),
        "metadata" => "user001",
        'customer_surname' => "SénAgri",
        'customer_name' => "Express",
        'customer_email' => Auth::user()->email ?? '',
        'customer_phone_number' => '',
        'customer_address' => '',
        'customer_city' => '',
        'customer_country' => 'SN',
        'customer_state' => '',
        'customer_zip_code' => ''
    ];

    // Appel API pour créer la transaction
    $response = Http::withHeaders([
        'Content-Type' => 'application/json'
    ])->post('https://api-checkout.cinetpay.com/v2/payment', $cinetpay_data);

    $response_body = $response->json();

    if ($response->successful() && $response_body['code'] == '201') {
        $payment_link = $response_body["data"]["payment_url"];

        // Stocker les détails de la transaction dans un fichier JSON
        Storage::put("payments/{$transaction_id_full}.json", json_encode([
            'transaction_id' => $transaction_id_full,
            'amount' => $request->input('amount'),
            'currency' => $request->input('currency'),
            'status' => 'pending',
            'customer_email' => Auth::user()->email,
            'created_at' => now(),
            'operator_id' => $response_body['data']['operator_id'] ?? null,
            'operator' => $response_body['data']['operator'] ?? null,
            'paid_amount' => $response_body['data']['amount'] ?? null,
            'paid_currency' => $response_body['data']['currency'] ?? null,
            'payment_date' => $response_body['data']['payment_date'] ?? null,
        ]));

        // Sauvegarder la transaction en base de données
        $payment = new Payment([
            "transaction_id" => $transaction_id_full,
            'amount' => $request->input('amount'),
            'currency' => $request->input('currency'),
            'status' => 'pending',
            'customer_email' => Auth::user()->email,
            'operator_id' => $response_body['data']['operator_id'] ?? null,
            'operator' => $response_body['data']['operator'] ?? null,
            'paid_amount' => $response_body['data']['payment_method'] ?? null,
            'paid_currency' => $response_body['data']['cpm_currency'] ?? null,
            'payment_date' => $response_body['data']['payment_date'] ?? null,
        ]);
        $payment->save();

        // Retourner le lien de paiement à l'utilisateur
        return response()->json(['payment_link' => $payment_link], 200);
    } else {
        $error_message = $response_body['message'] ?? 'An error occurred during payment creation.';
        \Log::error('Payment creation failed: ' . $error_message);
        return response()->json(['error' => $error_message], 500);
    }
}

    public function notify_url(Request $request)
    {
        if ($request->has('cpm_trans_id')) {
            $transaction_id = $request->input('cpm_trans_id');

            $cinetpay_check = [
                "apikey" => env("APIKEY"),
                "site_id" => env("SITE_ID"),
                "transaction_id" => $transaction_id
            ];

            $response = $this->getPayStatus($cinetpay_check);
            $response_body = json_decode($response, true);

            if ($response_body['code'] == '00') {
                $payment_data = json_decode(Storage::get("payments/{$transaction_id}.json"), true);

                if ($payment_data) {
                    $payment = Payment::updateOrCreate(
                        ['transaction_id' => $transaction_id],
                        [
                            'amount' => $payment_data['amount'],
                            'currency' => $payment_data['currency'],
                            'status' => 'success',
                            'customer_email' => $payment_data['customer_email'],
                            'operator_id' => $response_body['data']['operator_id'] ?? null,
                            'operator' => $response_body['data']['operator'] ?? null,
                            'paid_amount' => $response_body['data']['amount'] ?? null,
                            'paid_currency' => $response_body['data']['currency'] ?? null,
                            'payment_date' => $response_body['data']['payment_date'] ?? null,
                            'created_at' => $payment_data['created_at'],
                            'updated_at' => now(),
                        ]
                    );
                    $payment->save();


                    Storage::delete("payments/{$transaction_id}.json");
                    return response()->json(['status' => 'success']);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'Payment data not found'], 404);
                }
            } else {
                return response()->json(['status' => 'error', 'message' => 'Transaction failed'], 400);
            }
        } else {
            return response()->json(['error' => 'cpm_trans_id not provided'], 400);
        }
    }
    public function fetchTransaction()
    {
        $apiKey = env('APIKEY'); // Assurez-vous que l'APIKEY est définie dans votre fichier .env
        $siteId = env('SITE_ID'); // Assurez-vous que le SITE_ID est définie dans votre fichier .env

        // Préparez les données pour la requête API
        if (!$apiKey || !$siteId) {
            return response()->json([
                'status' => 'error',
                'message' => 'API key or Site ID is missing in the environment variables.',
            ]);
        }

        // Préparez les données pour la requête API
        $data = [
            'apikey' => $apiKey,
            'cpm_site_id' => $siteId,
        ];

        try {
            // Faire la requête POST à l'API CinetPay
            $response = Http::post('https://api.cinetpay.com/v2/?method=getTransHistory', $data);

            // Vérifiez si la requête a réussi
            if ($response->successful()) {
                $responseData = $response->json();

                // Stocker les données dans un fichier JSON
                Storage::disk('local')->put('transactions.json', json_encode($responseData, JSON_PRETTY_PRINT));

                return response()->json([
                    'status' => 'success',
                    'message' => 'Transactions retrieved and stored successfully.',
                    'data' => $responseData
                ]);
            } else {
                Log::error('Failed to retrieve transactions.', ['response' => $response->body()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to retrieve transactions.',
                    'error' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while retrieving transactions.', ['exception' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving transactions.',
                'error' => $e->getMessage()
            ]);
        }
    }
    public function storeTransactions()
    {
        $json = Storage::get('transactions.json');
        $data = json_decode($json, true);

        $transactions = $data['Transactions']['transaction'];

        foreach ($transactions as $transaction) {
            foreach ($transactions as $transaction) {
                if ($transaction['cpm_trans_status'] === 'ACCEPTED') {
                    Payment::create([
                        'transaction_id' => $transaction['cpm_trans_id'],
                        'amount' => $transaction['cpm_amount'],
                        'currency' => $transaction['cpm_currency'],
                        'status' => $transaction['cpm_trans_status'],
                        'customer_email' => $transaction['cel_phone_num'], // Assurez-vous de mapper correctement les champs
                        'operator_id' => $transaction['cpm_site_id'],
                        'operator' => $transaction['payment_method'],
                        'paid_amount' => $transaction['cpm_amount'],
                        'paid_currency' => $transaction['cpm_currency'],
                        'payment_date' => $transaction['cpm_payment_date'] . ' ' . $transaction['cpm_payment_time'],
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Accepted transactions retrieved and stored successfully.'
            ]);
    }
}

    public function getTransactions()
    {
        $apiKey = '119959109366eb0db4dbcc05.22390365'; // Assurez-vous que l'APIKEY est définie dans votre fichier .env
        $siteId = '5880152'; // Assurez-vous que le SITE_ID est définie dans votre fichier .env

        // Préparez les données pour la requête API
        $data = [
            'apikey' => $apiKey,
            'cpm_site_id' => $siteId,
        ];

        // Faire la requête POST à l'API CinetPay
        $response = Http::post('https://api.cinetpay.com/v2/?method=getTransHistory', $data);

        // Vérifiez si la requête a réussi
        if ($response->successful()) {
            $responseData = $response->json();

            // Stocker les données dans un fichier JSON
            Storage::disk('local')->put('transactions.json', json_encode($responseData, JSON_PRETTY_PRINT));

            return response()->json([
                'status' => 'success',
                'message' => 'Transactions retrieved and stored successfully.',
                'data' => $responseData
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve transactions.',
                'error' => $response->body()
            ]);
        }
    }


    public function fetchTransactionA()
    {
        $apiUrl = 'https://api.cinetpay.com/v2/?method=getTransHistory';

        $transactionIds = Payment::where('status', 'pending')->pluck('transaction_id');

        foreach ($transactionIds as $transaction_id) {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => '119959109366eb0db4dbcc05.22390365',
            ])->post($apiUrl, [
                'site_id' => env("SITE_ID"),
                'transaction_id' => $transaction_id,
            ]);

            if ($response->successful()) {
                $response_body = $response->json();

                if (isset($response_body['status']) && $response_body['status'] === 'success' && isset($response_body['data'])) {
                    $transaction_data = $response_body['data'];

                    // Vérifier si les données de transaction sont vides
                    if (!empty($transaction_data)) {
                        $transaction = reset($transaction_data); // Obtenir la première transaction

                        // Vérifier si les clés existent dans la transaction
                        if (isset($transaction['created_at']) && isset($transaction['payment_date']) && isset($transaction['amount']) && isset($transaction['currency']) && isset($transaction['status'])) {
                            // Accéder aux détails de la transaction
                            $transaction_details = [
                                'date_Creation' => $transaction['created_at'],
                                'aate_Paiement' => $transaction['payment_date'],
                                'business' => 'SénAgri-express',
                                'business_ID' => 5866754,
                                'operateur' => $transaction['operator'] ?? 'N/A',
                                'ID_Transaction' => $transaction_id,
                                'ID_Opérateur' => $transaction['operator_id'] ?? 'N/A',
                                'Telephone' => $transaction['phone_number'] ?? 'N/A',
                                'Montant_paye' => $transaction['amount'],
                                'Devise' => $transaction['currency'],
                                'Sync' => 'Y',
                                'Statut' => $transaction['status'],
                                'Commentaire' => $transaction['comment'] ?? 'N/A',
                            ];

                            // Stocker les détails de la transaction dans un fichier JSON
                            Storage::put("transactions/{$transaction_id}.json", json_encode($transaction_details, JSON_PRETTY_PRINT));

                            // Mettre à jour l'état de la transaction dans la base de données
                            $payment = Payment::where('transaction_id', $transaction_id)->first();
                            if ($payment) {
                                $payment->status = 'success';
                                $payment->operator_id = $transaction['operator_id'] ?? null;
                                $payment->operator = $transaction['operator'] ?? null;
                                $payment->paid_amount = $transaction['amount'] ?? null;
                                $payment->paid_currency = $transaction['currency'] ?? null;
                                $payment->payment_date = $transaction['payment_date'] ?? null;
                                $payment->save();
                            }
                        } else {
                            Log::error('Missing key in transaction data');
                        }
                    } else {
                        Log::error('Empty transaction data');
                    }
                } else {
                    Log::error('Invalid response status or data');
                }
            } else {
                $errorBody = $response->body();
                Log::error('Failed to fetch transaction from CinetPay API.', ['response' => $errorBody]);
                return response()->json(['status' => 'error', 'message' => 'Failed to fetch transactions', 'error' => $errorBody]);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Transactions fetched and stored successfully']);
    }

    public function return_url(Request $request)
    {
        if ($request->has('transaction_id') || $request->has('token')) {
            $cinetpay_check = [
                "apikey" => env("APIKEY"),
                "site_id" => env("SITE_ID"),
                "secret_key" => env("SECRET"),
                "transaction_id" => $request->input('transaction_id')
            ];
            $response = $this->getPayStatus($cinetpay_check);
            $response_body = json_decode($response, true);
            if ($response_body['code'] == '00') {
                return back()->with('info', 'Félicitations, votre paiement a été effectué avec succès');
            } else {
                return back()->with('info', 'Échec, votre paiement a échoué');
            }
        } else {
            return back()->with('info', 'Transaction non fourni');
        }
    }

    private function getPayStatus($data)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'apikey' => env("APIKEY"),
        ])->post('https://api-checkout.cinetpay.com/v2/payment/check', $data);

        if ($response->successful()) {
            return $response->body();
        } else {
            $errorBody = $response->body();
            Log::error('Failed to check payment status', ['response' => $errorBody]);
            return json_encode(['code' => 'error', 'message' => 'Failed to check payment status']);
        }
    }
}




