<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // فقط الـ admin يمكنه رؤية قائمة جميع المستخدمين
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // المستخدم يرى بياناته، أو الـ admin يرى بيانات أي مستخدم
        return $user->id === $model->id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // الـ admin يمكنه إضافة مستخدمين من الداشبورد
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // المستخدم يعدل بياناته، أو الـ admin يعدل أي مستخدم
        return $user->id === $model->id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // فقط الـ admin يمكنه حذف المستخدمين
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // فقط الـ admin يمكنه استرجاع مستخدمين محذوفين
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // فقط الـ admin يمكنه الحذف النهائي
        return $user->role === 'admin';
    }
}
