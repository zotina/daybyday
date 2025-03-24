<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceStatus extends Model
{
    protected $table = 'v_invoices_status'; // Nom de la vue
    public $timestamps = false; // Pas de timestamps dans une vue

    protected $fillable = [
        'Nom du Statut',
        'Nombre de Factures',
        'Pourcentage'
    ];

    protected $casts = [
        'Nom du Statut' => 'string',
        'Nombre de Factures' => 'integer',
        'Pourcentage' => 'float'
    ];
}