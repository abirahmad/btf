<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->hasRole('admin') || $user->id === $order->user_id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('vendor') && $this->isVendorOrder($user, $order)) ||
               ($user->hasRole('customer') && $user->id === $order->user_id && $order->canBeCancelled());
    }

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return null;
    }

    private function isVendorOrder(User $user, Order $order): bool
    {
        return $order->items()->whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();
    }
}