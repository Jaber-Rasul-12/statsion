<?php namespace Statsion\Statsion\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;
class Product extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use LogChanges;
    public $logBookModelName = 'statsion.statsion::lang.plugin.products';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'statsion.statsion::lang.model.product.' . $column;
  }

      public $rules = [
     'name' => 'required|string|max:255|unique:statsion_statsion_products',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'statsion_statsion_products';


    public $hasMany = [
        'points' => [Point::class, 'key' => 'product_id'],
        'inputs' => [Input::class, 'key' => 'product_id'],

    ];

                  /**
     * Perform actions before deleting 
     *
     * @throws \ValidationException
     */
    public function beforeDelete()
    {
        foreach ($this->hasMany as $relation => $details) {
            if ($this->{$relation}->count() > 0) {
                throw new \ValidationException(['name' => trans('statsion.statsion::lang.plugin.message_delete')]);
            }
        }
    }


}
