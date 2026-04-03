<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sondaggio extends Model
{
    protected $table = 'sondaggi';

    const CREATED_AT = 'data_creazione';

    const UPDATED_AT = null;

    protected $fillable = [
        'titolo',
        'descrizione',
        'autore_id',
        'is_pubblico',
        'data_scadenza',
    ];

    protected function casts(): array
    {
        return [
            'is_pubblico' => 'boolean',
            'data_creazione' => 'datetime',
            'data_scadenza' => 'datetime',
        ];
    }

    public function autore(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autore_id');
    }

    public function domande(): HasMany
    {
        return $this->hasMany(Domanda::class, 'sondaggio_id')->orderBy('ordine');
    }

    public function risposte(): HasMany
    {
        return $this->hasMany(Risposta::class, 'sondaggio_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'sondaggio_tag', 'sondaggio_id', 'tag_id');
    }

    public function scopePubblici($query)
    {
        return $query->where('is_pubblico', true);
    }
}
