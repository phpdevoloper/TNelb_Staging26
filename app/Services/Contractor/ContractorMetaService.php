<?php

namespace App\Services\Contractor;

use App\Models\EA_Application_model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContractorMetaService
{
    public const META_TABLE = 'ccl_forma_meta';

    public function findModel(string $applicationId): ?EA_Application_model
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '' || ! Schema::hasTable(self::META_TABLE)) {
            return null;
        }

        return EA_Application_model::where('application_id', $applicationId)->first();
    }

    /**
     * Direct children (old_application = $parentId).
     *
     * @return list<string>
     */
    public function childrenOf(string $parentId): array
    {
        $parentId = trim($parentId);
        if ($parentId === '' || ! Schema::hasTable(self::META_TABLE) || ! Schema::hasColumn(self::META_TABLE, 'old_application')) {
            return [];
        }

        $children = [];
        $seen = [];

        $rows = DB::table(self::META_TABLE)
            ->where('old_application', $parentId)
            ->pluck('application_id');

        foreach ($rows as $child) {
            $childId = trim((string) $child);
            if ($childId === '' || isset($seen[$childId])) {
                continue;
            }
            $seen[$childId] = true;
            $children[] = $childId;
        }

        return $children;
    }

    /**
     * Current application plus descendant renewal / alteration IDs.
     *
     * @return list<string>
     */
    public function relatedApplicationIds(string $applicationId): array
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return [];
        }

        $ids = [$applicationId];
        $queue = [$applicationId];
        $seen = [$applicationId => true];

        while ($queue !== []) {
            $parentId = array_shift($queue);
            foreach ($this->childrenOf($parentId) as $childId) {
                if (isset($seen[$childId])) {
                    continue;
                }
                $seen[$childId] = true;
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }
}
