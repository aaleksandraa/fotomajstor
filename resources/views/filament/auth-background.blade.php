@php($pfAuthImages = \App\Support\AuthBackground::images())

@if ($pfAuthImages->isNotEmpty())
    <style>
        .pf-auth-bg,
        .pf-auth-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
        }

        .pf-auth-bg {
            z-index: -10;
            overflow: hidden;
            background: #0a0a0a;
        }

        .pf-auth-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            grid-auto-rows: 120px;
            gap: 3px;
            width: 100%;
            height: 100%;
        }

        @media (min-width: 768px) {
            .pf-auth-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                grid-auto-rows: 150px;
            }
        }

        .pf-auth-grid img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(1) contrast(1.05) brightness(0.95);
            opacity: 0.65;
        }

        .pf-auth-overlay {
            z-index: -9;
            background: radial-gradient(ellipse at center, rgba(9, 9, 11, 0.55) 0%, rgba(9, 9, 11, 0.86) 78%);
        }

        @media (prefers-color-scheme: light) {
            .pf-auth-bg {
                background: #f4f4f5;
            }

            .pf-auth-grid img {
                opacity: 0.55;
            }

            .pf-auth-overlay {
                background: radial-gradient(ellipse at center, rgba(244, 244, 245, 0.74) 0%, rgba(244, 244, 245, 0.92) 78%);
            }
        }
    </style>

    <div class="pf-auth-bg" aria-hidden="true">
        <div class="pf-auth-grid">
            @foreach ($pfAuthImages as $src)
                <img src="{{ $src }}" alt="" loading="lazy" decoding="async" />
            @endforeach
        </div>
    </div>
    <div class="pf-auth-overlay" aria-hidden="true"></div>
@endif
