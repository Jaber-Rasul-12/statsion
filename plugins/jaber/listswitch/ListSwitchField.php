<?php

namespace Jaber\ListSwitch;

use Backend\Classes\ListColumn;
use Lang;
use Model;


/**
 * listSwitch Plugin 
 * @author Jaber Rasul 
 * @package Jaber
 */
class ListSwitchField
{
    /**
     * Default field configuration
     * all these params can be overrided by column config
     * @var array
     */
    private static $defaultFieldConfig = [
        'icon'       => true,
        'titleTrue'  => 'jaber.listswitch::lang.jaber.listswitch.title_true',
        'titleFalse' => 'jaber.listswitch::lang.jaber.listswitch.title_false',
        'textTrue'   => 'jaber.listswitch::lang.jaber.listswitch.text_true',
        'textFalse'  => 'jaber.listswitch::lang.jaber.listswitch.text_false',
        'text-color' => true,
        'font-size' => '16',
        'request'    => 'onSwitchJaberListField'
    ];

    private $name;
    private $value;
    private $column;
    private $record;
    private $config;

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
        $config = $field->getConfig();

        $text_color = $field->getTextColor();
        

        return '
            <a href="javascript:;"
                class="btn btn-' . $text_color . '";
                data-request="' . $config['request'] . '"
                data-request-data="' . $field->getRequestData() . '"
                data-stripe-load-indicator
                title="' . $field->getButtonTitle() . '">
                ' . $field->getButtonValue() . '
            </a>
               ';
    }

    public function getTextColor()
    {
        if (!$this->getConfig('icon')) {
            return $this->value ? 'success' : 'danger';
        }
        return null;
    }

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

        $this->config = array_merge(self::$defaultFieldConfig, $column->config);
    }

    /**
     * @param $config
     *
     * @return mixed
     */
    private function getConfig($config = null)
    {
        return data_get($this->config, $config);
    }

    /**
     * Return data-request-data params for the switch button
     *
     * @return string
     */
    public function getRequestData()
    {
        $modelClass = str_replace('\\', '\\\\', get_class($this->record));

        $data = [
            "id: {$this->record->{$this->record->getKeyName()}}",
            "field: '$this->name'",
            "model: '$modelClass'"
        ];

        if (post('page')) {
            $data[] = "page: " . post('page');
        }

        return implode(', ', $data);
    }

    /**
     * Return button text or icon
     *
     * @return string
     */
    public function getButtonValue()
    {
        if (!$this->getConfig('icon')) {
            return Lang::get($this->getConfig($this->value ? 'textTrue' : 'textFalse'));
        }
        $textColor = $this->getConfig('text-color');
        $fontSize = $this->getConfig('font-size') . 'px!important;';
        $fontSize = str_replace(' ', '', $fontSize);
        if ($this->value) {
            $textColor = $textColor ? 'text-success' : '';
            return '<i class="oc-icon-check ' . $textColor . '" style="font-size: ' . $fontSize . '"></i>';
        }
        $textColor = $textColor ? 'text-danger' : '';
        return '<i class="oc-icon-times ' . $textColor . '" style="font-size: ' . $fontSize . '"></i>';
    }

    /**
     * Return button hover title
     *
     * @return string
     */
    public function getButtonTitle()
    {
        return Lang::get($this->getConfig($this->value ? 'titleTrue' : 'titleFalse'));
    }
}
