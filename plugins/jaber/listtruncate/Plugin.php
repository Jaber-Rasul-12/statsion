<?php

namespace Jaber\ListTruncate;

use ApplicationException;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use Event;
use System\Classes\PluginBase;

/**
 * ListTruncate Plugin 
 * @author Jaber Rasul 
 * @package Jaber
 */

class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'jaber.listtruncate::lang.jaber.plugin.name',
            'description' => 'jaber.listtruncate::lang.jaber.plugin.description',
            'author'      => 'jaber',
            'icon'        => 'icon-toggle-on',
        ];
    }

    /**
     * Register custom list type
     *
     * @return array
     */
    public function registerListColumnTypes()
    {
        return [
            'jaber-list-truncate' => [ListTruncateField::class, 'render'],
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        Event::listen('backend.list.extendColumns', function ($widget) {
            /** @var \Backend\Widgets\Lists $widget */
            /** @var \Backend\Classes\ListColumn $listColumn */
            foreach ($widget->getColumns() as $name => $listColumn) {
                if (data_get($listColumn, 'config.type') !== 'jaber-list-truncate') {
                    continue;
                }

                $widget->addColumns([
                    $name => array_merge($listColumn->config, [
                        'clickable' => false,
                    ]),
                ]);
            }
        });
    }
}
