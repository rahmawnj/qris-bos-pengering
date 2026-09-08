<div class="tab-overflow">
    <ul class="nav nav-tabs nav-tabs-inverse">
        <li class="nav-item prev-button">
            <a href="javascript:;" data-click="prev-tab" class="nav-link text-primary">
                <i class="fa fa-arrow-left"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.members.verified') ? 'active' : '' }}" href="{{ route('partner.members.verified', request()->query()) }}">
                Terverifikasi <span class="badge bg-success ms-1">{{ $verifiedMembersCount ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.members.unverified') ? 'active' : '' }}" href="{{ route('partner.members.unverified', request()->query()) }}">
                Belum Diverifikasi <span class="badge bg-warning ms-1">{{ $unverifiedMembersCount ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item next-button">
            <a href="javascript:;" data-click="next-tab" class="nav-link text-primary">
                <i class="fa fa-arrow-right"></i>
            </a>
        </li>
    </ul>
</div>