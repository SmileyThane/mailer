<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('contact', 'ContactCrudController');
    Route::crud('contact-group', 'ContactGroupCrudController');
    Route::crud('campaign', 'CampaignCrudController');
    Route::crud('campaign-item', 'CampaignItemCrudController');
    Route::crud('template', 'TemplateCrudController');
    Route::crud('company', 'CompanyCrudController');
    Route::crud('import', 'ImportCrudController');
}); // this should be the absolute last line of this file