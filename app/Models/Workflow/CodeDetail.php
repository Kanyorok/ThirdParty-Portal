<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Auth;

class CodeDetail extends Model
{
    use HasFactory, SoftDeletes;


    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 't_CodeDetails';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ID';

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

     public static function getPrimaryKey(): string
    {
        return 'CodeDetailsId';
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'CodeID',
        'Value',
        'Description',
        'DisplayOrder',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'CodeID' => 'integer',
        'DisplayOrder' => 'integer',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
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
            $model->CreatedBy = optional(Auth::user())->id ?? 1;
            if (empty($model->DisplayOrder)) {
                $model->DisplayOrder = 1;
            }
            if (is_null($model->IsActive)) {
                $model->IsActive = true;
            }
        });

        static::updating(function ($model) {
            $model->ModifiedOn = now();
            $model->ModifiedBy = optional(Auth::user())->id ?? 1;
        });
    }

    /**
     * Scope a query to only include active records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }

    /**
     * Scope a query to order by display order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('DisplayOrder');
    }

    /**
     * Scope a query by specific code ID.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $codeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCodeId($query, $codeId)
    {
        return $query->where('CodeID', $codeId);
    }

    /**
     * Scope a query by specific code value.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByValue($query, $value)
    {
        return $query->where('Value', $value);
    }

    /**
     * Get the creator of the code detail.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    /**
     * Get the modifier of the code detail.
     */
    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }

    /**
     * Get the user who deleted the code detail.
     */
    public function deletor()
    {
        return $this->belongsTo(User::class, 'DeletedBy');
    }

    /**
     * Check if the code detail is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->IsActive;
    }

    /**
     * Activate the code detail.
     *
     * @return bool
     */
    public function activate(): bool
    {
        return $this->update(['IsActive' => true]);
    }

    /**
     * Deactivate the code detail.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        return $this->update(['IsActive' => false]);
    }

    /**
     * Get the display order.
     *
     * @return int
     */
    public function getDisplayOrder(): int
    {
        return $this->DisplayOrder;
    }

    /**
     * Set the display order.
     *
     * @param int $order
     * @return bool
     */
    public function setDisplayOrder(int $order): bool
    {
        return $this->update(['DisplayOrder' => $order]);
    }

    /**
     * Get code details by code ID.
     *
     * @param int $codeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByCodeId($codeId)
    {
        return static::byCodeId($codeId)->active()->ordered()->get();
    }

    /**
     * Get code detail by code ID and value.
     *
     * @param int $codeId
     * @param string $value
     * @return \App\Models\CodeDetail|null
     */
    public static function getByCodeIdAndValue($codeId, $value)
    {
        return static::where('CodeID', $codeId)
                    ->where('Value', $value)
                    ->active()
                    ->first();
    }

    /**
     * Get code detail ID by code ID and value.
     *
     * @param int $codeId
     * @param string $value
     * @return int|null
     */
    public static function getIdByCodeIdAndValue($codeId, $value)
    {
        return static::where('CodeID', $codeId)
                    ->where('Value', $value)
                    ->active()
                    ->value('ID');
    }

    /**
     * Get options for dropdown select by code ID.
     *
     * @param int $codeId
     * @return array
     */
    public static function getOptions($codeId)
    {
        return static::byCodeId($codeId)
                    ->active()
                    ->ordered()
                    ->pluck('Description', 'ID')
                    ->toArray();
    }

    /**
     * Get options for dropdown select by code ID with value as key.
     *
     * @param int $codeId
     * @return array
     */
    public static function getOptionsByValue($codeId)
    {
        return static::byCodeId($codeId)
                    ->active()
                    ->ordered()
                    ->pluck('Description', 'Value')
                    ->toArray();
    }

    /**
     * Get description by code ID and value.
     *
     * @param int $codeId
     * @param string $value
     * @return string|null
     */
    public static function getDescription($codeId, $value)
    {
        return static::where('CodeID', $codeId)
                    ->where('Value', $value)
                    ->active()
                    ->value('Description');
    }

    /**
     * Get value by code ID and description.
     *
     * @param int $codeId
     * @param string $description
     * @return string|null
     */
    public static function getValue($codeId, $description)
    {
        return static::where('CodeID', $codeId)
                    ->where('Description', $description)
                    ->active()
                    ->value('Value');
    }
}