<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ContactRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactGroup;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ContactCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ContactCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Contact::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/contact');
        CRUD::setEntityNameStrings('contact', 'contacts');
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
        CRUD::column('email');
        CRUD::addColumn(['name' => 'contactGroup', 'type' => 'relationship', 'label' => 'Group', 'attribute' => 'name']);
        CRUD::addColumn(['name' => 'companyItem', 'type' => 'relationship', 'label' => 'Company', 'attribute' => 'name']);
        CRUD::column('name');
        CRUD::column('lastname');
        CRUD::column('updated_at');
        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    protected function setupShowOperation()
    {
        CRUD::column('id');
        CRUD::column('email');
        CRUD::addColumn(['name' => 'contactGroup', 'type' => 'relationship', 'label' => 'Group', 'attribute' => 'name']);
        CRUD::addColumn(['name' => 'companyItem', 'type' => 'relationship', 'label' => 'Company', 'attribute' => 'name']);
        CRUD::addColumn(['name' => 'contacts', 'type' => 'relationship', 'label' => 'Statues for items', 'attribute' => 'campaign_item_plus_status']);
        CRUD::column('name');
        CRUD::column('lastname');
        CRUD::column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ContactRequest::class);

        CRUD::field('email');
        CRUD::addField([
            'name' => 'group_id',
            'label' => "Group",
            'type' => 'select2_from_array',
            'options' => ContactGroup::query()->where('user_id', backpack_user()->id)->get()->pluck('name', 'id')->toArray()

        ]);
        CRUD::field('name');
        CRUD::field('lastname');
        CRUD::addField([
            'name' => 'company_id',
            'label' => "Company",
            'type' => 'select2_from_array',
            'options' => Company::query()->get()->pluck('name', 'id')->toArray()

        ]);
        CRUD::addField([
            'name' => 'user_id',
            'type' => 'hidden',
            'value' => backpack_user()->id,
        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
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
