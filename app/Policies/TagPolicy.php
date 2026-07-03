<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tag;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        // أي يوزر يقدر يفتح صفحة التاجس
        return true;
    }

    public function view(User $user, Tag $tag): bool
    {
        // الأدمن يشوف أي تاج، واليوزر العادي يشوف بس تاجه
        return $user->role === 'admin' || $user->id === $tag->user_id;
    }

    public function create(User $user): bool
    {
        // أي يوزر مسجل يقدر يضيف تاج (هيبقى تاجه هو تلقائيًا)
        return true;
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->role === 'admin' || $user->id === $tag->user_id;
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->role === 'admin' || $user->id === $tag->user_id;
    }

    public function restore(User $user, Tag $tag): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Tag $tag): bool
    {
        return $user->role === 'admin';
    }
}
