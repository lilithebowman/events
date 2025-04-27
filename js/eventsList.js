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

		this.events.forEach((event) => {
			const eventElement = document.createElement("div");
			eventElement.classList.add("event");

			// Generate Google Calendar link
			const googleCalendarLink = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(event.name)}&dates=${this.formatDateForCalendar(event.date, event.startTime, event.endTime)}&details=${encodeURIComponent(event.description)}&location=${encodeURIComponent(event.location)}`;

			eventElement.innerHTML = `
                <h2>${event.name}</h2>
                <p><strong>Date:</strong> ${event.date.toDateString()}</p>
                <p><strong>Time:</strong> ${event.startTime} - ${event.endTime}</p>
                <p><strong>Location:</strong> ${event.location}</p>
                <p><a href="${event.googleMapsLink}" target="_blank">View on Google Maps</a></p>
                <p>${event.description}</p>
                ${event.guestList.length > 0 ? `<p><strong>Guests:</strong> ${event.guestList.map(guest => guest.name).join(", ")}</p>` : ""}
                <p><a href="${googleCalendarLink}" target="_blank" class="add-to-calendar">Add to Google Calendar</a></p>
            `;

			container.appendChild(eventElement);
		});
	}

	// Helper method to format date and time for Google Calendar
	formatDateForCalendar(date, startTime, endTime) {
		const parseTime = (time) => {
			const [hour, minute] = time.match(/\d+/g).map(Number);
			const isPM = time.toLowerCase().includes("pm");
			const adjustedHour = isPM && hour !== 12 ? hour + 12 : !isPM && hour === 12 ? 0 : hour;
			return `${adjustedHour.toString().padStart(2, "0")}:${minute.toString().padStart(2, "0")}`;
		};

		const startDateTime = new Date(`${date.toISOString().split('T')[0]}T${parseTime(startTime)}:00`);
		const endDateTime = new Date(`${date.toISOString().split('T')[0]}T${parseTime(endTime)}:00`);

		const formatDate = (date) => date.toISOString().replace(/-|:|\.\d+/g, ""); // Format: YYYYMMDDTHHMMSSZ
		return `${formatDate(startDateTime)}/${formatDate(endDateTime)}`;
	}
}
