<?php namespace Jaber\NoFrontend;

use App;
use Route;
use Backend;
use Redirect;
use System\Classes\PluginBase;

/**
 * NoFrontend Plugin Information File
 * 
 * If you want to use the plugin in .env edit APP_URL="you websit url" 
 * @author  Jaber Rasul
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
            'name'        => 'No Frontend',
            'description' => 'Simply removes the applcation\'s front-end and redirects it to the admin area.',
            'author'      => 'Jaber Rasul',
            'icon'        => 'icon-ban',
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        /**
         * Define URIs for later
         */
        $backendUri = Backend::uri();

        /**
         * Route homepage to admin area
         */
        Route::get('/', function ()  {
            return Redirect::to("/backend");
        });

        /**
         * Route all other front-end pages to admin area
         */

         Route::get('/{any}', function () {
            if(!App::runningInBackend()){
                return Redirect::to("/backend");
            }  
        });
        Route::get('/{any}', function ($any) use ($backendUri) {
            if(!App::runningInBackend() ){
                return Redirect::to($backendUri);
            }
        })->where('any', '^(?!'.ltrim($backendUri, '/').').*$');
        /**
         * Stop if not running in admin area
         */
        if(!App::runningInBackend()){
            return;
        }

    }
}
