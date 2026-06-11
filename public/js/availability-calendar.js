window.availabilityCalendar = (config) => ({
    calendar: null,
    busyDates: [...config.busyDates],
    visibleMonth: config.initialDate.slice(0, 7),
    modalOpen: false,
    selectedDate: null,
    selectedBusy: false,
    selectedLabel: '',
    saving: false,

    init() {
        this.calendar = new FullCalendar.Calendar(this.$refs.calendar, {
            initialView: 'dayGridMonth',
            initialDate: config.initialDate,
            firstDay: 1,
            locale: config.calendarLocale,
            height: 'auto',
            fixedWeekCount: false,
            showNonCurrentDates: true,
            dayHeaderContent: (info) => this.formatWeekday(info.date),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '',
            },
            buttonText: {
                today: 'Danas',
            },
            validRange: {
                start: config.today,
            },
            dateClick: (info) => this.openDate(info.dateStr, this.busyDates.includes(info.dateStr)),
            eventClick: (info) => this.openDate(info.event.startStr.slice(0, 10), true),
            datesSet: (info) => {
                const date = info.view.currentStart;
                const month = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

                this.renderLocalizedTitle(date);

                if (month !== this.visibleMonth) {
                    this.visibleMonth = month;
                    this.$wire.setMonthFromCalendar(month);
                }

                this.$nextTick(() => this.refreshDayClasses());
            },
            events: () => this.busyEvents(),
        });

        this.calendar.render();
        this.refreshDayClasses();
    },

    openDate(date, busy) {
        this.selectedDate = date;
        this.selectedBusy = busy;
        this.selectedLabel = new Intl.DateTimeFormat(config.intlLocale, {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(new Date(`${date}T12:00:00`));
        this.modalOpen = true;
    },

    async applyDateStatus() {
        if (this.saving || !this.selectedDate) return;

        const nextBusy = !this.selectedBusy;
        this.saving = true;

        try {
            await this.$wire.setDateStatus(this.selectedDate, nextBusy);

            this.busyDates = nextBusy
                ? [...new Set([...this.busyDates, this.selectedDate])].sort()
                : this.busyDates.filter((date) => date !== this.selectedDate);

            this.modalOpen = false;
            this.refreshCalendar();
        } finally {
            this.saving = false;
        }
    },

    refreshCalendar() {
        this.calendar.removeAllEvents();
        this.calendar.addEventSource(this.busyEvents());
        this.$nextTick(() => this.refreshDayClasses());
    },

    markVisibleMonth(busy) {
        const start = new Date(this.calendar.view.currentStart);
        const end = new Date(this.calendar.view.currentEnd);
        const dates = [];

        for (const date = new Date(start); date < end; date.setDate(date.getDate() + 1)) {
            const value = this.localDate(date);
            if (value >= config.today) dates.push(value);
        }

        this.busyDates = busy
            ? [...new Set([...this.busyDates, ...dates])].sort()
            : this.busyDates.filter((date) => !dates.includes(date));

        this.refreshCalendar();
    },

    busyEvents() {
        return this.busyDates.map((date) => ({
            id: date,
            title: 'Zauzet',
            start: date,
            allDay: true,
            classNames: ['availability-busy-event'],
        }));
    },

    refreshDayClasses() {
        this.$refs.calendar.querySelectorAll('.fc-daygrid-day[data-date]').forEach((element) => {
            const busy = this.busyDates.includes(element.dataset.date);
            element.classList.toggle('availability-busy-day', busy);
            element.classList.toggle('availability-free-day', !busy);
        });
    },

    renderLocalizedTitle(date) {
        const title = this.$refs.calendar.querySelector('.fc-toolbar-title');

        if (title) {
            title.textContent = new Intl.DateTimeFormat(config.intlLocale, {
                month: 'long',
                year: 'numeric',
            }).format(date);
        }
    },

    formatWeekday(date) {
        return new Intl.DateTimeFormat(config.intlLocale, {
            weekday: 'long',
        }).format(date);
    },

    localDate(date) {
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    },
});
