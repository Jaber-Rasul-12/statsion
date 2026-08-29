/**
 * نظام التقارير - Statsion
 */

$(document).ready(function() {
    
    'use strict';
    
    // =============================================
    // كائن Reports الرئيسي
    // =============================================
    window.reports = {
        chartInstance: null,
        currentData: null,
        
        /**
         * تهيئة النظام
         */
        init: function() {
            this.bindEvents();
            this.generateReport();
        },
        
        /**
         * ربط الأحداث
         */
        bindEvents: function() {
            // تغيير نوع التقرير
            $('#reportType').on('change', function() {
                reports.toggleControls($(this).val());
            });
            
            // زر إنشاء التقرير
            $('#generateReportBtn').on('click', function() {
                reports.generateReport();
            });
            
            // تحديث عند تغيير التاريخ
            $('#reportDate, #reportMonth, #reportYear, #startDate, #endDate, #productFilter').on('change', function() {
                // لا نقوم بإنشاء التقرير تلقائياً، ننتظر الضغط على زر التقرير
            });
            
            // مفتاح Enter
            $(document).on('keydown', function(e) {
                if (e.key === 'Enter' && $('#reportType').is(':visible')) {
                    $('#generateReportBtn').click();
                }
            });
        },
        
        /**
         * إظهار/إخفاء عناصر التحكم حسب نوع التقرير
         */
        toggleControls: function(type) {
            // إخفاء الكل
            $('#dateControl, #monthControl, #yearControl, #customDateControl').hide();
            
            // إظهار المناسب
            switch (type) {
                case 'daily':
                    $('#dateControl').show();
                    break;
                case 'monthly':
                    $('#monthControl').show();
                    break;
                case 'yearly':
                    $('#yearControl').show();
                    break;
                case 'custom':
                    $('#customDateControl').show();
                    break;
            }
        },
        
        /**
         * إنشاء التقرير
         */
        generateReport: function() {
            var type = $('#reportType').val();
            var data = {
                type: type,
                product_id: $('#productFilter').val()
            };
            
            // إضافة التواريخ حسب النوع
            switch (type) {
                case 'daily':
                    data.date = $('#reportDate').val();
                    break;
                case 'monthly':
                    data.month = $('#reportMonth').val();
                    break;
                case 'yearly':
                    data.year = $('#reportYear').val();
                    break;
                case 'custom':
                    data.start_date = $('#startDate').val();
                    data.end_date = $('#endDate').val();
                    break;
            }
            
            // عرض حالة التحميل
            $('#generateReportBtn').html('<i class="fa fa-spinner fa-pulse"></i> جاري التحميل...').prop('disabled', true);
            
            $.request('onGetSalesReport', {
                data: data,
                success: function(response) {
                    if (response.success) {
                        reports.renderReport(response.data);
                        reports.currentData = response.data;
                    } else {
                        reports.showError('حدث خطأ في تحميل التقرير');
                    }
                },
                error: function() {
                    reports.showError('حدث خطأ في الاتصال بالخادم');
                },
                complete: function() {
                    $('#generateReportBtn').html('<i class="fa fa-search"></i> عرض التقرير').prop('disabled', false);
                }
            });
        },
        
        /**
         * عرض التقرير
         */
        renderReport: function(data) {
            // تحديث الإحصائيات
            $('#totalSales').text(data.summary.total_sales);
            $('#totalQuantity').text(data.summary.total_quantity);
            $('#totalTransactions').text(data.summary.total_transactions);
            $('#averageSale').text(data.summary.average_sale);
            
            // تحديث عدد السجلات
            $('#recordCount').text(data.points.length + ' سجل');
            
            // تحديث الجدول
            this.renderTable(data.points);
            
            // تحديث الرسم البياني
            this.renderChart(data.chart);
            
            // تحديث تحليل المنتجات
            this.renderProductAnalysis(data.product_data);
            
            // إظهار تحليل المنتجات
            if (data.product_data && data.product_data.length > 0) {
                $('#productAnalysis').show();
            } else {
                $('#productAnalysis').hide();
            }
        },
        
        /**
         * عرض الجدول
         */
        renderTable: function(points) {
            var tbody = $('#tableBody');
            
            if (points.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center">لا توجد بيانات</td></tr>');
                return;
            }
            
            var html = '';
            $.each(points, function(index, point) {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${point.product_name}</td>
                        <td>${point.qt}</td>
                        <td>${point.price}</td>
                        <td>${point.currency_label}</td>
                        <td>${point.created_at}</td>
                    </tr>
                `;
            });
            
            tbody.html(html);
        },
        
        /**
         * عرض الرسم البياني
         */
        renderChart: function(chartData) {
            var ctx = document.getElementById('salesChart').getContext('2d');
            
            // تدمير الرسم البياني السابق
            if (this.chartInstance) {
                this.chartInstance.destroy();
            }
            
            if (chartData.labels.length === 0) {
                // عرض رسالة في حالة عدم وجود بيانات
                this.chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['لا توجد بيانات'],
                        datasets: [{
                            label: 'المبيعات',
                            data: [0],
                            backgroundColor: ['#e8eaed'],
                            borderColor: ['#dadce0'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                return;
            }
            
            // تحديد الألوان
            var colors = [
                '#1a73e8', '#34a853', '#f9ab00', '#ea4335',
                '#9c27b0', '#00bcd4', '#ff5722', '#4caf50'
            ];
            
            this.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'المبيعات',
                        data: chartData.values,
                        backgroundColor: colors.slice(0, chartData.labels.length),
                        borderColor: colors.slice(0, chartData.labels.length).map(c => c),
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'المبيعات: ' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(2);
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        },
        
        /**
         * عرض تحليل المنتجات
         */
        renderProductAnalysis: function(productData) {
            var body = $('#analysisBody');
            
            if (!productData || productData.length === 0) {
                body.html('<p style="text-align:center;color:#80868b;">لا توجد بيانات كافية لتحليل المنتجات</p>');
                return;
            }
            
            // ترتيب حسب إجمالي المبيعات تنازلياً
            productData.sort(function(a, b) {
                return b.total - a.total;
            });
            
            var html = '<div class="analysis-grid">';
            $.each(productData, function(index, product) {
                html += `
                    <div class="analysis-item">
                        <div class="product-name">${product.name}</div>
                        <div class="product-stats">
                            <div>الكمية: <strong>${product.quantity}</strong></div>
                            <div>المبيعات: <strong>${product.total.toFixed(2)}</strong></div>
                            <div>العمليات: <strong>${product.transactions}</strong></div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            body.html(html);
        },
        
        /**
         * طباعة التقرير
         */
        printReport: function() {
            var data = this.currentData;
            
            if (!data || data.points.length === 0) {
                this.showError('لا توجد بيانات للطباعة');
                return;
            }
            
            // عرض نافذة الطباعة
            var printBody = $('#printBody');
            
            // بناء محتوى الطباعة
            var html = `
                <div style="margin-bottom:20px;">
                    <h3 style="margin:0;">تقرير المبيعات</h3>
                    <p style="color:#5f6368;margin:4px 0;">
                        التاريخ: ${new Date().toLocaleString('ar-EG')}
                    </p>
                    <p style="color:#5f6368;margin:4px 0;">
                        عدد السجلات: ${data.points.length}
                    </p>
                </div>
                
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center;">
                        <div style="font-size:12px;color:#5f6368;">إجمالي المبيعات</div>
                        <div style="font-size:20px;font-weight:700;">${data.summary.total_sales}</div>
                    </div>
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center;">
                        <div style="font-size:12px;color:#5f6368;">إجمالي الكمية</div>
                        <div style="font-size:20px;font-weight:700;">${data.summary.total_quantity}</div>
                    </div>
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center;">
                        <div style="font-size:12px;color:#5f6368;">عدد العمليات</div>
                        <div style="font-size:20px;font-weight:700;">${data.summary.total_transactions}</div>
                    </div>
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;text-align:center;">
                        <div style="font-size:12px;color:#5f6368;">متوسط البيع</div>
                        <div style="font-size:20px;font-weight:700;">${data.summary.average_sale}</div>
                    </div>
                </div>
                
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f1f3f4;">
                            <th style="padding:8px 12px;text-align:right;border:1px solid #e8eaed;">#</th>
                            <th style="padding:8px 12px;text-align:right;border:1px solid #e8eaed;">المنتج</th>
                            <th style="padding:8px 12px;text-align:right;border:1px solid #e8eaed;">الكمية</th>
                            <th style="padding:8px 12px;text-align:right;border:1px solid #e8eaed;">السعر</th>
                            <th style="padding:8px 12px;text-align:right;border:1px solid #e8eaed;">العملة</th>
                            <th style="padding:8px 12px;text-align:right;border:1px solid #e8eaed;">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            $.each(data.points, function(index, point) {
                html += `
                    <tr>
                        <td style="padding:6px 12px;border:1px solid #e8eaed;">${index + 1}</td>
                        <td style="padding:6px 12px;border:1px solid #e8eaed;">${point.product_name}</td>
                        <td style="padding:6px 12px;border:1px solid #e8eaed;">${point.qt}</td>
                        <td style="padding:6px 12px;border:1px solid #e8eaed;">${point.price}</td>
                        <td style="padding:6px 12px;border:1px solid #e8eaed;">${point.currency_label}</td>
                        <td style="padding:6px 12px;border:1px solid #e8eaed;">${point.created_at}</td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
                
                <div style="margin-top:20px;text-align:center;color:#80868b;font-size:12px;border-top:1px solid #e8eaed;padding-top:12px;">
                    تم إنشاء هذا التقرير بواسطة نظام Statsion
                </div>
            `;
            
            printBody.html(html);
            $('#printModal').addClass('active');
            $('body').css('overflow', 'hidden');
        },
        
        /**
         * إغلاق نافذة الطباعة
         */
        closePrint: function() {
            $('#printModal').removeClass('active');
            $('body').css('overflow', '');
        },
        
        /**
         * تصدير إلى Excel
         */
        exportExcel: function() {
            var data = this.currentData;
            
            if (!data || data.points.length === 0) {
                this.showError('لا توجد بيانات للتصدير');
                return;
            }
            
            // إظهار رسالة تحميل
            var btn = $('.btn-export');
            var originalText = btn.html();
            btn.html('<i class="fa fa-spinner fa-pulse"></i> جاري التصدير...').prop('disabled', true);
            
            $.request('onExportExcel', {
                data: {
                    points: data.points
                },
                success: function(response) {
                    if (response.success) {
                        // تحميل الملف
                        var link = document.createElement('a');
                        link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(atob(response.content));
                        link.download = response.filename;
                        link.click();
                        
                        reports.showSuccess('تم تصدير التقرير بنجاح');
                    } else {
                        reports.showError('حدث خطأ في التصدير');
                    }
                },
                error: function() {
                    reports.showError('حدث خطأ في التصدير');
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        },
        
        /**
         * عرض رسالة نجاح
         */
        showSuccess: function(message) {
            this.showNotification(message, 'success');
        },
        
        /**
         * عرض رسالة خطأ
         */
        showError: function(message) {
            this.showNotification(message, 'error');
        },
        
        /**
         * عرض إشعار
         */
        showNotification: function(message, type) {
            var notification = $('#reportNotification');
            if (notification.length === 0) {
                $('body').append(`
                    <div id="reportNotification" style="
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        padding: 12px 20px;
                        border-radius: 10px;
                        background: #ffffff;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                        z-index: 99999;
                        font-weight: 500;
                        display: none;
                        max-width: 90%;
                    "></div>
                `);
                notification = $('#reportNotification');
            }
            
            var colors = {
                success: { bg: '#e6f4ea', color: '#1e7e34', border: '#b7e1cd' },
                error: { bg: '#fce8e6', color: '#b31412', border: '#f5c6cb' },
                warning: { bg: '#fef7e0', color: '#856404', border: '#ffeeba' }
            };
            
            var style = colors[type] || colors.success;
            
            notification
                .css('background', style.bg)
                .css('color', style.color)
                .css('border', '2px solid ' + style.border)
                .html(message)
                .fadeIn(300);
            
            clearTimeout(this.notificationTimeout);
            this.notificationTimeout = setTimeout(function() {
                notification.fadeOut(400);
            }, 3000);
        }
    };
    
    // =============================================
    // تهيئة النظام
    // =============================================
    reports.init();
    
    console.log('✅ نظام التقارير جاهز');
});