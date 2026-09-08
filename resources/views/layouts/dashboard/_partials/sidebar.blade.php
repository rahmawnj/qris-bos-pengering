<div id="sidebar" class="app-sidebar" style="background-color: rgb(227, 243, 255)">
    <div class="app-sidebar-content" data-scrollbar="true" data-height="100%">
        <div class="menu">
            <div class="menu-profile">
                <a href="javascript:;" class="menu-profile-link" data-toggle="app-sidebar-profile"
                    data-target="#appSidebarProfileMenu">
                    <div class="menu-profile-cover with-shadow"></div>
                    <div class="menu-profile-image">
                        <img src="{{ $authImage}}" alt="" />
                    </div>
                    <div class="menu-profile-info">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                {{-- Nama diambil dari data user yang sedang login --}}
                                {{ optional($authUser)->name ?? 'Guest User' }}
                            </div>
                            <div class="menu-caret ms-auto"></div>
                        </div>
                        <small>
                            {{-- Email diambil dari data user yang sedang login --}}
                            {{ optional($authUser)->email ?? 'guest@example.com' }}
                        </small>
                    </div>
                </a>
            </div>

            <div id="appSidebarProfileMenu" class="collapse">
                <div class="menu-item pt-5px">
                    <a href="{{ route('profile.form') }}" class="menu-link">
                        <div class="menu-icon"><i class="fa fa-cog"></i></div>
                        <div class="menu-text">Account Management</div>
                    </a>
                </div>
                <div class="menu-divider m-0"></div>
            </div>

            {{-- LOGIKA MENU DINAMIS BERDASARKAN ROLE --}}
            @include($menuInclude)
            {{-- End of Dynamic Menu --}}

            <div class="menu-item d-flex">
                <a href="javascript:;" class="app-sidebar-minify-btn ms-auto" data-toggle="app-sidebar-minify"><i
                        class="fa fa-angle-double-left"></i></a>
            </div>
        </div>
    </div>
</div>
