<?php namespace Statsion\Statsion\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;
class Point extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
     use \Winter\Storm\Database\Traits\Purgeable;
    protected $purgeable = ['avilable_qy','avilable_selling_price'];

        use LogChanges;
    public $logBookModelName = 'statsion.statsion::lang.plugin.points';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'statsion.statsion::lang.model.point.' . $column;
  }


    /**
     * @var string The database table used by the model.
     */
    public $table = 'statsion_statsion_points';

    /**
     * @var array Validation rules
     */
   public $rules = [
        'input_id' => 'required|integer|exists:statsion_statsion_inputs,id',
        'product_id' => 'required|integer|exists:statsion_statsion_products,id',
        'qt' => 'required|numeric|min:0.01|max:999999.99',
        'price' => 'required|numeric|min:0|max:99999999.99',
        'currency' => 'required|string|max:3|in:USD,SYR',

    ];

        /**
     * @var array Relations
     */
    public $belongsTo = [
        'input' => [
            'Statsion\Statsion\Models\Input',
            'key' => 'input_id'
        ],
        'product' => [
            'Statsion\Statsion\Models\Product',
            'key' => 'product_id'
        ]
    ];

             public function getCurrencyListsAttribute()
  {
    return $this->attributes['currency'] ? trans('statsion.statsion::lang.model.point.' . $this->attributes['currency']) : 'لا يوجد بيانات';
  }


      /**
     * Filter and set options for form fields based on certain conditions.
     *
     * This method dynamically updates the form field options and visibility based 
     * on specific conditions, such as field values and dependencies. It helps to 
     * ensure that only relevant data is displayed to the user while interacting with the form.
     *
     * @param object $fields   The form fields to be filtered and updated.
     * @param mixed  $context  Additional context information if needed.
     */
    public function filterFields($fields, $context = null)
    {

        if (isset($fields->product) && !empty($fields->product->value)) {

            $fields->input_id->options = Input::where('product_id', $fields->product->value)->get()->lists('FullQualityName', 'id');
            if (isset($fields->input_id) && !empty($fields->input_id->value)) {
            $fields->avilable_qy->value = Input::find($fields->input_id->value)->residual_lists;
            $fields->avilable_selling_price->value = Input::find($fields->input_id->value)->selling_price; ;
            $fields->currency->options = [Input::find($fields->input_id->value)->currency => trans('statsion.statsion::lang.model.input.' . Input::find($fields->input_id->value)->currency)];
             

            if (isset($fields->qt) && !empty($fields->qt->value)) {
              $fields->price->value = Input::find($fields->input_id->value)->selling_price * $fields->qt->value;
            }else{
                $fields->price->value = 0;
            }
            

            }else{
                  $fields->avilable_qy->value = 0;
                  $fields->avilable_selling_price->value = 0;
                  $fields->currency->options = [];
            }
        }else{
            $fields->input_id->options = [];
             $fields->currency->options = [];
        }
    }

   /**
     * التحقق من الكمية الكافية قبل الحفظ
     */
    public function beforeValidate()
    {
        // التحقق من وجود input_id
        if (!$this->input_id) {
            return;
        }

        // جلب المدخل
        $input = Input::find($this->input_id);
        
        if (!$input) {
            throw new \ValidationException([
                'input_id' => 'المدخل المحدد غير موجود'
            ]);
        }

        // حساب الكمية المستخدمة من هذا المدخل (باستثناء السجل الحالي إذا كان موجوداً)
        $usedQt = Point::where('input_id', $this->input_id);
        
        if ($this->exists && $this->id) {
            $usedQt->where('id', '!=', $this->id);
        }
        
        $usedQt = $usedQt->sum('qt') ?? 0;

        // الكمية المتاحة = الكمية الإجمالية - الكمية المستخدمة
        $availableQt = $input->qt - $usedQt;

        // التحقق من أن الكمية المطلوبة لا تتجاوز الكمية المتاحة
        if ($this->qt > $availableQt) {
            throw new \ValidationException([
                'qt' => "الكمية المطلوبة ({$this->qt}) تتجاوز الكمية المتاحة ({$availableQt})"
            ]);
        }

        // التحقق من أن الكمية المطلوبة موجبة
        if ($this->qt <= 0) {
            throw new \ValidationException([
                'qt' => 'الكمية يجب أن تكون أكبر من صفر'
            ]);
        }
    }

    /**
     * دالة مساعدة للتحقق من الكمية المتاحة للمدخل
     */
    public static function getAvailableQuantity($inputId, $excludePointId = null)
    {
        $input = Input::find($inputId);
        
        if (!$input) {
            return 0;
        }

        $usedQt = Point::where('input_id', $inputId);
        
        if ($excludePointId) {
            $usedQt->where('id', '!=', $excludePointId);
        }
        
        $usedQt = $usedQt->sum('qt') ?? 0;
        
        return $input->qt - $usedQt;
    }

  

    /**
     * دالة للتحقق من الكمية الكافية في الـ Controller
     */
    public static function validateQuantity($inputId, $qt, $excludePointId = null)
    {
        $available = self::getAvailableQuantity($inputId, $excludePointId);
        
        if ($qt > $available) {
            return [
                'valid' => false,
                'message' => "الكمية المطلوبة ({$qt}) تتجاوز الكمية المتاحة ({$available})",
                'available' => $available
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'الكمية متوفرة',
            'available' => $available
        ];
    }

}
