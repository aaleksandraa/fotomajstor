window.availabilityCalendar = (config) => ({
    Calendar: null,
    calendar: null,
    busyDates: [...config.busyDates],
    visibleMonth: config.initialDate.slice(0, 7),
    modalOpen: false,
    selectedDate: null,
    selectedBusy: false,
    selectedLabel: '',
    saving: false,

    async init() {
        ({ Calendar: this.Calendar } = await import(config.libraryUrl));
        this.mountCalendar();
    },

    mountCalendar() {
        const [year, month] = this.visibleMonth.split('-').map(Number);

        this.calendar?.destroy();
        this.calendar = new this.Calendar(this.$refs.calendar, {
            locale: this.localizedLabels(),
            firstWeekday: 1,
            selectedMonth: month - 1,
            selectedYear: year,
            selectedWeekends: [0, 6],
            selectionDatesMode: false,
            selectionMonthsMode: 'only-arrows',
            selectionYearsMode: 'only-arrows',
            displayDatesOutside: true,
            displayDisabledDates: true,
            disableDatesPast: true,
            enableMonthChangeOnDayClick: false,
            selectedTheme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            themeAttrDetect: false,
            onClickArrow: (calendar) => {
                window.setTimeout(() => this.syncVisibleMonth(calendar), 0);
            },
            onInit: () => this.decorateDays(),
            onUpdate: () => this.decorateDays(),
        });

        this.calendar.init();
        this.decorateDays();
    },

    handleCalendarClick(event) {
        const dateElement = event.target.closest('[data-vc-date]');
        const date = dateElement?.dataset.vcDate;
        const isDisabled = dateElement?.hasAttribute('data-vc-date-disabled');

        if (date && !isDisabled) {
            this.openDate(date, this.busyDates.includes(date));
        }
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

    async applyDateStatus(busy) {
        if (this.saving || !this.selectedDate) return;

        if (busy === this.selectedBusy) {
            this.modalOpen = false;

            return;
        }

        const date = this.selectedDate;
        const previousBusy = this.selectedBusy;
        this.saving = true;
        this.setDateStatusLocally(date, busy);
        this.selectedBusy = busy;
        this.modalOpen = false;
        this.paintDate(date, busy, true);

        try {
            await this.$wire.setDateStatus(date, busy);
        } catch (error) {
            this.setDateStatusLocally(date, previousBusy);
            this.selectedBusy = previousBusy;
            this.modalOpen = true;
            this.paintDate(date, previousBusy);

            throw error;
        } finally {
            this.saving = false;
        }
    },

    markVisibleMonth(busy) {
        const [year, month] = this.visibleMonth.split('-').map(Number);
        const end = new Date(year, month, 0).getDate();
        const dates = [];

        for (let day = 1; day <= end; day += 1) {
            const date = `${this.visibleMonth}-${String(day).padStart(2, '0')}`;
            if (date >= config.today) dates.push(date);
        }

        this.busyDates = busy
            ? [...new Set([...this.busyDates, ...dates])].sort()
            : this.busyDates.filter((date) => !dates.includes(date));

        this.decorateDays();
    },

    syncVisibleMonth(calendar) {
        const month = `${calendar.context.selectedYear}-${String(calendar.context.selectedMonth + 1).padStart(2, '0')}`;

        if (month !== this.visibleMonth) {
            this.visibleMonth = month;
            this.$wire.setMonthFromCalendar(month);
        }

        this.decorateDays();
    },

    syncTheme(theme) {
        const dark = theme === 'dark'
            || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        this.calendar?.set({ selectedTheme: dark ? 'dark' : 'light' });
        this.decorateDays();
    },

    decorateDays() {
        this.$refs.calendar.querySelectorAll('[data-vc-date]').forEach((element) => {
            const isCurrentMonth = element.dataset.vcDateMonth === 'current';
            const busy = this.busyDates.includes(element.dataset.vcDate);

            this.paintDateElement(element, busy, isCurrentMonth);
        });
    },

    setDateStatusLocally(date, busy) {
        this.busyDates = busy
            ? [...new Set([...this.busyDates, date])].sort()
            : this.busyDates.filter((busyDate) => busyDate !== date);
    },

    paintDate(date, busy, animate = false) {
        this.$refs.calendar.querySelectorAll('[data-vc-date]').forEach((element) => {
            if (element.dataset.vcDate !== date || element.dataset.vcDateMonth !== 'current') return;

            this.paintDateElement(element, busy, true);

            if (animate) {
                element.classList.remove('availability-status-updated');
                void element.offsetWidth;
                element.classList.add('availability-status-updated');
                window.setTimeout(() => element.classList.remove('availability-status-updated'), 700);
            }
        });
    },

    paintDateElement(element, busy, isCurrentMonth) {
        const button = element.querySelector('[data-vc-date-btn]');
        const dark = document.documentElement.classList.contains('dark');

        element.classList.toggle('availability-busy-day', isCurrentMonth && busy);
        element.classList.toggle('availability-free-day', isCurrentMonth && !busy);

        if (button && isCurrentMonth) {
            button.dataset.availabilityState = busy ? 'busy' : 'free';
            button.dataset.availabilityStatus = busy ? config.busyLabel : config.freeLabel;
            this.applyStatusColors(button, busy, dark);
        } else if (button) {
            delete button.dataset.availabilityState;
            delete button.dataset.availabilityStatus;
            ['background-color', 'border-color', 'color'].forEach((property) => button.style.removeProperty(property));
        }
    },

    applyStatusColors(button, busy, dark) {
        const colors = dark
            ? (busy
                ? { background: 'rgb(127 29 29)', border: 'rgb(248 113 113)', text: 'rgb(254 226 226)' }
                : { background: 'rgb(20 83 45)', border: 'rgb(74 222 128)', text: 'rgb(220 252 231)' })
            : (busy
                ? { background: 'rgb(254 202 202)', border: 'rgb(239 68 68)', text: 'rgb(127 29 29)' }
                : { background: 'rgb(187 247 208)', border: 'rgb(34 197 94)', text: 'rgb(20 83 45)' });

        button.style.setProperty('background-color', colors.background, 'important');
        button.style.setProperty('border-color', colors.border, 'important');
        button.style.setProperty('color', colors.text, 'important');
    },

    localizedLabels() {
        const months = Array.from({ length: 12 }, (_, month) => new Date(2026, month, 1));
        const weekdays = Array.from({ length: 7 }, (_, day) => new Date(2026, 0, 4 + day));

        return {
            months: {
                long: months.map((date) => this.formatDate(date, { month: 'long' })),
                short: months.map((date) => this.formatDate(date, { month: 'short' })),
            },
            weekdays: {
                long: weekdays.map((date) => this.formatDate(date, { weekday: 'long' })),
                short: weekdays.map((date) => this.formatDate(date, { weekday: 'short' })),
            },
        };
    },

    formatDate(date, options) {
        return new Intl.DateTimeFormat(config.intlLocale, options).format(date);
    },
});
