<?php


declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Ticket extends Model
{
    use HasFactory, HasUlids;

    public $incrementing = false;   // ULID is string, not auto-increment
    protected $keyType = 'string';

    protected $fillable = [
        'subject', 'body', 'status', 'category', 'note', 'explanation', 'confidence',
        'manual_category_at', 'classified_at',
    ];

    protected $casts = ['confidence' => 'float'];

    public const STATUSES = ['new', 'open', 'pending', 'closed'];
    public const CATEGORIES = ['Billing', 'Technical', 'Account', 'Other'];
}
