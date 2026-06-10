@php $iconClass = 'flex h-10 w-10 items-center justify-center rounded-full border border-ink-200 text-ink-600 transition hover:border-ink-400 hover:text-ink-900'; @endphp

@if ($social?->instagram)
    <a href="{{ $social->instagram }}" target="_blank" rel="nofollow noopener" aria-label="Instagram" class="{{ $iconClass }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>
    </a>
@endif
@if ($social?->facebook)
    <a href="{{ $social->facebook }}" target="_blank" rel="nofollow noopener" aria-label="Facebook" class="{{ $iconClass }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M13 22v-8h2.5l.5-3H13V9c0-.9.3-1.5 1.6-1.5H16V5c-.3 0-1.3-.1-2.4-.1C11.3 4.9 10 6.3 10 8.7V11H7.5v3H10v8h3Z"/></svg>
    </a>
@endif
@if ($social?->tiktok)
    <a href="{{ $social->tiktok }}" target="_blank" rel="nofollow noopener" aria-label="TikTok" class="{{ $iconClass }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 3c.3 2 1.6 3.7 3.7 4v2.5c-1.3 0-2.6-.4-3.7-1.1V15a5.5 5.5 0 1 1-5.5-5.5c.3 0 .6 0 .9.1v2.6a2.9 2.9 0 1 0 2 2.8V3H16Z"/></svg>
    </a>
@endif
@if ($social?->youtube)
    <a href="{{ $social->youtube }}" target="_blank" rel="nofollow noopener" aria-label="YouTube" class="{{ $iconClass }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-2-.2-3.1-.4-3.7a2.5 2.5 0 0 0-1.8-1.8C18.6 6.2 12 6.2 12 6.2s-6.6 0-7.8.3A2.5 2.5 0 0 0 2.4 8.3C2.2 8.9 2 10 2 12s.2 3.1.4 3.7a2.5 2.5 0 0 0 1.8 1.8c1.2.3 7.8.3 7.8.3s6.6 0 7.8-.3a2.5 2.5 0 0 0 1.8-1.8c.2-.6.4-1.7.4-3.7Zm-12 3V9l5 3-5 3Z"/></svg>
    </a>
@endif
@if ($social?->linkedin)
    <a href="{{ $social->linkedin }}" target="_blank" rel="nofollow noopener" aria-label="LinkedIn" class="{{ $iconClass }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6.9 8.5H4V20h2.9V8.5ZM5.4 4a1.7 1.7 0 1 0 0 3.4 1.7 1.7 0 0 0 0-3.4ZM20 20v-6.3c0-3.4-1.8-4.9-4.2-4.9-1.9 0-2.8 1-3.2 1.8V8.5H9.7V20h2.9v-6.1c0-1.6.8-2.4 1.9-2.4s1.6.8 1.6 2.4V20H20Z"/></svg>
    </a>
@endif
@if (! empty($website))
    <a href="{{ $website }}" target="_blank" rel="nofollow noopener" aria-label="Web" class="{{ $iconClass }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/></svg>
    </a>
@endif
