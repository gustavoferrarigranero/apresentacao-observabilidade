<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderDelivery extends Model
{
    use HasFactory;

    protected $fillable = ['appointment_id', 'provider', 'request_id', 'status', 'sent_at'];

    protected function casts(): array
    {
        return ['sent_at' => 'immutable_datetime'];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
