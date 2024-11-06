<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-email', function () {
    
    try {
        Mail::raw('This is a test email.', function ($message) {
            $message->to('exemple@gmail.com')
                    ->subject('Test Email');
        });
        return response()->json(['message' => 'Email de test envoyé avec succès !']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

