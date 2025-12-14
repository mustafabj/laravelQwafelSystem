<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'driver';

    protected $primaryKey = 'driverId';

    public $timestamps = false;

    protected $fillable = [
        'driverName',
        'driverPhone',
    ];

    protected $casts = [
        'driverId' => 'integer',
    ];

    // Parcels assigned to this driver
    public function parcels()
    {
        return $this->hasMany(DriverParcel::class, 'driverId', 'driverId');
    }

    /**
     * Get all drivers.
     */
    public static function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return self::all();
    }

    /**
     * Get all drivers with relations and filters.
     */
    public static function getAllWithRelations(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = self::query();

        if (isset($filters['search']) && $filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('driverName', 'like', '%'.$filters['search'].'%')
                    ->orWhere('driverPhone', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest('driverId')->paginate(15);
    }

    /**
     * Find driver by ID.
     */
    public static function findById(int $id): ?self
    {
        return self::find($id);
    }

    /**
     * Create a new driver.
     */
    public static function createDriver(array $data): self
    {
        return self::create([
            'driverName' => $data['driverName'],
            'driverPhone' => $data['driverPhone'],
        ]);
    }

    /**
     * Update driver.
     */
    public function updateDriver(array $data): bool
    {
        return $this->update([
            'driverName' => $data['driverName'],
            'driverPhone' => $data['driverPhone'],
        ]);
    }

    /**
     * Delete driver.
     */
    public function deleteDriver(): bool
    {
        return $this->delete();
    }
}
