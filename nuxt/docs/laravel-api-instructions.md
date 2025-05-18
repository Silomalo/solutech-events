## Laravel API Instructions for Authentication

### Overview
This document outlines how to connect your Nuxt.js frontend with a Laravel backend using Laravel Sanctum for authentication.

### Laravel Configuration Requirements

1. **Install Laravel Sanctum**
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

2. **Configure CORS in Laravel**
   Update `config/cors.php`:
   ```php
   'paths' => ['api/*', 'sanctum/csrf-cookie'],
   'allowed_origins' => ['http://localhost:3000', 'https://your-production-url.com'],
   'allowed_methods' => ['*'],
   'allowed_headers' => ['*'],
   'exposed_headers' => [],
   'max_age' => 0,
   'supports_credentials' => true,
   ```

3. **Update User Model**
   ```php
   // app/Models/User.php
   use Laravel\Sanctum\HasApiTokens;

   class User extends Authenticatable
   {
       use HasApiTokens, HasFactory, Notifiable;
       // ...
   }
   ```

### Required API Endpoints

1. **Login**
   - **Endpoint**: `/api/login`
   - **Method**: `POST`
   - **Body**:
     ```json
     {
       "email": "user@example.com",
       "password": "password"
     }
     ```
   - **Response**:
     ```json
     {
       "token": "1|XXXXXXXXXX...",
       "user": {
         "id": 1,
         "name": "John Doe",
         "email": "user@example.com",
         "created_at": "2023-01-01T00:00:00.000000Z",
         "updated_at": "2023-01-01T00:00:00.000000Z"
       }
     }
     ```

2. **Register**
   - **Endpoint**: `/api/register`
   - **Method**: `POST`
   - **Body**:
     ```json
     {
       "name": "John Doe",
       "email": "user@example.com",
       "password": "password",
       "password_confirmation": "password"
     }
     ```
   - **Response**: Same as login

3. **Logout**
   - **Endpoint**: `/api/logout`
   - **Method**: `POST`
   - **Headers**: `Authorization: Bearer <token>`
   - **Response**:
     ```json
     {
       "message": "Logged out successfully"
     }
     ```

4. **Get User Details**
   - **Endpoint**: `/api/user`
   - **Method**: `GET`
   - **Headers**: `Authorization: Bearer <token>`
   - **Response**:
     ```json
     {
       "id": 1,
       "name": "John Doe",
       "email": "user@example.com",
       "created_at": "2023-01-01T00:00:00.000000Z",
       "updated_at": "2023-01-01T00:00:00.000000Z"
     }
     ```

5. **Update Profile**
   - **Endpoint**: `/api/user/profile-information`
   - **Method**: `PUT`
   - **Headers**: `Authorization: Bearer <token>`
   - **Body**:
     ```json
     {
       "name": "John Doe Updated",
       "email": "updated@example.com"
     }
     ```
   - **Response**:
     ```json
     {
       "message": "Profile updated successfully"
     }
     ```

6. **Update Password**
   - **Endpoint**: `/api/user/password`
   - **Method**: `PUT`
   - **Headers**: `Authorization: Bearer <token>`
   - **Body**:
     ```json
     {
       "current_password": "current-password",
       "password": "new-password",
       "password_confirmation": "new-password"
     }
     ```
   - **Response**:
     ```json
     {
       "message": "Password updated successfully"
     }
     ```

7. **Forgot Password**
   - **Endpoint**: `/api/forgot-password`
   - **Method**: `POST`
   - **Body**:
     ```json
     {
       "email": "user@example.com"
     }
     ```
   - **Response**:
     ```json
     {
       "message": "Password reset link sent"
     }
     ```

8. **Reset Password**
   - **Endpoint**: `/api/reset-password`
   - **Method**: `POST`
   - **Body**:
     ```json
     {
       "token": "reset-token-from-email",
       "email": "user@example.com",
       "password": "new-password",
       "password_confirmation": "new-password"
     }
     ```
   - **Response**:
     ```json
     {
       "message": "Password reset successfully"
     }
     ```

### Laravel Controller Example

Here's a basic example of an AuthController for Laravel:

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous tokens
        $user->tokens()->delete();
        
        return response()->json([
            'token' => $user->createToken('auth-token')->plainTextToken,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        return $request->user();
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        $request->user()->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent']);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->update([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully']);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
```

### Laravel Routes Example

```php
<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/profile-information', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
});
```
