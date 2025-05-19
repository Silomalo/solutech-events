<?php

namespace App\Livewire;

use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Jobs\Central\CreateDatabase;
use Illuminate\Support\Facades\Storage;

class TenantManage extends Component
{
    use WithFileUploads;
    public $serial_number;
    public $tenant_name;
    public $company_logo;
    public $company_logo_temp;
    public $tenant_domain;
    public $phone;
    public $email;
    // public $database_name;
    public $description;
    public $database_prefix = 'tenant_';
    public $fullDomain;
    public $sub_domain;

    // addition
    public $registration_no;
    public $kra_pin;
    public $address;
    public $postal_code;
    public $city;
    public $website;

    //contact person
    public $contact_name;
    public $contact_title;
    public $contact_email;
    public $contact_phone;
    public $legal_entity;

    public function mount($serial_number = null)
    {
        $this->serial_number = $serial_number;
        if ($this->serial_number) {
            $tenant = Tenant::where('serial_number', $this->serial_number)->first();
            $this->tenant_name = $tenant->tenant_name;
            $this->company_logo = $tenant->company_logo;
            $this->company_logo_temp = $tenant->company_logo;
            $this->tenant_domain = $tenant->tenant_domain;
            $this->phone = $tenant->phone;
            $this->email = $tenant->email;
            $this->description = $tenant->description;
            $this->sub_domain = $tenant->tenant_domain;
            $this->fullDomain = Tenant::getFullDomainProperty($this->sub_domain);
            $this->registration_no = $tenant->registration_no;
            $this->kra_pin = $tenant->kra_pin;
            $this->address = $tenant->address;
            $this->postal_code = $tenant->postal_code;
            $this->city = $tenant->city;
            $this->website = $tenant->website;
            $this->contact_name = $tenant->contact_name;
            $this->contact_title = $tenant->contact_title;
            $this->contact_email = $tenant->contact_email;
            $this->contact_phone = $tenant->contact_phone;
            $this->legal_entity = $tenant->legal_entity;

        }
    }



    public function updated($field)
    {
        if ($field == 'tenant_domain') {
            $subDomain = strtolower(str_replace(' ', '', $this->tenant_domain));
            $this->sub_domain = $subDomain;
            $this->fullDomain = Tenant::getFullDomainProperty($subDomain);
        }
    }
    public function clearField($field)
    {
        $this->$field = null;
    }
    // public function post()
    // {
    //     // use a full syntax
    //     $this->dialog()->confirm([
    //         'title' => 'Are you Sure?',
    //         'description' => 'This is very sensitive operation, are you sure you want to continue ?',
    //         'icon' => 'error',
    //         'accept' => [
    //             'label' => 'Yes, continue',
    //             'method' => 'writeToDB',
    //             'params' => '',
    //         ],
    //         'reject' => [
    //             'label' => 'No, cancel',
    //             'method' => '',
    //         ],
    //     ]);
    // }
    public function post()
    {
        $this->validate([
            'tenant_name' => 'required',
            'tenant_domain' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            // 'description' => 'required',
            'postal_code' => 'required',
            'website' => 'required',
            'city' => 'required',
            'address' => 'required',
            'contact_name' => 'required',
            'contact_title' => 'required',
            'contact_email' => 'required|email',
            'contact_phone' => 'required',
        ]);



        $data = [
            'tenant_name' => $this->tenant_name,
            'tenant_domain' => $this->sub_domain,
            'phone' => $this->phone,
            'email' => $this->email,
            'database_host' => env('DB_HOST'),
            'database_port' => env('DB_PORT'),
            'database_username' => env('DB_USERNAME'),
            'description' => $this->description,
            'registration_no' => $this->registration_no,
            'kra_pin' => $this->kra_pin,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'website' => $this->website,
            'contact_name' => $this->contact_name,
            'contact_title' => $this->contact_title,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'legal_entity' => $this->legal_entity,
        ];

        $this->uploadImage();
        if ($this->company_logo) {
            // dd($this->company_logo);
            $data = array_merge($data, ['company_logo' => $this->company_logo]);
        }


        //check if tenant exists
        if ($this->serial_number) {
            $tenant = Tenant::where('serial_number', $this->serial_number)->first();
            $same_url = Tenant::where('tenant_domain', $this->sub_domain)->where('serial_number', '!=', $this->serial_number)->first();
            if ($same_url) {
                $this->dialog()->error(
                    $title = 'Error !!!',
                    $description = 'Tenant already exists, please update the tenant domain to a unique one',
                );
                return;
            }
            $tenant->update($data);
            return redirect()->route('central.tenants')->with('success', 'Tenant updated successfully');
        } else {
            $tenant = Tenant::where('tenant_domain', $this->sub_domain)->first();
            // append to $data
            $data = array_merge($data, ['database_name' => $this->database_prefix . $this->sub_domain]);
            if ($tenant) {
                $this->dialog()->error(
                    $title = 'Error !!!',
                    $description = 'Tenant already exists, please update the tenant domain to a unique one',
                );
                return;
            }
            try {
                Tenant::create($data);
                // Tenant::createDatabase($this->database_prefix . $this->sub_domain);
                CreateDatabase::dispatch($this->database_prefix . $this->sub_domain);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error creating tenant: ' . $e->getMessage());
            }

            return redirect()->route('central.tenants')->with('success', 'Company created successfully');
        }
    }

    private function uploadImage()
    {
        $storeUrl = 'public/central/company_logos';

        if (!$this->company_logo) {
            $this->company_logo = null;
            return;
        }
        if ($this->serial_number && $this->company_logo) {
            $existingLogoPath = str_replace('/storage/', 'public/', $this->company_logo);
            if (Storage::exists($existingLogoPath)) {
                return; // Image hasn't changed
            }
            $existingCompany = Tenant::where('serial_number', $this->serial_number)->first();
            if ($existingCompany && $existingCompany->company_logo) {
                Storage::delete($storeUrl . '/' . basename($existingCompany->company_logo));
            }
        }

        $path = $this->company_logo->store($storeUrl);
        $this->company_logo = Storage::url($path);
    }



    public function render()
    {
        return view('livewire.tenant-manage');
    }
}