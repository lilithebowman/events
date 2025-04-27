import { Guest } from "./modules.js";

// Event class definition
export class Event {
	constructor(name, date, startTime, endTime, location, googleMapsLink, description, image, host, guestList = []) {
		this.name = name;
		this.date = new Date(date);
		this.startTime = startTime;
		this.endTime = endTime;
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
}
