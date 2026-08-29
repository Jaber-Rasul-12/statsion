<?php

return [
    'plugin' => [
        'name' => 'Statsion',
        'description' => '',
        'points' => 'Points',
        'products' => 'Products',
        'inputs' => 'Inputs',
        'menu' => 'Statsion',
        'select' => 'Select',
        
        'log_changes_statsion' => 'Log changes',
        'message_delete' => 'You cannot delete this product because it has points',
    ],
    'model' => [
        'point' => [
            'id' => 'Id',
            'input' => 'Input',
            'qt' => 'Qt',
            'price' => 'Price',
            'product' => 'Product',
            'USD'=>"Dollar",
            'SYR'=>"Syrian Pound",
            'avilable_qy' => 'Avilable qy',
            'avilable_selling_price' => 'selling price',
            'currency' => 'Currency',
            'product_id' => 'Product id',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
        'product' => [
            'id' => 'Id',
            'name' => 'Name',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
        'input' => [
            'id' => 'Id',
            'product' => 'Product',
            'buying_price' => 'Buying price',
            'selling_price' => 'Selling price',
            'qt' => 'Qt',
            'residual' => 'Residual',
            'USD'=>"Dollar",
            'SYR'=>"Syrian Pound",
            'currency' => 'Currency',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
    ],
    'controller' => [
        'products' => [
            'products' => 'Products',
        ],
        'inputs' => [
            'inputs' => 'Inputs',
        ],
                'points' => [
            'points' => 'Points',
        ],
    ],
];
