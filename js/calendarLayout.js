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

		// Set background image if available
		if (event.image) {
			eventElement.style.backgroundImage = `url(${event.image})`;
			eventElement.style.backgroundSize = 'cover';
			eventElement.style.backgroundPosition = 'center';
			eventElement.classList.add('has-image');
		} else {
			eventElement.classList.add('no-image');
		}

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

		const keepAlphanumeric = (str) => {
			return str.replace(/[^a-zA-Z0-9\-]/g, '-');
		}

		// Generate URL link for the event
		const eventDate = event.parsedDate.toISOString().split('T')[0];
		// Generate short-name for URL
		event.shortName = keepAlphanumeric(event.name).substr(0, 25).replace(/\s+/g, '-').toLowerCase();
		const eventLink = `/events/details/${encodeURIComponent(event.shortName)}/${encodeURIComponent(eventDate)}/${encodeURIComponent(event.id || 0)}`;

		// Set content - using a div instead of a link to handle clicks separately
		eventElement.innerHTML = `
			<div class="event-content">
				<span class="event-time">${timeDisplay}</span>
				<span class="event-name">${event.shortName}</span>
			</div>
		`;

		// Add click event instead of hover
		eventElement.addEventListener('click', (e) => {
			e.stopPropagation(); // Prevent bubbling
			this.showEventModal(event, e);
		});

		// Add a way to navigate to the event details
		eventElement.dataset.eventLink = eventLink;

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
		this.modalElement.style.position = 'fixed';
		this.modalElement.style.zIndex = '1000';
		this.modalElement.style.maxWidth = '300px';
		this.modalElement.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
		this.modalElement.style.background = 'white';
		this.modalElement.style.border = '1px solid #ddd';
		this.modalElement.style.borderRadius = '4px';
		this.modalElement.style.overflow = 'auto';

		document.body.appendChild(this.modalElement);

		// Add click event to close modal when clicking outside
		document.addEventListener('click', (e) => {
			if (this.modalElement.style.display === 'block' &&
				!this.modalElement.contains(e.target) &&
				!e.target.closest('.calendar-event')) {
				this.hideEventModal();
			}
		});
	}

	/**
	 * Show event modal with details on click
	 * @param {Object} event - Event data
	 * @param {MouseEvent} clickEvent - Click event for positioning
	 */
	showEventModal(event, clickEvent) {
		// Hide any existing modal first
		this.hideEventModal();

		const rect = clickEvent.currentTarget.getBoundingClientRect();

		// Get viewport dimensions
		const viewportWidth = window.innerWidth;
		const viewportHeight = window.innerHeight;

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

		// Get the event link from the dataset
		const eventLink = clickEvent.currentTarget.dataset.eventLink;

		// Set modal background image if available
		if (event.image) {
			this.modalElement.style.backgroundImage = `url(${event.image})`;
			this.modalElement.style.backgroundSize = 'cover';
			this.modalElement.style.backgroundPosition = 'center';
			this.modalElement.classList.add('has-image');
		} else {
			this.modalElement.style.backgroundImage = '';
			this.modalElement.classList.remove('has-image');
		}

		// Create HTML content for modal with close button and view details link
		this.modalElement.innerHTML = `
			<div class="modal-content">
				<div class="modal-header">
					<h3>${event.name}</h3>
					<button class="modal-close">&times;</button>
				</div>
				<p><strong>Date:</strong> ${formattedDate}</p>
				<p><strong>Time:</strong> ${startTime} - ${endTime}</p>
				<p><strong>Location:</strong> ${event.location || 'TBD'}</p>
				<p><strong>Description:</strong> ${event.description || 'No description available'}</p>
				<div class="modal-footer">
					<a href="${eventLink}" class="view-details-btn">View Full Details</a>
				</div>
			</div>
		`;

		// Add event listener to close button
		const closeButton = this.modalElement.querySelector('.modal-close');
		if (closeButton) {
			closeButton.addEventListener('click', (e) => {
				e.stopPropagation();
				this.hideEventModal();
			});
		}

		// Make the modal visible to calculate its dimensions
		this.modalElement.style.display = 'block';
		this.modalElement.style.visibility = 'hidden'; // Hide it temporarily while measuring

		// Get modal dimensions
		const modalWidth = this.modalElement.offsetWidth;
		const modalHeight = this.modalElement.offsetHeight;

		// Calculate position for the modal
		let top, left;

		// Check if there's enough space below the element
		if (rect.bottom + modalHeight + 10 <= viewportHeight) {
			// Position below
			top = rect.bottom + 10;
		} else if (rect.top - modalHeight - 10 >= 0) {
			// Position above if there's space
			top = rect.top - modalHeight - 10;
		} else {
			// Position at the middle of the viewport if no space above or below
			top = Math.max(10, (viewportHeight - modalHeight) / 2);
		}

		// Horizontal positioning
		left = Math.min(
			rect.left,
			viewportWidth - modalWidth - 10 // Ensure it doesn't go off right edge
		);
		left = Math.max(10, left); // Ensure it doesn't go off left edge

		// Apply positioning
		this.modalElement.style.top = `${top}px`;
		this.modalElement.style.left = `${left}px`;
		this.modalElement.style.maxHeight = `${viewportHeight * 0.8}px`; // Limit height to 80% of viewport
		this.modalElement.style.visibility = 'visible'; // Make visible again
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
