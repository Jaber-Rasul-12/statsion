<?php namespace Winter\Builder\Behaviors;

use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\ControllerModel;
use Winter\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;
use System\Classes\PluginManager;
use Winter\Storm\Parse\PHP\ArrayFile;


/**
 * Plugin controller management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexControllerOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/controllermodel/fields.yaml';

    public function onControllerOpen()
    {
        $controller = Input::get('controller');
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $widget = $this->makeBaseFormWidget($controller, $options);
        $this->vars['controller'] = $controller;

        $result = [
            'tabTitle' => $this->getTabName($widget->model),
            'tabIcon' => 'icon-signs-post',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $controller),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onControllerCreate()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $model = $this->loadOrCreateBaseModel(null, $options);
        $model->fill($_POST);
        $model->save();

        $this->vars['controller'] = $model->controller;

        $result = $this->controller->widget->controllerList->updateList();

        if ($model->behaviors) {
            // Create a new tab only for controllers
            // with behaviors.

            $widget = $this->makeBaseFormWidget($model->controller, $options);
            
            $tab = [
                'tabTitle' => $this->getTabName($widget->model),
                'tabIcon' => 'icon-signs-post',
                'tabId' => $this->getTabId($pluginCodeObj->toCode(), $model->controller),
                'tab' => $this->makePartial('tab', [
                    'form'  => $widget,
                    'pluginCode' => $pluginCodeObj->toCode()
                ])
            ];

            $result = array_merge($result, $tab);
        }

        $this->mergeRegistryDataIntoResult($result, $pluginCodeObj);

        return $result;
    }

    public function onControllerSave()
    {
        $pluginCode = strtolower($this->getPluginCode()->toCode());
        $controller = Input::get('controller');

        $model = $this->loadModelFromPost();
        $behaviors_list = json_decode($_POST['behaviors']['Backend\Behaviors\ListController']);
        $behaviors_form = json_decode($_POST['behaviors']['Backend\Behaviors\FormController']);

        $behaviors_list->title = (!str_starts_with($behaviors_list->title, "{$pluginCode}::lang.controller")) ? $pluginCode . '::lang.controller.' . strtolower($behaviors_list->title)  . '.' . strtolower($behaviors_list->title) :$behaviors_list->title;
        $behaviors_form->name = (!str_starts_with($behaviors_form->name, "{$pluginCode}::lang.controller")) ? $pluginCode . '::lang.controller.' . strtolower($behaviors_form->name)  . '.' . strtolower($behaviors_form->name) : $behaviors_form->name;
        $_POST['behaviors']['Backend\Behaviors\ListController'] = json_encode($behaviors_list);
        $_POST['behaviors']['Backend\Behaviors\FormController'] = json_encode($behaviors_form);

        $model->fill($_POST);
        $model->save();

        $pluginManager = PluginManager::instance();
        $path_plugin = $pluginManager->getPluginPath($pluginCode);
    
        // Get the language keys to be set
        $langKeys = $this->getLangKeys($behaviors_list->title);
        if (empty($langKeys)) {
            return;
        }
    
        // // Load the existing language file if it exists
        $langFilePath = $path_plugin . DIRECTORY_SEPARATOR
            . 'lang'
            . DIRECTORY_SEPARATOR
            . 'en'
            . DIRECTORY_SEPARATOR
            . 'lang.php';
    
        // // Check if the language file exists
        if (file_exists($langFilePath)) {
            // If the file exists, open it and merge the new keys with the existing ones
            $existingLang = include($langFilePath);
    
            // Merge the existing language keys with the new ones, preserving existing ones
            $mergedLang = array_merge_recursive($existingLang, $langKeys);
        } else {
            // If the language file doesn't exist, use the new keys as is
            $mergedLang = $langKeys;
        }
    
        // // Write the merged language keys back to the language file
        ArrayFile::open($langFilePath)->set($mergedLang)->write();


        // dd(nl2br(htmlspecialchars(print_r($, false))));


        Flash::success(Lang::get('winter.builder::lang.controller.saved'));

        $result['builderResponseData'] = [];

        return $result;
    }

    protected function getLangKeys($controller)
    {
        $name_controller = explode('.' , $controller);
        $name_controller = end($name_controller);
        return [
            'controller' => [
                strtolower($name_controller) => [
                    strtolower($name_controller) => str_replace('_' , ' ' , ucfirst($name_controller))
                ]
            ]
        ];
    }

    public function onControllerShowCreatePopup()
    {
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $this->baseFormConfigFile = '~/plugins/winter/builder/classes/controllermodel/new-controller-fields.yaml';
        $widget = $this->makeBaseFormWidget(null, $options);

        return $this->makePartial('create-controller-popup-form', [
            'form'=>$widget,
            'pluginCode' =>  $pluginCodeObj->toCode()
        ]);
    }

    protected function getTabName($model)
    {
        $pluginName = Lang::get($model->getModelPluginName());

        return $pluginName.'/'.$model->controller;
    }

    protected function getTabId($pluginCode, $controller)
    {
        return 'controller-'.$pluginCode.'-'.$controller;
    }

    protected function loadModelFromPost()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $controller = Input::get('controller');

        return $this->loadOrCreateBaseModel($controller, $options);
    }

    protected function loadOrCreateBaseModel($controller, $options = [])
    {
        $model = new ControllerModel();

        if (isset($options['pluginCode'])) {
            $model->setPluginCode($options['pluginCode']);
        }

        if (!$controller) {
            return $model;
        }

        $model->load($controller);
        return $model;
    }

    protected function mergeRegistryDataIntoResult(&$result, $pluginCodeObj)
    {
        if (!array_key_exists('builderResponseData', $result)) {
            $result['builderResponseData'] = [];
        }

        $pluginCode = $pluginCodeObj->toCode();
        $result['builderResponseData']['registryData'] = [
            'urls' => ControllerModel::getPluginRegistryData($pluginCode, null),
            'pluginCode' => $pluginCode
        ];
    }
}
