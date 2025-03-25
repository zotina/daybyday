<?php
namespace App\Services\Csv;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use App\Models\DataSeeder;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;

class CsvImportService
{
    protected $csvService;

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
    }

    public function importCsvFiles($files, $types, $dtoClasses)
    {
        $results = [];
        $allErrors = [];
        $counts = ['clients' => 0, 'tasks' => 0, 'leads' => 0];

        foreach ($types as $index => $type) {
            $file = $files[$type] ?? null;
            if (!$file) {
                $results[$type] = [];
                continue;
            }

            $fileName = "{$type}_import_" . time() . '.csv';
            $file->move(storage_path('app/imports'), $fileName);
            $fullPath = storage_path('app/imports/' . $fileName);

            list($dtos, $errors) = $this->csvService->importCsv($fullPath, $dtoClasses[$index]);
            $results[$type] = $dtos;

            if (!empty($errors)) {
                $allErrors = array_merge($allErrors, $errors->toArray());
            }
        }

        if (!empty($allErrors)) {
            return [$counts, $allErrors];
        }

        DB::beginTransaction();
        try {
            foreach ($types as $index => $type) {
                if (isset($results[$type])) {
                    foreach ($results[$type] as $dto) {
                        $this->{"upsert{$type}"}($dto);
                        $counts[$types[$index] . 's']++;
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error during insertion: ' . $e->getMessage());
            $allErrors[] = ['file' => 'N/A', 'line' => 'N/A', 'errors' => ['Database insertion error: ' . $e->getMessage()]];
        }

        return [$counts, $allErrors];
    }

    protected function upsertClient($dto)
    {
        $user_id = User::getId();

        $client_id = DataSeeder::insertClientForUser($user_id,$dto->client_name);
        DataSeeder::insertProjectForClient($client_id, $dto->project_title);
        
        
        DB::statement("CALL insert_random_contacts(?)", [Client::count()]);
    }

    protected function upsertTask($dto)
    {
        $project_id = Project::getProjectIdByTitle($dto->project_title);
        DataSeeder::insertTaskForProject($project_id, $dto->task_title);
    }

    protected function upsertLead($dto)
    {
        Log::info("begin " . $dto->produit);
        $produit_id = DataSeeder::createProduct($dto->produit);
        Log::info("product");

        $client_id = Client::getIdClientByCompanyName($dto->client_name);

        $lead_id = DataSeeder::createLead($dto->lead_title, $client_id);
        $offer_id = DataSeeder::createOffer($client_id, $lead_id);

        if ($dto->type == "offers") {
            Log::info("offers");
            DataSeeder::createInvoiceLine(null, $offer_id, $dto->prix, $dto->quantite, $produit_id);
        } else {
            Log::info("else");
            $invoice_id = DataSeeder::createInvoice($client_id, $offer_id);
            DataSeeder::createInvoiceLine($invoice_id, null, $dto->prix, $dto->quantite, $produit_id);
        }
    }
}