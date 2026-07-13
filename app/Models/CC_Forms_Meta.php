<?php



namespace App\Models;



use App\Models\Competency\CC_Form_S_Meta;

use App\Services\Competency\CompetencyMetaService;

use Illuminate\Database\Eloquent\Model;



/**

 * Backward-compatible alias for Form S meta (`cc_form_s_meta`).

 * Use CompetencyMetaService for cross-form lookups (S / W / WH / P).

 */

class CC_Forms_Meta extends CC_Form_S_Meta

{

    public static function findByApplicationId(string $applicationId, ?string $formName = null): ?Model

    {

        return app(CompetencyMetaService::class)->findModel($applicationId, $formName);

    }



    /**

     * @param  array<string, mixed>  $attributes

     */

    public static function createForForm(string $formName, array $attributes): Model

    {

        return app(CompetencyMetaService::class)->createForForm($formName, $attributes);

    }



    public static function existsByApplicationId(string $applicationId): bool

    {

        return self::findByApplicationId($applicationId) !== null;

    }



    /**

     * @param  array<string, mixed>  $attributes

     */

    public static function updateByApplicationId(string $applicationId, array $attributes): bool

    {

        $model = self::findByApplicationId($applicationId);

        if (! $model) {

            return false;

        }



        return (bool) $model->update($attributes);

    }



    /**

     * @param  array<string, mixed>  $attributes

     */

    public static function updateOrCreateByApplicationId(

        string $applicationId,

        array $attributes,

        ?string $formName = null

    ): Model {

        return app(CompetencyMetaService::class)->updateOrCreateForForm(

            (string) ($formName ?? $attributes['form_name'] ?? 'S'),

            $applicationId,

            $attributes

        );

    }

}

