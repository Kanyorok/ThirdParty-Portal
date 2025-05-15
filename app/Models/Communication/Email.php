<?php

namespace App\Models\Communication;

use App\Enums\EmailPriorityEnum;
use App\Enums\EmailStatusEnum;
use App\Enums\EmailTypeEnum;
use App\Models\DMS\Image;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use ImageTrait, UserActorTrait, SoftDeletes;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Emails';
    protected $primaryKey = 'EmailID';

    protected $fillable = [
        'MailID', 'Type', 'Status', 'Priority', 'From', 'To', 'CC', 'BCC', 'Subject', 'Body', 'Text', 'Party', 'PartyID',
        'Extra', 'Source', 'SourceID', 'EmailConversationId', 'ReferenceId', 'ReadBy', 'ReadOn', 'Dated',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];


    protected $casts = [
        'Extra' => 'object',
        'To' => 'array',
        'CC' => 'array',
        'BCC' => 'array',
        'Dated' => 'datetime',
        'ReadOn' => 'datetime',
        'ReadBy' => 'integer',
        'EmailConversationId' => 'integer',
        'Priority' => EmailPriorityEnum::class,
        'Status' => EmailStatusEnum::class,
        'Type' => EmailTypeEnum::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'EmailID';
    }

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 't_EmailImage', 'EmailId', 'ImageId', 'EmailID', 'ImageID')
            ->withTimestamps('CreatedOn', 'ModifiedOn');
    }

    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID");
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(EmailConversation::class, 'EmailConversationId', 'Id');
    }

    protected function getImageName(): string
    {
        return $this->Subject;
    }
}
