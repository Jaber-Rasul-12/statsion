<?php

namespace Winter\Builder\Behaviors;

use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\PermissionsModel;
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
 * Plugin permissions management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexPermissionsOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/permissionsmodel/fields.yaml';

    public function onPermissionsOpen()
    {
        $pluginCodeObj = $this->getPluginCode();

        $pluginCode = $pluginCodeObj->toCode();
        $widget = $this->makeBaseFormWidget($pluginCode);

        $result = [
            'tabTitle' => Lang::get($widget->model->getPluginName()) . '/' . Lang::get('winter.builder::lang.permission.tab'),
            'tabIcon' => 'icon-unlock-alt',
            'tabId' => $this->getTabId($pluginCode),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onPermissionsSave()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $pluginCode = $pluginCodeObj->toCode();
        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());
        $model->setPluginCodeObj($pluginCodeObj);

        $permissions = Request::input('permissions');

        $lawerPluginCode = strtolower($pluginCode);
        if (is_array($permissions)) {
            foreach ($permissions as &$permission) {

                if (!str_starts_with($permission['label'], "{$lawerPluginCode}::lang.plugin")) {
                    $permission['label'] = "{$lawerPluginCode}::lang.plugin." . $permission['label'];
                }
                if (!str_starts_with($permission['tab'], "{$lawerPluginCode}::lang.plugin")) {
                    $permission['tab'] = "{$lawerPluginCode}::lang.plugin." . $permission['tab'];
                }
            }
        }

        // // Fill the model with updated data
        $_POST['permissions'] = $permissions;
        // dd(nl2br(htmlspecialchars(print_r($_POST, false))));

        $model->fill($_POST);
        $model->save();

        $pluginManager = PluginManager::instance();
        $path_plugin = $pluginManager->getPluginPath($_POST['plugin_code']);

        // // Get the language keys to be set
        $langKeys = $this->getLangKeys($_POST['permissions']);
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


        Flash::success(Lang::get('winter.builder::lang.permission.saved'));

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName() . '/' . Lang::get('winter.builder::lang.permission.tab'),
            'pluginCode' => $pluginCode
        ];

        return $result;
    }

    protected function getLangKeys($permissions)
    {
        // Create an empty array to hold all language keys
        $langKeys = [];

        // Iterate through the columns and map them to the language key format
        foreach ($permissions as $permission) {
            $langKeys[strtolower($permission['permission'])] = str_replace('_', ' ', ucfirst(strtolower($permission['permission'])));
        }

        return [
            'plugin' => $langKeys
            
        ];
    }

    protected function getTabId($pluginCode)
    {
        return 'permissions-' . $pluginCode;
    }

    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new PermissionsModel();

        $model->loadPlugin($pluginCode);
        return $model;
    }
}
