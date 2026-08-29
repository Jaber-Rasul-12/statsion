<?php

namespace Winter\Builder\Behaviors;

use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\ModelListModel;
use Winter\Builder\Classes\PluginCode;
use Winter\Builder\Classes\ModelModel;
use ApplicationException;
use Exception;
use Winter\Storm\Parse\PHP\ArrayFile;
use Request;
use Flash;
use Input;
use Lang;
use System\Classes\PluginManager;

/**
 * Model list management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexModelListOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/modellistmodel/fields.yaml';

    public function onModelListCreateOrOpen()
    {
        $fileName = Input::get('file_name');
        $modelClass = Input::get('model_class');

        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode(),
            'modelClass' => $modelClass
        ];

        $widget = $this->makeBaseFormWidget($fileName, $options);
        $this->vars['fileName'] = $fileName;

        $result = [
            'tabTitle' => $widget->model->getDisplayName(Lang::get('winter.builder::lang.list.tab_new_list')),
            'tabIcon' => 'icon-list',
            'tabId' => $this->getTabId($modelClass, $fileName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'fileName' => $fileName,
                'modelClass' => $modelClass
            ])
        ];

        return $result;
    }

    public function onModelListSave()
    {
        $model = $this->loadOrCreateListFromPost();
        $model->fill($_POST);
        $model->save();

        $pluginManager = PluginManager::instance();
        $path_plugin = $pluginManager->getPluginPath($_POST['plugin_code']);
    
        // Get the language keys to be set
        $langKeys = $this->getLangKeys($_POST['model_class'], $_POST['columns']);
        if (empty($langKeys)) {
            return;
        }
    
        // Load the existing language file if it exists
        $langFilePath = $path_plugin . DIRECTORY_SEPARATOR
            . 'lang'
            . DIRECTORY_SEPARATOR
            . 'en'
            . DIRECTORY_SEPARATOR
            . 'lang.php';
    
        // Check if the language file exists
        if (file_exists($langFilePath)) {
            // If the file exists, open it and merge the new keys with the existing ones
            $existingLang = include($langFilePath);
    
            // Merge the existing language keys with the new ones, preserving existing ones
            $mergedLang = array_merge_recursive($existingLang, $langKeys);
        } else {
            // If the language file doesn't exist, use the new keys as is
            $mergedLang = $langKeys;
        }
    
        // Write the merged language keys back to the language file
        ArrayFile::open($langFilePath)->set($mergedLang)->write();


        $result = $this->controller->widget->modelList->updateList();

        Flash::success(Lang::get('winter.builder::lang.list.saved'));

        $modelClass = Input::get('model_class');
        $result['builderResponseData'] = [
            'builderObjectName' => $model->fileName,
            'tabId' => $this->getTabId($modelClass, $model->fileName),
            'tabTitle' => $model->getDisplayName(Lang::get('winter.builder::lang.list.tab_new_list'))
        ];

        $this->mergeRegistryDataIntoResult($result, $model, $modelClass);

        return $result;
    }



    protected function getLangKeys($model, $columns)
    {
        // Create an empty array to hold all language keys
        $langKeys = [];
    
        // Iterate through the columns and map them to the language key format
        foreach ($columns as $column) {
            // Each column is a key-value pair like ['id' => 'Id']
            foreach ($column as $key) {
                // Add the formatted key-value pair to the $langKeys array
                $langKeys[ strtolower($column['field'])] = str_replace('_', ' ', ucfirst(strtolower($column['field'])));
            }
        }
    
        return [
            'model' => [
                strtolower($model) => $langKeys
            ]
        ];
    }

    public function onModelListDelete()
    {
        $model = $this->loadOrCreateListFromPost();

        $model->deleteModel();

        $result = $this->controller->widget->modelList->updateList();

        $modelClass = Input::get('model_class');
        $this->mergeRegistryDataIntoResult($result, $model, $modelClass);

        return $result;
    }

    public function onModelListGetModelFields()
    {
        $columnNames = ModelModel::getModelFields($this->getPluginCode(), Input::get('model_class'));

        $result = [];
        foreach ($columnNames as $columnName) {
            $result[] = [
                'title' => $columnName,
                'value' => $columnName
            ];
        }

        return [
            'responseData' => [
                'options' => $result
            ]
        ];
    }

    public function onModelListLoadDatabaseColumns()
    {
        $columns = ModelModel::getModelColumnsAndTypes($this->getPluginCode(), Input::get('model_class'));

        $pluginCode = $this->getPluginCode()->toCode();
        [$author, $plugin] = explode('.', $pluginCode);

        // Convert both to lowercase
        $author = strtolower($author);
        $plugin = strtolower($plugin);

        $columns = array_map(function ($column) use ($author, $plugin) {
            $column['label'] = $author . '.' . $plugin . '::lang.model.' . strtolower(Request::input('model_class')) . '.' . strtolower($column['name']);
            return $column;
        }, $columns);
        // dd(nl2br(htmlspecialchars(print_r($columns, false))));
        // Get the language keys to be set

        return [
            'responseData' => [
                'columns' => $columns
            ]
        ];
    }

    protected function loadOrCreateListFromPost()
    {
        $pluginCode = Request::input('plugin_code');
        $modelClass = Input::get('model_class');
        $fileName = Input::get('file_name');

        $options = [
            'pluginCode' => $pluginCode,
            'modelClass' => $modelClass
        ];

        return $this->loadOrCreateBaseModel($fileName, $options);
    }

    protected function getTabId($modelClass, $fileName)
    {
        if (!strlen($fileName)) {
            return 'modelForm-' . uniqid(time());
        }

        return 'modelList-' . $modelClass . '-' . $fileName;
    }

    protected function loadOrCreateBaseModel($fileName, $options = [])
    {
        $model = new ModelListModel();

        if (isset($options['pluginCode']) && isset($options['modelClass'])) {
            $model->setPluginCode($options['pluginCode']);
            $model->setModelClassName($options['modelClass']);
        }

        if (!$fileName) {
            $model->initDefaults();

            return $model;
        }

        $model->loadForm($fileName);
        return $model;
    }

    protected function mergeRegistryDataIntoResult(&$result, $model, $modelClass)
    {
        if (!array_key_exists('builderResponseData', $result)) {
            $result['builderResponseData'] = [];
        }

        $fullClassName = $model->getPluginCodeObj()->toPluginNamespace() . '\\Models\\' . $modelClass;
        $pluginCode = $model->getPluginCodeObj()->toCode();
        $result['builderResponseData']['registryData'] = [
            'lists' => ModelListModel::getPluginRegistryData($pluginCode, $modelClass),
            'pluginCode' => $pluginCode,
            'modelClass' => $fullClassName
        ];
    }
}
