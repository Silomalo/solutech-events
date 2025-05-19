<?php

namespace App\Http\Controllers\Events;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenants\ActivityLog;
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
        // $data = $request->only('name', 'email', 'password', 'phone');
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => password_hash($request->input('password'), PASSWORD_BCRYPT),
            'phone' => $request->input('phone'),
            'user_system_category' => 3,
        ];
        //check if email already exists
        $existingUser = User::where('email', $request->input('email'))->first();
        if ($existingUser) {
            return response()->json(['message' => 'Email already exists, try another one!'], 409);
        }

        // $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        // $data['user_system_category'] = 3;
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

    public function subscriptionEventIds()
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized Joe'], 401);
        }

        $userId = auth()->user()->id;
        $subscribedEventIds = DB::table('attendees')
            ->where('user_id', $userId)
            ->pluck('event_id')
            ->toArray();

        return response()->json([$subscribedEventIds]);
    }


    public function toggleEventSubscription($event_id)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized Joe'], 401);
        }

        try {
            $userId = auth()->user()->id;
            $event = DB::table('events')->where('id', $event_id)->first();

            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            $isSubscribed = DB::table('attendees')->where('event_id', $event_id)->where('user_id', $userId)->exists();

            if ($isSubscribed) {
                DB::table('attendees')->where('event_id', $event_id)->where('user_id', $userId)->delete();
                $message = "user " . auth()->user()->name . " unsubscribed from event " . $event->title;
                $action = 'unsubscribing_event';
                ActivityLog::createLog(auth()->id(), $event_id ? $event_id : 0, $action, $message);
                return response()->json(['message' => 'Unsubscribed from event'], 200);
            } else {
                DB::table('attendees')->insert(['event_id' => $event_id, 'user_id' => $userId]);
                $message = "user " . auth()->user()->name . " subscribed to event " . $event->title;
                $action = 'subscribing_event';
                ActivityLog::createLog(auth()->id(), $event_id ? $event_id : 0, $action, $message);
                return response()->json(['message' => 'Subscribed to event'], 200);
            }
        } catch (\Exception $e) {
            Log::error("Error toggling event subscription: " . $e->getMessage());
            return response()->json(['message' => 'Error processing your request. Please try again later.'], 500);
        }
    }
}
