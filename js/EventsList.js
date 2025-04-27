// Guest class definition
class Guest {
	constructor(name, email) {
		this.name = name;
		this.email = email;
	}
}

// Event class definition
class Event {
	constructor(name, date, startTime, endTime, location, googleMapsLink, description, guestList = []) {
		this.name = name;
		this.date = new Date(date);
		this.startTime = startTime;
		this.endTime = endTime;
		this.location = location;
		this.googleMapsLink = googleMapsLink;
		this.description = description;
		this.guestList = guestList; // Array of Guest objects
	}

	addGuest(guest) {
		if (guest instanceof Guest) {
			this.guestList.push(guest);
		} else {
			throw new Error("Only Guest objects can be added to the guest list.");
		}
	}
}

// EventsList class definition
class EventsList {
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

			eventElement.innerHTML = `
                <h2>${event.name}</h2>
                <p><strong>Date:</strong> ${event.date.toDateString()}</p>
                <p><strong>Time:</strong> ${event.startTime} - ${event.endTime}</p>
                <p><strong>Location:</strong> ${event.location}</p>
                <p><a href="${event.googleMapsLink}" target="_blank">View on Google Maps</a></p>
                <p>${event.description}</p>
                ${event.guestList.length > 0 ? `<p><strong>Guests:</strong> ${event.guestList.map(guest => guest.name).join(", ")}</p>` : ""}
            `;

			container.appendChild(eventElement);
		});
	}
}
