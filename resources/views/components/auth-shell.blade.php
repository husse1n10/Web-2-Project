@props([
    'title',
    'subtitle' => null,
    'badgeIcon' => null,
    'navHref' => null,
    'navLabel' => null,
    'asideEyebrow' => null,
    'asideTitle' => null,
    'asideCopy' => null,
    'asidePoints' => [],
    'wide' => false,
    'cardClass' => '',
])

<div>
    <nav class="es-auth-nav">
        <a href="{{ route('home') }}" class="es-auth-brand">
            <span class="es-auth-brand-mark">
                <img src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}" alt="CedarGov icon">
            </span>
            <span>
                <span class="es-auth-brand-name d-block">CedarGov</span>
                <span class="es-auth-brand-sub">Lebanon Gov Portal</span>
            </span>
        </a>

        @if($navHref && $navLabel)
            <a href="{{ $navHref }}" class="es-auth-nav-link">{{ $navLabel }}</a>
        @endif
    </nav>

    <div class="es-auth-center">
        <div @class(['es-auth-stage', 'es-auth-stage-wide' => $wide])>
            <aside class="es-auth-aside">
                <div class="es-auth-aside-inner">
                    @if($asideEyebrow)
                        <span class="es-auth-aside-eyebrow">{{ $asideEyebrow }}</span>
                    @endif

                    @if($asideTitle)
                        <h2 class="es-auth-aside-title">{{ $asideTitle }}</h2>
                    @endif

                    @if($asideCopy)
                        <p class="es-auth-aside-copy">{{ $asideCopy }}</p>
                    @endif

                    @if(count($asidePoints))
                        <ul class="es-auth-aside-list">
                            @foreach($asidePoints as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(isset($asideFooter))
                        <div class="es-auth-aside-footer">
                            {{ $asideFooter }}
                        </div>
                    @endif
                </div>
            </aside>

            <section @class(['es-auth-card', 'es-auth-card-wide' => $wide, $cardClass])>
                @if($badgeIcon)
                    <div class="es-auth-card-badge">
                        <i class="bi {{ $badgeIcon }}"></i>
                    </div>
                @endif

                <h1 class="auth-heading">{{ $title }}</h1>

                @if($subtitle)
                    <p class="auth-sub">{{ $subtitle }}</p>
                @endif

                {{ $slot }}

                @if(isset($footer))
                    <div class="es-auth-card-note">
                        {{ $footer }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
