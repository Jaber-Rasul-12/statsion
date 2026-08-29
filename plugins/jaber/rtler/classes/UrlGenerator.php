<?php 
namespace Jaber\Rtler\Classes;

use Illuminate\Routing\RouteCollection;
use Illuminate\Http\Request;
use Winter\Storm\Router\UrlGenerator as WinterUrlGenerator;
use App;
use File;
use Config;
use Jaber\Rtler\Models\Settings;
use RainLab\Translate\Classes\Translator;
use Backend\Models\Preference as BackendPreference;
use Illuminate\Support\Facades\Lang;

/**
 * It shifts the controller from right to left
 *
 * @package Jaber\Rtler
 * @author Jaber Rasul 
 */
class UrlGenerator extends WinterUrlGenerator
{

    // add any language if you wnat and auto run rtl when you change language on backend
    public static $rtlLanguages = [
        'fa',   /* 'فارسی', Persian */
        'ae',    /* Avestan */
        'ar',   /* 'العربية', Arabic */
        'arc',  /* Aramaic */
        'bcc',  /* 'بلوچی مکرانی', Southern Balochi */
        'bqi',  /* 'بختياري', Bakthiari */
        'ckb',  /* 'Soranî / کوردی', Sorani */
        'dv',   /* Dhivehi */
        'glk',  /* 'گیلکی', Gilaki */
        'he',   /* 'עברית', Hebrew */
        // 'ku',   /* 'Kurdî / كوردی', Kurdish */
        'mzn',  /* 'مازِرونی', Mazanderani */
        'nqo',  /* N'Ko */
        'pnb',  /* 'پنجابی', Western Punjabi */
        'ps',   /* 'پښتو', Pashto, */
        'sd',   /* 'سنڌي', Sindhi */
        'ug',   /* 'Uyghurche / ئۇيغۇرچە', Uyghur */
        'ur',   /* 'اردو', Urdu */
        'yi'    /* 'ייִדיש', Yiddish */
    ];

    public function __construct(RouteCollection $routes, Request $request)
    {
        parent::__construct($routes, $request);
    }

    /**
     * Generate a URL to an application asset.
     *
     * @param  string $path
     * @param  bool|null $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if ($this->isValidUrl($path)) return $path;
        if (self::checkForRtl('editor_mode') || self::checkForRtl('markdown_editor_mode')) {
            if (strpos($path, 'modules/backend/formwidgets/codeeditor/assets/js/build-min.js')) {
                return parent::asset('/plugins/jaber/rtler/assets/js/codeeditor.min.js');
            }
        }
        if (self::checkForRtl('markdown_editor_mode')) {
            if (strpos($path, 'modules/backend/formwidgets/markdowneditor/assets/js/markdowneditor.js')) {
                return parent::asset('/plugins/jaber/rtler/assets/js/markdowneditor.js');
            }
        }
        if (self::checkForRtl('layout_mode')) {
            if (!strpos($path, '/jaber/rtler/assets/css/rtler.css')) {
                $backendUri = Config::get('cms.backendUri', 'backend');
                $requestUrl = $this->request->url();
                $rtlFilePath = base_path(dirname($path)) . '/' . pathinfo($path, PATHINFO_FILENAME) . '.rtl.' . File::extension($path);
                if (File::exists($rtlFilePath)) {
                    $path = dirname($path) . '/' . pathinfo($path, PATHINFO_FILENAME) . '.rtl.' . File::extension($path);
                } else if (File::extension($path) == 'css' && (strpos($requestUrl, $backendUri) || strpos($path, 'plugins/') || strpos($path, 'modules/'))) {
                    $path = CssFlipper::flipCss($path);
                }
            }
        }
        return parent::asset($path, $secure);
    }

    /**
     * Generate an absolute URL to the given path.
     *
     * @param  string  $path
     * @param  mixed  $extra
     * @param  bool|null  $secure
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }
        if (!strpos($path, '/jaber/rtler/assets/css/rtler.css')) {
            $backendUri = Config::get('cms.backendUri', 'backend');
            $requestUrl = $this->request->url();
            $rtlFilePath = base_path(dirname($path)) . '/' . pathinfo($path, PATHINFO_FILENAME) . '.rtl.' . File::extension($path);

            if (File::exists($rtlFilePath)) {
                $path = dirname($path) . '/' . pathinfo($path, PATHINFO_FILENAME) . '.rtl.' . File::extension($path);
            } else if (File::extension($path) == 'css' && (strpos($requestUrl, $backendUri) || strpos($path, 'plugins/') || strpos($path, 'modules/'))) {
                $path = CssFlipper::flipCss($path);
            }
        }
        return parent::to($path, $extra, $secure);
    }

    /**
     * Get user locale
     *
     * @return string
     */
    private static function getCurrentLocale()
    {
        BackendPreference::setAppLocale();
        BackendPreference::setAppFallbackLocale();

        if (class_exists('RainLab\Translate\Classes\Translator')) {
            $translator = Translator::instance();
            return $translator->getLocale();
        }

        return App::getLocale();
    }

    /**
     * Detect user language is RTL or not
     *
     * @return boolean
     */
    protected static function isLanguageRtl()
    {
        $locale = self::getCurrentLocale();
        $locale = Lang::getLocale();
        if (in_array($locale, static::$rtlLanguages)) {
            return true;
        }
        return false;
    }

    /**
     * Check settings for rtl mode
     *
     * @param string $what  What should check?
     *
     * @return boolean
     */
    public static function checkForRtl($what)
    {
        $value = Settings::get($what, 'language');
        if ($value === 'never') {
            return false;
        }
        if ($value === 'always') {
            return true;
        }
        return self::isLanguageRtl();
    }
}
