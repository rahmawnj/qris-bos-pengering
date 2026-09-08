<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title> Topup | {{ $title ?? '' }}</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
<meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/facebook/app.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
    @stack('styles')
    <style>
        @media (max-width: 767.98px) {
            .pagination-container,
            .panel-body > .d-flex.justify-content-between.align-items-center:has(.pagination) {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                gap: .75rem;
                width: 100%;
                text-align: center;
            }

            .pagination-container > div,
            .pagination-container > nav,
            .panel-body > .d-flex.justify-content-between.align-items-center:has(.pagination) > div {
                width: 100%;
            }

            .pagination,
            nav[role="navigation"] .pagination {
                flex-wrap: wrap;
                justify-content: center;
                gap: .25rem;
                margin-bottom: 0;
                max-width: 100%;
            }

            .pagination .page-link {
                min-width: 2.375rem;
                text-align: center;
            }
        }
    </style>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/logo.png') }}">

    </head>

<body>


@php
    // --- Helper Function Sementara (Harusnya di Helper Global) ---
    /**
     * Determines the blade menu path to include based on the role.
     */
    if (!function_exists('getSidebarIncludeForRole')) {
        function getSidebarIncludeForRole($role) {
            switch ($role) {
                case 'super_admin':
                case 'admin':
                    return 'layouts.dashboard._partials._menu_user';
                case 'owner':
                case 'cashier':
                    return 'layouts.dashboard._partials._menu_brand';
                default:
                    return 'layouts.dashboard._partials._menu_default';
            }
        }
    }
    // -----------------------------------------------------------------

    // 1. Get user data from the active guard and determine the Role
    if (session('impersonating') && Auth::guard('web')->check()) {
        $authUser = Auth::guard('web')->user();
    } else {
        $authUser = Auth::guard('admin_config')->user() ?? Auth::user();
    }

    $authImage = asset('assets/img/default-user.png');
    $authName = 'Guest';
    $authEmail = '';
    $authRole = 'guest';
    $userRole = 'guest';

    // Ensure user exists before accessing properties
    if ($authUser) {
        // Safely retrieve user data, as it can be from GenericUser (array) or Eloquent Model (object)
        // We use the data_get() helper function to safely access properties/array keys.
        $userImage = data_get($authUser, 'image');
        $userName = data_get($authUser, 'name') ?? 'User Name';
        $userEmail = data_get($authUser, 'email') ?? 'no.email@provided.com';
        $userRoleFromDb = data_get($authUser, 'role') ?? 'guest'; // Role from the users table

        $authName = $userName;
        $authEmail = $userEmail;
        $authRole = $userRoleFromDb;

        // Determine image path
        if ($userImage) {
            // Assume 'image' field stores a path that should be accessed via Storage
            $authImage = Storage::url(path: $userImage);
        }

        // Determine the highest role (admin_config is always prioritized)
        // If logged in via admin_config, the role is automatically 'super_admin'
        if (!session('impersonating') && Auth::guard('admin_config')->check()) {
            $userRole = 'super_admin';
        } elseif (Auth::guard('web')->check() && $authUser) {
             $userRole = $userRoleFromDb;
        } else {
            $userRole = 'guest';
        }

    } else {
        // Variables have been initialized as 'Guest' at the beginning, no need to repeat
    }


    // 2. Initialize Default Brand
    $appName = env('APP_NAME', 'Emoney Platform');
    $logoSrc = asset('assets/img/logo.png');
    $brandName = $appName; // Default brand name (for Super Admin)
    $brandId = null; // Initialize Tenant/Merchant ID

    // 3. Brand Name Determination Logic
    // Check if userRole is defined and is not Super Admin or Admin
    if ($userRole && !in_array($userRole, ['super_admin', 'admin'])) {
        $entityName = null;
        $entityId = null;

        // This logic only works if $authUser is an Eloquent Model from the 'web' guard, not GenericUser.
        if (!($authUser instanceof \Illuminate\Auth\GenericUser) && $authUser) {
             switch ($userRole) {
                 case 'owner':
                     // User is Tenant Admin, take Tenant name directly
                     if (method_exists($authUser, 'owner') && $authUser->owner) {
                         $entityName = $authUser->owner->name;
                         $entityId = $authUser->owner->id;
                     }
                     break;
                 case 'merchant':
                 case 'cashier':
                     // User is Merchant Admin/Cashier: Use Merchant Name (AS REQUESTED)
                     if (method_exists($authUser, 'merchantAdmin') && $authUser->merchantAdmin) {
                         $merchant = $authUser->merchantAdmin;
                         // Use Merchant Name as Brand Name
                         $entityName = $merchant->name;
                         $entityId = $merchant->id;
                     }
                     break;
                 case 'member':
                     // User is Member, get Tenant through Member Account relation
                     if (method_exists($authUser, 'memberAccount') && $authUser->memberAccount) {
                         $memberData = $authUser->memberAccount;
                         if ($memberData && method_exists($memberData, 'tenant') && $memberData->tenant) {
                             $entityName = $memberData->tenant->name;
                             $entityId = $memberData->tenant->id;
                         }
                     }
                     break;
                 default:
                     break;
             }
        }

        // Apply the Entity name (Tenant or Merchant) if found
        if ($entityName) {
            $brandName = $entityName;
            $brandId = $entityId;
        }
    }

    // Override brand name and logo for owner/cashier using actual owner data
    if ($authUser && !($authUser instanceof \Illuminate\Auth\GenericUser)) {
        if ($userRole === 'owner' && $authUser->owner) {
            $brandName = $authUser->owner->brand_name ?? $brandName;
            if (!empty($authUser->owner->brand_logo)) {
                $logoSrc = asset('storage/' . $authUser->owner->brand_logo);
            }
        }
        if ($userRole === 'cashier' && $authUser->cashier && $authUser->cashier->outlet && $authUser->cashier->outlet->owner) {
            $brandName = $authUser->cashier->outlet->owner->brand_name ?? $brandName;
            if (!empty($authUser->cashier->outlet->owner->brand_logo)) {
                $logoSrc = asset('storage/' . $authUser->cashier->outlet->owner->brand_logo);
            }
        }
    }

    // 4. Determine Sidebar Menu (Using the Helper/Function above)
    $menuInclude = getSidebarIncludeForRole($userRole);

    // 5. Collect variables to be shared with partial views
    $sharedData = [
        'brandName' => $brandName,
        'tenantId' => $brandId, // Renamed to brandId but often holds the ID of the entity (Tenant/Merchant)
        'logoSrc' => $logoSrc,
        'userRole' => $userRole,
        'authUser' => $authUser,
        'menuInclude' => $menuInclude,
        'authImage' => $authImage,
        'authName' => $authName,
        'authEmail' => $authEmail,
        'authRole' => $authRole,
    ];
@endphp




    <div id="app" class="app app-header-fixed app-sidebar-fixed">

        @include('layouts.dashboard._partials.headbar', $sharedData)
        @include('layouts.dashboard._partials.sidebar', $sharedData)
        <div class="app-sidebar-bg"></div>
        <div class="app-sidebar-mobile-backdrop"><a href="#" data-dismiss="app-sidebar-mobile"
                class="stretched-link"></a></div>
        <div id="content" class="app-content">
            @yield('content')

            @include('layouts.dashboard._partials.footbar')

        </div>
        </div>
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme/facebook.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>

    @stack('scripts')

    </body>

</html>
