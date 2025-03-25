<?php

namespace App\Services\table;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class TableResetter
{
    
    protected $tableDependencies;

    
    public function __construct(array $tableDependencies = [])
    {
        $this->tableDependencies = $tableDependencies;
    }

    
    public function resetTable(string $tableName): bool
    {
        try {
            Log::info("Début de la réinitialisation de la table '{$tableName}'");

            
            if (!Schema::hasTable($tableName)) {
                throw new \Exception("La table '{$tableName}' n'existe pas.");
            }

            
            Log::info("Désactivation des contraintes de clés étrangères.");
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            
            Log::info("Vidage de la table '{$tableName}'.");
            DB::table($tableName)->truncate();

            
            Log::info("Réinitialisation de l'AUTO_INCREMENT de la table '{$tableName}'.");
            DB::statement("ALTER TABLE {$tableName} AUTO_INCREMENT = 1");

            
            Log::info("Réactivation des contraintes de clés étrangères.");
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            Log::info("Table '{$tableName}' réinitialisée avec succès.");
            return true;
        } catch (\Exception $e) {
            
            Log::error("Erreur lors de la réinitialisation de '{$tableName}': " . $e->getMessage());
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return false;
        }
    }

    
    public function resetTables(array $tables = []): array
    {
        $results = [];
        $tablesToReset = !empty($tables) ? $tables : $this->tableDependencies;

        if (empty($tablesToReset)) {
            Log::warning("Aucune table spécifiée pour la réinitialisation.");
            return $results;
        }

        Log::info("Début de la réinitialisation de plusieurs tables.");

        foreach ($tablesToReset as $table) {
            $results[$table] = $this->resetTable($table);
        }

        Log::info("Fin de la réinitialisation des tables.");
        return $results;
    }


    public function resetTableExcept(string $tableName, string $column, $value): bool
    {
        try {
            Log::info("Réinitialisation de la table '{$tableName}' en conservant '{$column} = {$value}'");

            if (!Schema::hasTable($tableName)) {
                throw new \Exception("La table '{$tableName}' n'existe pas.");
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table($tableName)->where($column, '!=', $value)->delete();
            
            
            $maxId = DB::table($tableName)->max('id');
            $nextId = ($maxId ?? 0) + 1;
            
            
            DB::statement("ALTER TABLE {$tableName} AUTO_INCREMENT = {$nextId}");
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            Log::info("Table '{$tableName}' nettoyée avec succès sauf '{$column} = {$value}', AUTO_INCREMENT réinitialisé à {$nextId}");
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression des données de '{$tableName}': " . $e->getMessage());
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return false;
        }
    }
    public function resetAllDatabaseTables(): array
    {
        Log::info("Détection automatique de l'ordre des tables.");
        $this->detectTableDependencies();

        Log::info("Réinitialisation de toutes les tables dans l'ordre détecté.");
        return $this->resetTables();
    }

    
    public function setTableDependencies(array $dependencies): self
    {
        $this->tableDependencies = $dependencies;
        return $this;
    }

    
    public function getTableDependencies(): array
    {
        return $this->tableDependencies;
    }

    
    public function detectTableDependencies(): self
    {
        Log::info("Début de la détection des dépendances entre les tables.");

        $tables = Schema::getAllTables(); 
        $foreignKeys = [];

        foreach ($tables as $table) {
            $constraints = DB::select("
                SELECT TABLE_NAME, REFERENCED_TABLE_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$table]);

            foreach ($constraints as $constraint) {
                $foreignKeys[$constraint->TABLE_NAME][] = $constraint->REFERENCED_TABLE_NAME;
            }
        }

        
        $visited = [];
        $sorted = [];

        $visit = function ($table) use (&$visit, &$visited, &$sorted, $foreignKeys) {
            if (isset($visited[$table])) return;
            $visited[$table] = true;

            if (!empty($foreignKeys[$table])) {
                foreach ($foreignKeys[$table] as $dependency) {
                    $visit($dependency);
                }
            }

            $sorted[] = $table;
        };

        foreach ($tables as $table) {
            $visit($table);
        }

        $this->tableDependencies = array_reverse($sorted);

        Log::info("Ordre des tables détecté : " . implode(', ', $this->tableDependencies));
        return $this;
    }
}