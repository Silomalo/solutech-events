<?php

namespace App\Http\Controllers\Events;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $tenantQuery = Tenant::select('id', 'tenant_name', 'database_name', 'tenant_domain');

        if ($domain) {
            $tenantQuery->where('tenant_domain', $domain);
        }

        $organizations = $tenantQuery->get();
        $userId = auth()->check() ? auth()->user()->id : null;

        foreach ($organizations as $organization) {
            try {
                Tenant::switchingDBConnection($organization->database_name);
            } catch (\Exception $e) {
                Log::error("Failed to switch to DB '{$organization->database_name}': {$e->getMessage()}");
                continue;
            }

            $events = DB::table('events')
                ->select('id', 'description', 'title', 'venue', 'date', 'price')
                ->get();

            if ($userId) {
                $eventIds = $events->pluck('id');
                $subscribedEventIds = DB::table('attendees')
                    ->where('user_id', $userId)
                    ->whereIn('event_id', $eventIds)
                    ->pluck('event_id')
                    ->toArray();

                $events = $events->map(function ($event) use ($subscribedEventIds) {
                    $event->is_subscribed = in_array($event->id, $subscribedEventIds);
                    return $event;
                });
            } else {
                $events = $events->map(function ($event) {
                    $event->is_subscribed = false;
                    return $event;
                });
            }

            $organization->events = $events;
        }

        return response()->json($organizations);
    }

    // public function getOrganizations(Request $request, $domain = null)
    // {

    //     $organizations = [];
    //     if($domain){
    //         $organizations = Tenant::select('id', 'tenant_name', 'database_name','tenant_domain')->where('tenant_domain', $domain)->get();
    //     }else{
    //         $organizations = Tenant::select('id', 'tenant_name', 'database_name','tenant_domain')->get();
    //     }

    //     foreach ($organizations as $organization) {
    //         Tenant::switchingDBConnection($organization->database_name);
    //         // Fetch events from the tenant's database
    //         $events = DB::table('events')->select('id', 'description', 'title', 'venue', 'date', 'price')->get();

    //         //if user is logged in take auth user id and check attendees table to see is the user is subscribed to the event
    //         if (auth()->check()) {
    //             $userId = auth()->user()->id;
    //             foreach ($events as $event) {
    //                 $event->is_subscribed = DB::table('attendees')->where('event_id', $event->id)->where('user_id', $userId)->exists();
    //             }
    //         }
    //         $organization->events = $events;
    //     }

    //     return response()->json($organizations);
    // }

    public function toggleEventSubscription($event_id)
    {
        if (auth()->check()) {
            $userId = auth()->user()->id;
            $event = DB::table('events')->where('id', $event_id)->first();
            if ($event) {
                $isSubscribed = DB::table('attendees')->where('event_id', $event_id)->where('user_id', $userId)->exists();
                if ($isSubscribed) {
                    DB::table('attendees')->where('event_id', $event_id)->where('user_id', $userId)->delete();
                    return response()->json(['message' => 'Unsubscribed from event'], 200);
                } else {
                    DB::table('attendees')->insert(['event_id' => $event_id, 'user_id' => $userId]);
                    return response()->json(['message' => 'Subscribed to event'], 200);
                }
            } else {
                return response()->json(['message' => 'Event not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Unauthorized Joe'], 401);
        }
    }
}
