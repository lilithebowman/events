import { Guest } from "./modules.js";

// Event class definition
export class Event {
	constructor(id, name, date, startTime, endTime, location, googleMapsLink, description, image, host, guestList = []) {
		this.id = id; // Unique identifier for the event
		this.name = name;
		this.date = date;
		this.startTime = this.to24HourTime(startTime);
		this.endTime = this.to24HourTime(endTime);
		this.location = location;
		this.googleMapsLink = googleMapsLink;
		this.description = description;
		this.image = image; // URL to an image representing the event
		this.host = host; // Host's name or object
		this.guestList = guestList; // Array of Guest objects
	}

	addGuest(guest) {
		if (guest instanceof Guest) {
			this.guestList.push(guest);
		} else {
			throw new Error("Only Guest objects can be added to the guest list.");
		}
	}

	// Convert time to 24-hour format
	to24HourTime(time) {
		const [hour, minute, meridian] = time.toLowerCase().replace('am', '').replace('pm', '').split(":").map(Number);
		// Pad the minutes with leading zero if necessary
		const paddedMinute = minute < 10 ? `0${minute}` : minute;

		const isPM = time.toLowerCase().includes("pm");
		if (isPM && hour < 12) {
			return `${hour + 12}:${paddedMinute}`;
		} else if (!isPM && hour === 12) {
			return `00:${paddedMinute}`;
		}
		return time;
	}
}
