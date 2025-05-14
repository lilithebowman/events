/**
 * Calendar Layout Module for Events Display
 * Displays events from JSON in a calendar view
 */
export class CalendarLayout {
	constructor() {
		this.events = [];
		this.currentDate = new Date();
		this.selectedMonth = this.currentDate.getMonth();
		this.selectedYear = this.currentDate.getFullYear();
		this.modalElement = null;
	}

	/**
	 * Initialize the calendar and load events
	 * @param {string} containerId - The ID of the container element
	 * @param {string} eventsJsonUrl - URL to the events JSON file
	 */
	async init(containerId, eventsJsonUrl = '/events/events.json') {
		this.container = document.getElementById(containerId);
		if (!this.container) {
			console.error(`Container with ID "${containerId}" not found`);
			return;
		}

		try {
			// Fetch events data
			const response = await fetch(eventsJsonUrl);
			this.events = await response.json();

			// Process event dates
			this.events.forEach(event => {
				event.parsedDate = new Date(event.date);
				event.shortName = event.shortName || this.generateShortName(event.name);
			});

			// Create calendar controls
			this.createControls();

			// Render the initial calendar
			this.renderCalendar();
		} catch (error) {
			console.error('Error initializing calendar:', error);
			this.container.innerHTML = `<div class="error-message">Failed to load calendar. Please try again later.</div>`;
		}
	}

	/**
	 * Create month navigation controls
	 */
	createControls() {
		const controlsContainer = document.createElement('div');
		controlsContainer.className = 'calendar-controls';

		const prevButton = document.createElement('button');
		prevButton.innerHTML = '&laquo; Previous';
		prevButton.addEventListener('click', () => this.changeMonth(-1));

		const nextButton = document.createElement('button');
		nextButton.innerHTML = 'Next &raquo;';
		nextButton.addEventListener('click', () => this.changeMonth(1));

		const monthYearDisplay = document.createElement('h2');
		monthYearDisplay.className = 'current-month';
		monthYearDisplay.textContent = this.getMonthYearString();
		this.monthYearDisplay = monthYearDisplay;

		controlsContainer.appendChild(prevButton);
		controlsContainer.appendChild(monthYearDisplay);
		controlsContainer.appendChild(nextButton);

		this.container.appendChild(controlsContainer);
	}

	/**
	 * Change the displayed month
	 * @param {number} delta - The number of months to shift (-1 or +1)
	 */
	changeMonth(delta) {
		this.selectedMonth += delta;

		if (this.selectedMonth > 11) {
			this.selectedMonth = 0;
			this.selectedYear++;
		} else if (this.selectedMonth < 0) {
			this.selectedMonth = 11;
			this.selectedYear--;
		}

		this.monthYearDisplay.textContent = this.getMonthYearString();
		this.renderCalendar();
	}

	/**
	 * Get formatted month and year string
	 * @returns {string} Formatted month year (e.g., "May 2025")
	 */
	getMonthYearString() {
		const months = [
			'January', 'February', 'March', 'April',
			'May', 'June', 'July', 'August',
			'September', 'October', 'November', 'December'
		];
		return `${months[this.selectedMonth]} ${this.selectedYear}`;
	}

	/**
	 * Generate a short name for an event
	 * @param {string} name - Full event name
	 * @returns {string} Short name (max 20 chars)
	 */
	generateShortName(name) {
		if (name.length <= 20) return name;
		return name.substring(0, 17) + '...';
	}

	/**
	 * Render the calendar for the selected month/year
	 */
	renderCalendar() {
		// Remove existing calendar if present
		const existingCalendar = this.container.querySelector('.calendar-grid');
		if (existingCalendar) {
			this.container.removeChild(existingCalendar);
		}

		// Create calendar grid
		const calendarGrid = document.createElement('div');
		calendarGrid.className = 'calendar-grid';

		// Add day headers
		const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		dayNames.forEach(day => {
			const dayHeader = document.createElement('div');
			dayHeader.className = 'day-header';
			dayHeader.textContent = day;
			calendarGrid.appendChild(dayHeader);
		});

		// Get first day of month and number of days in month
		const firstDay = new Date(this.selectedYear, this.selectedMonth, 1);
		const lastDay = new Date(this.selectedYear, this.selectedMonth + 1, 0);
		const daysInMonth = lastDay.getDate();

		// Add empty cells for days before the first of the month
		let dayOfWeek = firstDay.getDay();
		for (let i = 0; i < dayOfWeek; i++) {
			const emptyCell = document.createElement('div');
			emptyCell.className = 'day empty';
			calendarGrid.appendChild(emptyCell);
		}

		// Add cells for each day in the month
		for (let day = 1; day <= daysInMonth; day++) {
			const cell = document.createElement('div');
			cell.className = 'day';

			const dateLabel = document.createElement('div');
			dateLabel.className = 'date-label';
			dateLabel.textContent = day;
			cell.appendChild(dateLabel);

			// Get events for this day
			const currentDate = new Date(this.selectedYear, this.selectedMonth, day);
			const dayEvents = this.getEventsForDate(currentDate);

			// Create event elements
			const eventsContainer = document.createElement('div');
			eventsContainer.className = 'day-events';

			dayEvents.forEach(event => {
				const eventElement = this.createEventElement(event);
				eventsContainer.appendChild(eventElement);
			});

			cell.appendChild(eventsContainer);
			calendarGrid.appendChild(cell);
		}

		this.container.appendChild(calendarGrid);

		// Create the modal for event details
		this.createEventModal();
	}

	/**
	 * Get events for a specific date
	 * @param {Date} date - The date to find events for
	 * @returns {Array} Array of events on the given date
	 */
	getEventsForDate(date) {
		const dateString = date.toISOString().split('T')[0];
		return this.events.filter(event => {
			const eventDate = event.parsedDate.toISOString().split('T')[0];
			return eventDate === dateString;
		});
	}

	/**
	 * Create an event element for the calendar
	 * @param {Object} event - Event data
	 * @returns {HTMLElement} Event element
	 */
	createEventElement(event) {
		const eventElement = document.createElement('div');
		eventElement.className = 'calendar-event';

		// Format the time
		let timeDisplay = event.startTime || '';
		if (timeDisplay.length > 5) {
			// Convert from ISO format if needed
			try {
				const timeDate = new Date(timeDisplay);
				if (!isNaN(timeDate.getTime())) {
					timeDisplay = timeDate.toLocaleTimeString([], {
						hour: '2-digit',
						minute: '2-digit'
					});
				}
			} catch (e) {
				console.warn('Error formatting time:', e);
			}
		}

		// Generate URL link for the event
		const eventDate = event.parsedDate.toISOString().split('T')[0];
		const eventLink = `/events/details/${encodeURIComponent(event.shortName)}/${encodeURIComponent(eventDate)}/${encodeURIComponent(event.id || 0)}`;

		// Set content
		eventElement.innerHTML = `
      <a href="${eventLink}" class="event-link">
        <span class="event-time">${timeDisplay}</span>
        <span class="event-name">${event.shortName}</span>
      </a>
    `;

		// Add event listeners
		eventElement.addEventListener('mouseover', (e) => {
			this.showEventModal(event, e);
		});

		eventElement.addEventListener('mouseout', () => {
			this.hideEventModal();
		});

		return eventElement;
	}

	/**
	 * Create modal element for event details
	 */
	createEventModal() {
		// Remove existing modal if present
		if (this.modalElement) {
			document.body.removeChild(this.modalElement);
		}

		// Create new modal
		this.modalElement = document.createElement('div');
		this.modalElement.className = 'event-modal';
		this.modalElement.style.display = 'none';
		this.modalElement.style.position = 'absolute';
		this.modalElement.style.zIndex = '1000';

		document.body.appendChild(this.modalElement);
	}

	/**
	 * Show event modal with details on hover
	 * @param {Object} event - Event data
	 * @param {MouseEvent} mouseEvent - Mouse event for positioning
	 */
	showEventModal(event, mouseEvent) {
		const rect = mouseEvent.target.getBoundingClientRect();
		const scrollTop = window.scrollY || document.documentElement.scrollTop;
		const scrollLeft = window.scrollX || document.documentElement.scrollLeft;

		// Format the date
		const formattedDate = event.parsedDate.toLocaleDateString(undefined, {
			weekday: 'long',
			year: 'numeric',
			month: 'long',
			day: 'numeric'
		});

		// Format the time
		let startTime = event.startTime || '';
		let endTime = event.endTime || '';

		// Create HTML content for modal
		this.modalElement.innerHTML = `
      <div class="modal-content">
        <h3>${event.name}</h3>
        <p><strong>Date:</strong> ${formattedDate}</p>
        <p><strong>Time:</strong> ${startTime} - ${endTime}</p>
        <p><strong>Location:</strong> ${event.location || 'TBD'}</p>
        <p><strong>Description:</strong> ${event.description || 'No description available'}</p>
        <div class="modal-footer">Click event to view details</div>
      </div>
    `;

		// Position the modal
		this.modalElement.style.left = `${rect.left + scrollLeft}px`;
		this.modalElement.style.top = `${rect.bottom + scrollTop + 10}px`;
		this.modalElement.style.display = 'block';
	}

	/**
	 * Hide the event modal
	 */
	hideEventModal() {
		if (this.modalElement) {
			this.modalElement.style.display = 'none';
		}
	}
}
