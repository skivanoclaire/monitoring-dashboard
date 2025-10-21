<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $table = 'mail_logs';

    protected $fillable = [
        'sent_at',
        'sender_email',
        'recipient_email',
    ];

    protected $dates = [
        'sent_at',
    ];
}
