<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\UUID;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // use HasFactory, Notifiable, UUID, HasApiTokens;
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'user_system_category',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public static function storePublicFile(string $folder, $file, string $oldFile = null): string
    {
        $disk = 'public';
        if ($oldFile) {
            self::deletePublicFile($oldFile, $disk);
        }
        $originalName = $file->getClientOriginalName();
        // $extension = $file->getClientOriginalExtension();
        $fileName = Str::random(5) . "-" . $originalName;
        try {
            $path = $file->storeAs($folder, $fileName, $disk);
            Storage::disk($disk)->url($path);
        } catch (\Exception $e) {
            throw new \Exception("Error storing file: " . $e->getMessage());
        }
        // return $path;
        return 'storage/' . $path;
    }

    public static function deletePublicFile(string $path, string $disk = 'public'): void
    {// trim storage from path
        $path = str_replace('storage/', '', $path);
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }


}