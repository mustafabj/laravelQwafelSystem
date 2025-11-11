<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    public static function getLastParcels()
    {
        $user = Auth::user();
        $query = self::with(['customer', 'user', 'originOffice', 'destinationOffice'])->selectRaw("
        parcels.*,
        CASE
            WHEN parcels.officeId = ? THEN 'صادر'
            ELSE 'وارد'
        END AS status_label", [$user->officeId])->orderByDesc('parcelDate')->limit(10000);

        if ($user->role !== 'admin') {
            $query->where(function ($sub) use ($user) {
                $sub->where('officeReId', $user->officeId)
                    ->orWhere('officeId', $user->officeId)
                    ->orWhere('userId', $user->id);

                if ($user->officeId == 3) {
                    $sub->orWhere('officeReId', 6);
                }
            });
        }

        return $query->get();
    }
    public function scopeWithFullRelations($query)
    {
        return $query->with([
            'customer:customerId,FName,LName,customerPassport',
            'originOffice:officeId,officeName,officeImage',
            'destinationOffice:officeId,officeName',
            'details',
        ]);
    }
}
