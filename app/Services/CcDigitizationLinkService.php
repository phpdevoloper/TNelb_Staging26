<?php

namespace App\Services;

use App\Models\Tnelb_CC_Digitization;

class CcDigitizationLinkService
{
    /**
     * Link a digitization capture row to the real application ID.
     */
    public function linkToApplication(?string $tempAppId, string $applicationId, string $loginId): bool
    {
        $tempAppId = trim((string) $tempAppId);
        $loginId = trim($loginId);
        $applicationId = trim($applicationId);

        if ($tempAppId === '' || $loginId === '' || $applicationId === '') {
            return false;
        }

        $updated = Tnelb_CC_Digitization::where('temp_app_id', $tempAppId)
            ->where('login_id', $loginId)
            ->where(function ($q) use ($applicationId) {
                $q->whereNull('application_id')
                    ->orWhere('application_id', $applicationId);
            })
            ->update([
                'application_id' => $applicationId,
                'flag' => 1,
                'updated_at' => db_now(),
            ]);

        return $updated > 0;
    }

    /**
     * Resolve temp_app_id from request, existing link, or latest unlinked capture.
     */
    public function resolveTempAppId(
        ?string $tempAppId,
        string $loginId,
        ?string $applicationId = null,
        ?string $formName = null
    ): ?string {
        $tempAppId = trim((string) $tempAppId);
        if ($tempAppId !== '') {
            return $tempAppId;
        }

        $loginId = trim($loginId);
        if ($loginId === '') {
            return null;
        }

        $applicationId = trim((string) $applicationId);
        if ($applicationId !== '') {
            $linked = Tnelb_CC_Digitization::where('application_id', $applicationId)
                ->where('login_id', $loginId)
                ->value('temp_app_id');
            if ($linked) {
                return $linked;
            }
        }

        $query = Tnelb_CC_Digitization::where('login_id', $loginId)
            ->whereNull('application_id')
            ->orderByDesc('id');

        $formName = trim((string) $formName);
        if ($formName !== '') {
            $query->where('form_name', $formName);
        }

        return $query->value('temp_app_id');
    }

    /**
     * Guard: new digitization saves must have a valid unlinked temp_app_id for this user.
     */
    public function assertValidForNewSave(?string $tempAppId, string $loginId): bool
    {
        $tempAppId = trim((string) $tempAppId);
        if ($tempAppId === '') {
            return false;
        }

        return Tnelb_CC_Digitization::where('temp_app_id', $tempAppId)
            ->where('login_id', $loginId)
            ->whereNull('application_id')
            ->exists();
    }

    /**
     * Guard: updates may proceed if already linked to this application or temp_id is valid.
     */
    public function assertCanSave(?string $tempAppId, string $loginId, ?string $applicationId = null): bool
    {
        $applicationId = trim((string) $applicationId);
        if ($applicationId !== '') {
            $alreadyLinked = Tnelb_CC_Digitization::where('application_id', $applicationId)
                ->where('login_id', $loginId)
                ->exists();
            if ($alreadyLinked) {
                return true;
            }
        }

        return $this->assertValidForNewSave($tempAppId, $loginId);
    }
}
