<?php

namespace App\Jobs;

use App\Imports\StudentsImport;
use App\Models\StudentBatchModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessStudentBatchImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries   = 1;

    public function __construct(
        protected string $filePath,
        protected int $batchId,
        protected string $progressKey,
        protected int $schoolclassid,
        protected int $termid,
        protected int $sessionid,
        protected ?int $userId = null
    ) {}

    public function handle(): void
    {
        $batch = StudentBatchModel::find($this->batchId);

        if (!$batch) {
            Log::error("ProcessStudentBatchImport: batch {$this->batchId} not found.");
            return;
        }

        if (!Storage::disk('local')->exists($this->filePath)) {
            $this->markFailed($batch, 'Uploaded file is missing — it may have been cleaned up before processing started.');
            return;
        }

        $totalRows = $this->countDataRows();

        Cache::put($this->progressKey, [
            'status'   => 'processing',
            'progress' => 0,
            'total'    => $totalRows,
            'message'  => 'Import started',
        ], now()->addMinutes(45));

        try {
            $import = new StudentsImport(
                $this->schoolclassid,
                $this->termid,
                $this->sessionid,
                $this->batchId,
                $this->userId
            );
            $import->setProgressTracking($this->progressKey, $totalRows);

            Excel::import($import, $this->filePath, 'local');

            $this->finalizeResult($batch, $import, $totalRows);

        } catch (\Throwable $e) {
            Log::error('ProcessStudentBatchImport fatal error: ' . $e->getMessage(), [
                'batch_id' => $this->batchId,
                'trace'    => $e->getTraceAsString(),
            ]);
            $this->markFailed($batch, $e->getMessage(), $totalRows);

        } finally {
            Storage::disk('local')->delete($this->filePath);
        }
    }

    protected function finalizeResult(StudentBatchModel $batch, StudentsImport $import, int $totalRows): void
    {
        $details = [];

        foreach ($import->failures() as $failure) {
            $details[] = [
                'row'       => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors'    => $failure->errors(),
            ];
        }

        foreach ($import->errors() as $error) {
            $details[] = [
                'row'       => null,
                'attribute' => null,
                'errors'    => [$error->getMessage()],
            ];
        }

        $failedCount = count($details);

        if ($totalRows === 0) {
            $status  = 'Failed';
            $message = 'The file contained no data rows to import.';
        } elseif ($failedCount === 0) {
            $status  = 'Success';
            $message = "All {$totalRows} row(s) imported successfully.";
        } elseif ($failedCount >= $totalRows) {
            $status  = 'Failed';
            $message = "All {$totalRows} row(s) failed to import.";
        } else {
            $status   = 'Partial';
            $imported = $totalRows - $failedCount;
            $message  = "{$imported} of {$totalRows} row(s) imported. {$failedCount} row(s) failed — see details.";
        }

        $batch->update([
            'status'        => $status,
            'import_errors' => $failedCount > 0 ? json_encode($details) : null,
        ]);

        Cache::put($this->progressKey, [
            'status'   => strtolower($status) === 'success' ? 'complete' : ($status === 'Partial' ? 'partial' : 'failed'),
            'progress' => $totalRows,
            'total'    => $totalRows,
            'message'  => $message,
        ], now()->addMinutes(45));
    }

protected function countDataRows(): int
{
    try {
        // Only read the "Student Data" sheet
        $sheets = Excel::toArray([], $this->filePath, 'local');

        // Find the sheet named "Student Data"
        // (Maatwebsite returns sheets in the order they appear)
        $dataSheet = null;

        // Try by name first (safer)
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile(
            Storage::disk('local')->path($this->filePath)
        );
        $spreadsheet = $reader->load(Storage::disk('local')->path($this->filePath));

        $sheet = $spreadsheet->getSheetByName('Student Data');
        if ($sheet) {
            $rows = $sheet->toArray();
            return max(0, count($rows) - 1); // exclude header
        }

        // Fallback: first sheet that has the expected headers
        foreach ($sheets as $sheetRows) {
            if (!empty($sheetRows[0]) && in_array('Admission No*', $sheetRows[0])) {
                return max(0, count($sheetRows) - 1);
            }
        }

        // Last resort
        return max(0, count($sheets[0] ?? []) - 1);

    } catch (\Throwable $e) {
        Log::warning('Could not pre-count batch import rows: ' . $e->getMessage());
        return 0;
    }
}

    protected function markFailed(StudentBatchModel $batch, string $message, int $total = 0): void
    {
        $batch->update([
            'status'        => 'Failed',
            'import_errors' => json_encode([['row' => null, 'attribute' => null, 'errors' => [$message]]]),
        ]);

        Cache::put($this->progressKey, [
            'status'   => 'failed',
            'progress' => 0,
            'total'    => $total,
            'message'  => $message,
        ], now()->addMinutes(45));
    }

    public function failed(\Throwable $exception): void
    {
        $batch = StudentBatchModel::find($this->batchId);
        if ($batch) {
            $this->markFailed($batch, $exception->getMessage());
        }
        Storage::disk('local')->delete($this->filePath);
    }
}