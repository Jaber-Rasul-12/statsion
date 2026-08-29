<?php namespace Statsion\Statsion\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;
class Input extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    
  
        use LogChanges;
    public $logBookModelName = 'statsion.statsion::lang.plugin.inputs';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'statsion.statsion::lang.model.input.' . $column;
  }



    /**
     * @var string The database table used by the model.
     */
    public $table = 'statsion_statsion_inputs';

   public $rules = [
        'product_id' => 'required|integer|exists:statsion_statsion_products,id',
        'buying_price' => 'required|numeric|min:0|max:99999999.99',
        'selling_price' => 'required|numeric|min:0|max:99999999.99|gt:buying_price',
        'qt' => 'required|numeric|min:0.01|max:999999.99',
        'currency' => 'required|string|max:3|in:USD,SYR',
        'supplier_id' => 'nullable|integer|exists:statsion_statsion_suppliers,id'
    ];


        /**
     * @var array Relations
     */
    public $belongsTo = [
        'product' => [
            'Statsion\Statsion\Models\Product',
            'key' => 'product_id'
        ]
    ];

    public $hasMany = [
        'points' => [Point::class, 'key' => 'input_id'],

    ];

         public function getCurrencyListsAttribute()
  {
    return $this->attributes['currency'] ? trans('statsion.statsion::lang.model.input.' . $this->attributes['currency']) : 'لا يوجد بيانات';
  }

        public function getFullQualityNameAttribute()
  {
    return $this->product->name . ' ( ' . $this->selling_price . ' )' . ' ( ' . trans('statsion.statsion::lang.model.input.' . $this->currency) . ' )';
  }




    /**
     * Get residual quantity (remaining quantity)
     */
    public function getResidualListsAttribute()
    {
        // التحقق من وجود ID
        if (!isset($this->attributes['id'])) {
            return 0;
        }
        
        // جلب الكمية المستخدمة من النقاط
        $usedQt = $this->points()->sum('qt') ?? 0;
        
        // حساب المتبقي
        $totalQt = $this->attributes['qt'] ?? 0;
        
        return $totalQt - $usedQt;
    }
  


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
