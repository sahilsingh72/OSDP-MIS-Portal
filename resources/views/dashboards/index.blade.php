@extends('layouts.app', ['title' => 'Executive Dashboard', 'headerTitle' => 'Monitoring Dashboard'])

@section('content')
<div class="space-y-6">

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Total Batches -->
        <a href="{{ route('batches.index') }}" class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-400/60 hover:-translate-y-0.5 transition duration-200 block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider group-hover:text-blue-600 transition">Total Batches</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900 group-hover:text-blue-600 transition">{{ number_format($totalBatches) }}</span>
                <span class="text-xs text-slate-500 ml-1">/ {{ $activePlan ? number_format($activePlan->target_batches) : '2,149' }} target</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ $scheduledBatches }} Scheduled</span>
                <span class="text-emerald-600 font-semibold">{{ $verifiedBatches }} Verified</span>
            </div>
        </a>

        <!-- Verified & Accepted -->
        <a href="{{ route('batches.index', ['status' => 'verified']) }}" class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-400/60 hover:-translate-y-0.5 transition duration-200 block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider group-hover:text-emerald-600 transition">Verified Batches</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-emerald-600">{{ number_format($verifiedBatches) }}</span>
                <span class="text-xs text-slate-500 ml-1">settlement ready</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ $submittedBatches }} In Review</span>
                <span class="text-indigo-600 font-semibold">{{ $inProgressBatches }} In Progress</span>
            </div>
        </a>

        <!-- Participants Attendance -->
        <a href="{{ auth()->user()->can('participants.view') ? route('participants.index') : route('batches.index') }}" class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-purple-400/60 hover:-translate-y-0.5 transition duration-200 block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider group-hover:text-purple-600 transition">Trained Officials</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900 group-hover:text-purple-600 transition">{{ number_format($totalPresent) }}</span>
                <span class="text-xs text-slate-500 ml-1">officials present</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ number_format($totalRegistered) }} Registered</span>
                <span class="text-purple-600 font-semibold">{{ $totalRegistered > 0 ? round(($totalPresent / $totalRegistered) * 100, 1) : 0 }}% Turnout</span>
            </div>
        </a>

        <!-- Overdue SLA & Feedback -->
        <a href="{{ route('batches.index', ['overdue_only' => 1]) }}" class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-rose-400/60 hover:-translate-y-0.5 transition duration-200 block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider group-hover:text-rose-600 transition">Overdue (>48h)</span>
                <span class="p-2 rounded-xl {{ $overdueBatches > 0 ? 'bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white' : 'bg-slate-50 text-slate-400 group-hover:bg-slate-600 group-hover:text-white' }} transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold {{ $overdueBatches > 0 ? 'text-rose-600' : 'text-slate-900 group-hover:text-rose-600' }} transition">
                    {{ $overdueBatches }}
                </span>
                <div class="text-right">
                    <span class="text-xs font-semibold text-amber-500">★ {{ number_format($avgFeedbackScore, 2) }}</span>
                    <span class="text-[10px] text-slate-400 block">Avg Quality</span>
                </div>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                @if($overdueBatches > 0)
                    <span class="text-rose-600 font-medium">⚠️ Action needed within 48h SLA</span>
                @else
                    <span class="text-emerald-600 font-medium">✓ All evidence packs compliant</span>
                @endif
            </div>
        </a>

    </div>

    <!-- Training Event Calendar Section -->
    <script>
    window.trainingCalendarEvents = @json($calendarBatches);

    window.trainingCalendar = function(eventsData, initialYear, initialMonth) {
        return {
            currentYear: initialYear,
            currentMonth: initialMonth,
            selectedDate: '{{ Carbon\Carbon::now()->format('Y-m-d') }}',
            statusFilter: 'all',
            events: eventsData || window.trainingCalendarEvents || [],
            selectedEvent: null,
            modalOpen: false,

            init() {
                // If today has no events, auto-select first date that has events in current month
                const datesWithEvents = Object.keys(this.eventsByDate || {});
                if ((!this.eventsByDate[this.selectedDate] || this.eventsByDate[this.selectedDate].length === 0) && datesWithEvents.length > 0) {
                    const prefix = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}`;
                    const thisMonthDates = datesWithEvents.filter(d => d.startsWith(prefix));
                    if (thisMonthDates.length > 0) {
                        this.selectedDate = thisMonthDates[0];
                    }
                }
            },

            get monthName() {
                const date = new Date(this.currentYear, this.currentMonth, 1);
                return date.toLocaleString('default', { month: 'long', year: 'numeric' });
            },

            get filteredEvents() {
                if (this.statusFilter === 'all') return this.events;
                if (this.statusFilter === 'in_progress') {
                    return this.events.filter(e => ['in_progress', 'awaiting_evidence', 'correction_required'].includes(e.status));
                }
                if (this.statusFilter === 'submitted') {
                    return this.events.filter(e => ['submitted', 'under_review'].includes(e.status));
                }
                return this.events.filter(e => e.status === this.statusFilter);
            },

            get eventsByDate() {
                const map = {};
                this.filteredEvents.forEach(e => {
                    if (e.date) {
                        if (!map[e.date]) map[e.date] = [];
                        map[e.date].push(e);
                    }
                });
                return map;
            },

            get calendarDays() {
                const days = [];
                const firstDayOfMonth = new Date(this.currentYear, this.currentMonth, 1);
                const lastDayOfMonth = new Date(this.currentYear, this.currentMonth + 1, 0);

                let startDay = firstDayOfMonth.getDay() - 1;
                if (startDay === -1) startDay = 6;

                // Previous month padding
                const prevMonthLastDate = new Date(this.currentYear, this.currentMonth, 0).getDate();
                for (let i = startDay - 1; i >= 0; i--) {
                    const dayNum = prevMonthLastDate - i;
                    const d = new Date(this.currentYear, this.currentMonth - 1, dayNum);
                    const dateStr = this.formatDate(d);
                    days.push({
                        dayNumber: dayNum,
                        dateString: dateStr,
                        isCurrentMonth: false,
                        isToday: this.isToday(dateStr),
                        isSelected: this.selectedDate === dateStr,
                        events: this.eventsByDate[dateStr] || []
                    });
                }

                // Current month days
                for (let d = 1; d <= lastDayOfMonth.getDate(); d++) {
                    const dateObj = new Date(this.currentYear, this.currentMonth, d);
                    const dateStr = this.formatDate(dateObj);
                    days.push({
                        dayNumber: d,
                        dateString: dateStr,
                        isCurrentMonth: true,
                        isToday: this.isToday(dateStr),
                        isSelected: this.selectedDate === dateStr,
                        events: this.eventsByDate[dateStr] || []
                    });
                }

                // Next month padding to complete 35 or 42 grid cells
                const targetTotal = days.length <= 35 ? 35 : 42;
                const remaining = targetTotal - days.length;
                for (let d = 1; d <= remaining; d++) {
                    const dateObj = new Date(this.currentYear, this.currentMonth + 1, d);
                    const dateStr = this.formatDate(dateObj);
                    days.push({
                        dayNumber: d,
                        dateString: dateStr,
                        isCurrentMonth: false,
                        isToday: this.isToday(dateStr),
                        isSelected: this.selectedDate === dateStr,
                        events: this.eventsByDate[dateStr] || []
                    });
                }

                return days;
            },

            get selectedDateEvents() {
                return this.eventsByDate[this.selectedDate] || [];
            },

            get selectedDateFormatted() {
                if (!this.selectedDate) return '';
                const parts = this.selectedDate.split('-');
                const date = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                return date.toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            },

            get activeMonthDates() {
                const prefix = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}`;
                const dates = [];
                Object.keys(this.eventsByDate).sort().forEach(dateStr => {
                    if (dateStr.startsWith(prefix) && this.eventsByDate[dateStr].length > 0) {
                        const parts = dateStr.split('-');
                        const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                        dates.push({
                            dateString: dateStr,
                            dayNum: parseInt(parts[2]),
                            label: d.toLocaleDateString('en-US', { day: 'numeric', month: 'short' }),
                            count: this.eventsByDate[dateStr].length
                        });
                    }
                });
                return dates;
            },

            get monthStats() {
                const prefix = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}`;
                let total = 0;
                let scheduled = 0;
                let inProgress = 0;
                let verified = 0;

                this.events.forEach(e => {
                    if (e.date && e.date.startsWith(prefix)) {
                        total++;
                        if (e.status === 'scheduled') scheduled++;
                        else if (['in_progress', 'awaiting_evidence', 'correction_required'].includes(e.status)) inProgress++;
                        else if (e.status === 'verified') verified++;
                    }
                });

                return { total, scheduled, inProgress, verified };
            },

            formatDate(d) {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            },

            isToday(dateStr) {
                const now = new Date();
                return dateStr === this.formatDate(now);
            },

            prevMonth() {
                if (this.currentMonth === 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                } else {
                    this.currentMonth--;
                }
            },

            nextMonth() {
                if (this.currentMonth === 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                } else {
                    this.currentMonth++;
                }
            },

            goToToday() {
                const now = new Date();
                this.currentYear = now.getFullYear();
                this.currentMonth = now.getMonth();
                this.selectedDate = this.formatDate(now);
            },

            selectDate(dateStr) {
                this.selectedDate = dateStr;
            },

            openEventModal(event) {
                this.selectedEvent = event;
                this.modalOpen = true;
            },

            getStatusBadgeClass(status) {
                switch(status) {
                    case 'scheduled':
                        return 'bg-sky-50 text-sky-800 border-sky-200/80';
                    case 'in_progress':
                    case 'awaiting_evidence':
                        return 'bg-amber-50 text-amber-800 border-amber-200/80';
                    case 'submitted':
                    case 'under_review':
                        return 'bg-purple-50 text-purple-800 border-purple-200/80';
                    case 'verified':
                        return 'bg-emerald-50 text-emerald-800 border-emerald-200/80';
                    case 'correction_required':
                        return 'bg-orange-50 text-orange-800 border-orange-200/80';
                    default:
                        return 'bg-slate-50 text-slate-700 border-slate-200';
                }
            },

            getStatusDotClass(status) {
                switch(status) {
                    case 'scheduled': return 'bg-sky-500';
                    case 'in_progress':
                    case 'awaiting_evidence': return 'bg-amber-500';
                    case 'submitted':
                    case 'under_review': return 'bg-purple-500';
                    case 'verified': return 'bg-emerald-500';
                    case 'correction_required': return 'bg-orange-500';
                    default: return 'bg-slate-400';
                }
            }
        };
    };
    </script>

    <div x-data="trainingCalendar(window.trainingCalendarEvents, {{ Carbon\Carbon::now()->year }}, {{ Carbon\Carbon::now()->month - 1 }})"
         class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-6">

        <!-- Calendar Header & Controls -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <!-- Left: Title & Subtitle -->
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Training Event Calendar</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            Live Schedule
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Interactive monthly roster of scheduled sessions, active training, and completed batches</p>
                </div>
            </div>

            <!-- Right: Month Navigators & Action -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Month Navigator -->
                <div class="flex items-center bg-slate-50 border border-slate-200/80 rounded-2xl p-1 shadow-2xs">
                    <button type="button" @click="prevMonth()"
                            class="p-1.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-white transition"
                            title="Previous Month">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="px-3 py-1 font-bold text-slate-800 text-xs sm:text-sm tracking-tight text-center min-w-[130px]" x-text="monthName"></div>
                    <button type="button" @click="nextMonth()"
                            class="p-1.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-white transition"
                            title="Next Month">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Today Button -->
                <button type="button" @click="goToToday()"
                        class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span>Today</span>
                </button>
            </div>
        </div>

        <!-- Filter Pills & Month Statistics Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <!-- Filter Pills -->
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 sm:pb-0">
                <button type="button" @click="statusFilter = 'all'"
                        :class="statusFilter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100/80 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap">
                    All Statuses
                </button>
                <button type="button" @click="statusFilter = 'scheduled'"
                        :class="statusFilter === 'scheduled' ? 'bg-sky-600 text-white shadow-2xs' : 'bg-sky-50 text-sky-700 hover:bg-sky-100'"
                        class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap flex items-center space-x-1">
                    <span class="w-1.5 h-1.5 rounded-full" :class="statusFilter === 'scheduled' ? 'bg-white' : 'bg-sky-500'"></span>
                    <span>Scheduled</span>
                </button>
                <button type="button" @click="statusFilter = 'in_progress'"
                        :class="statusFilter === 'in_progress' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                        class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap flex items-center space-x-1">
                    <span class="w-1.5 h-1.5 rounded-full" :class="statusFilter === 'in_progress' ? 'bg-white' : 'bg-amber-500'"></span>
                    <span>In Progress</span>
                </button>
                <button type="button" @click="statusFilter = 'verified'"
                        :class="statusFilter === 'verified' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        class="px-3 py-1.5 rounded-xl font-semibold transition whitespace-nowrap flex items-center space-x-1">
                    <span class="w-1.5 h-1.5 rounded-full" :class="statusFilter === 'verified' ? 'bg-white' : 'bg-emerald-500'"></span>
                    <span>Verified</span>
                </button>
            </div>

            <!-- Month Quick Stat -->
            <div class="text-slate-500 text-xs flex items-center space-x-2 shrink-0">
                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-200/70 font-medium">
                    <span class="font-bold text-slate-800" x-text="monthStats.total"></span>
                    <span>training events in <span x-text="monthName"></span></span>
                </span>
            </div>
        </div>

        <!-- 2-Column Calendar Layout -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            <!-- Left: Calendar Grid (8 Cols) -->
            <div class="xl:col-span-8 flex flex-col justify-between">
                <div>
                    <!-- Days of Week Header -->
                    <div class="grid grid-cols-7 text-center font-bold text-[11px] uppercase tracking-wider text-slate-500 pb-2.5 border-b border-slate-200/80">
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div class="text-rose-500">Sat</div>
                        <div class="text-rose-500">Sun</div>
                    </div>

                    <!-- Month Days 7-Col Grid -->
                    <div class="grid grid-cols-7 gap-px bg-slate-200/80 rounded-2xl overflow-hidden border border-slate-200/80 mt-2 shadow-2xs">
                        <template x-for="(day, idx) in calendarDays" :key="day.dateString + '_' + idx">
                            <div @click="selectDate(day.dateString)"
                                 :class="{
                                     'bg-blue-50/60 ring-2 ring-blue-500/80 ring-inset z-10': day.isSelected,
                                     'bg-slate-50/60': !day.isCurrentMonth,
                                     'bg-white': day.isCurrentMonth && !day.isSelected,
                                     'hover:bg-blue-50/30': day.isCurrentMonth
                                 }"
                                 class="p-1.5 sm:p-2 min-h-[92px] sm:min-h-[105px] flex flex-col justify-between transition-colors cursor-pointer relative group">

                                <!-- Day Number & Count -->
                                <div class="flex items-center justify-between">
                                    <span :class="{
                                              'w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs ring-2 ring-blue-200': day.isToday,
                                              'w-6 h-6 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center text-xs shadow-xs': day.isSelected && !day.isToday,
                                              'text-xs font-semibold text-slate-700': day.isCurrentMonth && !day.isToday && !day.isSelected,
                                              'text-xs font-normal text-slate-300': !day.isCurrentMonth
                                          }"
                                          x-text="day.dayNumber">
                                    </span>

                                    <!-- Session count badge for day -->
                                    <template x-if="day.events.length > 0">
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs"
                                              x-text="day.events.length"></span>
                                    </template>
                                </div>

                                <!-- Events Pills for Day -->
                                <div class="mt-1 space-y-1 overflow-hidden">
                                    <template x-for="(event, eIdx) in day.events.slice(0, 2)" :key="event.id">
                                        <div @click.stop="selectDate(day.dateString); openEventModal(event)"
                                             :class="getStatusBadgeClass(event.status)"
                                             class="px-1.5 py-0.5 rounded-lg text-[10px] font-medium border truncate transition flex items-center space-x-1 cursor-pointer shadow-2xs hover:scale-[1.02]"
                                             :title="event.batch_code + ' • ' + event.district_name + ' • ' + event.status_label">
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="getStatusDotClass(event.status)"></span>
                                            <span class="truncate" x-text="event.batch_code"></span>
                                        </div>
                                    </template>

                                    <!-- +N more events pill -->
                                    <template x-if="day.events.length > 2">
                                        <div class="text-[10px] font-bold text-blue-600 hover:text-blue-800 px-1 py-0.5 rounded transition">
                                            +<span x-text="day.events.length - 2"></span> more
                                        </div>
                                    </template>
                                </div>

                                <!-- Bottom Indicator Dot if events exist -->
                                <div class="mt-1 flex items-center justify-center space-x-1">
                                    <template x-if="day.events.length > 0 && day.events.length <= 2">
                                        <div class="h-1 w-4 rounded-full bg-blue-500/40"></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Calendar Legend -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="flex items-center space-x-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                            <span>Scheduled</span>
                        </span>
                        <span class="flex items-center space-x-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>In Progress / Awaiting Evidence</span>
                        </span>
                        <span class="flex items-center space-x-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <span>Submitted for Review</span>
                        </span>
                        <span class="flex items-center space-x-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>Verified</span>
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400">Click any date to view detailed agenda</span>
                </div>
            </div>

            <!-- Right: Selected Day Schedule Details (4 Cols) -->
            <div class="xl:col-span-4 flex flex-col">
                <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <!-- Day Schedule Header -->
                        <div class="flex items-start justify-between pb-3.5 border-b border-slate-200/80 mb-4">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Selected Date Agenda</span>
                                <h3 class="text-sm font-bold text-slate-900 tracking-tight mt-0.5" x-text="selectedDateFormatted"></h3>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                  :class="selectedDateEvents.length > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-600'"
                                  x-text="selectedDateEvents.length + (selectedDateEvents.length === 1 ? ' Session' : ' Sessions')">
                            </span>
                        </div>

                        <!-- Sessions List -->
                        <div class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
                            <!-- When events exist on selected date -->
                            <template x-for="event in selectedDateEvents" :key="event.id">
                                <div class="p-3.5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:border-blue-300 hover:shadow-xs transition space-y-2.5">
                                    <!-- Header: Code + Status -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <a :href="event.show_url" class="font-bold text-xs text-blue-600 hover:underline block" x-text="event.batch_code"></a>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 mt-0.5" x-text="event.tier_name"></span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border"
                                              :class="getStatusBadgeClass(event.status)"
                                              x-text="event.status_label"></span>
                                    </div>

                                    <!-- Details (Time, Location, Venue) -->
                                    <div class="space-y-1.5 text-[11px] text-slate-600">
                                        <div class="flex items-center space-x-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="font-medium text-slate-800" x-text="event.time_range"></span>
                                        </div>

                                        <div class="flex items-center space-x-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>
                                                <strong class="text-slate-800" x-text="event.district_name"></strong>
                                                <template x-if="event.block_name">
                                                    <span class="text-slate-500" x-text="' • ' + event.block_name"></span>
                                                </template>
                                            </span>
                                        </div>

                                        <div class="flex items-center space-x-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span class="truncate" x-text="event.venue_name"></span>
                                        </div>

                                        <div class="flex items-center space-x-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span class="truncate text-slate-700" x-text="'Trainer: ' + event.trainer_names"></span>
                                        </div>

                                        <div class="flex items-center space-x-1.5 pt-0.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <span>
                                                Turnout: <strong class="text-slate-900" x-text="event.present_participants"></strong> / <span x-text="event.registered_participants"></span> Present
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Quick Actions -->
                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                        <a :href="event.show_url" class="font-semibold text-blue-600 hover:text-blue-800 transition">
                                            View Batch →
                                        </a>
                                        <div class="flex items-center space-x-2">
                                            <a :href="event.attendance_url" class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-[11px] border border-emerald-200 transition">
                                                Attendance
                                            </a>
                                            <a :href="event.evidence_url" class="px-2 py-1 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 font-semibold text-[11px] border border-purple-200 transition">
                                                Evidence
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- When NO events exist on selected date -->
                            <template x-if="selectedDateEvents.length === 0">
                                <div class="text-center py-6 px-4 space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200/80 shadow-2xs mx-auto flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-800">No Sessions on this Date</h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5">There are no training batches scheduled for this specific day.</p>
                                    </div>

                                    <!-- Jump to other active dates in month -->
                                    <div class="pt-2 text-left" x-show="activeMonthDates.length > 0">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Active Training Days this Month:</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="d in activeMonthDates" :key="d.dateString">
                                                <button type="button" @click="selectDate(d.dateString)"
                                                        class="px-2 py-1 rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 text-[11px] font-medium transition shadow-2xs flex items-center space-x-1">
                                                    <span x-text="d.label"></span>
                                                    <span class="px-1 py-0.2 bg-blue-100 text-blue-800 rounded-full font-bold text-[9px]" x-text="d.count"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Bottom helper link -->
                    <div class="pt-3 border-t border-slate-200/80 mt-3 text-right">
                        <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                            Open Full Batches Directory →
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Event Detail Modal (Preview) -->
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="modalOpen = false"></div>

                <div class="relative inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100 p-6 space-y-4"
                     @click.stop>
                    <template x-if="selectedEvent">
                        <div>
                            <!-- Modal Header -->
                            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border mb-1"
                                          :class="getStatusBadgeClass(selectedEvent.status)"
                                          x-text="selectedEvent.status_label"></span>
                                    <h3 class="text-base font-bold text-slate-900" x-text="selectedEvent.batch_code"></h3>
                                    <p class="text-xs text-slate-500" x-text="selectedEvent.formatted_date + ' • ' + selectedEvent.time_range"></p>
                                </div>
                                <button type="button" @click="modalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal Body Details Grid -->
                            <div class="grid grid-cols-2 gap-3 text-xs pt-3">
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Tier & District</span>
                                    <span class="font-bold text-slate-800" x-text="selectedEvent.tier_name"></span>
                                    <span class="text-slate-500 block text-[11px]" x-text="selectedEvent.district_name + (selectedEvent.block_name ? ' (' + selectedEvent.block_name + ')' : '')"></span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Venue</span>
                                    <span class="font-bold text-slate-800 truncate block" x-text="selectedEvent.venue_name"></span>
                                    <span class="text-slate-500 block text-[11px]" x-text="'Time: ' + selectedEvent.time_range"></span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Primary Trainer</span>
                                    <span class="font-bold text-slate-800 truncate block" x-text="selectedEvent.trainer_names"></span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Trainee Turnout</span>
                                    <span class="font-bold text-slate-800" x-text="selectedEvent.present_participants + ' Present / ' + selectedEvent.registered_participants + ' Registered'"></span>
                                </div>
                            </div>

                            <!-- Modal Action Links -->
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2">
                                <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                    Close
                                </button>
                                <a :href="selectedEvent.attendance_url" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition">
                                    Attendance
                                </a>
                                <a :href="selectedEvent.show_url" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition">
                                    View Batch
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Status Breakdown Doughnut Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                            Batch Lifecycle Distribution
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Click any slice or badge to filter</p>
                    </div>
                    <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                        View Directory →
                    </a>
                </div>
                <div class="relative h-64 flex items-center justify-center cursor-pointer" title="Click chart segment to filter batches">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Clickable status legend badges -->
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <a href="{{ route('batches.index', ['status' => 'verified']) }}"
                   class="group flex items-center p-2 rounded-xl border border-slate-200/70 hover:border-emerald-300 hover:bg-emerald-50/70 transition"
                   title="Filter Verified Batches">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 flex-shrink-0 group-hover:scale-110 transition"></span>
                    <span class="text-slate-600 group-hover:text-emerald-900 font-medium">Verified: <strong class="text-slate-900 group-hover:text-emerald-800">{{ $verifiedBatches }}</strong></span>
                </a>
                <a href="{{ route('batches.index', ['status' => 'submitted']) }}"
                   class="group flex items-center p-2 rounded-xl border border-slate-200/70 hover:border-purple-300 hover:bg-purple-50/70 transition"
                   title="Filter Submitted Batches">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500 mr-2 flex-shrink-0 group-hover:scale-110 transition"></span>
                    <span class="text-slate-600 group-hover:text-purple-900 font-medium">Submitted: <strong class="text-slate-900 group-hover:text-purple-800">{{ $submittedBatches }}</strong></span>
                </a>
                <a href="{{ route('batches.index', ['status' => 'in_progress']) }}"
                   class="group flex items-center p-2 rounded-xl border border-slate-200/70 hover:border-amber-300 hover:bg-amber-50/70 transition"
                   title="Filter In Progress Batches">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 flex-shrink-0 group-hover:scale-110 transition"></span>
                    <span class="text-slate-600 group-hover:text-amber-900 font-medium">In Progress: <strong class="text-slate-900 group-hover:text-amber-800">{{ $inProgressBatches }}</strong></span>
                </a>
                <a href="{{ route('batches.index', ['status' => 'scheduled']) }}"
                   class="group flex items-center p-2 rounded-xl border border-slate-200/70 hover:border-sky-300 hover:bg-sky-50/70 transition"
                   title="Filter Scheduled Batches">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500 mr-2 flex-shrink-0 group-hover:scale-110 transition"></span>
                    <span class="text-slate-600 group-hover:text-sky-900 font-medium">Scheduled: <strong class="text-slate-900 group-hover:text-sky-800">{{ $scheduledBatches }}</strong></span>
                </a>
            </div>
        </div>

        <!-- Training Tier Progress Bar Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                            Training Reach by Administrative Tier
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Click any bar to filter batches by tier</p>
                    </div>
                    <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                        View All Batches →
                    </a>
                </div>
                <div class="relative h-64 cursor-pointer" title="Click bar to view batches for tier">
                    <canvas id="tierChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center space-x-1.5 text-xs text-slate-500">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Clicking any tier bar filters the Training Batches Directory for that administrative level.</span>
            </div>
        </div>

    </div>

    <!-- Recent Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Recent Training Batches
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Real-time status updates and evidence completeness</p>
            </div>
            <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                View All ({{ $totalBatches }}) →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3 w-16">S.No</th>
                        <th class="px-6 py-3">Batch Code</th>
                        <th class="px-6 py-3">Tier</th>
                        <th class="px-6 py-3">District / Block</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Trainer</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Evidence</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($recentBatches as $batch)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">
                            <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:underline">
                                {{ $batch->batch_code }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-800">{{ $batch->tier?->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $batch->district?->name ?? 'State-Level' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $batch->block?->name ?? 'All Blocks' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $batch->scheduled_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $batch->trainers->first()?->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-6 py-4">
                            <x-status-badge :status="$batch->status" />
                        </td>
                        <td class="px-6 py-4">
                            @php $ev = $batch->evidence_status; @endphp
                            <div class="flex items-center space-x-1.5">
                                <div class="w-16 bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $ev['is_complete'] ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                         style="width: {{ ($ev['completed_count'] / 6) * 100 }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-600">{{ $ev['completed_count'] }}/6</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:text-blue-900 font-semibold">View</a>
                            <a href="{{ route('batches.evidence.index', $batch) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Uploads</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center text-slate-400">
                            No training batches recorded yet. Schedule your first batch to begin tracking.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Status Doughnut Chart
    const statusMap = ['verified', 'submitted', 'in_progress', 'scheduled', 'planned'];
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Verified', 'Submitted', 'In Progress', 'Scheduled', 'Planned'],
            datasets: [{
                data: [
                    {{ $verifiedBatches }},
                    {{ $submittedBatches }},
                    {{ $inProgressBatches }},
                    {{ $scheduledBatches }},
                    {{ $plannedBatches }}
                ],
                backgroundColor: ['#10b981', '#a855f7', '#f59e0b', '#0ea5e9', '#94a3b8'],
                hoverBackgroundColor: ['#059669', '#9333ea', '#d97706', '#0284c7', '#64748b'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: function() {
                            return 'Click to filter batches →';
                        }
                    }
                }
            },
            cutout: '70%',
            onClick: (event, elements) => {
                if (elements && elements.length > 0) {
                    const index = elements[0].index;
                    const status = statusMap[index];
                    if (status) {
                        window.location.href = "{{ route('batches.index') }}?status=" + encodeURIComponent(status);
                    }
                }
            },
            onHover: (event, elements) => {
                event.native.target.style.cursor = elements && elements.length > 0 ? 'pointer' : 'default';
            }
        }
    });

    // 2. Tier Bar Chart
    const tierCtx = document.getElementById('tierChart').getContext('2d');
    const tierLabels = {!! json_encode($tierStats->pluck('name')) !!};
    const tierCounts = {!! json_encode($tierStats->pluck('batches_count')) !!};
    const tierIds = {!! json_encode($tierStats->pluck('id')) !!};

    new Chart(tierCtx, {
        type: 'bar',
        data: {
            labels: tierLabels,
            datasets: [{
                label: 'Conducted Batches',
                data: tierCounts,
                backgroundColor: '#3b82f6',
                hoverBackgroundColor: '#1d4ed8',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: function() {
                            return 'Click to view batches for this tier →';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            },
            onClick: (event, elements) => {
                if (elements && elements.length > 0) {
                    const index = elements[0].index;
                    const tierId = tierIds[index];
                    if (tierId) {
                        window.location.href = "{{ route('batches.index') }}?tier_id=" + encodeURIComponent(tierId);
                    }
                }
            },
            onHover: (event, elements) => {
                event.native.target.style.cursor = elements && elements.length > 0 ? 'pointer' : 'default';
            }
        }
    });
});
</script>
@endpush
