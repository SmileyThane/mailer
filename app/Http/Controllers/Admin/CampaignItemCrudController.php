<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CampaignItemRequest;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Models\Template;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CampaignItemCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CampaignItemCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\CampaignItem::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/campaign-item');
        CRUD::setEntityNameStrings('campaign item', 'campaign items');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('id');
        CRUD::addColumn(['name' => 'campaign', 'type' => 'relationship', 'label' => 'Campaign']);
        CRUD::addColumn(['name' => 'template', 'type' => 'relationship', 'label' => 'Template']);
        CRUD::column('status_name');
        CRUD::column('created_at');
        CRUD::column('processed_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @return void
     */
    protected function setupShowOperation()
    {
        CRUD::column('id');
        CRUD::addColumn(['name' => 'campaign', 'type' => 'relationship', 'label' => 'Campaign']);
        CRUD::addColumn(['name' => 'template', 'type' => 'relationship', 'label' => 'Template']);
        CRUD::column('status');
        CRUD::column('created_at');
        CRUD::column('processed_at');
        CRUD::column('status_log');

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CampaignItemRequest::class);

        CRUD::addField([
            'label'     => "Campaign",
            'type'      => 'select_from_array',
            'name'      => 'campaign_id',
            'options'   => Campaign::query()->where('user_id', backpack_user()->id)->get()->pluck('name','id')->toArray()
        ]);
        CRUD::addField([
            'label'     => "Template",
            'type'      => 'select_from_array',
            'name'      => 'template_id', // the db column for the foreign key
            'options'   => Template::query()->where('user_id', backpack_user()->id)->get()->pluck('name','id')->toArray()
        ]);
        CRUD::addField([
            'name'  => 'user_id',
            'type'  => 'hidden',
            'value' => backpack_user()->id,
        ]);
        CRUD::addField([
            'label'     => "Process after (days)",
            'name'  => 'processed_at',
            'type'  => 'text',
        ]);


        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }


    public function store(Request $request)
    {
        $previousCampaignItem = CampaignItem::query()->where('campaign_id', $request->campaign_id )->latest()->first();
        $initialTime = $previousCampaignItem !== null ? $previousCampaignItem->processed_at :
            Campaign::query()->find($request->campaign_id)->started_at;
        $this->crud->getRequest()->request->add(
            ['processed_at' =>
                Carbon::parse(
                    $initialTime
                )->addDays(request()->get('processed_at'))
            ]
        );

        $response = $this->traitStore();

        return $response;
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
