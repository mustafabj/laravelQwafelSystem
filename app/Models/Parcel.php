<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcel extends Model
{
    use HasFactory;

    protected $table = 'parcels';
    protected $primaryKey = 'parcelId';
    public $timestamps = false;

    protected $fillable = [
        'parcelNumber',
        'customerId',
        'parcelDate',
        'recipientName',
        'recipientNumber',
        'sendTo',
        'cost',
        'paid',
        'costRest',
        'custNumber',
        'currency',
        'userId',
        'officeReId',
        'officeId',
        'accept',
        'editToId',
        'token',
        'paidMethod',
        'paidInMainOffice',
    ];

    protected $casts = [
        'parcelId' => 'integer',
        'customerId' => 'integer',
        'userId' => 'integer',
        'officeId' => 'integer',
        'officeReId' => 'integer',
        'cost' => 'float',
        'paid' => 'float',
        'costRest' => 'float',
        'paidInMainOffice' => 'boolean',
    ];

    // relationships
    public function details()
    {
        return $this->hasMany(ParcelDetail::class, 'parcelId', 'parcelId');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerId', 'customerId');
    }

    public function originOffice()
    {
        return $this->belongsTo(Office::class, 'officeId', 'officeId');
    }

    public function destinationOffice()
    {
        return $this->belongsTo(Office::class, 'officeReId', 'officeId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }
}
