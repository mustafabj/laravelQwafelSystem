<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverParcelDetail extends Model
{
    use HasFactory;

    protected $table = 'driverparceldetails';
    protected $primaryKey = 'detailId';
    public $timestamps = false;

    protected $fillable = [
        'detailQun',
        'detailInfo',
        'parcelId',
    ];

    protected $casts = [
        'detailId' => 'integer',
        'detailQun' => 'integer',
        'parcelId' => 'integer',
    ];

    public function driverParcel()
    {
        return $this->belongsTo(DriverParcel::class, 'parcelId', 'parcelId');
    }
}
