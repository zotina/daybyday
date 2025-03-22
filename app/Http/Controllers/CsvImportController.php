<?php
namespace App\Http\Controllers;

use App\DTOs\UserDto;
use App\Services\Csv\CsvService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;

class CsvImportController extends Controller
{
    protected $csvService;

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
    }

    public function index()
    {
        return view('csv.import');
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $fileName = 'users_import_' . time() . '.csv';
        $file->move(storage_path('app/imports'), $fileName);
        $fullPath = storage_path('app/imports/' . $fileName);

        DB::beginTransaction();

        try {
            $userDtos = $this->csvService->importCsv($fullPath, UserDto::class);
            
            $count = 0;
            foreach ($userDtos as $userDto) {
                if (is_string($userDto->number)) {
                    $processedNumber = array_map('trim', explode(',', $userDto->number));
                    Log::info('Tableau après traitement:', $processedNumber);
                }
            
                User::updateOrCreate(
                    ['email' => $userDto->email],
                    [
                        'external_id' => $userDto->external_id ?? Uuid::uuid4()->toString(),
                        'name' => $userDto->name,
                        'password' => Hash::make($userDto->password),
                        'address' => $userDto->address ?? '',
                        'primary_number' => $processedNumber[0],
                        'secondary_number' => $processedNumber[1] ?? null,
                        'image_path' => $userDto->image_path ?? '',
                        'remember_token' => $userDto->remember_token ?? null,
                        'created_at' => $userDto->created_at ?? now(),
                        'updated_at' => $userDto->updated_at ?? now(),
                    ]
                );
                $count++;
            }

            DB::commit();
            return back()->with('success', 'Imported ' . $count . ' users successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error importing users: ' . $e->getMessage());
        }
    }
}
