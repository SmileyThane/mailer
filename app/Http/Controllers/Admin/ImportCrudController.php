<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ImportRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Models\Import;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * Class ImportCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ImportCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation {
        store as traitStore;
    }

//    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
//    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Import::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/import');
        CRUD::setEntityNameStrings('import', 'imports');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // columns

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
        CRUD::setValidation(ImportRequest::class);
        CRUD::addField(['name' => 'file', 'type' => 'browse']);

//        CRUD::setFromDb(); // fields

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    public function store(Request $request)
    {
        $collection = (new FastExcel)->import($request->file);

        foreach ($collection as $item) {
            $contactGroup = ContactGroup::query()->firstOrCreate(
                ['name' => $item['Group']],
                ['user_id' => backpack_user()->id]
            );

            $company = Company::query()->firstOrCreate(
                ['name' => $item['Organisation Name']],
                ['user_id' => backpack_user()->id]
            );
            $contact = Contact::query()->firstOrCreate(
                ['email' => $item['Primary Email']],
                [
                    'name' => $item['First Name'],
                    'lastname' => $item['Last Name'],
                    'group_id' => $contactGroup->id,
                    'company_id' => $company->id,
                    'internal_ref' => $item['Internal Reference'],
                    'job_title' => $item['Job Title'],
                    'user_id' => backpack_user()->id,
                ]
            );
        }

//        $response = $this->traitStore();

        return redirect()->back();
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
//        $this->setupCreateOperation();
    }
}
