<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Komentar extends Model
{
    protected $table = 'komentar';

    protected $fillable = ['user_id', 'isi'];

    protected function casts(): array
    {
        return ['user_id' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Logbook atau Progress tempat komentar ini ditulis. */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
