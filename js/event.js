import { Guest } from "./modules.js";

// Event class definition
export class Event {
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
