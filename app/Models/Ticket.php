<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';
    protected $primaryKey = 'ticketId';
    public $timestamps = false;

    protected $fillable = [
        'tecketNumber',
        'customerId',
        'cost',
        'costRest',
        'paid',
        'destination',
        'Seat',
        'travelDate',
        'travelTime',
        'ticketDate',
        'custNumber',
        'currency',
        'userId',
        'addressId',
        'accept',
        'officeId',
        'token',
    ];

    protected $casts = [
        'ticketId' => 'integer',
        'customerId' => 'integer',
        'userId' => 'integer',
        'officeId' => 'integer',
        'addressId' => 'integer',
    ];

    /**
     * Relationships
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerId', 'customerId');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'addressId', 'addressId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId', 'officeId');
    }
}
