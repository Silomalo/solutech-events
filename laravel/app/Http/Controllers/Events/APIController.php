<?php

namespace App\Http\Controllers\Events;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class APIController extends Controller
{
    public function login(Request $request)
    {
        // Handle login logic here having email and password

        $credentials = $request->only('email', 'password');
        // Validate the credentials
        $user = User::where('email', $credentials['email'])->first();
        if ($user && password_verify($credentials['password'], $user->password)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

    }
    public function register(Request $request)
    {
        // Handle registration logic here
        $data = $request->only('name', 'email', 'password');
        //check if email already exists
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            return response()->json(['message' => 'Email already exists'], 409);
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $user = User::create($data);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['token' => $token], 201);

    }
    public function getOrganizations(Request $request, $domain = null)
    {

        $organizations = [];
        if($domain){
            $organizations = Tenant::select('id', 'tenant_name', 'database_name','tenant_domain')->where('tenant_domain', $domain)->get();
        }else{
            $organizations = Tenant::select('id', 'tenant_name', 'database_name','tenant_domain')->get();
        }

        foreach ($organizations as $organization) {
            Tenant::switchingDBConnection($organization->database_name);
            // Fetch events from the tenant's database
            $events = DB::table('events')->select('id', 'title', 'venue','date','price')->get();
            $organization->events = $events;

            //if user is logged in take auth user id and check attendees table to see is the user is subscribed to the event
            if (auth()->check()) {
                $userId = auth()->user()->id;
                foreach ($events as $event) {
                    $event->is_subscribed = DB::table('attendees')->where('event_id', $event->id)->where('user_id', $userId)->exists();
                }
            }
        }

        return response()->json($organizations);
    }
}
