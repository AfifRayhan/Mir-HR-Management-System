<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class MenuService
{
    /**
     * Get the sidebar menu items filtered by user access and enriched with state.
     *
     * @return Collection
     */
    public function getSidebarMenu(): Collection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $currentRoute = Request::route() ? Request::route()->getName() : '';

        $items = MenuItem::whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return $items->map(function ($item) use ($user, $currentRoute) {
            // Check access for parent
            $hasParentAccess = $user && $user->hasMenuAccess($item->slug);

            // Filter children by access
            $filteredChildren = $item->children->filter(function ($child) use ($user) {
                return $user && $user->hasMenuAccess($child->slug);
            });

            // Find if any child has an exact route match
            $exactChildMatch = $filteredChildren->first(function ($child) use ($currentRoute) {
                return $child->route_name && $currentRoute === $child->route_name;
            });

            $filteredChildren = $filteredChildren->map(function ($child) use ($currentRoute, $exactChildMatch) {
                if ($exactChildMatch) {
                    // If there's an exact match in the group, only that one is active
                    $child->is_active = ($child->id === $exactChildMatch->id);
                } else {
                    // Fallback to prefix matching for resource sub-routes (show, edit, etc.)
                    $baseRoute = str_replace('.index', '', $child->route_name);
                    $child->is_active = $child->route_name && ($currentRoute === $baseRoute || str_starts_with($currentRoute, $baseRoute . '.'));
                }
                return $child;
            });

            $hasChildAccess = $filteredChildren->isNotEmpty();

            // If no access at all, skip this item
            if (!$hasParentAccess && !$hasChildAccess) {
                return null;
            }

            // Determine if parent is open (if it has children)
            $isOpen = false;
            if ($filteredChildren->isNotEmpty()) {
                if ($item->route_name && str_starts_with($currentRoute, explode('.', $item->route_name)[0] ?? '')) {
                    $isOpen = true;
                } else {
                    $isOpen = $filteredChildren->contains('is_active', true);
                }
            }

            // Enrich item with calculated properties
            $item->is_open = $isOpen;
            $item->is_active = $item->route_name && $currentRoute === $item->route_name;
            $item->filtered_children = $filteredChildren;

            return $item;
        })->filter();
    }
}
