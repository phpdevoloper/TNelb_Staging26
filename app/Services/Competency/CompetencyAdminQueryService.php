<?php

namespace App\Services\Competency;

use App\Helpers\RoleHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Admin queue/list queries for competency apps stored in per-form meta tables
 * (cc_form_s_meta, cc_form_w_meta, cc_form_wh_meta, cc_form_p_meta).
 */
class CompetencyAdminQueryService
{
    /** mst_licences.id values for competency meta forms. */
    public const CC_META_FORM_IDS = [1, 2, 3, 6];

    /** @var array<int, string> */
    public const FORM_ID_TO_CODE = [
        1 => 'S',
        2 => 'W',
        3 => 'WH',
        6 => 'P',
    ];

    public function isCcMetaFormId(int $formId): bool
    {
        return in_array($formId, self::CC_META_FORM_IDS, true);
    }

    public function formCodeForFormId(int $formId): ?string
    {
        return self::FORM_ID_TO_CODE[$formId] ?? null;
    }

    public function workflowTableForFormId(int $formId): ?string
    {
        $code = $this->formCodeForFormId($formId);

        return $code ? app(CompetencyWorkflowService::class)->tableForForm($code) : null;
    }

    public function metaTableForFormId(int $formId): ?string
    {
        return app(CompetencyMetaService::class)->tableForFormId($formId);
    }

    /** Standard cc_form_s_meta row shape expected by admin supervisor views. */
    public function metaSelectColumns(): array
    {
        return [
            'ta.app_id as id',
            'ta.app_id',
            'ta.application_id',
            'ta.login_id',
            'ta.applicant_name',
            'ta.form_name',
            'ta.form_id',
            'ta.appl_type',
            DB::raw('TRIM(ta.app_status) as status'),
            DB::raw('TRIM(ta.app_status) as app_status'),
            DB::raw('TRIM(ta.payment_status) as payment_status'),
            'ta.processed_by',
            'ta.submitted_date',
            'ta.created_at',
            'ta.updated_at',
            'ta.old_application',
            'ta.certificate_name',
            DB::raw('ta.certificate_name as license_name'),
            DB::raw('ml.licence_name as licence_name'),
            DB::raw('COALESCE(ml.form_name, ta.form_name) as form_label'),
        ];
    }

    public function paidPaymentConstraint(string $alias = 'ta'): \Closure
    {
        return function ($query) use ($alias) {
            $query->whereIn(DB::raw("LOWER(TRIM({$alias}.payment_status))"), ['y', 'payment', 'paid']);
        };
    }

    /**
     * Supervisor pending queue from cc_form_s_meta + cc_workflow_*.
     */
    public function supervisorPendingApplications(int $formId, ?string $applTypeFilter = null, $staff = null): Collection
    {
        $workflowTable = $this->workflowTableForFormId($formId);
        if ($workflowTable === null) {
            return collect();
        }

        $metaTable = $this->metaTableForFormId($formId);
        if ($metaTable === null) {
            return collect();
        }

        $supervisorRoleId = RoleHelper::supervisorWorkflowRoleId($staff ?? auth()->user());

        $twLast = DB::table($workflowTable)
            ->select('application_id', DB::raw('MAX(w_id) as max_id'))
            ->groupBy('application_id');

        $query = DB::table("{$metaTable} as ta")
            ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
            ->leftJoinSub($twLast, 'tw_last', function ($join) {
                $join->on('ta.application_id', '=', 'tw_last.application_id');
            })
            ->leftJoin("{$workflowTable} as tw", function ($join) {
                $join->on('tw.application_id', '=', 'tw_last.application_id')
                    ->on('tw.w_id', '=', 'tw_last.max_id');
            })
            ->where('ta.form_id', $formId)
            ->whereIn(DB::raw('TRIM(ta.app_status)'), ['P', 'RE'])
            ->where($this->paidPaymentConstraint('ta'))
            ->where(function ($q) use ($supervisorRoleId) {
                $q->whereNull('tw.w_id')
                    ->orWhere(function ($q2) use ($supervisorRoleId) {
                        $q2->where('tw.forwarded_to', $supervisorRoleId)
                            ->where(DB::raw('TRIM(tw.appl_status)'), 'RE');
                    });
            });

        if ($applTypeFilter) {
            $query->where('ta.appl_type', $applTypeFilter);
        }

        $workflows = $query
            ->select(array_merge($this->metaSelectColumns(), [
                DB::raw("(SELECT COUNT(*) FROM {$workflowTable} tw2 WHERE tw2.application_id = ta.application_id AND TRIM(tw2.appl_status) = 'QU') > 0 AS has_return_history"),
            ]))
            ->distinct()
            ->orderByDesc('ta.submitted_date')
            ->orderByDesc('ta.app_id')
            ->get();

        if ($workflows->isEmpty()) {
            $fallback = DB::table("{$metaTable} as ta")
                ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
                ->where('ta.form_id', $formId)
                ->whereIn(DB::raw('TRIM(ta.app_status)'), ['P', 'RE'])
                ->where($this->paidPaymentConstraint('ta'));

            if ($applTypeFilter) {
                $fallback->where('ta.appl_type', $applTypeFilter);
            }

            $workflows = $fallback
                ->select(array_merge($this->metaSelectColumns(), [
                    DB::raw("(SELECT COUNT(*) FROM {$workflowTable} tw2 WHERE tw2.application_id = ta.application_id AND TRIM(tw2.appl_status) = 'QU') > 0 AS has_return_history"),
                ]))
                ->orderByDesc('ta.submitted_date')
                ->orderByDesc('ta.app_id')
                ->get();
        }

        return $workflows;
    }

    public function supervisorReturnedApplications(int $formId, ?string $applTypeFilter = null): Collection
    {
        $workflowTable = $this->workflowTableForFormId($formId);
        $metaTable = $this->metaTableForFormId($formId);
        if ($workflowTable === null || $metaTable === null) {
            return collect();
        }

        $query = DB::table("{$metaTable} as ta")
            ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
            ->where('ta.form_id', $formId)
            ->where($this->paidPaymentConstraint('ta'))
            ->where(function ($q) use ($workflowTable) {
                $q->where(DB::raw('TRIM(ta.app_status)'), 'QU')
                    ->orWhereRaw("(TRIM(ta.app_status) IN ('P','RE') AND EXISTS (SELECT 1 FROM {$workflowTable} tw WHERE tw.application_id = ta.application_id AND TRIM(tw.appl_status) = 'QU'))");
            });

        if ($applTypeFilter) {
            $query->where('ta.appl_type', $applTypeFilter);
        }

        return $query
            ->select($this->metaSelectColumns())
            ->orderByDesc('ta.submitted_date')
            ->orderByDesc('ta.app_id')
            ->get();
    }

    /**
     * Non-supervisor role: apps forwarded to current role via cc_workflow_*.
     */
    public function rolePendingApplications(int $formId, int $roleId, array $previousProcessedBy, ?string $applTypeFilter = null): Collection
    {
        $workflowTable = $this->workflowTableForFormId($formId);
        $metaTable = $this->metaTableForFormId($formId);
        if ($workflowTable === null || $metaTable === null) {
            return collect();
        }

        $twLast = DB::table($workflowTable)
            ->select('application_id', DB::raw('MAX(w_id) as max_id'))
            ->groupBy('application_id');

        $currentAppIds = DB::table("{$workflowTable} as tw")
            ->joinSub($twLast, 'tw_last', function ($join) {
                $join->on('tw.application_id', '=', 'tw_last.application_id')
                    ->on('tw.w_id', '=', 'tw_last.max_id');
            })
            ->where('tw.forwarded_to', $roleId)
            ->whereIn(DB::raw('TRIM(tw.appl_status)'), ['F', 'RF'])
            ->select('tw.application_id');

        $fallbackAppIds = DB::table("{$metaTable} as ta")
            ->where('ta.form_id', $formId)
            ->whereIn(DB::raw('TRIM(ta.app_status)'), ['F', 'RF'])
            ->where($this->paidPaymentConstraint('ta'))
            ->when($previousProcessedBy !== [], fn ($q) => $q->whereIn('ta.processed_by', $previousProcessedBy))
            ->whereNotExists(function ($q) use ($workflowTable) {
                $q->select(DB::raw(1))
                    ->from("{$workflowTable} as tw")
                    ->whereRaw('tw.application_id = ta.application_id');
            })
            ->select('ta.application_id');

        $allIds = $currentAppIds->union($fallbackAppIds);

        $query = DB::query()
            ->fromSub($allIds, 'cur')
            ->join("{$metaTable} as ta", 'ta.application_id', '=', 'cur.application_id')
            ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
            ->where('ta.form_id', $formId)
            ->where($this->paidPaymentConstraint('ta'));

        if ($applTypeFilter) {
            $query->where('ta.appl_type', $applTypeFilter);
        }

        return $query
            ->select(array_merge($this->metaSelectColumns(), [
                DB::raw("(SELECT COUNT(*) FROM {$workflowTable} tw2 WHERE tw2.application_id = ta.application_id AND TRIM(tw2.appl_status) = 'QU') > 0 AS has_return_history"),
            ]))
            ->distinct()
            ->orderByDesc('ta.submitted_date')
            ->orderByDesc('ta.app_id')
            ->get();
    }

    public function completedApplications(int $formId, ?string $applTypeFilter = null): Collection
    {
        $formCode = $this->formCodeForFormId($formId);
        $metaTable = $this->metaTableForFormId($formId);
        if ($metaTable === null) {
            return collect();
        }

        $certTable = $formCode
            ? app(CompetencyCertificateService::class)->certTableForForm($formCode)
            : 'cc_forms_cert';

        $query = DB::table("{$metaTable} as ta")
            ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
            ->leftJoin("{$certTable} as c", 'c.application_id', '=', 'ta.application_id')
            ->where('ta.form_id', $formId)
            ->where(DB::raw('TRIM(ta.app_status)'), 'A')
            ->select(array_merge($this->metaSelectColumns(), [
                DB::raw('c.certificate_no as license_number'),
                DB::raw('c.dateof_issue as issued_at'),
                DB::raw('c.valid_to as expires_at'),
            ]))
            ->orderByDesc('ta.app_id');

        if ($applTypeFilter) {
            $query->where('ta.appl_type', $applTypeFilter);
        }

        return $query->get();
    }

    /**
     * New or renewal queue (P/RE) for secretary-style supervisor views.
     */
    public function applicationsByApplType(int $formId, string $applType): Collection
    {
        $workflowTable = $this->workflowTableForFormId($formId);
        $metaTable = $this->metaTableForFormId($formId);
        if ($workflowTable === null || $metaTable === null) {
            return collect();
        }

        return DB::table("{$metaTable} as ta")
            ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
            ->where('ta.form_id', $formId)
            ->where('ta.appl_type', $applType)
            ->whereIn(DB::raw('TRIM(ta.app_status)'), ['P', 'RE'])
            ->where($this->paidPaymentConstraint('ta'))
            ->select(array_merge($this->metaSelectColumns(), [
                DB::raw("(SELECT COUNT(*) FROM {$workflowTable} tw2 WHERE tw2.application_id = ta.application_id AND TRIM(tw2.appl_status) = 'QU') > 0 AS has_return_history"),
            ]))
            ->orderByDesc('ta.app_id')
            ->get();
    }

    public function rejectedApplications(int $formId): Collection
    {
        $metaTable = $this->metaTableForFormId($formId);
        if ($metaTable === null) {
            return collect();
        }

        return DB::table("{$metaTable} as ta")
            ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
            ->where('ta.form_id', $formId)
            ->where(DB::raw('TRIM(ta.app_status)'), 'RJ')
            ->select($this->metaSelectColumns())
            ->orderByDesc('ta.app_id')
            ->get();
    }

    /**
     * In-progress list (F/A/RF) for completed-style supervisor views.
     */
    public function inProgressApplications(int $formId, array $statuses, array $processedBy): Collection
    {
        $metaTable = $this->metaTableForFormId($formId);
        if ($metaTable === null) {
            return collect();
        }

        return DB::table("{$metaTable} as ta")
            ->leftJoin('mst_licences as ml', 'ta.form_id', '=', 'ml.id')
            ->where('ta.form_id', $formId)
            ->whereIn(DB::raw('TRIM(ta.app_status)'), $statuses)
            ->whereIn('ta.processed_by', $processedBy)
            ->select($this->metaSelectColumns())
            ->orderByDesc('ta.app_id')
            ->get();
    }

    /**
     * Admin dashboard pending counts for Form S / W / WH (cc_form_s_meta + cc_workflow_*).
     *
     * @param  list<int>  $ccFormIds  mst_licences.id values (1, 2, 3)
     * @return Collection<int, object{form_id: int, appl_type: string, cnt: int}>
     */
    public function dashboardCcPendingCountRows(
        array $ccFormIds,
        bool $isSupervisorRole,
        int $supervisorRoleId,
        int $roleId,
        int $roleLevel
    ): Collection {
        $rows = collect();

        foreach ($ccFormIds as $formId) {
            $formId = (int) $formId;
            if (! $this->isCcMetaFormId($formId)) {
                continue;
            }

            $workflowTable = $this->workflowTableForFormId($formId);
            if ($workflowTable === null) {
                continue;
            }

            $metaTable = $this->metaTableForFormId($formId);
            if ($metaTable === null) {
                continue;
            }

            $twLast = DB::table($workflowTable)
                ->select('application_id', DB::raw('MAX(w_id) as max_id'))
                ->groupBy('application_id');

            if ($isSupervisorRole) {
                $query = DB::table("{$metaTable} as ta")
                    ->leftJoinSub($twLast, 'tw_last', function ($join) {
                        $join->on('ta.application_id', '=', 'tw_last.application_id');
                    })
                    ->leftJoin("{$workflowTable} as tw", function ($join) {
                        $join->on('tw.application_id', '=', 'tw_last.application_id')
                            ->on('tw.w_id', '=', 'tw_last.max_id');
                    })
                    ->where('ta.form_id', $formId)
                    ->whereIn(DB::raw('TRIM(ta.app_status)'), ['P', 'RE'])
                    ->where($this->paidPaymentConstraint('ta'))
                    ->where(function ($q) use ($supervisorRoleId) {
                        $q->whereNull('tw.w_id')
                            ->orWhere(function ($q2) use ($supervisorRoleId) {
                                $q2->where(DB::raw('TRIM(tw.appl_status)'), 'RE')
                                    ->where(function ($q3) use ($supervisorRoleId) {
                                        $q3->where('tw.raised_by', 'AP')
                                            ->orWhere('tw.forwarded_to', $supervisorRoleId);
                                    });
                            });
                    });
            } else {
                $previousProcessedBy = match ($roleLevel) {
                    2 => ['S', 'S2'],
                    3 => ['A'],
                    4 => ['SE'],
                    default => [],
                };

                $currentAppIds = DB::table("{$workflowTable} as tw")
                    ->joinSub($twLast, 'tw_last', function ($join) {
                        $join->on('tw.application_id', '=', 'tw_last.application_id')
                            ->on('tw.w_id', '=', 'tw_last.max_id');
                    })
                    ->where('tw.forwarded_to', $roleId)
                    ->whereIn(DB::raw('TRIM(tw.appl_status)'), ['F', 'RF'])
                    ->select('tw.application_id');

                $fallbackAppIds = DB::table("{$metaTable} as ta")
                    ->where('ta.form_id', $formId)
                    ->whereIn(DB::raw('TRIM(ta.app_status)'), ['F', 'RF'])
                    ->where($this->paidPaymentConstraint('ta'))
                    ->when($previousProcessedBy !== [], fn ($q) => $q->whereIn('ta.processed_by', $previousProcessedBy))
                    ->whereNotExists(function ($q) use ($workflowTable) {
                        $q->select(DB::raw(1))
                            ->from("{$workflowTable} as tw")
                            ->whereRaw('tw.application_id = ta.application_id');
                    })
                    ->select('ta.application_id');

                $query = DB::query()
                    ->fromSub($currentAppIds->union($fallbackAppIds), 'cur')
                    ->join("{$metaTable} as ta", 'ta.application_id', '=', 'cur.application_id')
                    ->where('ta.form_id', $formId)
                    ->where($this->paidPaymentConstraint('ta'));
            }

            $formRows = $query
                ->selectRaw('ta.form_id, ta.appl_type, COUNT(DISTINCT ta.application_id) as cnt')
                ->groupBy('ta.form_id', 'ta.appl_type')
                ->get();

            if ($isSupervisorRole && $formRows->isEmpty()) {
                $formRows = DB::table("{$metaTable} as ta")
                    ->where('ta.form_id', $formId)
                    ->whereIn(DB::raw('TRIM(ta.app_status)'), ['P', 'RE'])
                    ->where($this->paidPaymentConstraint('ta'))
                    ->selectRaw('ta.form_id, ta.appl_type, COUNT(*) as cnt')
                    ->groupBy('ta.form_id', 'ta.appl_type')
                    ->get();
            }

            $rows = $rows->merge($formRows);
        }

        return $rows;
    }

    /**
     * Form P dashboard pending counts (cc_form_p_meta + cc_workflow_formp).
     *
     * @return Collection<int, object{appl_type: string, cnt: int}>
     */
    public function dashboardFormPPendingCountRows(
        bool $isSupervisorRole,
        int $supervisorRoleId,
        int $roleId
    ): Collection {
        $workflowTable = app(CompetencyWorkflowService::class)->tableForForm('P');
        $twLast = DB::table($workflowTable)
            ->select('application_id', DB::raw('MAX(w_id) as max_id'))
            ->groupBy('application_id');

        if ($isSupervisorRole) {
            return DB::table('cc_form_p_meta as ta')
                ->leftJoinSub($twLast, 'tw_last', function ($join) {
                    $join->on('ta.application_id', '=', 'tw_last.application_id');
                })
                ->leftJoin("{$workflowTable} as tw", function ($join) {
                    $join->on('tw.application_id', '=', 'tw_last.application_id')
                        ->on('tw.w_id', '=', 'tw_last.max_id');
                })
                ->where($this->paidPaymentConstraint('ta'))
                ->whereIn(DB::raw('TRIM(ta.app_status)'), ['P', 'RE'])
                ->where(function ($q) use ($supervisorRoleId) {
                    $q->whereNull('tw.w_id')
                        ->orWhere(function ($q2) use ($supervisorRoleId) {
                            $q2->where(DB::raw('TRIM(tw.appl_status)'), 'RE')
                                ->where(function ($q3) use ($supervisorRoleId) {
                                    $q3->where('tw.raised_by', 'AP')
                                        ->orWhere('tw.forwarded_to', $supervisorRoleId);
                                });
                        });
                })
                ->selectRaw('ta.appl_type, COUNT(DISTINCT ta.application_id) as cnt')
                ->groupBy('ta.appl_type')
                ->get();
        }

        $currentAppIds = DB::table("{$workflowTable} as tw")
            ->joinSub($twLast, 'tw_last', function ($join) {
                $join->on('tw.application_id', '=', 'tw_last.application_id')
                    ->on('tw.w_id', '=', 'tw_last.max_id');
            })
            ->where('tw.forwarded_to', $roleId)
            ->whereIn(DB::raw('TRIM(tw.appl_status)'), ['F', 'RF'])
            ->select('tw.application_id');

        return DB::query()
            ->fromSub($currentAppIds, 'cur')
            ->join('cc_form_p_meta as ta', 'ta.application_id', '=', 'cur.application_id')
            ->where($this->paidPaymentConstraint('ta'))
            ->selectRaw('ta.appl_type, COUNT(*) as cnt')
            ->groupBy('ta.appl_type')
            ->get();
    }

    /** Secretary/President: competency apps received from Assistant Secretary. */
    public function dashboardReceivedFromCcMeta(): Collection
    {
        $rows = collect();

        foreach (app(CompetencyMetaService::class)->allMetaTables() as $metaTable) {
            $rows = $rows->merge(
                DB::table("{$metaTable} as ta")
                    ->leftJoin('mst_licences as f', 'ta.form_id', '=', 'f.id')
                    ->whereIn(DB::raw('TRIM(ta.app_status)'), ['F', 'RF'])
                    ->where('ta.processed_by', 'A')
                    ->where($this->paidPaymentConstraint('ta'))
                    ->select(
                        'ta.application_id',
                        DB::raw('COALESCE(f.form_code, ta.form_name) as form_name'),
                        'ta.created_at',
                        'ta.updated_at',
                        'ta.processed_by'
                    )
                    ->get()
            );
        }

        return $rows->sortByDesc('created_at')->values();
    }

    /** Secretary/President: competency apps in progress (F/RF/QU). */
    public function dashboardInProgressFromCcMeta(): Collection
    {
        $rows = collect();

        foreach (app(CompetencyMetaService::class)->allMetaTables() as $metaTable) {
            $rows = $rows->merge(
                DB::table("{$metaTable} as ta")
                    ->leftJoin('mst_licences as f', 'ta.form_id', '=', 'f.id')
                    ->whereIn(DB::raw('TRIM(ta.app_status)'), ['F', 'RF', 'QU'])
                    ->where($this->paidPaymentConstraint('ta'))
                    ->select(
                        'ta.application_id',
                        DB::raw('COALESCE(f.form_code, ta.form_name) as form_name'),
                        'ta.created_at',
                        'ta.updated_at',
                        'ta.processed_by',
                        DB::raw('TRIM(ta.app_status) as status')
                    )
                    ->get()
            );
        }

        return $rows->sortByDesc('updated_at')->values();
    }

    /**
     * Merge dashboard pending count rows into pendingCountsMap structure.
     *
     * @param  array<int, array{N: int, R: int, D: int, A: int}>  $pendingCountsMap
     */
    public function mergePendingCountRows(Collection $rows, array &$pendingCountsMap, ?int $fixedFormId = null): void
    {
        foreach ($rows as $row) {
            $fid = $fixedFormId ?? (int) ($row->form_id ?? 0);
            if ($fid <= 0) {
                continue;
            }

            $applType = strtoupper((string) ($row->appl_type ?? ''));
            $type = in_array($applType, ['N', 'R', 'D', 'A'], true) ? $applType : 'N';

            if (! isset($pendingCountsMap[$fid])) {
                $pendingCountsMap[$fid] = ['N' => 0, 'R' => 0, 'D' => 0, 'A' => 0];
            }

            $pendingCountsMap[$fid][$type] = (int) ($row->cnt ?? 0);
        }
    }
}
