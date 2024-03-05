<?php

namespace App\Course\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class CourseServiceProvider extends ServiceProvider {

    public function boot(Router $router) {
        $this->registerRoutes($router);
    }

    protected function registerRoutes(Router $router) {
        $router->group(
            ['namespace' => 'App\Course\Http\Controllers', 'prefix' => 'course'],
            function ($router) {

            // editingteacher-routes
            $router->group(['middleware' => ['web', 'auth', 'editingteacher']], function ($router) {
                $router->get('/create', [
                    'as' => 'course.create',
                    'uses' => 'CourseController@create'
                ]);

                $router->post('/store', [
                    'as' => 'course.store',
                    'uses' => 'CourseController@store'
                ]);

                $router->delete('/{course}', [
                    'as' => 'course.delete',
                    'uses' => 'CourseController@delete'
                ]);

                $router->get('{course}/members', [
                    'as' => 'course.members.index',
                    'uses' => 'CourseMemberController@index'
                ]);

                $router->put('members/{courseMember}', [
                    'as' => 'course.members.update',
                    'uses' => 'CourseMemberController@update'
                ]);

                $router->delete('/members/{courseMember}', [
                    'as' => 'course.members.delete',
                    'uses' => 'CourseMemberController@delete'
                ]);

                $router->get('roles', [
                    'as' => 'course.roles',
                    'uses' => 'CourseController@getRoles'
                ]);
            });
    
            $router->group(['middleware' => ['web', 'auth']], function ($router) {
                $router->get('/{course}', [
                    'as' => 'course.index',
                    'uses' => 'CourseController@index'
                ]);

                $router->get('/{course}/infos', [
                    'as' => 'course.infos',
                    'uses' => 'CourseController@indexData'
                ]);

                $router->post('/add-member', [
                    'as' => 'course.members.store',
                    'uses' => 'CourseMemberController@store'
                ]);

                $router->delete('/{course}/leave', [
                    'as' => 'course.member.leave',
                    'uses' => 'CourseMemberController@leave'
                ]);

                $router->get('/{course}/exercises', [
                    'as' => 'course.exercises',
                    'uses' => 'CourseController@getExercisesView'
                ]);

                $router->get('/{course}/tasks', [
                    'as' => 'course.tasks',
                    'uses' => 'CourseController@getTasksView'
                ]);

                $router->get('/{course}/pseudonym', [
                    'as' => 'course.pseudonym',
                    'uses' => 'CourseController@getPseudonym'
                ]);

                $router->get('/{course}/statistics', [
                    'as' => 'course.statistics',
                    'uses' => 'CourseController@getStatistics'
                ]);
            });
        }); 
    }

}
