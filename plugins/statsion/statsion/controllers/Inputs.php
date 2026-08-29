<?php namespace Statsion\Statsion\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Inputs extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'inputs' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Statsion.Statsion', 'menu', 'inputs');
    }
}
