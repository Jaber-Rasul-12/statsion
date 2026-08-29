<?php

namespace Jaber\Rtler;

use App;
use Lang;
use Event;
use Config;
use Backend;
use Request;
use Jaber\Rtler\Classes\UrlGenerator;
use Jaber\Rtler\Models\Settings;
use System\Classes\PluginBase;

/**
 * It shifts the controller from right to left
 *
 * @package Jaber\Rtler
 * @author Jaber Rasul 
 */
class Plugin extends PluginBase
{
    /**
     * @var bool Plugin requires elevated permissions.
     */
    public $elevated = true;

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'jaber.rtler::lang.plugin.name',
            'description' => 'jaber.rtler::lang.plugin.description',
            'author'      => 'jaber',
            'icon'        => 'icon-anchor'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
        // Check if we are currently in backend module.
        if (!App::runningInBackend()) {
            return;
        }
        $this->registerUrlGenerator();
        // Listen for `backend.page.beforeDisplay` event.
        Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            if (!Request::ajax() && UrlGenerator::checkForRtl('layout_mode')) {
                $controller->addCss(Config::get('cms.pluginsPath') . ('/jaber/rtler/assets/css/rtler.css'));
                $controller->addJs(Config::get('cms.pluginsPath') . ('/jaber/rtler/assets/js/rtler.min.js'));
            }
        });
    }


    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'jaber.rtler.change_settings' => [
                'tab' => 'jaber.rtler::lang.permissions.tab',
                'label' => 'jaber.rtler::lang.permissions.label'
            ],
        ];
    }


    protected function registerUrlGenerator()
{
    $this->app->singleton('url', function ($app) {
        $routes = $app['router']->getRoutes();
        $url = new \Jaber\Rtler\Classes\UrlGenerator(
            $routes,
            $app->rebinding(
                'request',
                $this->requestRebinder()
            )
        );
        $url->setSessionResolver(function () {
            return $this->app['session'];
        });
        $app->rebinding('routes', function ($app, $routes) {
            $app['url']->setRoutes($routes);
        });
        return $url;
    });
}


    protected function requestRebinder()
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }

    public function registerSettings()
    {
        return [
            'rtler' => [
                'label'       => 'jaber.rtler::lang.setting.menu',
                'description' => 'jaber.rtler::lang.setting.description',
                'category'    => 'jaber',
                'icon'        => 'icon-anchor',
                'class'       => 'Jaber\Rtler\Models\Settings',
                'order'       => 500,
                'keywords'    => 'jaber rtler',
                'permissions' => ['jaber.rtler.change_settings']
            ]
        ];
    }
}
