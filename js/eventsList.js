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

		// Check if there are no events
		if (this.events.length === 0) {
			const noEventsMessage = document.createElement("p");
			noEventsMessage.textContent = "No events found.";
			noEventsMessage.classList.add("no-events-message"); // Optional: Add a class for styling
			container.appendChild(noEventsMessage);
			return; // Exit the function early
		}

		// Iterate over events and render them
		this.events.forEach((event) => {
			const eventElement = document.createElement("div");
			eventElement.classList.add("event-tile");

			// Generate Google Calendar link
			const googleCalendarLink = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(event.name)}&dates=${this.formatDateForCalendar(event.startTime, event.endTime)}&details=${encodeURIComponent(event.description)}&location=${encodeURIComponent(event.location)}`;

			// Format start and end times in a standard English format
			const startTimeFormatted = new Date(event.startTime).toLocaleString("en-US", {
				dateStyle: "medium",
				timeStyle: "short",
			});
			const endTimeFormatted = new Date(event.endTime).toLocaleString("en-US", {
				dateStyle: "medium",
				timeStyle: "short",
			});

			if (!event.googleMapsLink) {
				// Generate Google Maps link from location
				event.googleMapsLink = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(event.location)}`;
			}

			// Ensure guestList is an array of objects
			const guestList = Array.isArray(event.guestList) && event.guestList.every(guest => typeof guest === 'object')
				? event.guestList
				: [];

			eventElement.innerHTML = `
				<div class="event-tile-content">
					<div class="event-image">
						<img src="${event.image || 'placeholder.jpg'}" alt="${event.name}" />
					</div>
					<div class="event-details">
						<h2>${event.name}</h2>
						<p><strong>Date:</strong> ${event.date.toDateString()}</p>
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

	// Helper method to format date and time for Google Calendar
	formatDateForCalendar(startTime, endTime) {
		const formatDate = (date) => {
			return new Date(this.date, date);
		};

		return `${formatDate(startTime)}/${formatDate(endTime)}`;
	}
}
