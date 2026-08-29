<?php

namespace Jaber\Listrevisionhistory;

use Backend;
use System\Classes\PluginBase;
use Event;
use Backend\Classes\Controller;

/**
 * listrevisionhistory Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'jaber.listrevisionhistory::lang.plugin.name',
            'description' => 'jaber.listrevisionhistory::lang.plugin.description',
            'author'      => 'jaber',
            'icon'        => 'icon-leaf'
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
            'jaber-list-revision-history' => [ListRevisionHistoryColumn::class, 'render'],
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
                if (data_get($listColumn, 'config.type') !== 'jaber-list-revision-history') {
                    continue;
                }

                $widget->addColumns([
                    $name => array_merge($listColumn->config, [
                        'clickable' => false,
                    ]),
                ]);
            }
        });



        Controller::extend(function ($controller) {
            /** @var Controller $controller */
            $controller->addDynamicMethod('onLoadContent', function () use ($controller) {
                $recordId = post('id');
                $record = $controller->formFindModelObject($recordId);
                $output = '';
                foreach ($record->revision_history as $historyRecord) {
                    $output .= '<div data-control="balloon-selector" class="control-balloon-selector" style="width: 100%;">
                            <ul style="width: 100%; ">
                                <li style="width: 41%; color: #34495e;" class="text-center">' . htmlspecialchars($historyRecord->old_value) . '</li>
                                <li style="width: 10%; color: #34495e;" class="text-center">=></li>
                                <li style="width: 41%;" class="active text-center">' . htmlspecialchars($historyRecord->new_value) . '</li>
                            </ul>
                       </div>';
                }
                return '<div class="modal-header">
                        <button type="button" class="close" data-dismiss="popup">&times;</button>
                        </div>
                        <div class="modal-body">
                            '. $output .'
                        </div>
                        <br>';
            });
        });
    }
}
