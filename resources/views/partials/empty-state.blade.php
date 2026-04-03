<div class="sm-empty-state" role="status">
    <div class="sm-empty-state__icon" aria-hidden="true">
        <i class="{{ $emptyIconBootstrapClasses }}"></i>
    </div>
    <h2 class="sm-empty-state__title h5">{{ $emptyTitle }}</h2>
    <p class="sm-empty-state__text mb-0">{{ $emptyText }}</p>
    @if(!empty($emptyCtaHref) && !empty($emptyCtaLabel))
        <a class="btn btn-primary mt-3" href="{{ $emptyCtaHref }}">{{ $emptyCtaLabel }}</a>
    @endif
</div>
