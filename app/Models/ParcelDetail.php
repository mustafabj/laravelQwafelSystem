<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParcelDetail extends Model
{
    use HasFactory;

    protected $table = 'parcelsdetails';
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

    public function parcel()
    {
        return $this->belongsTo(Parcel::class, 'parcelId', 'parcelId');
    }
}
