<div>
    @includeIf(data_get($setUp, 'header.includeViewOnTop'))

    <div class="mb-3 flex justify-end items-center">
        <div class="">
            @includeWhen(boolval(data_get($setUp, 'header.wireLoading')),
                data_get($theme, 'root') . '.header.loading')
        </div>
        <div class="flex flex-row gap-1 w-2/3 justify-end items-center">
            <div x-data="pgRenderActions">
                <span class="pg-actions flex flex-row gap-1" x-html="toHtml"></span>
            </div>
            <div class="flex flex-row items-center text-sm">
                @if (data_get($setUp, 'exportable'))
                    <div
                        class="mr-2 mt-2 sm:mt-0"
                        id="pg-header-export"
                    >
                        @include(data_get($theme, 'root') . '.header.export')
                    </div>
                @endif
                @includeIf(data_get($theme, 'root') . '.header.toggle-columns')
                @includeIf(data_get($theme, 'root') . '.header.soft-deletes')
                @if (config('livewire-powergrid.filter') == 'outside' && count($this->filters()) > 0)
                    @includeIf(data_get($theme, 'root') . '.header.filters')
                @endif
            </div>
            @include(data_get($theme, 'root') . '.header.search')
        </div>
    </div>

    @includeIf(data_get($theme, 'root') . '.header.enabled-filters')

    @includeWhen(data_get($setUp, 'exportable.batchExport.queues', 0), data_get($theme, 'root') . '.header.batch-exporting')
    @includeWhen($multiSort, data_get($theme, 'root') . '.header.multi-sort')
    @includeIf(data_get($setUp, 'header.includeViewOnBottom'))
    @includeIf(data_get($theme, 'root') . '.header.message-soft-deletes')
</div>
