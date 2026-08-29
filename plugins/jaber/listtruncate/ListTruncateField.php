<?php

namespace Jaber\ListTruncate;

use Backend\Classes\ListColumn;
use Lang;
use Model;
use Str;

/**
 * ListTruncate Plugin 
 * @author Jaber Rasul 
 * @package Jaber
 */
class ListTruncateField
{
    /**
     * Default field configuration
     * all these params can be overrided by column config
     * @var array
     */
    private static $defaultFieldConfig = [
        'icon'       => true,
        'titleTrue'  => 'jaber.listtruncate::lang.jaber.listtruncate.title_true',
        'titleFalse' => 'jaber.listtruncate::lang.jaber.listtruncate.title_false',
        'textTrue'   => 'jaber.listtruncate::lang.jaber.listtruncate.text_true',
        'textFalse'  => 'jaber.listtruncate::lang.jaber.listtruncate.text_false',
        'text-color' => true,
        'font-size' => '16',
        'truncate'   => false,
        'truncate_limit' => 12, 
        'show_more'  => true 
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

    
        if (is_string($value) && $config['truncate']) {
            return $field->renderTruncatedField();
        }

        $text_color = $field->getTextColor();

        return '
            <a href="javascript:;"
                class="btn btn-' . $text_color . '";
                title="' . $field->getButtonTitle() . '">
                ' . $field->getButtonValue() . '
            </a>
        ';
    }


    public function renderTruncatedField()
    {
        $config = $this->getConfig();
        $limit = $config['truncate_limit'];
        $fieldId = 'truncate-' . $this->name . '-' . $this->record->getKey();
        
        $shortText = Str::limit(e($this->value), $limit, '...');
        $fullText = e($this->value);
        
        $output = '<div class="truncate-container" id="' . $fieldId . '">';
        $output .= '<span class="truncate-short" style="display:inline-block;">' . $shortText . '</span>';
        $output .= '<span class="truncate-full" style="display:none;">' . $fullText . '</span>';
        
        if ($config['show_more']) {
            $output .= '<a href="javascript:;" class="truncate-toggle" onclick="toggleTruncate(\'' . $fieldId . '\')" style="font-size: 20px;margin-left:5px;padding: 10px;color:#3490dc;"> + </a>';
        }
        
        $output .= '</div>';
        
        
        static $scriptAdded = false;
        if (!$scriptAdded) {
            $output .= '
            <script>
            function toggleTruncate(fieldId) {
                var container = document.getElementById(fieldId);
                var short = container.querySelector(".truncate-short");
                var full = container.querySelector(".truncate-full");
                var toggle = container.querySelector(".truncate-toggle");
                
                if (short.style.display === "none") {
                    short.style.display = "inline-block";
                    full.style.display = "none";
                    toggle.textContent = " + ";
                } else {
                    short.style.display = "none";
                    full.style.display = "inline-block";
                    toggle.textContent = " - ";
                }
            }
            </script>';
            $scriptAdded = true;
        }
        
        return $output;
    }

    public function getTextColor()
    {
        if (!$this->getConfig('icon')) {
            return $this->value ? 'success' : 'danger';
        }
        return null;
    }

    /**
     * ListTruncate constructor.
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