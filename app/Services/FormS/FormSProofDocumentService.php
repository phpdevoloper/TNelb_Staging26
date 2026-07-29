<?php

namespace App\Services\FormS;

use App\Models\CC_Forms_Meta;
use App\Models\Competency\CC_CompetencyMeta;
use App\Models\CC_Proof_doc;
use App\Models\CC_Doc_Log;
use App\Models\TnelbApplicantPhoto;
use App\Services\DocumentVersion\DocumentStorageService;
use App\Models\TnelbApplicantsSign;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class FormSProofDocumentService
{
    public const PROOF_AADHAAR = 'AADHAAR';
    public const PROOF_PAN = 'PAN';
    public const PROOF_PHOTO = 'PHOTO';
    public const PROOF_SIGN = 'SIGN';
    public const PROOF_NAME_CHANGE = 'NAME_CHANGE';
    public const PROOF_ADDRESS = 'ADDRESS_PROOF';

    public const ALTERATION_APP_TYPE = 'A';

    /** @var array<string, array{module_type: string, document_type: string, proof_type: string}> */
    private const PROOF_CONFIG = [
        self::PROOF_AADHAAR => [
            'module_type' => 'aadhaar',
            'document_type' => 'aadhaar_doc',
            'proof_type' => 'aadhaar',
        ],
        self::PROOF_PAN => [
            'module_type' => 'pan',
            'document_type' => 'pancard_doc',
            'proof_type' => 'pan',
        ],
        self::PROOF_PHOTO => [
            'module_type' => 'photo',
            'document_type' => 'photo',
            'proof_type' => 'photo',
        ],
        self::PROOF_SIGN => [
            'module_type' => 'signature',
            'document_type' => 'signature',
            'proof_type' => 'signature',
        ],
        self::PROOF_NAME_CHANGE => [
            'module_type' => 'alteration',
            'document_type' => 'name_proof',
            'proof_type' => 'name_change',
        ],
        self::PROOF_ADDRESS => [
            'module_type' => 'alteration',
            'document_type' => 'address_proof',
            'proof_type' => 'address',
        ],
    ];

    public function __construct(
        protected FormSDocumentUploadHandler $uploadHandler,
        protected SensitiveProofCryptService $sensitiveProofCrypt
    ) {}

    /**
     * @return array{module_type: string, document_type: string, proof_type: string}
     */
    public static function configFor(string $proofName): array
    {
        if (! isset(self::PROOF_CONFIG[$proofName])) {
            throw new RuntimeException('Unknown proof document type: ' . $proofName);
        }

        return self::PROOF_CONFIG[$proofName];
    }

    public static function isAlterationProof(string $proofName): bool
    {
        return in_array($proofName, [self::PROOF_NAME_CHANGE, self::PROOF_ADDRESS], true);
    }

    public static function proofNameFromAlterationDocumentType(string $documentType): string
    {
        return match ($documentType) {
            'name_proof' => self::PROOF_NAME_CHANGE,
            'address_proof' => self::PROOF_ADDRESS,
            default => throw new RuntimeException('Unknown alteration document type: ' . $documentType),
        };
    }

    public function hasProofDocument(string $applicationId, string $proofName, ?string $appType = null): bool
    {
        if ($this->hasStoredProofPath($applicationId, $proofName, $appType)) {
            return true;
        }

        if ($appType === self::ALTERATION_APP_TYPE || self::isAlterationProof($proofName)) {
            return $this->hasAlterationProofVersion($applicationId, $proofName);
        }

        return false;
    }

    public function resolveProofPath(string $applicationId, string $proofName, ?string $appType = null): ?string
    {
        $path = $this->resolveStoredProofPath($applicationId, $proofName, $appType);
        if ($path) {
            return $path;
        }

        if ($appType === self::ALTERATION_APP_TYPE || self::isAlterationProof($proofName)) {
            return $this->resolveAlterationProofPathFromLog($applicationId, $proofName);
        }

        return null;
    }

    protected function hasStoredProofPath(string $applicationId, string $proofName, ?string $appType = null): bool
    {
        return $this->resolveStoredProofPath($applicationId, $proofName, $appType) !== null;
    }

    protected function resolveStoredProofPath(string $applicationId, string $proofName, ?string $appType = null): ?string
    {
        $query = CC_Proof_doc::where('application_id', $applicationId)
            ->where('proof_name', $proofName);

        if ($appType !== null) {
            $query->where('app_type', $appType);
        }

        $path = $query->value('proof_doc');

        return $path ?: null;
    }

    protected function hasAlterationProofVersion(string $applicationId, string $proofName): bool
    {
        return $this->resolveAlterationProofPathFromLog($applicationId, $proofName) !== null
            || CC_Proof_doc::where('application_id', $applicationId)
                ->where('proof_name', $proofName)
                ->where('app_type', self::ALTERATION_APP_TYPE)
                ->exists();
    }

    protected function resolveAlterationProofPathFromLog(string $applicationId, string $proofName): ?string
    {
        $proof = CC_Proof_doc::where('application_id', $applicationId)
            ->where('proof_name', $proofName)
            ->where('app_type', self::ALTERATION_APP_TYPE)
            ->first();

        if (! $proof) {
            return null;
        }

        $workflowPk = (int) (\App\Models\CC_Forms_meta::where('application_id', $applicationId)->value('app_id') ?? 0);
        if ($workflowPk <= 0) {
            return null;
        }

        $config = self::configFor($proofName);
        $log = \App\Models\CC_Doc_Log::forGroup(
            $workflowPk,
            $config['module_type'],
            (int) $proof->getKey(),
            $config['document_type']
        )
            ->orderByDesc('version_no')
            ->first();

        return $log?->file_path ?: null;
    }

    /**
     * @return list<object{document_type: string, label: string, file_name: string, url: ?string, proof_doc: ?string}>
     */
    public function collectAlterationProofsForReview(string $alterationApplicationId, array $workflowAppPks = []): array
    {
        $rows = CC_Proof_doc::where('application_id', $alterationApplicationId)
            ->where('app_type', self::ALTERATION_APP_TYPE)
            ->whereIn('proof_name', [self::PROOF_NAME_CHANGE, self::PROOF_ADDRESS])
            ->get();

        $itemsByType = [];

        foreach ($rows as $row) {
            $config = self::configFor((string) $row->proof_name);
            $documentType = $config['document_type'];
            $storedPath = (string) ($row->proof_doc ?: $this->resolveAlterationProofPathFromLog(
                $alterationApplicationId,
                (string) $row->proof_name
            ) ?? '');

            $item = $this->buildAlterationProofReviewItem(
                (string) $row->proof_name,
                $storedPath,
                (int) $row->getKey(),
                $workflowAppPks
            );

            if ($item) {
                $itemsByType[$documentType] = $item;
            }
        }

        foreach ($this->collectAlterationProofsFromDocLogs($workflowAppPks) as $item) {
            $documentType = (string) ($item->document_type ?? '');
            if ($documentType !== '' && ! isset($itemsByType[$documentType])) {
                $itemsByType[$documentType] = $item;
            }
        }

        return array_values($itemsByType);
    }

    /**
     * @param  list<int>  $workflowAppPks
     */
    protected function buildAlterationProofReviewItem(
        string $proofName,
        string $storedPath,
        int $moduleRefId,
        array $workflowAppPks
    ): ?object {
        $storedPath = trim($storedPath);
        if ($storedPath === '') {
            return null;
        }

        $config = self::configFor($proofName);
        $url = competency_document_path_url($storedPath);
        if (! $url) {
            $url = competency_document_url(
                $storedPath,
                $config['module_type'],
                $moduleRefId,
                $config['document_type'],
                $workflowAppPks
            );
        }
        if (! $url) {
            $url = competency_media_url($storedPath);
        }

        return (object) [
            'document_type' => $config['document_type'],
            'label' => match ($proofName) {
                self::PROOF_NAME_CHANGE => 'Name alteration supporting proof',
                self::PROOF_ADDRESS => 'Address alteration supporting proof',
                default => ucwords(str_replace('_', ' ', strtolower($proofName))),
            },
            'file_name' => basename($storedPath),
            'url' => $url,
            'proof_doc' => $storedPath,
        ];
    }

    /**
     * @param  list<int>  $workflowAppPks
     * @return list<object{document_type: string, label: string, file_name: string, url: ?string, proof_doc: ?string}>
     */
    protected function collectAlterationProofsFromDocLogs(array $workflowAppPks): array
    {
        $items = [];
        $seenTypes = [];

        foreach ($workflowAppPks as $workflowPk) {
            if ($workflowPk <= 0) {
                continue;
            }

            $logs = \App\Models\CC_Doc_Log::query()
                ->where('application_id', $workflowPk)
                ->where('module_type', 'alteration')
                ->whereIn('document_type', ['name_proof', 'address_proof'])
                ->orderByDesc('doc_id')
                ->get();

            foreach ($logs as $log) {
                $documentType = (string) ($log->document_type ?? '');
                if ($documentType === '' || isset($seenTypes[$documentType])) {
                    continue;
                }

                $storedPath = trim((string) ($log->file_path ?? ''));
                if ($storedPath === '') {
                    continue;
                }

                $seenTypes[$documentType] = true;
                $proofName = self::proofNameFromAlterationDocumentType($documentType);
                $item = $this->buildAlterationProofReviewItem(
                    $proofName,
                    $storedPath,
                    (int) ($log->module_ref_id ?? 0),
                    $workflowAppPks
                );

                if ($item) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    public function saveAlterationProofUpload(object $alterationWorkflow, UploadedFile $file, string $documentType): ?string
    {
        $proofName = self::proofNameFromAlterationDocumentType($documentType);
        $applicationId = (string) $alterationWorkflow->application_id;
        $proof = $this->ensureProofRow($applicationId, self::ALTERATION_APP_TYPE, $proofName);

        $path = $this->uploadHandler->handleAlterationProofUpload($alterationWorkflow, $proof, $file);

        if ($path) {
            $proof->update([
                'proof_doc' => $path,
                'updated_at' => now()->toDateString(),
            ]);
        }

        return $path;
    }

    public function syncAlterationProofFromLog(CC_CompetencyMeta $workflow, string $documentType): ?string
    {
        $proofName = self::proofNameFromAlterationDocumentType($documentType);
        $applicationId = (string) $workflow->application_id;
        $proof = $this->ensureProofRow($applicationId, self::ALTERATION_APP_TYPE, $proofName);

        if (trim((string) ($proof->proof_doc ?? '')) !== '') {
            return trim((string) $proof->proof_doc);
        }

        $path = $this->resolveAlterationProofPathFromLog($applicationId, $proofName);
        if ($path) {
            $proof->update([
                'proof_doc' => $path,
                'updated_at' => now()->toDateString(),
            ]);
        }

        return $path;
    }

    public function ensureProofRow(
        string $applicationId,
        string $appType,
        string $proofName,
        ?string $proofNo = null
    ): CC_Proof_doc {
        $config = self::configFor($proofName);
        $proof = CC_Proof_doc::firstOrNew([
            'application_id' => $applicationId,
            'proof_name' => $proofName,
        ]);

        $isNew = ! $proof->exists;
        $proof->app_type = $appType;
        $proof->proof_type = $config['proof_type'];
        if ($proofNo !== null && $proofNo !== '') {
            $proof->proof_no = self::requiresEncryption($proofName)
                ? $this->sensitiveProofCrypt->encryptProofNumber($proofNo)
                : $proofNo;
        }
        $proof->status = $proof->status ?: 'A';
        $proof->updated_at = now()->toDateString();
        if ($isNew) {
            $proof->created_at = now()->toDateString();
        }
        $proof->save();

        return $proof->fresh();
    }

    public function syncProofNumber(
        string $applicationId,
        string $appType,
        string $proofName,
        ?string $proofNo
    ): void {
        if ($proofNo === null || $proofNo === '') {
            return;
        }

        $this->ensureProofRow($applicationId, $appType, $proofName, $proofNo);
    }

    public function clearProofDocument(string $applicationId, string $proofName): void
    {
        CC_Proof_doc::where('application_id', $applicationId)
            ->where('proof_name', $proofName)
            ->update([
                'proof_doc' => null,
                'updated_at' => now()->toDateString(),
            ]);
    }

    /**
     * Encrypt legacy plain PDF proof files (.pdf) to encrypted .bin on disk and update DB paths.
     */
    public function ensureProofDocumentEncryptedAtRest(string $applicationId, string $proofName): void
    {
        if (! self::requiresEncryption($proofName)) {
            return;
        }

        $proof = CC_Proof_doc::where('application_id', $applicationId)
            ->where('proof_name', $proofName)
            ->first();

        if (! $proof || empty($proof->proof_doc)) {
            return;
        }

        $oldPath = trim(str_replace('\\', '/', (string) $proof->proof_doc));
        if ($oldPath === '' || SensitiveProofCryptService::isEncryptedProofDocumentPath($oldPath)) {
            return;
        }

        $newPath = app(DocumentStorageService::class)->encryptPlainProofFileAtPath($oldPath);
        if ($newPath === null || $newPath === $oldPath) {
            return;
        }

        $proof->update([
            'proof_doc' => $newPath,
            'updated_at' => now()->toDateString(),
        ]);

        CC_Doc_Log::query()
            ->where('file_path', $oldPath)
            ->update([
                'file_path' => $newPath,
                'file_name' => basename($newPath),
            ]);

        CC_Doc_Log::query()
            ->where('old_file_path', $oldPath)
            ->update(['old_file_path' => $newPath]);
    }

    public function saveProofUpload(
        CC_CompetencyMeta $workflowApp,
        string $masterApplicationId,
        string $appType,
        string $proofName,
        UploadedFile $file,
        ?string $proofNo,
        ?string $formName,
        ?string $replacementReason = null
    ): ?string {
        $proof = $this->ensureProofRow($masterApplicationId, $appType, $proofName, $proofNo);

        if ($this->uploadHandler->usesVersionedStorage($formName)) {
            $path = $this->uploadHandler->handleProofUpload(
                $workflowApp,
                $proof,
                $file,
                $replacementReason
            );
        } else {
            $path = $this->storeLegacyProofFile($proofName, $file);
        }

        // Only update master proof_doc when we have an approved path.
        // Pending renewal/alteration replacements stay in cc_doc_log until approved.
        if ($path && trim((string) $path) !== trim((string) ($proof->proof_doc ?? ''))) {
            $proof->update([
                'proof_doc' => $path,
                'updated_at' => now()->toDateString(),
            ]);
        }

        return $path ?? $proof->fresh()->proof_doc;
    }

    public function saveProofUploadWithoutWorkflow(
        string $masterApplicationId,
        string $appType,
        string $proofName,
        UploadedFile $file,
        ?string $proofNo = null
    ): ?string {
        $proof = $this->ensureProofRow($masterApplicationId, $appType, $proofName, $proofNo);
        $path = $this->storeLegacyProofFile($proofName, $file);

        $proof->update([
            'proof_doc' => $path,
            'updated_at' => now()->toDateString(),
        ]);

        return $path;
    }

    /**
     * Legacy-compatible object for views expecting upload_path.
     */
    public function loadPhotoForView(string $applicationId): ?object
    {
        $path = $this->resolveProofPath($applicationId, self::PROOF_PHOTO);
        if ($path) {
            return (object) ['upload_path' => $path];
        }

        return TnelbApplicantPhoto::where('application_id', $applicationId)->first();
    }

    /**
     * Legacy-compatible object for views expecting uploaded_doc.
     */
    public function loadSignForView(string $applicationId): ?object
    {
        $path = $this->resolveProofPath($applicationId, self::PROOF_SIGN);
        if ($path) {
            return (object) ['uploaded_doc' => $path];
        }

        return TnelbApplicantsSign::where('application_id', $applicationId)->first();
    }

    protected function storeLegacyProofFile(string $proofName, UploadedFile $file): string
    {
        if (self::requiresEncryption($proofName)) {
            return $this->storeLegacyEncryptedProofFile($proofName, $file);
        }

        $prefix = match ($proofName) {
            self::PROOF_PHOTO => 'user_',
            self::PROOF_SIGN => 'sign_',
            self::PROOF_AADHAAR => 'aadhaar_',
            self::PROOF_PAN => 'pan_',
            self::PROOF_NAME_CHANGE => 'name_change_',
            self::PROOF_ADDRESS => 'address_proof_',
            default => 'proof_',
        };

        $destination = public_path('attached_documents');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $filename = $prefix . time() . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);

        return 'attached_documents/' . $filename;
    }

    protected function storeLegacyEncryptedProofFile(string $proofName, UploadedFile $file): string
    {
        $contents = file_get_contents($file->getRealPath());
        if ($contents === false) {
            throw new RuntimeException('Unable to read uploaded proof document.');
        }

        $encrypted = $this->sensitiveProofCrypt->encryptFileContents($contents);
        $suffix = $proofName === self::PROOF_PAN ? '_pan' : '';
        $filename = time() . '_' . random_int(10000, 9999999) . $suffix . '.bin';
        $destination = storage_path('app/private_documents');

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        file_put_contents($destination . DIRECTORY_SEPARATOR . $filename, $encrypted);

        return $filename;
    }

    public static function requiresEncryption(string $proofName): bool
    {
        return SensitiveProofCryptService::requiresEncryption($proofName);
    }
}
