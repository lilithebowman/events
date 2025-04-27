import { Guest } from "./modules.js";

// Event class definition
export class Event {
	constructor(name, date, startTime, endTime, location, googleMapsLink, description, guestList = []) {
		this.name = name;
		this.date = new Date(date);
		this.startTime = this.convertToUTC(date, startTime);
		this.endTime = this.convertToUTC(date, endTime);
		this.location = location;
		this.googleMapsLink = googleMapsLink;
		this.description = description;
		this.guestList = guestList; // Array of Guest objects
	}

	// Helper method to convert date and time to a UTC timestamp
	convertToUTC(date, time) {
		const [hour, minute] = time.match(/\d+/g).map(Number);
		const isPM = time.toLowerCase().includes("pm");
		const adjustedHour = isPM && hour !== 12 ? hour + 12 : !isPM && hour === 12 ? 0 : hour;

		const dateTime = new Date(date);
		dateTime.setUTCHours(adjustedHour, minute, 0, 0); // Set time in UTC
		return dateTime.toISOString(); // Store as ISO 8601 string in UTC
	}

	addGuest(guest) {
		if (guest instanceof Guest) {
			this.guestList.push(guest);
		} else {
			throw new Error("Only Guest objects can be added to the guest list.");
		}
	}
}
