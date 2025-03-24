<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDay extends Model
{
    protected $table = 'paymentDay'; 
    public $timestamps = false; 

    protected $fillable = [
        'payment_date',
        'amountTotal'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amountTotal' => 'float'
    ];
}