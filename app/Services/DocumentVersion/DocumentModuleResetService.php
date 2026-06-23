<?php

namespace App\Services\DocumentVersion;

use App\Models\DApplication;
use App\Models\DDocument;
use App\Models\DEducation;
use App\Models\DExperience;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentModuleResetService
{
    public function __construct(
        protected DocumentStorageService $storageService
    ) {}

    /**
     * @return array{documents: int, educations: int, experiences: int, applications: int, files_removed: int}
     */
    public function resetAll(): array
    {
        return DB::transaction(function () {
            $documents = DDocument::query()->count();
            $educations = DEducation::query()->count();
            $experiences = DExperience::query()->count();
            $applications = DApplication::query()->count();

            $this->truncateModuleTables();

            $filesRemoved = $this->wipeStorage();

            return [
                'documents' => $documents,
                'educations' => $educations,
                'experiences' => $experiences,
                'applications' => $applications,
                'files_removed' => $filesRemoved,
            ];
        });
    }

    protected function truncateModuleTables(): void
    {
        $tables = ['d_documents', 'd_educations', 'd_experiences', 'd_applications'];

        match (DB::getDriverName()) {
            'pgsql' => DB::statement(
                'TRUNCATE TABLE ' . implode(', ', $tables) . ' RESTART IDENTITY CASCADE'
            ),
            'sqlite' => $this->truncateSqliteTables($tables),
            default => $this->truncateMysqlTables($tables),
        };
    }

    /**
     * @param  list<string>  $tables
     */
    protected function truncateSqliteTables(array $tables): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        foreach ($tables as $table) {
            DB::table($table)->delete();
            DB::statement('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
        }

        DB::statement('PRAGMA foreign_keys = ON');
    }

    /**
     * @param  list<string>  $tables
     */
    protected function truncateMysqlTables(array $tables): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function wipeStorage(): int
    {
        $disk = Storage::disk($this->storageService->disk());
        $files = $disk->allFiles();

        foreach ($files as $file) {
            $disk->delete($file);
        }

        foreach ($disk->directories('') as $directory) {
            $disk->deleteDirectory($directory);
        }

        return count($files);
    }
}
