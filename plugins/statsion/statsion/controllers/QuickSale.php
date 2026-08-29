<?php namespace Statsion\Statsion\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Statsion\Statsion\Models\Product;
use Statsion\Statsion\Models\Input;
use Statsion\Statsion\Models\Point;
use Illuminate\Support\Facades\Validator;
use DB;
use Response;

class QuickSale extends Controller
{
    public $implement = [];
    
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Statsion.Statsion', 'menu', 'quick_sale');
        
        $this->addCss('/plugins/statsion/statsion/assets/css/quick-sale.css');
        $this->addJs('/plugins/statsion/statsion/assets/js/quick-sale.js');
    }
    
    /**
     * عرض صفحة البيع السريع
     */
    public function index()
    {
        $this->pageTitle = 'نظام البيع السريع - الكازية';
        
        // جلب جميع المنتجات مع المدخلات المتاحة
        $products = Product::with(['inputs' => function($query) {
            $query->withCount(['points' => function($q) {
                $q->select(DB::raw('COALESCE(SUM(qt), 0)'));
            }]);
        }])->get();
        
        // تجهيز المنتجات مع الكميات المتاحة
        $availableProducts = [];
        foreach ($products as $product) {
            foreach ($product->inputs as $input) {
                $usedQt = $input->points()->sum('qt') ?? 0;
                $availableQt = $input->qt - $usedQt;
                
                if ($availableQt > 0) {
                    $currency = trans('statsion.statsion::lang.model.input.' . $input->currency);
                    
                    $availableProducts[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'input_id' => $input->id,
                        'selling_price' => $input->selling_price,
                        'available_qt' => $availableQt,
                        'currency' => $input->currency,
                        'currency_label' => $currency,
                        'buying_price' => $input->buying_price,
                    ];
                }
            }
        }
        
        $this->vars['products'] = $availableProducts;
                $this->vars['points'] = Point::with(['product', 'input'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
        $this->vars['stats'] = $this->getDashboardStats();
        
        // return $this->makeView('index');
    }
    
    /**
     * جلب إحصائيات لوحة التحكم
     */
    protected function getDashboardStats()
    {
        $today = date('Y-m-d');
        
        return [
            'today_sales' => Point::whereDate('created_at', $today)->sum('price') ?? 0,
            'today_transactions' => Point::whereDate('created_at', $today)->count(),
            'total_products' => Product::count(),
            'total_points' => Point::count(),
        ];
    }
    
    /**
     * جلب تفاصيل المدخل المحدد
     */
    public function onGetInputDetails()
    {
        $inputId = post('input_id');
        
        if (!$inputId) {
            return ['success' => false, 'message' => 'الرجاء اختيار منتج'];
        }
        
        $input = Input::with('product')->find($inputId);
        
        if (!$input) {
            return ['success' => false, 'message' => 'المدخل غير موجود'];
        }
        
        $availableQt = Point::getAvailableQuantity($inputId);
        $currency = trans('statsion.statsion::lang.model.input.' . $input->currency);
        
        return [
            'success' => true,
            'data' => [
                'input_id' => $input->id,
                'product_name' => $input->product->name,
                'selling_price' => $input->selling_price,
                'available_qt' => $availableQt,
                'currency' => $input->currency,
                'currency_label' => $currency,
            ]
        ];
    }
    
    /**
     * تنفيذ عملية البيع (AJAX)
     */
    public function onProcessSale()
    {
        DB::beginTransaction();
        
        try {
            $data = post();
            
            $validator = Validator::make($data, [
                'input_id' => 'required|integer|exists:statsion_statsion_inputs,id',
                'qt' => 'required|numeric|min:0.01',
            ]);
            
            if ($validator->fails()) {
                return ['success' => false, 'message' => $validator->errors()->first()];
            }
            
            $input = Input::find($data['input_id']);
            if (!$input) {
                return ['success' => false, 'message' => 'المدخل غير موجود'];
            }
            
            // التحقق من الكمية المتاحة
            $availableQt = Point::getAvailableQuantity($data['input_id']);
            
            if ($data['qt'] > $availableQt) {
                return [
                    'success' => false, 
                    'message' => "الكمية المتاحة: {$availableQt}"
                ];
            }
            
            // حساب السعر الإجمالي
            $totalPrice = $data['qt'] * $input->selling_price;
            
            // إنشاء نقطة البيع
            $point = new Point();
            $point->input_id = $data['input_id'];
            $point->product_id = $input->product_id;
            $point->qt = $data['qt'];
            $point->price = $totalPrice;
            $point->currency = $input->currency;
            $point->save();
            
            DB::commit();
            
            // تحديث الكمية المتبقية
            $newAvailable = $availableQt - $data['qt'];
            
            return [
                'success' => true,
                'message' => 'تمت عملية البيع بنجاح',
                'data' => [
                    'point_id' => $point->id,
                    'product_name' => $input->product->name,
                    'qt' => $data['qt'],
                    'price' => number_format($totalPrice, 2),
                    'currency' => $input->currency,
                    'currency_label' => trans('statsion.statsion::lang.model.input.' . $input->currency),
                    'remaining_qt' => $newAvailable,
                    'selling_price' => $input->selling_price,
                    'created_at' => $point->created_at->format('Y-m-d H:i:s')
                ]
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * جلب آخر العمليات (AJAX)
     */
    public function onGetRecentSales()
    {
        $limit = post('limit', 20);
        
         
                $this->vars['points'] = Point::with(['product', 'input'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
            
     
    }
    
    /**
     * تحديث الكميات المتاحة بعد البيع (لتحديث الواجهة)
     */
    public function onRefreshProducts()
    {
        $products = Product::with(['inputs' => function($query) {
            $query->withCount(['points' => function($q) {
                $q->select(DB::raw('COALESCE(SUM(qt), 0)'));
            }]);
        }])->get();
        
        $availableProducts = [];
        foreach ($products as $product) {
            foreach ($product->inputs as $input) {
                $usedQt = $input->points()->sum('qt') ?? 0;
                $availableQt = $input->qt - $usedQt;
                
                if ($availableQt > 0) {
                    $currency = trans('statsion.statsion::lang.model.input.' . $input->currency);
                    
                    $availableProducts[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'input_id' => $input->id,
                        'selling_price' => $input->selling_price,
                        'available_qt' => $availableQt,
                        'currency' => $input->currency,
                        'currency_label' => $currency,
                    ];
                }
            }
        }
        
        return ['products' => $availableProducts];
    }
}