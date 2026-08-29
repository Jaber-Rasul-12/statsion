<?php namespace Winter\Builder\Behaviors;

use Winter\Builder\Classes\IndexOperationsBehaviorBase;
use Winter\Builder\Classes\MenusModel;
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
 * Plugin back-end menu management functionality for the Builder index controller
 *
 * @package winter\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexMenusOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/winter/builder/classes/menusmodel/fields.yaml';

    public function onMenusOpen()
    {
        $pluginCodeObj = $this->getPluginCode();

        $pluginCode = $pluginCodeObj->toCode();
        $widget = $this->makeBaseFormWidget($pluginCode);

        $result = [
            'tabTitle' => $widget->model->getPluginName().'/'.Lang::get('winter.builder::lang.menu.tab'),
            'tabIcon' => 'icon-sitemap',
            'tabId' => $this->getTabId($pluginCode),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onMenusSave()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $pluginCode = $pluginCodeObj->toCode();
        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());
        $model->setPluginCodeObj($pluginCodeObj);
        $menus = json_decode(Request::input('menus'), true);
        $lawerPluginCode = strtolower($pluginCode);
        if (is_array($menus)) {
            foreach ($menus as &$menu) {
                
                if (!str_starts_with($menu['label'], "{$lawerPluginCode}::lang.plugin")) {
                    $menu['label'] = strtolower("{$lawerPluginCode}::lang.plugin." . $menu['label']);
                }
                if (!empty($menu['sideMenu']) && is_array($menu['sideMenu'])) {
                    foreach ($menu['sideMenu'] as &$sideMenu) {
                        if (!str_starts_with($sideMenu['label'], "{$lawerPluginCode}::lang.plugin")) {
                            $sideMenu['label'] = strtolower("{$lawerPluginCode}::lang.plugin.".$sideMenu['label']."");
                        }
                        
                    }
                }
            }
        }

        // Fill the model with updated data
        $_POST['menus'] = json_encode($menus);
        $model->fill($_POST);
        $model->save();



        $pluginManager = PluginManager::instance();
        $path_plugin = $pluginManager->getPluginPath($_POST['plugin_code']);

        // // // Get the language keys to be set
        $langKeys = $this->getLangKeys($menus);
        if (empty($langKeys)) {
            return;
        }
        // dd(nl2br(htmlspecialchars(print_r($menus, false))));

        // // // Load the existing language file if it exists
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
        

        // // // Write the merged language keys back to the language file
        ArrayFile::open($langFilePath)->set($mergedLang)->write();


        Flash::success(Lang::get('winter.builder::lang.menu.saved'));

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName().'/'.Lang::get('winter.builder::lang.menu.tab'),
        ];


        return $result;
    }

    protected function getLangKeys($menus)
    {
        // Create an empty array to hold all language keys
        $langKeys = [];

        // Iterate through the columns and map them to the language key format
        foreach ($menus as $menu) {
            $menu_label = explode('.' , $menu['label']);
            $menu_label = end($menu_label);
            $langKeys[strtolower($menu_label)] = str_replace('_', ' ', ucfirst(strtolower($menu_label)));

            if(isset($menu['sideMenu'])){
                foreach($menu['sideMenu'] as $sideMenu){
                    $menu_label = explode('.' , $sideMenu['label']);
                    $menu_label = end($menu_label);
                    $langKeys[strtolower($menu_label)] = str_replace('_', ' ', ucfirst(strtolower($menu_label)));
        
                }
            }
        }

        return [
            'plugin' => $langKeys
            
        ];
    }

    protected function getTabId($pluginCode)
    {
        return 'menus-'.$pluginCode;
    }

    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new MenusModel();

        $model->loadPlugin($pluginCode);
        return $model;
    }
}
