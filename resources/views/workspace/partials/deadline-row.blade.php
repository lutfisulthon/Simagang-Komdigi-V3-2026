{{--
    Satu baris tugas di tab Deadline. Dipakai di dalam x-for (variabel Alpine `card`) pada scope wsDeadlines
    (public/js/workspace/deadlines.js). Klik baris membuka detail; mentor/admin juga punya tombol perpanjang deadline.
--}}
<li class="ws-dl-row" :class="'ws-dl-row--' + tone(card)">
    <button type="button" class="ws-dl-row__open" x-on:click="open(card)">
        <span class="ws-dl-row__main">
            <span class="flex items-baseline gap-2 min-w-0">
                <span class="ws-mono text-[11px] font-medium ws-text-faint whitespace-nowrap" x-text="card.code"></span>
                <span class="text-sm font-semibold text-gray-800 truncate" x-text="card.title"></span>
            </span>
            <span class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1.5">
                <span class="ws-badge !text-[11px] !px-2 !py-0" :class="'ws-status--' + card.status">
                    <span class="ws-badge__dot"></span><span x-text="WS.STATUS[card.status]"></span>
                </span>
                <span class="ws-badge !text-[11px] !px-2 !py-0" :class="'ws-priority--' + card.priority" x-show="card.priority === 'high' || card.priority === 'urgent'">
                    <i class="fas text-[9px]" :class="WS.tasks.priorityIcon(card.priority)" aria-hidden="true"></i><span x-text="WS.PRIORITY[card.priority]"></span>
                </span>
                <span class="text-[11px] font-semibold ws-text-faint" x-show="sprintLabel(card)" x-text="sprintLabel(card)"></span>
            </span>
        </span>

        <span class="ws-dl-row__people">
            <span class="ws-avatar-stack" x-show="card.assignees.length">
                <template x-for="a in card.assignees" :key="a.id"><x-workspace.avatar-js user="a" size="xs" /></template>
            </span>
            <span class="ws-bl-unassigned" x-show="!card.assignees.length" title="Belum dibagi"><i class="fas fa-user" aria-hidden="true"></i><span class="sr-only">Belum dibagi</span></span>
        </span>

        <span class="ws-dl-row__due">
            <span class="ws-dl-row__date" x-text="dueDateLine(card)"></span>
            <span class="ws-dl-row__rel" x-text="relText(card)"></span>
        </span>
    </button>

    <button type="button" class="ws-icon-btn ws-icon-btn--sm ws-dl-row__extend" x-show="card.can.extend_deadline"
            x-on:click="$dispatch('ws-open-deadline', { task: card })"
            :aria-label="'Perpanjang deadline ' + card.code" title="Perpanjang deadline">
        <i class="fas fa-calendar-plus" aria-hidden="true"></i>
    </button>
</li>
