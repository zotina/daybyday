<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    protected $table = 'v_tasks_tatus'; // Nom de la vue
    public $timestamps = false; // Pas de timestamps dans une vue

    protected $fillable = [
        'Nom du Statut',
        'Nombre de Tâches',
        'Pourcentage'
    ];

    protected $casts = [
        'Nom du Statut' => 'string',
        'Nombre de Tâches' => 'integer',
        'Pourcentage' => 'float'
    ];
}