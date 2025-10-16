<?php

namespace App\Models\Core\Approval;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;

class WorkflowPending extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 't_WorkFlowPending';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'Id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Source',
        'SourceID',
        'Stage',
        'UserId',
        'CreatedBy',
        'CreatedOn',
        'EscalatedOn',
        'ModifiedBy',
        'ModifiedOn'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'SourceID' => 'integer',
        'Stage' => 'integer',
        'UserId' => 'integer',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'CreatedOn' => 'datetime',
        'EscalatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'CreatedOn',
        'EscalatedOn',
        'ModifiedOn',
        'DeletedOn',
    ];

    /**
     * Boot function for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->CreatedOn)) {
                $model->CreatedOn = now();
            }
            if (empty($model->CreatedBy)) {
                $model->CreatedBy = Auth::id() ?? 1;
            }
        });

        static::updating(function ($model) {
            $model->ModifiedOn = now();
            $model->ModifiedBy = Auth::id() ?? 1;
        });
    }

    /**
     * Scope a query to only include pending items for a specific source.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $source
     * @param  int  $sourceId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSource($query, $source, $sourceId = null)
    {
        $query = $query->where('Source', $source);
        
        if ($sourceId) {
            $query = $query->where('SourceID', $sourceId);
        }
        
        return $query;
    }

    /**
     * Scope a query to only include pending items for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('UserId', $userId);
    }

    /**
     * Scope a query to only include pending items for a specific stage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $stage
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStage($query, $stage)
    {
        return $query->where('Stage', $stage);
    }

    /**
     * Scope a query to include only escalated items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEscalated($query)
    {
        return $query->whereNotNull('EscalatedOn');
    }

    /**
     * Scope a query to include only non-escalated items.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotEscalated($query)
    {
        return $query->whereNull('EscalatedOn');
    }

    /**
     * Get the workflow stage associated with this pending item.
     */
    public function workflowStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'Stage', 'Id');
    }

    /**
     * Get the user who needs to approve this pending item.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }

    /**
     * Get the creator of this pending record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    /**
     * Get the modifier of this pending record.
     */
    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    /**
     * Get the source model (polymorphic relationship).
     * This assumes your source models have a consistent way to be retrieved.
     */
    public function source()
    {
        // This is a polymorphic relationship based on Source and SourceID
        // You'll need to map your source types to models
        return $this->morphTo('source', 'Source', 'SourceID');
    }

    /**
     * Check if this pending item is escalated.
     *
     * @return bool
     */
    public function isEscalated(): bool
    {
        return !is_null($this->EscalatedOn);
    }

    /**
     * Mark this pending item as escalated.
     *
     * @return bool
     */
    public function markAsEscalated(): bool
    {
        return $this->update([
            'EscalatedOn' => now(),
            'ModifiedBy' => Auth::id() ?? 1,
            'ModifiedOn' => now()
        ]);
    }

    /**
     * Get pending approvals for a specific document.
     *
     * @param string $source
     * @param int $sourceId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPendingForDocument($source, $sourceId)
    {
        return static::forSource($source, $sourceId)
                    ->with(['user', 'workflowStage'])
                    ->get();
    }

    /**
     * Get pending approvals for a specific user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getPendingForUser($userId)
    {
        return static::forUser($userId)
                    ->with(['workflowStage', 'source'])
                    ->get();
    }

    /**
     * Check if a user has pending approval for a specific document.
     *
     * @param int $userId
     * @param string $source
     * @param int $sourceId
     * @return bool
     */
    public static function hasPendingApproval($userId, $source, $sourceId): bool
    {
        return static::forUser($userId)
                    ->forSource($source, $sourceId)
                    ->exists();
    }

    /**
     * Get the next pending approval for a document.
     *
     * @param string $source
     * @param int $sourceId
     * @return \App\Models\Workflow\WorkFlowPending|null
     */
    public static function getNextPending($source, $sourceId)
    {
        return static::forSource($source, $sourceId)
                    ->with(['user', 'workflowStage'])
                    ->orderBy('Stage')
                    ->first();
    }

    /**
     * Complete this pending approval (soft delete).
     *
     * @param int $deletedBy
     * @return bool
     */
    public function complete($deletedBy = null): bool
    {
        return $this->update([
            'DeletedBy' => $deletedBy ?? Auth::id() ?? 1,
            'DeletedOn' => now()
        ]);
    }

    /**
     * Get the elapsed time since this item was created.
     *
     * @return string
     */
    public function getElapsedTime(): string
    {
        return $this->CreatedOn->diffForHumans();
    }

    /**
     * Get the escalation time if escalated.
     *
     * @return string|null
     */
    public function getEscalationTime(): ?string
    {
        return $this->EscalatedOn?->diffForHumans();
    }
     public function stage()
    {
        return $this->belongsTo(WorkflowStage::class, 'Stage');
    }

    public function escalations()
{
    return $this->hasMany(WorkflowEscalation::class, 'PendingID', 'Id');
}
}