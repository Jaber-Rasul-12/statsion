<?php namespace Statsion\Statsion\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Statsion\Statsion\Models\Point;
use Statsion\Statsion\Models\Product;
use Statsion\Statsion\Models\Input;
use DB;
use Carbon\Carbon;

class Reports extends Controller
{
    public $implement = [];
    
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Statsion.Statsion', 'menu', 'reports');
        
        $this->addCss('/plugins/statsion/statsion/assets/css/reports.css');
        $this->addJs('/plugins/statsion/statsion/assets/js/reports.js');
        $this->addJs('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js');
    }
    
    /**
     * عرض صفحة التقارير
     */
    public function index()
    {
        $this->pageTitle = 'نظام التقارير';
        
        // جلب المنتجات للفلترة
        $products = Product::all();
        
        $this->vars['products'] = $products;
        $this->vars['today'] = date('Y-m-d');
        $this->vars['firstDayOfMonth'] = date('Y-m-01');
        
        // return $this->makeView('index');
    }
    
    /**
     * جلب تقرير المبيعات (AJAX)
     */
    public function onGetSalesReport()
    {
        $type = post('type', 'daily');
        $startDate = post('start_date');
        $endDate = post('end_date');
        $productId = post('product_id');
        
        $query = Point::with(['product', 'input']);
        
        // تطبيق الفلترة حسب النوع
        switch ($type) {
            case 'daily':
                $date = post('date', date('Y-m-d'));
                $query->whereDate('created_at', $date);
                break;
                
            case 'monthly':
                $month = post('month', date('Y-m'));
                $query->whereYear('created_at', substr($month, 0, 4))
                      ->whereMonth('created_at', substr($month, 5, 2));
                break;
                
            case 'yearly':
                $year = post('year', date('Y'));
                $query->whereYear('created_at', $year);
                break;
                
            case 'custom':
                if ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                }
                break;
        }
        
        // فلترة حسب المنتج
        if ($productId) {
            $query->where('product_id', $productId);
        }
        
        // جلب البيانات
        $points = $query->orderBy('created_at', 'desc')->get();
        
        // حساب الإحصائيات
        $totalSales = $points->sum('price');
        $totalQuantity = $points->sum('qt');
        $totalTransactions = $points->count();
        $averageSale = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        
        // تجميع البيانات حسب التاريخ للرسم البياني
        $chartData = $this->getChartData($points, $type);
        
        // تجميع البيانات حسب المنتج
        $productData = $this->getProductData($points);
        
        return [
            'success' => true,
            'data' => [
                'points' => $points->map(function($point) {
                    return [
                        'id' => $point->id,
                        'product_name' => $point->product->name ?? 'غير معروف',
                        'qt' => $point->qt,
                        'price' => number_format($point->price, 2),
                        'currency' => $point->currency,
                        'currency_label' => trans('statsion.statsion::lang.model.input.' . $point->currency),
                        'created_at' => $point->created_at->format('Y-m-d H:i:s'),
                        'time' => $point->created_at->diffForHumans(),
                    ];
                }),
                'summary' => [
                    'total_sales' => number_format($totalSales, 2),
                    'total_quantity' => number_format($totalQuantity, 2),
                    'total_transactions' => $totalTransactions,
                    'average_sale' => number_format($averageSale, 2),
                ],
                'chart' => $chartData,
                'product_data' => $productData,
            ]
        ];
    }
    
    /**
     * تجهيز بيانات الرسم البياني
     */
    protected function getChartData($points, $type)
    {
        $grouped = [];
        
        foreach ($points as $point) {
            $date = $point->created_at->format('Y-m-d');
            
            if ($type == 'monthly') {
                $date = $point->created_at->format('d');
            } elseif ($type == 'yearly') {
                $date = $point->created_at->format('M');
            }
            
            if (!isset($grouped[$date])) {
                $grouped[$date] = 0;
            }
            $grouped[$date] += $point->price;
        }
        
        // ترتيب المصفوفة
        if ($type == 'monthly') {
            ksort($grouped);
        } elseif ($type == 'yearly') {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $sorted = [];
            foreach ($months as $month) {
                if (isset($grouped[$month])) {
                    $sorted[$month] = $grouped[$month];
                } else {
                    $sorted[$month] = 0;
                }
            }
            $grouped = $sorted;
        }
        
        return [
            'labels' => array_keys($grouped),
            'values' => array_values($grouped),
        ];
    }
    
    /**
     * تجميع البيانات حسب المنتج
     */
    protected function getProductData($points)
    {
        $grouped = [];
        
        foreach ($points as $point) {
            $name = $point->product->name ?? 'غير معروف';
            if (!isset($grouped[$name])) {
                $grouped[$name] = [
                    'name' => $name,
                    'quantity' => 0,
                    'total' => 0,
                    'transactions' => 0,
                ];
            }
            $grouped[$name]['quantity'] += $point->qt;
            $grouped[$name]['total'] += $point->price;
            $grouped[$name]['transactions']++;
        }
        
        return array_values($grouped);
    }
    
    /**
     * تصدير التقرير إلى PDF (طباعة)
     */
    public function onPrintReport()
    {
        $data = post('data');
        
        if (!$data) {
            return ['success' => false, 'message' => 'لا توجد بيانات للطباعة'];
        }
        
        return [
            'success' => true,
            'data' => $data
        ];
    }
    
    /**
     * تصدير التقرير إلى Excel (CSV)
     */
    public function onExportExcel()
    {
        $points = post('points', []);
        
        if (empty($points)) {
            return ['success' => false, 'message' => 'لا توجد بيانات للتصدير'];
        }
        
        // إنشاء ملف CSV
        $filename = 'report_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://memory', 'w');
        
        // إضافة رأس الأعمدة
        fputcsv($handle, ['#', 'المنتج', 'الكمية', 'السعر', 'العملة', 'التاريخ']);
        
        // إضافة البيانات
        foreach ($points as $index => $point) {
            fputcsv($handle, [
                $index + 1,
                $point['product_name'],
                $point['qt'],
                $point['price'],
                $point['currency'],
                $point['created_at']
            ]);
        }
        
        fseek($handle, 0);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return [
            'success' => true,
            'filename' => $filename,
            'content' => base64_encode($content)
        ];
    }
}