<?php

namespace App\Counselling\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class CounsellingServiceProvider extends ServiceProvider {

    public function boot(Router $router) {
        $this->registerRoutes($router);
    }

    protected function registerRoutes(Router $router) {
        $router->group(
            ['namespace' => 'App\Counselling\Http\Controllers', 'prefix' => 'counselling', 'middleware' => ['web', 'auth']],
            function ($router) {
                $router->get('/{counselling}', [
                    'as' => 'counselling.index',
                    'uses' => 'CounsellingController@index'
                ]);

                $router->get('/{counselling}/data', [
                    'as' => 'counselling.indexData',
                    'uses' => 'CounsellingController@indexData'
                ]);

                $router->get('/setup/{counsellingSetup}', [
                    'as' => 'counselling.setup.id',
                    'uses' => 'CounsellingController@getCounsellingBySetupId'
                ]);

                $router->get('/create/{counsellingSetup}', [
                    'as' => 'counselling.createView',
                    'uses' => 'CounsellingController@showWizard'
                ]);

                $router->post('/{counsellingSetup}', [
                    'as' => 'counselling.store',
                    'uses' => 'CounsellingController@store'
                ]);

                $router->put('/{counselling}/finish-chat', [
                    'as' => 'counselling.finishChat',
                    'uses' => 'CounsellingController@finishChat'
                ]);

                $router->delete('/{counselling}', [
                    'as' => 'counselling.delete',
                    'uses' => 'CounsellingController@delete'
                ]);

                $router->put('/{counselling}', [
                    'as' => 'counselling.update',
                    'uses' => 'CounsellingController@update'
                ]);

                $router->post('/{counselling}/message', [
                    'as' => 'counselling.message.store',
                    'uses' => 'CounsellingController@storeMessage'
                ]);

                $router->get('/{counselling}/message', [
                    'as' => 'counselling.message.generate',
                    'uses' => 'CounsellingController@generate'
                ]);

                $router->put('/note/{counsellingId}/{messageNumber}', [
                    'as' => 'counselling.messageNote',
                    'uses' => 'CounsellingController@editMessageNote'
                ]);
            }
        );
    }
}
