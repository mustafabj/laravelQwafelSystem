<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverParcel extends Model
{
    use HasFactory;

    protected $table = 'driverparcels';
    protected $primaryKey = 'parcelId';
    public $timestamps = false;

    protected $fillable = [
        'parcelNumber',
        'driverName',
        'parcelDate',
        'cost',
        'paid',
        'costRest',
        'driverNumber',
        'currency',
        'userId',
        'sendTo',
        'officeId',
        'token',
    ];

    protected $casts = [
        'parcelId' => 'integer',
        'parcelNumber' => 'integer',
        'userId' => 'integer',
        'officeId' => 'integer',
        'cost' => 'float',
        'paid' => 'float',
        'costRest' => 'float',
    ];

    /**
     * Relationships
     */

    // Items under this driver parcel
    public function details()
    {
        return $this->hasMany(DriverParcelDetail::class, 'parcelId', 'parcelId');
    }

    // Office that owns this parcel
    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId', 'officeId');
    }

    // User who created or assigned this parcel
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId', 'driverId');
    }

}
