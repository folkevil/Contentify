<?php

ModuleRoute::context(__DIR__);

ModuleRoute::get('users/{id}/password', 'UsersController@editPassword');
ModuleRoute::put('users/{id}/password', 'UsersController@updatePassword');
ModuleRoute::resource('users', 'UsersController', ['only' => ['index', 'show', 'edit', 'update']]);
ModuleRoute::get('users/{id}/{slug}', 'UsersController@show');
ModuleRoute::post('users/search', 'UsersController@search');

ModuleRoute::resource('admin/users', 'AdminUsersController');
ModuleRoute::post('admin/users/search', 'AdminUsersController@search');
