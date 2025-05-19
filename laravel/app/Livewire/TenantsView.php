<?php

namespace App\Livewire;

use App\Models\Tenant;
use Livewire\Component;

class TenantsView extends Component
{
    public $search;
    public $pagination = 10;



    public function toggleStatus($serial_number)
    {
        $tenant = Tenant::where('serial_number', $serial_number)->first();
        $title = $tenant->status == true ? 'Deactivate Tenant' . $tenant->tenant_name : 'Activate Tenant' . $tenant->tenant_name;
        $description = $tenant->status == true ?
            'Deactivate the tenant? Remember that the tenant will not be able to access the system.'
            :
            'Activate the tenant? Tenant will be able to access the system using their domain.';


        // use a full syntax
        // $this->dialog()->confirm([
        //     'title' => $title,
        //     'description' => $description,
        //     'icon' => $tenant->status == true ? 'error' : 'success',
        //     'accept' => [
        //         'label' => 'Yes, update it',
        //         'method' => 'updateStatus',
        //         'params' => [$serial_number],
        //     ],
        //     'reject' => [
        //         'label' => 'No, cancel',
        //         'method' => '',
        //     ],
        // ]);
        $this->updateStatus($serial_number);
    }
    public function updateStatus($serial_number)
    {
        // dd($serial_number);
        $tenant = Tenant::where('serial_number', $serial_number)->first();
        if ($tenant->status == false) {
            $tenant->account_activated_at = now();
            $tenant->account_activated_by = auth()->user()->id;
        } else {
            $tenant->account_deactivated_at = now();
            $tenant->account_deactivated_by = auth()->user()->id;
        }
        $tenant->status = !$tenant->status;
        $tenant->save();
    }


    public function deleteTenant($serial_number)
    {
        $tenant = Tenant::where('serial_number', $serial_number)->first();
        // dd($tenant);
        Tenant::forceDeleteDatabase($tenant->database_name);
        $tenant->delete();
        return redirect()->route('central.tenants')->with('success', 'Tenant and its database deleted successfully');
    }

    // public function googleStoreBackup()
    // {

    //     //call a job FullSystemBackUpJob
    //     try {
    //         FullSystemBackUpJob::dispatch('all');
    //         // FullSystemBackUpJob::dispatch('central');
    //         $this->dispatch('notification-trigger', [
    //             'type' => 'success',
    //             'title' => 'Backup Triggered',
    //             'message' => 'Backup has been triggered successfully, this will take a while to complete. On completion, you will receive a notification email via: ' . env('ADMIN_EMAIL'),
    //             'link' => null
    //         ]);
    //     } catch (\Exception $e) {
    //         $this->dispatch('notification-trigger', [
    //             'type' => 'error',
    //             'title' => 'Error Occurred',
    //             'message' => $e->getMessage(),
    //             'link' => null
    //         ]);
    //     }

    // }

    public function render()
    {
        // $tenants = Tenant::all();
        // dd($tenants);
        $tenants = Tenant::where('tenant_name', 'like', '%' . $this->search . '%')
            ->orWhere('tenant_domain', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate($this->pagination);
        return view('livewire.tenants-view', compact('tenants'));
    }


    // broadcasting with reverb
    // public $title = 'Account Security';
    // public $message = 'Always remember to secure your account, do not share your login details with anyone.';
    // public $link = '/login';

    // public function sendPublicNotifications()
    // {
    //     NotificationsModel::triggerPublicNotification($this->title, $this->message, $this->link);
    // }
}