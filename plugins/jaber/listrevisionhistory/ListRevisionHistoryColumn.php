<?php

namespace Jaber\Listrevisionhistory;

use Backend\Classes\ListColumn;
use Lang;
use Model;

class ListRevisionHistoryColumn
{


    private $name;
    private $value;
    private $column;
    private $record;
    private $config;

    /**
     * ListSwitchField constructor.
     *
     * @param            $value
     * @param ListColumn $column
     * @param Model      $record
     */
    public function __construct($value, ListColumn $column, $record)
    {
        $this->name = $column->columnName;
        $this->value = $value;
        $this->column = $column;
        $this->record = $record;
    }

    /**
     * @param            $value
     * @param ListColumn $column
     * @param Model      $record
     *
     * @return string HTML
     */
    public static function render($value, ListColumn $column, $record)
    {
        $field = new self($value, $column, $record);
        return $record->revision_history->count() > 0 ? '<div class="text-center"><a
        data-control="popup"
        data-handler="onLoadContent"
        href="javascript:;"
        data-size="large"
        data-keyboard="true"
        data-extra-data="' . $field->getRequestData() . '"
        class="btn btn-primary wn-icon-hdd-o">
        ' . trans('jaber.listrevisionhistory::lang.plugin.show_old_changes') . '
        </a> </div>' : '<p class="flash-message static info">' . trans('jaber.listrevisionhistory::lang.plugin.there_are_no_changes') . '</p>';
    }

    public function getRequestData()
    {

        $data = [
            "id: {$this->record->{$this->record->getKeyName()}}",
        ];

        if (post('page')) {
            $data[] = "page: " . post('page');
        }

        return implode(', ', $data);
    }
}
