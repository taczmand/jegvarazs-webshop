@props([
    'id',
    'title' => '',
    'formId' => null,
    'saveButtonId' => null,
    'paneLeft' => null,
    'paneMid' => null,
    'paneRight' => null,
])

@php
    $rightSlotHasContent = isset($right) && trim((string) $right) !== '';

    $effectivePaneLeft = $paneLeft ?? '25%';
    $effectivePaneMid = $paneMid ?? ($rightSlotHasContent ? '50%' : '75%');
    $effectivePaneRight = $paneRight ?? ($rightSlotHasContent ? '25%' : '0%');

    $hasRight = $rightSlotHasContent && trim((string) $effectivePaneRight) !== '' && trim((string) $effectivePaneRight) !== '0' && trim((string) $effectivePaneRight) !== '0%';
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen m-0 p-0 d-flex flex-column" style="height: 100vh;">
        <form id="{{ $formId ?? $id . '_form' }}" class="w-100 d-flex flex-column" style="height: 100%;">
            <div class="modal-content d-flex flex-column" style="height: 100%;">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                </div>
                <div class="modal-body p-0 flex-grow-1" style="min-height: 0;">
                    <div class="w-100 h-100 d-flex flex-column flex-lg-row" id="{{ $id }}_split" style="min-height: 0; min-width: 0; overflow-x: hidden; --pane-left: {{ $effectivePaneLeft }}; --pane-mid: {{ $effectivePaneMid }}; --pane-right: {{ $effectivePaneRight }};">
                        <div class="p-4 border-end h-100" data-pane="left" style="overflow-y:auto; overflow-x:hidden; min-height: 0; min-width: 0; flex: 0 0 calc(var(--pane-left) - 8px);">
                            {{ $left }}
                        </div>

                        <div class="d-none d-lg-block" data-resizer="left" style="width: 8px; cursor: col-resize; background: #f8f9fa; border-right: 1px solid #dee2e6;"></div>

                        <div class="p-4 {{ $hasRight ? 'border-end' : '' }} h-100" data-pane="middle" style="overflow-y:auto; overflow-x:hidden; min-height: 0; min-width: 0; flex: 0 0 calc(var(--pane-mid) - {{ $hasRight ? '16px' : '8px' }});">
                            {{ $middle }}
                        </div>

                        @if($hasRight)
                            <div class="d-none d-lg-block" data-resizer="right" style="width: 8px; cursor: col-resize; background: #f8f9fa; border-right: 1px solid #dee2e6;"></div>

                            <div class="p-4 h-100" data-pane="right" style="overflow-y:auto; overflow-x:hidden; min-height: 0; min-width: 0; flex: 0 0 calc(var(--pane-right) - 8px);">
                                {{ $right }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    {{ $footer ?? '' }}
                    <button type="submit" class="btn btn-success" id="{{ $saveButtonId ?? $id . '_save' }}">Mentés</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                </div>
            </div>
        </form>
    </div>
</div>
