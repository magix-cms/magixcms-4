class MagixCalendar {
    constructor(config) {
        this.container = document.getElementById(config.containerId);
        this.titleElement = document.getElementById(config.titleId);
        this.year = config.currentYear;
        this.month = config.currentMonth;
        this.events = config.events || [];
        this.lang = config.lang || 'fr';
        this.baseUrl = window.location.pathname;
    }

    init() {
        this.render();
        this.bindEvents();
    }

    bindEvents() {
        document.getElementById('prev-month').addEventListener('click', () => this.changeMonth(-1));
        document.getElementById('next-month').addEventListener('click', () => this.changeMonth(1));
        document.getElementById('today-btn').addEventListener('click', () => {
            const now = new Date();
            this.fetchMonth(now.getFullYear(), now.getMonth() + 1);
        });
    }

    async changeMonth(delta) {
        let newMonth = this.month + delta;
        let newYear = this.year;

        if (newMonth < 1) { newMonth = 12; newYear--; }
        else if (newMonth > 12) { newMonth = 1; newYear++; }

        await this.fetchMonth(newYear, newMonth);
    }

    async fetchMonth(year, month) {
        try {
            const response = await fetch(`${this.baseUrl}?ajax=1&year=${year}&month=${month}`);
            const data = await response.json();

            this.year = data.year;
            this.month = data.month;
            this.events = data.events;

            this.render();
            this.updateURL();
        } catch (error) {
            console.error("Erreur lors du chargement du calendrier :", error);
        }
    }

    render() {
        console.log("Mes évènements reçus :", this.events);
        // Mise à jour du titre
        const date = new Date(this.year, this.month - 1);
        this.titleElement.innerText = date.toLocaleString(this.lang, { month: 'long', year: 'numeric' });

        // Calcul de la grille
        const firstDay = new Date(this.year, this.month - 1, 1).getDay();
        const startOffset = (firstDay === 0 ? 7 : firstDay) - 1; // Ajustement pour commencer le Lundi
        const daysInMonth = new Date(this.year, this.month, 0).getDate();

        let html = '';

        // Jours vides du mois précédent
        for (let i = 0; i < startOffset; i++) {
            html += '<div class="calendar-day bg-light text-muted"></div>';
        }

        // Jours du mois
        // Jours du mois
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${this.year}-${String(this.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayEvents = this.events.filter(e => e.date_start.startsWith(dateStr));

            // Si on a des évènements, on ajoute la classe "has-event"
            const isEventDay = dayEvents.length > 0 ? 'has-event' : '';

            html += `
                <div class="calendar-day border ${isEventDay}">
                    <div class="fw-bold small mb-1">${day}</div>
                    <div class="events-container">
                        ${dayEvents.map(e => `
                            <a href="${e.slug}" class="event-link">
                                ${e.title}
                            </a>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        this.container.innerHTML = html;
    }

    updateURL() {
        const newUrl = `${this.baseUrl}?year=${this.year}&month=${this.month}`;
        window.history.pushState({ path: newUrl }, '', newUrl);
    }
}