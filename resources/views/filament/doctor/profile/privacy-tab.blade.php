@php
    $page = \App\Models\Page::query()->where('slug', 'privacy-policy-doctor')->first();
    $body = $page
        ? (app()->getLocale() === 'ar'
            ? (string) ($page->content_ar ?: $page->content)
            : (string) ($page->content ?: $page->content_ar))
        : '<p>'.e(__('This policy has not been published yet.')).'</p>';
@endphp

<div class="prose prose-sm dark:prose-invert max-w-none rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
    {!! $body !!}
</div>
