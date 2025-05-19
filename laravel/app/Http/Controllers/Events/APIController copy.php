<?php

// namespace App\Http\Controllers\Events;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Tenants\ActivityLog;

class APIController extends Controller
{
    public function login(Request $request)
    {
        // Handle login logic here having email and password

        $credentials = $request->only('email', 'password');
        // Validate the credentials
        $user = User::where('email', $credentials['email'])->first();
        if ($user && password_verify($credentials['password'], $user->password)) {
            // Revoke all existing tokens
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Log token creation
            Log::info("User {$user->id} logged in, token created successfully");

            // Get token info for debugging
            $tokenId = substr($token, 0, strpos($token, '|'));
            $personalAccessToken = DB::table('personal_access_tokens')->find($tokenId);

            if ($personalAccessToken) {
                Log::info("Token ID {$tokenId} found in database");
            } else {
                Log::warning("Token ID {$tokenId} not found in database after creation");
            }

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 200);
        } else {
            Log::warning("Failed login attempt for email: {$credentials['email']}");
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

        // Get authenticated user ID if any
        $userId = $this->getAuthenticatedUserIdFromRequest($request);

        // Debug: Log the authenticated user ID and token status
        Log::info("Bearer Token Present: " . ($request->bearerToken() ? 'Yes' : 'No'));
        Log::info("Authenticated User ID: " . ($userId ?? 'null'));

        foreach ($organizations as $organization) {
            try {
                Tenant::switchingDBConnection($organization->database_name);

                // Debug: Log current organization
                Log::info("Processing organization: {$organization->tenant_name} with DB: {$organization->database_name}");

            } catch (\Exception $e) {
                Log::error("Failed to switch to DB '{$organization->database_name}': {$e->getMessage()}");
                continue;
            }

            $events = DB::table('events')
                ->select('id', 'description', 'title', 'venue', 'date', 'price')
                ->get();

            // Debug: Log events count
            Log::info("Found " . $events->count() . " events for organization {$organization->tenant_name}");

            if ($userId) {
                $eventIds = $events->pluck('id')->toArray();

                // Debug: Log event IDs
                Log::info("Event IDs: " . implode(', ', $eventIds));

                if (!empty($eventIds)) {
                    // Get subscribed events for this user in this organization's database
                    $subscribedEventIds = DB::table('attendees')
                        ->where('user_id', $userId)
                        ->whereIn('event_id', $eventIds)
                        ->pluck('event_id')
                        ->toArray();

                    // Debug: Log subscribed event IDs
                    Log::info("User {$userId} subscribed to events: " . implode(', ', $subscribedEventIds));

                    // Alternative method: Check attendees table structure
                    $attendeesCount = DB::table('attendees')
                        ->where('user_id', $userId)
                        ->count();
                    Log::info("Total attendees records for user {$userId}: {$attendeesCount}");

                    // Debug: Show all attendees for this user (remove in production)
                    $allUserAttendees = DB::table('attendees')
                        ->where('user_id', $userId)
                        ->get(['event_id', 'user_id']);
                    Log::info("All attendees for user {$userId}: " . json_encode($allUserAttendees->toArray()));
                } else {
                    $subscribedEventIds = [];
                    Log::info("No events found, setting subscribedEventIds to empty array");
                }

                // Map events with subscription status
                $events = $events->map(function ($event) use ($subscribedEventIds, $userId) {
                    $isSubscribed = in_array($event->id, $subscribedEventIds);

                    // Debug: Log each event's subscription status
                    Log::info("Event {$event->id} subscription status for user {$userId}: " . ($isSubscribed ? 'true' : 'false'));

                    $event->is_subscribed = $isSubscribed;
                    return $event;
                });
            } else {
                // No authenticated user
                $events = $events->map(function ($event) {
                    $event->is_subscribed = false;
                    return $event;
                });
                Log::info("No authenticated user, setting all events as not subscribed");
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

    public function toggleEventSubscription(Request $request, $event_id)
    {
        $userId = $this->getAuthenticatedUserIdFromRequest($request);

        if (!$userId) {
            return response()->json(['message' => 'Unauthorized. Please login to subscribe to events.'], 401);
        }

        try {
            // Log the subscription attempt
            Log::info("User {$userId} attempting to toggle subscription for event {$event_id}");

            $event = DB::table('events')->where('id', $event_id)->first();

            if (!$event) {
                Log::warning("Event {$event_id} not found for subscription toggle");
                return response()->json(['message' => 'Event not found'], 404);
            }

            // Check if the user is already subscribed
            $isSubscribed = DB::table('attendees')
                ->where('event_id', $event_id)
                ->where('user_id', $userId)
                ->exists();

            Log::info("User {$userId} subscription status for event {$event_id}: " . ($isSubscribed ? 'Subscribed' : 'Not Subscribed'));

            // Toggle subscription status
            if ($isSubscribed) {
                // Unsubscribe
                $deleted = DB::table('attendees')
                    ->where('event_id', $event_id)
                    ->where('user_id', $userId)
                    ->delete();

                Log::info("Unsubscribe result: {$deleted} record(s) deleted");

                $message = "User {$userId} unsubscribed from event {$event->title}";
                $action = 'unsubscribing_event';
                ActivityLog::createLog($userId, $event_id, $action, $message);

                return response()->json([
                    'message' => 'Unsubscribed from event',
                    'is_subscribed' => false,
                    'event_id' => $event_id
                ], 200);
            } else {
                // Subscribe
                $inserted = DB::table('attendees')->insert([
                    'event_id' => $event_id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info("Subscribe result: " . ($inserted ? 'Success' : 'Failed'));

                $message = "User {$userId} subscribed to event {$event->title}";
                $action = 'subscribing_event';
                ActivityLog::createLog($userId, $event_id, $action, $message);

                return response()->json([
                    'message' => 'Subscribed to event',
                    'is_subscribed' => true,
                    'event_id' => $event_id
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("Error toggling event subscription: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['message' => 'Error processing your request. Please try again later.'], 500);
        }
    }

    /**
     * Check if the current authenticated user is subscribed to a specific event
     *
     * @param Request $request
     * @param int $eventId Event ID to check
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEventSubscription(Request $request, $eventId)
    {
        $userId = $this->getAuthenticatedUserIdFromRequest($request);

        if (!$userId) {
            return response()->json(['message' => 'Unauthorized', 'is_subscribed' => false], 401);
        }

        try {
            // Verify the event exists
            $event = DB::table('events')->where('id', $eventId)->first();
            if (!$event) {
                return response()->json(['message' => 'Event not found', 'is_subscribed' => false], 404);
            }

            // Get attendee record details
            $attendee = DB::table('attendees')
                ->where('user_id', $userId)
                ->where('event_id', $eventId)
                ->first();

            $isSubscribed = !is_null($attendee);

            // Log detailed information for debugging
            Log::info("Subscription check: User {$userId}, Event {$eventId}, Status: " . ($isSubscribed ? 'Subscribed' : 'Not Subscribed'));
            if ($isSubscribed) {
                Log::info("Attendee record: " . json_encode($attendee));
            }

            return response()->json([
                'message' => $isSubscribed ? 'User is subscribed to this event' : 'User is not subscribed to this event',
                'is_subscribed' => $isSubscribed,
                'user_id' => $userId,
                'event_id' => $eventId,
                'attendee_record' => $attendee
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error checking event subscription: " . $e->getMessage());
            return response()->json(['message' => 'Error processing your request', 'is_subscribed' => false], 500);
        }
    }

    /**
     * Debug endpoint to verify token and show auth status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        $response = [
            'token_present' => false,
            'token_valid' => false,
            'user_authenticated' => false,
            'user_id' => null,
            'user_info' => null,
            'debug_info' => []
        ];

        try {
            // Check if token is present
            $token = $request->bearerToken();
            $response['token_present'] = !empty($token);

            if ($response['token_present']) {
                $response['debug_info'][] = "Token found in request: " . substr($token, 0, 10) . "...";

                // Check if token exists in database
                $personalAccessToken = DB::table('personal_access_tokens')
                    ->where('token', hash('sha256', $token))
                    ->first();

                if ($personalAccessToken) {
                    $response['debug_info'][] = "Token found in database with ID: {$personalAccessToken->id}";
                    $response['token_valid'] = true;

                    // Check token expiration
                    if (!empty($personalAccessToken->expires_at) && $personalAccessToken->expires_at < now()) {
                        $response['debug_info'][] = "Token has expired at: {$personalAccessToken->expires_at}";
                        $response['token_valid'] = false;
                    }

                    // Get user
                    $user = User::find($personalAccessToken->tokenable_id);
                    if ($user) {
                        $response['user_authenticated'] = true;
                        $response['user_id'] = $user->id;
                        $response['user_info'] = [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email
                        ];
                        $response['debug_info'][] = "User found with ID: {$user->id}";
                    } else {
                        $response['debug_info'][] = "No user found with ID: {$personalAccessToken->tokenable_id}";
                    }
                } else {
                    $response['debug_info'][] = "Token not found in database";

                    // Check for any tokens for debugging
                    $tokensCount = DB::table('personal_access_tokens')->count();
                    $response['debug_info'][] = "Total tokens in database: {$tokensCount}";

                    if ($tokensCount > 0) {
                        $latestToken = DB::table('personal_access_tokens')
                            ->latest('created_at')
                            ->first();
                        $response['debug_info'][] = "Latest token created at: {$latestToken->created_at}";
                    }
                }

                // Check if Sanctum can authenticate
                $sanctumUser = auth('sanctum')->user();
                if ($sanctumUser) {
                    $response['debug_info'][] = "Sanctum authentication successful";
                } else {
                    $response['debug_info'][] = "Sanctum authentication failed";
                }
            }

            // Add info about current auth configuration
            $response['debug_info'][] = "Default guard: " . config('auth.defaults.guard');
            $response['debug_info'][] = "API guard driver: " . (config('auth.guards.api.driver', 'not configured'));

            return response()->json($response);
        } catch (\Exception $e) {
            $response['debug_info'][] = "Error: " . $e->getMessage();
            return response()->json($response, 500);
        }
    }

    /**
     * Helper method to get authenticated user ID from token in request header
     *
     * @param \Illuminate\Http\Request $request
     * @return int|null User ID if authenticated, null otherwise
     */
    protected function getAuthenticatedUserIdFromRequest(Request $request)
    {
        try {
            if ($request->bearerToken()) {
                $token = $request->bearerToken();
                Log::info("Processing bearer token: " . substr($token, 0, 10) . "...");

                // Try authenticating with Sanctum
                $user = auth('sanctum')->user();

                if ($user) {
                    Log::info("Authentication successful for user ID: {$user->id}");
                    return $user->id;
                } else {
                    // If token is present but no user, try to debug
                    Log::warning("Bearer token present but no user found");

                    // Try finding the token in personal_access_tokens table
                    $tokenExists = DB::table('personal_access_tokens')
                        ->where('token', hash('sha256', $token))
                        ->orWhere('token', $token)  // Try both formats
                        ->exists();

                    if ($tokenExists) {
                        Log::warning("Token exists in database but could not authenticate user");
                    } else {
                        Log::warning("Token not found in personal_access_tokens table");
                    }

                    return null;
                }
            } else {
                Log::info("No bearer token present in request");
            }
        } catch (\Exception $e) {
            Log::error("Error authenticating user: " . $e->getMessage());
            Log::error($e->getTraceAsString());
        }

        return null;
    }
}