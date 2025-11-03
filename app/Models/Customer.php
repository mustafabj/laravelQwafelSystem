<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * Table configuration
     */
    protected $table = 'customer';
    protected $primaryKey = 'customerId';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'FName',
        'LName',
        'customerPassport',
        'customerState',
        'phone1',
        'phone2',
        'phone3',
        'phone4',
        'addedDate',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'customerId' => 'integer',
        'addedDate' => 'datetime',
    ];

    /**
     * Relationships
     */

    // Each customer may have many parcels
    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'customerId', 'customerId');
    }

    // Optionally: a customer could belong to a state or region table in the future
    // public function state()
    // {
    //     return $this->belongsTo(State::class, 'customerState', 'stateName');
    // }
}
