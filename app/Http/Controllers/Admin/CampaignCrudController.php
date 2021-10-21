<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CampaignRequest;
use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CampaignCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CampaignCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
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
        CRUD::setModel(\App\Models\Campaign::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/campaign');
        CRUD::setEntityNameStrings('campaign', 'campaigns');
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
        CRUD::column('name');
        CRUD::addColumn(['name' => 'user', 'type' => 'relationship', 'label' => 'Author', 'attribute' => 'full_name']);
        CRUD::column('status_name');
        CRUD::column('created_at');
        CRUD::column('started_at');
        CRUD::column('finished_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    protected function setupShowOperation()
    {
        CRUD::column('id');
        CRUD::column('name');
        CRUD::column('status');
        CRUD::column('created_at');
        CRUD::column('started_at');
        CRUD::column('finished_at');
        CRUD::addColumn(
            [
                'name'  => 'campaign_items',
                'label' => 'Campaign Sequence', // Table column heading
                'type'  => 'model_function',
                'function_name' => 'linksToCampaignItems', // the method in your Model
            ],
        );
        CRUD::addColumn(
            [
                'name'  => 'contacts',
                'label' => 'Contacts', // Table column heading
                'type'  => 'model_function',
                'function_name' => 'linksToContacts', // the method in your Model
                 'limit' => -1,
            ],
        );
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CampaignRequest::class);

        CRUD::field('name');
        CRUD::field('started_at');
        CRUD::addField([
            'name' => 'user_id',
            'type' => 'hidden',
            'value' => backpack_user()->id,
        ]);

        CRUD::addField([
            'name' => 'status',
            'label' => "Status",
            'type' => 'select2_from_array',
            'options' => Campaign::STATUSES
        ]);

        CRUD::addField([
            'name' => 'contacts',
            'label' => "Contacts (comma separated)",
            'type' => 'text'
        ]);

        CRUD::addField([
            'name' => 'contact_groups',
            'label' => "Contact groups (multiple)",
            'type' => 'select2_multiple',
            'model' => ContactGroup::class,
            'attribute' => 'name',

        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    public function store(Request $request)
    {

        $contactGroups = ContactGroup::query()->whereIn('id', $request->contact_groups)->get();
        $contactGroupsItemIds = [];
        if ($contactGroups) {
            $contactGroupsIds = $contactGroups->pluck('id')->toArray();
            $contactGroupsItems = Contact::query()->whereIn('group_id', $contactGroupsIds)->get();
            if ($contactGroupsItems) {
                $contactGroupsItemIds = $contactGroupsItems->pluck('id')->toArray();
            }
        }

        $contactIds = array_unique(
            array_merge(
                $this->storeContactProcess($request->contacts),
                $contactGroupsItemIds
            )
        );


        $response = $this->traitStore();
        $id = $this->crud->entry->id;

        foreach ($contactIds as $contactId) {
            CampaignContact::query()->create([
                'user_id' => backpack_user()->id,
                'campaign_id' => $id,
                'contact_id' => $contactId
            ]);
        }

        return $response;
    }

    private function storeContactProcess($contacts)
    {
        $contactIds = [];
        if (strlen($contacts) > 0) {
            foreach (explode(',', $contacts) as $contact) {
                $contact = Contact::query()->firstOrCreate(
                    ['email' => trim($contact)],
                    ['name' => trim(explode('@', $contact)[0]), 'user_id' => backpack_user()->id]
                );
                $contactIds[] = $contact->id;
            }
        }

        return $contactIds;
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
