<?php

namespace App\Models\Finance;

use App\Models\ThirdParty\ThirdParties;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceCreditManagement extends Model
{
    use SoftDeletes, UserActorTrait;

    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FinanceCreditManagement';
    protected $primaryKey = 'Id';
    protected $fillable = [
            'CustomerID',
            'CreditLimit',
            'PaymentTerms',
            'EffectiveFrom',
            'ExpiryDate',
            'Colleteral',
            'Remarks',
            'Status',
            'ApprovalStatus',
            'ApprovalReason',
            
            // Risk assessment fields
            'RiskScore',
            'RiskLevel',
            'ReviewCycleMonths',
            'LastReviewDate',
            'NextReviewDate',
            'RiskNotes',

            'CreatedBy',
            'CreatedOn',
            'ModifiedBy',
            'ModifiedOn',
            'DeletedBy',
            'DeletedOn'
    ];

    /**
     * Attributes that should never be mass assigned or persisted to database
     */
    protected $guarded = [
        'used',
        'utilization', 
        'available'
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceCreditManagementId';
    }

    public function customer()
    {
        return $this->belongsTo(ThirdParties::class, 'CustomerID', 'Id');
    }

    protected $casts = [
        'EffectiveFrom' => 'date',
        'ExpiryDate' => 'date',
        'LastReviewDate' => 'date',
        'NextReviewDate' => 'date',
        'CreditLimit' => 'decimal:2',
        'RiskScore' => 'integer',
        'ReviewCycleMonths' => 'integer',
    ];

    public function movements()
    {
        return $this->hasMany(FinanceCreditMovement::class, 'CreditID', 'Id');
    }

    /**
     * Get risk level badge class for display
     */
    public function getRiskBadgeClassAttribute(): string
    {
        return match(strtolower($this->RiskLevel ?? 'medium')) {
            'low' => 'bg-success',
            'high' => 'bg-danger',
            'medium' => 'bg-warning text-dark',
            default => 'bg-warning text-dark'
        };
    }

    /**
     * Check if credit review is due
     */
    public function isReviewDue(): bool
    {
        if (!$this->NextReviewDate) {
            return true; // No review date set, assume due
        }
        return now()->gte($this->NextReviewDate);
    }

    /**
     * Get days until next review
     */
    public function getDaysUntilReview(): int
    {
        if (!$this->NextReviewDate) {
            return 0;
        }
        return now()->diffInDays($this->NextReviewDate, false);
    }

    /**
     * Calculate and update risk score based on utilization and other factors
     */
    public function calculateRiskScore(float $utilizationPercentage): int
    {
        $score = 20; // Base score
        
        // Utilization risk (0-40 points)
        if ($utilizationPercentage > 90) {
            $score += 40;
        } elseif ($utilizationPercentage > 75) {
            $score += 30;
        } elseif ($utilizationPercentage > 50) {
            $score += 20;
        } elseif ($utilizationPercentage > 25) {
            $score += 10;
        }
        
        // Credit age risk (0-20 points)
        $daysSinceCreated = $this->CreatedOn ? now()->diffInDays($this->CreatedOn) : 0;
        if ($daysSinceCreated < 30) {
            $score += 20; // New customer = higher risk
        } elseif ($daysSinceCreated < 90) {
            $score += 10;
        }
        
        // Payment history risk (0-20 points) - placeholder for future implementation
        // This could check payment delays, defaults, etc.
        
        // Review frequency risk (0-20 points)
        if ($this->isReviewDue()) {
            $score += 15; // Overdue review
        }
        
        return min(100, max(0, $score));
    }

    /**
     * Update risk assessment
     */
    public function updateRiskAssessment(float $utilizationPercentage): void
    {
        $newScore = $this->calculateRiskScore($utilizationPercentage);
        
        // Determine risk level based on score
        $riskLevel = match(true) {
            $newScore >= 70 => 'High',
            $newScore >= 40 => 'Medium',
            default => 'Low'
        };
        
        // Determine review cycle based on risk level
        $reviewCycle = match($riskLevel) {
            'High' => 3,    // 3 months
            'Medium' => 6,  // 6 months
            'Low' => 12,    // 12 months
        };
        
        // Calculate next review date
        $nextReviewDate = $this->LastReviewDate 
            ? $this->LastReviewDate->addMonths($reviewCycle)
            : now()->addMonths($reviewCycle);
        
        $this->update([
            'RiskScore' => $newScore,
            'RiskLevel' => $riskLevel,
            'ReviewCycleMonths' => $reviewCycle,
            'NextReviewDate' => $nextReviewDate,
        ]);
    }
        
}
