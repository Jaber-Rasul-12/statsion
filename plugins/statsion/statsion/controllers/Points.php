<?php namespace Statsion\Statsion\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Points extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'points' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Statsion.Statsion', 'menu', 'points');
    }
}
