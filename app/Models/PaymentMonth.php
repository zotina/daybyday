<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMonth extends Model
{
    protected $table = 'paymentMonth'; // Nom de la vue
    public $timestamps = false; // Pas de timestamps dans une vue

    protected $fillable = [
        'payment_month',
        'amountTotal'
    ];

    protected $casts = [
        'payment_month' => 'string', // Format 'YYYY-MM'
        'amountTotal' => 'float'
    ];
}