/**
 * نظام البيع السريع - الكازية
 */

$(document).ready(function() {
    
    'use strict';
    
    // =============================================
    // كائن QuickSale الرئيسي
    // =============================================
    window.quickSale = {
        selectedInputId: null,
        selectedProductName: null,
        sellingPrice: 0,
        availableQt: 0,
        currency: '$',
        currencyLabel: '$',
        isProcessing: false,
        
        /**
         * اختيار منتج من البطاقات
         */
        selectProduct: function(button) {
            var card = $(button).closest('.product-card');
            
            // إزالة التحديد السابق
            $('.product-card').removeClass('active');
            card.addClass('active');
            
            // جلب البيانات
            this.selectedInputId = card.data('input-id');
            this.selectedProductName = card.data('product-name');
            this.sellingPrice = parseFloat(card.data('selling-price')) || 0;
            this.availableQt = parseFloat(card.data('available-qt')) || 0;
            this.currency = card.data('currency') || 'USD';
            this.currencyLabel = card.data('currency-label') || '$';
            
            // تحديث الواجهة
            $('#selectedInputId').val(this.selectedInputId);
            $('#selectedProductName').html('<i class="fa fa-cube"></i> <span>' + this.selectedProductName + '</span>');
            
            $('#detailPrice').text(this.sellingPrice.toFixed(2));
            $('#detailCurrency').text(this.currencyLabel);
            $('#detailAvailable').text(this.availableQt);
            $('#productDetails').show();
            
            // تمكين زر البيع
            $('#processSaleBtn').prop('disabled', false);
            
            // تحديث السعر
            this.updateTotal();
            
            // إظهار رسالة ترحيبية
            this.showResult('تم اختيار المنتج: ' + this.selectedProductName, 'success');
        },
        
        /**
         * تعديل الكمية
         */
        adjustQuantity: function(delta) {
            var input = $('#saleQuantity');
            var current = parseFloat(input.val()) || 0;
            var newValue = current + delta;
            
            if (newValue < 0.01) newValue = 0.01;
            if (this.availableQt > 0 && newValue > this.availableQt) {
                this.showResult('الكمية المطلوبة تتجاوز الكمية المتاحة (' + this.availableQt + ')', 'error');
                return;
            }
            
            input.val(newValue.toFixed(2));
            this.updateTotal();
        },
        
        /**
         * تحديث السعر الإجمالي
         */
        updateTotal: function() {
            var quantity = parseFloat($('#saleQuantity').val()) || 0;
            var total = quantity * this.sellingPrice;
            
            $('#totalPriceDisplay').text(total.toFixed(2));
            $('#totalCurrency').text(this.currencyLabel);
        },
        
        /**
         * تنفيذ عملية البيع
         */
        processSale: function() {
            var self = this;
            
            if (this.isProcessing) return;
            
            var inputId = $('#selectedInputId').val();
            var quantity = parseFloat($('#saleQuantity').val()) || 0;
            
            if (!inputId) {
                this.showResult('الرجاء اختيار منتج أولاً', 'error');
                return;
            }
            
            if (quantity <= 0) {
                this.showResult('الرجاء إدخال كمية صحيحة', 'error');
                return;
            }
            
            if (quantity > this.availableQt) {
                this.showResult('الكمية المطلوبة (' + quantity + ') تتجاوز الكمية المتاحة (' + this.availableQt + ')', 'error');
                return;
            }
            
            this.isProcessing = true;
            var btn = $('#processSaleBtn');
            var originalText = btn.html();
            btn.html('<i class="fa fa-spinner fa-pulse"></i> جاري التنفيذ...').prop('disabled', true);
            
            $.request('onProcessSale', {
                data: {
                    input_id: inputId,
                    qt: quantity
                },
                success: function(data) {
                    if (data.success) {
                        var details = '';
                        if (data.data) {
                            details = `
                                <div class="result-details">
                                    <span>المنتج: <strong>${data.data.product_name}</strong></span>
                                    <span>الكمية: <strong>${data.data.qt}</strong></span>
                                    <span>السعر: <strong>${data.data.price} ${data.data.currency_label}</strong></span>
                                    <span>المتبقي: <strong>${data.data.remaining_qt}</strong></span>
                                </div>
                            `;
                        }
                        self.showResult('✅ ' + data.message + details, 'success');
                        
                        // تحديث الكميات
                        self.refreshProducts();
                        self.loadRecentSales();
                        
                        // إعادة تعيين بعد نجاح البيع
                        setTimeout(function() {
                            self.resetForm();
                        }, 2000);
                        
                    } else {
                        self.showResult('❌ ' + (data.message || 'حدث خطأ'), 'error');
                    }
                },
                error: function(xhr) {
                    var message = 'حدث خطأ أثناء معالجة الطلب';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    self.showResult('❌ ' + message, 'error');
                },
                complete: function() {
                    self.isProcessing = false;
                    btn.html(originalText).prop('disabled', false);
                }
            });
        },
        
        /**
         * عرض النتيجة
         */
        showResult: function(message, type) {
            var result = $('#saleResult');
            result
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .show();
            
            if (type === 'success') {
                setTimeout(function() {
                    result.fadeOut(400);
                }, 3000);
            }
        },
        
        /**
         * إعادة تعيين النموذج
         */
        resetForm: function() {
            $('#selectedInputId').val('');
            $('#selectedProductName').html('<i class="fa fa-cube"></i> <span>لم يتم الاختيار</span>');
            $('#productDetails').hide();
            $('#saleQuantity').val('1');
            $('#totalPriceDisplay').text('0.00');
            $('#processSaleBtn').prop('disabled', true);
            $('#saleResult')
                .removeClass('success error')
                .html('')
                .hide();
            $('.product-card').removeClass('active');
            this.selectedInputId = null;
            this.sellingPrice = 0;
            this.availableQt = 0;
        },
        
        /**
         * تحديث المنتجات
         */
        refreshProducts: function() {
            $.request('onRefreshProducts', {
                success: function(data) {
                    if (data.products) {
                        var grid = $('#productsGrid');
                        if (data.products.length > 0) {
                            var html = '';
                            $.each(data.products, function(index, product) {
                                html += `
                                    <div class="product-card" 
                                         data-input-id="${product.input_id}"
                                         data-product-name="${product.product_name}"
                                         data-selling-price="${product.selling_price}"
                                         data-available-qt="${product.available_qt}"
                                         data-currency="${product.currency}"
                                         data-currency-label="${product.currency_label}">
                                        <div class="product-card-icon">
                                            <i class="fa fa-tint"></i>
                                        </div>
                                        <div class="product-card-name">${product.product_name}</div>
                                        <div class="product-card-price">${parseFloat(product.selling_price).toFixed(2)} ${product.currency_label}</div>
                                        <div class="product-card-stock">
                                            <i class="fa fa-archive"></i>
                                            <span>المتبقي: <strong>${product.available_qt}</strong></span>
                                        </div>
                                        <button class="btn btn-select" onclick="quickSale.selectProduct(this)">
                                            <i class="fa fa-plus-circle"></i> اختر
                                        </button>
                                    </div>
                                `;
                            });
                            grid.html(html);
                        } else {
                            grid.html(`
                                <div class="empty-products">
                                    <i class="fa fa-inbox fa-3x"></i>
                                    <p>لا توجد منتجات متاحة حالياً</p>
                                </div>
                            `);
                        }
                    }
                }
            });
        },
        
        /**
         * تحميل آخر العمليات
         */
        loadRecentSales: function() {
            $.request('onGetRecentSales', {
                update: { 'quick_sale/history_list': '#historyList' }
            });
        },
        
        /**
         * تحميل المزيد من السجل
         */
        loadMoreHistory: function() {
            var currentCount = $('.history-item').length;
            $.request('onGetRecentSales', {
                data: { limit: currentCount + 20 },
                update: { 'quick_sale/history_list': '#historyList' }
            });
        }
    };
    
    // =============================================
    // ربط الأحداث
    // =============================================
    
    // زر البيع
    $('#processSaleBtn').on('click', function() {
        quickSale.processSale();
    });
    
    // زر إعادة التعيين
    $('#resetSaleBtn').on('click', function() {
        quickSale.resetForm();
    });
    
    // تحديث السعر عند تغيير الكمية
    $('#saleQuantity').on('input', function() {
        quickSale.updateTotal();
    });
    
    // تحديث المنتجات
    $('#refreshProducts').on('click', function() {
        quickSale.refreshProducts();
    });
    
    // تحديث السجل
    $('#refreshHistory').on('click', function() {
        quickSale.loadRecentSales();
    });
    
    // اختصارات لوحة المفاتيح
    $(document).on('keydown', function(e) {
        // Enter = بيع
        if (e.key === 'Enter' && !e.ctrlKey && !e.metaKey) {
            var active = document.activeElement;
            if (active && (active.id === 'saleQuantity' || active.tagName === 'BODY')) {
                e.preventDefault();
                $('#processSaleBtn').click();
            }
        }
        // Escape = إعادة تعيين
        if (e.key === 'Escape') {
            quickSale.resetForm();
        }
    });
    
    // =============================================
    // تحميل البيانات الأولية
    // =============================================
    quickSale.loadRecentSales();
    
    console.log('✅ نظام البيع السريع - الكازية جاهز');
});