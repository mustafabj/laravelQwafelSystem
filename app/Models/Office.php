<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    /**
     * Table configuration
     */
    protected $table = 'office';
    protected $primaryKey = 'officeId';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'officeName',
        'officeImage',
        'userId',
        'officeAddress',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'officeId' => 'integer',
        'userId' => 'integer',
    ];

    /**
     * Relationships
     */

    // The user who created or manages this office
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    // All users that belong to this office
    public function users()
    {
        return $this->hasMany(User::class, 'officeId', 'officeId');
    }

    // All parcels related to this office
    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'officeId', 'officeId');
    }

    // All drivers related to this office
    public function drivers()
    {
        return $this->hasMany(Driver::class, 'officeId', 'officeId');
    }

    /**
     * Get all offices.
     */
    public static function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return self::all();
    }
}
