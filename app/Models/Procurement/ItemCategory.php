namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 't_item_categories';
    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
