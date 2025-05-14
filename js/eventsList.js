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

		// Iterate over sorted events and render them
		sortedEvents.forEach(async (event) => {
			if (event.date < new Date()) {
				return; // Skip past events
			}

			const eventElement = document.createElement("div");
			eventElement.classList.add("event-tile");

			event.startTime = event.startTime || "00:00";
			event.endTime = event.endTime || "23:59";
			event.date = event.date || new Date().toISOString().split('T')[0]; // Default to today if no date is provided
			event.guestList = event.guestList || [];
			event.host = event.host || "N/A";
			event.description = event.description || "No description available.";
			event.image = event.image || this.generatePlaceholderImage();
			event.parsedDate = new Date(event.date); // Parse the date string into a Date object
			// Generate Google Calendar link if we have startTime and endTime
			let googleCalendarLink = '';
			if (event.date && event.startTime && event.endTime) {
				googleCalendarLink = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(event.name)}&dates=${this.formatDateForCalendar(event?.date, event.startTime, event.endTime)}&details=${encodeURIComponent(event.description)}%0D%0A%0D%0A${window.location.href + event.image}&location=${encodeURIComponent(event.location)}&ctz=EST`;
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

			// Create a clickable link for the event using the .htaccess format
			const eventLink = `/events/event.html?name=${encodeURIComponent(event.name)}&date=${encodeURIComponent(event.date)}`;

			eventElement.innerHTML = `
					<div class="event-tile-content">
						<div class="event-image">
							<a href="${eventLink}"><img src="${event.image || this.generatePlaceholderImage()}" alt="${event.name}" /></a>
						</div>
						<div class="event-details">
							<h2>${event.name}</h2>
							<p><strong>Date:</strong> ${event.parsedDate.toDateString()}</p>
							<p><strong>Time:</strong> ${event.startTime} - ${event.endTime}</p>
							<p><strong>Location:</strong> <a href="${event.googleMapsLink}" target="_blank" class="google-maps-link">${event.location}</a></p>
							<p>${event.description}</p>
							${event.guestList?.length > 0 ? `<p><strong>Guests:</strong> ${event.guestList.map(guest => guest.name).join(", ")}</p>` : ""}
							<p><strong>Host:</strong> ${event.host || "N/A"}</p>
							<p><a href="${googleCalendarLink}" target="_blank" class="add-to-calendar">Add to Google Calendar</a></p>
						</div>
						<div class="event-actions">
							<a href="${eventLink}" class="view-event">View Event</a>
							<a href="${eventLink}edit" class="edit-event">Edit Event</a>
							<a href="${eventLink}delete" class="delete-event">Delete Event</a>
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
		// Google Calendar requires the date-time in the format 20211001T100000Z/20211001T110000Z
		// Actual output                                        20250504T140000Z/20250504T170000Z
		const formattedDate = new Date(date).toISOString().split('T')[0].replace(/-/g, '').slice(0, 8); // Format date as YYYYMMDD
		// console.log(formattedDate);

		const startTimeFormatted = formattedDate + 'T' + startTime.replace(/:/g, '') + '00'; // Assuming UTC+5:00
		const endTimeFormatted = formattedDate + 'T' + endTime.replace(/:/g, '') + '00'; // Assuming UTC+5:00

		const dateRangeString = `${startTimeFormatted}/${endTimeFormatted}`;
		// console.log(dateRangeString);
		return dateRangeString;
	}

	// Helper function to check if a date string is in ISO 8601 format
	isISODateFormat(dateString) {
		// Regular expression to match ISO 8601 date format
		const isoRegex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})?$/;
		return isoRegex.test(dateString);
	}
}
