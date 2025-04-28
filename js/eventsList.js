import { Event } from './modules.js';

// EventsList class definition
export class EventsList {
	constructor() {
		this.events = [];
	}

	addEvent(event) {
		if (event instanceof Event) {
			this.events.push(event);
		} else {
			throw new Error("Only Event objects can be added.");
		}
	}

	getEvents() {
		return this.events;
	}

	clearEvents() {
		this.events = [];
	}

	renderEvents(containerSelector) {
		const container = document.querySelector(containerSelector);
		if (!container) return;

		container.innerHTML = ""; // Clear existing content

		// Parse and sort all events by date
		const sortedEvents = this.events
			.map(event => ({
				...event,
				parsedDate: new Date(event.date) // Parse the date string into a Date object
			}))
			.sort((a, b) => a.parsedDate - b.parsedDate); // Sort by the parsed date

		// Check if there are no events
		if (sortedEvents.length === 0) {
			const noEventsMessage = document.createElement("p");
			noEventsMessage.textContent = "No events found.";
			noEventsMessage.classList.add("no-events-message"); // Optional: Add a class for styling
			container.appendChild(noEventsMessage);
			return; // Exit the function early
		}

		// Iterate over sorted events and render them
		sortedEvents.forEach(async (event) => {
			const eventElement = document.createElement("div");
			eventElement.classList.add("event-tile");

			// Generate Google Calendar link if we have startTime and endTime
			let googleCalendarLink = '';
			if (event.date && event.startTime && event.endTime) {
				googleCalendarLink = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(event.name)}&dates=${this.formatDateForCalendar(event?.date, event.startTime, event.endTime)}&details=${encodeURIComponent(event.description)}&location=${encodeURIComponent(event.location)}`;
			}

			// Use the startTime and endTime as they are
			let startTimeFormatted, endTimeFormatted;
			if (this.isISODateFormat(event.startTime)) {
				startTimeFormatted = new Date(event.startTime).toLocaleTimeString();
			} else {
				startTimeFormatted = event.startTime;
			}
			if (this.isISODateFormat(event.endTime)) {
				endTimeFormatted = new Date(event.endTime).toLocaleTimeString();
			} else {
				endTimeFormatted = event.endTime;
			}

			if (!event.googleMapsLink) {
				// Generate Google Maps link from location
				event.googleMapsLink = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(event.location)}`;
			}

			// Check the image URL by loading it
			const imageData = await fetch(event.image);
			if (imageData.ok && imageData.headers.get("Content-Type").startsWith("image/")) {
				event.image = event.image; // Use the provided image URL if it exists
			} else {
				event.image = this.generatePlaceholderImage(); // Use a placeholder image if the URL is invalid
			}

			// Ensure guestList is an array of objects
			const guestList = Array.isArray(event.guestList) && event.guestList.every(guest => typeof guest === 'object')
				? event.guestList
				: [];

			eventElement.innerHTML = `
				<div class="event-tile-content">
					<div class="event-image">
						<img src="${event.image || this.generatePlaceholderImage()}" alt="${event.name}" />
					</div>
					<div class="event-details">
						<h2>${event.name}</h2>
						<p><strong>Date:</strong> ${event.parsedDate.toDateString()}</p>
						<p><strong>Time:</strong> ${startTimeFormatted} - ${endTimeFormatted}</p>
						<p><strong>Location:</strong> ${event.location}</p>
						<p><a href="${event.googleMapsLink}" target="_blank">View on Google Maps</a></p>
						<p>${event.description}</p>
						${guestList.length > 0 ? `<p><strong>Guests:</strong> ${guestList.map(guest => guest.name).join(", ")}</p>` : ""}
						<p><strong>Host:</strong> ${event.host || "N/A"}</p>
						<p><a href="${googleCalendarLink}" target="_blank" class="add-to-calendar">Add to Google Calendar</a></p>
					</div>
				</div>
			`;

			container.appendChild(eventElement);
		});
	}

	// Generate placeholder image from a calendar emoji
	generatePlaceholderImage() {
		const canvas = document.createElement("canvas");
		canvas.width = 200;
		canvas.height = 200;
		const ctx = canvas.getContext("2d");

		// Draw a simple calendar icon
		ctx.fillStyle = "#f0f0f0";
		ctx.fillRect(0, 0, canvas.width, canvas.height);
		ctx.fillStyle = "#000";
		ctx.font = "48px Arial";
		ctx.textAlign = "center";
		ctx.textBaseline = "middle";
		ctx.fillText("📅", canvas.width / 2, canvas.height / 2);

		return canvas.toDataURL();
	}

	// Helper method to format date and time for Google Calendar
	formatDateForCalendar(date, startTime, endTime) {

		const formatDate = (time) => {
			return new Date(date, time);
		};
		const dateRangeString = `${this.convertISOToYYYYMMDD(date)} + ' ' + ${startTime}/${this.convertISOToYYYYMMDD(date)} + ' ' + ${endTime}`;
		console.log(dateRangeString);
		return dateRangeString;
	}

	// Helper to convert ISO date to YYYY/MM/DD format
	convertISOToYYYYMMDD(dateString) {
		const date = new Date(dateString);
		return date.toISOString().split('T')[0].replace(/-/g, '/');
	}

	// Helper function to check if a date string is in ISO 8601 format
	isISODateFormat(dateString) {
		// Regular expression to match ISO 8601 date format
		const isoRegex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})?$/;
		return isoRegex.test(dateString);
	}
}
