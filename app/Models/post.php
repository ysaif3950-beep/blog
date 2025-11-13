<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ هنا التعديل - غيّرنا الاسم بس
    public function getImageUrlAttribute()  // 👈 شيلنا "Default" من الاسم
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('storage/uploads/default.png');
    }
    public function tags(){
        return $this->belongsToMany(Tag::class);
    }
}
