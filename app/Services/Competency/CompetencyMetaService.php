<?php

namespace App\Services\Competency;

use App\Models\Competency\CC_CompetencyMeta;
use App\Models\Competency\CC_Form_P_Meta;
use App\Models\Competency\CC_Form_S_Meta;
use App\Models\Competency\CC_Form_W_Meta;
use App\Models\Competency\CC_Form_WH_Meta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Resolve per-form competency meta tables:
 * S → cc_form_s_meta, W → cc_form_w_meta, WH → cc_form_wh_meta, P → cc_form_p_meta.
 *
 * Education, experience, and proof documents use shared tables (see CompetencySchema):
 * cc_edu, cc_exp, cc_proof_doc — NOT split per form.
 */
class CompetencyMetaService
{
    /** @var array<string, string> */
    public const FORM_META_TABLES = [
        'S' => 'cc_form_s_meta',
        'W' => 'cc_form_w_meta',
        'WH' => 'cc_form_wh_meta',
        'P' => 'cc_form_p_meta',
    ];

    /** @var array<int, string> mst_licences.id */
    public const FORM_ID_META_TABLES = [
        1 => 'cc_form_s_meta',
        2 => 'cc_form_w_meta',
        3 => 'cc_form_wh_meta',
        6 => 'cc_form_p_meta',
    ];

    /** @var array<string, class-string<CC_CompetencyMeta>> */
    private const FORM_MODEL_MAP = [
        'S' => CC_Form_S_Meta::class,
        'W' => CC_Form_W_Meta::class,
        'WH' => CC_Form_WH_Meta::class,
        'P' => CC_Form_P_Meta::class,
    ];

    /** @return list<string> */
    public function allMetaTables(): array
    {
        return array_values(self::FORM_META_TABLES);
    }

    public function supportsForm(?string $formName): bool
    {
        return isset(self::FORM_META_TABLES[$this->normalizeFormName($formName)]);
    }

    public function tableForForm(?string $formName): ?string
    {
        $code = $this->normalizeFormName($formName);

        return self::FORM_META_TABLES[$code] ?? null;
    }

    public function tableForFormId(int $formId): ?string
    {
        return self::FORM_ID_META_TABLES[$formId] ?? null;
    }

    /** @return class-string<CC_CompetencyMeta> */
    public function modelClassForForm(string $formName): string
    {
        $code = $this->normalizeFormName($formName);
        if (! isset(self::FORM_MODEL_MAP[$code])) {
            throw new InvalidArgumentException("Unsupported competency meta form: {$formName}");
        }

        return self::FORM_MODEL_MAP[$code];
    }

    public function findModel(string $applicationId, ?string $formName = null): ?CC_CompetencyMeta
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return null;
        }

        if ($formName !== null && $formName !== '') {
            $class = $this->modelClassForForm($formName);

            return $class::where('application_id', $applicationId)->first();
        }

        foreach (self::FORM_MODEL_MAP as $class) {
            /** @var CC_CompetencyMeta|null $row */
            $row = $class::where('application_id', $applicationId)->first();
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    public function findRow(string $applicationId, ?string $formName = null): ?object
    {
        $model = $this->findModel($applicationId, $formName);

        return $model ? (object) $model->toArray() : null;
    }

    public function metaTableForApplicationId(string $applicationId): ?string
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return null;
        }

        foreach (self::FORM_META_TABLES as $table) {
            if (DB::table($table)->where('application_id', $applicationId)->exists()) {
                return $table;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForForm(string $formName, array $attributes): CC_CompetencyMeta
    {
        $class = $this->modelClassForForm($formName);

        /** @var CC_CompetencyMeta $row */
        $row = $class::create($attributes);

        return $row;
    }

    public function findModelByAppId(int $appId): ?CC_CompetencyMeta
    {
        foreach (self::FORM_MODEL_MAP as $class) {
            /** @var CC_CompetencyMeta|null $row */
            $row = $class::where('app_id', $appId)->first();
            if ($row) {
                return $row;
            }
        }

        return null;
    }

    public function normalizeFormName(?string $formName): string
    {
        return strtoupper(trim((string) $formName));
    }

    public function latestApplicationId(): ?string
    {
        $latest = null;
        $latestNum = -1;

        foreach ($this->allMetaTables() as $table) {
            $applicationId = DB::table($table)->orderByDesc('app_id')->value('application_id');
            if (! $applicationId) {
                continue;
            }

            $num = (int) substr((string) $applicationId, -7);
            if ($num > $latestNum) {
                $latestNum = $num;
                $latest = (string) $applicationId;
            }
        }

        return $latest;
    }

    public function nextAppIdForForm(string $formName): int
    {
        $table = $this->tableForForm($formName);
        if ($table === null) {
            return 1;
        }

        return (int) (DB::table($table)->max('app_id') ?? 0) + 1;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateOrCreateForForm(string $formName, string $applicationId, array $attributes): CC_CompetencyMeta
    {
        $existing = $this->findModel($applicationId, $formName);
        if ($existing) {
            $existing->update($attributes);

            return $existing->fresh();
        }

        return $this->createForForm($formName, array_merge(['application_id' => $applicationId], $attributes));
    }
}
